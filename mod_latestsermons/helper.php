<?php
// no direct access
defined('_JEXEC') or die;

abstract class modLatestsermonsHelper
{
	public static function getList($params)
	{
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('a.title, a.id, a.sermon_date, a.audiofile, a.videofile, a.sermon_time, a.picture, a.notes, a.hits');
		$query->select('b.title as speaker_title, b.pic, b.state AS speaker_state, c.title AS series_title, c.state AS series_state');
		$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
		$query->select('CASE WHEN CHAR_LENGTH(b.alias) THEN CONCAT_WS(\':\', b.id, b.alias) ELSE b.id END as speaker_slug');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as series_slug');
		$query->from('#__sermon_sermons AS a');
		$query->join('LEFT', '#__sermon_speakers AS b ON b.id = a.speaker_id');
		$query->join('LEFT', '#__sermon_series AS c ON c.id = a.series_id');
		$query->where('a.state = 1');
		if ($params->get('mode', 0))
		{
			$query->order('a.hits DESC, (sermon_number+0) DESC');
		}
		else
		{
			$query->order('sermon_date DESC, (sermon_number+0) DESC');
		}
		// Category
		if ($cat = (int)$params->get('cat', 0))
		{
			switch ($params->get('cat_type', 'sermons'))
			{
				case 'sermons':
					$type	= 'a';
					break;
				case 'speakers':
					$type	= 'b';
					break;
				case 'series':
					$type	= 'c';
					break;
			}
			// Subcategories
			if ($levels = (int) $params->get('show_subcategory_content', 0))
			{
				// Create a subquery for the subcategory list
				$subQuery = $db->getQuery(true);
				$subQuery->select('sub.id');
				$subQuery->from('#__categories as sub');
				$subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
				$subQuery->where('this.id = '.$cat);
				if ($levels > 0) {
					$subQuery->where('sub.level <= this.level + '.$levels);
				}
				// Add the subquery to the main query
				$query->where('('.$type.'.catid = '.$cat
					.' OR '.$type.'.catid IN ('.$subQuery->__toString().'))');
			}
			else
			{
				$query->where($type.'.catid = '.$cat);
			}
		}
		$query->select('cat.title AS category_title');
		$query->select('CASE WHEN CHAR_LENGTH(cat.alias) THEN CONCAT_WS(\':\', cat.id, cat.alias) ELSE cat.id END as catslug');
		$query->join('LEFT', '#__categories AS cat ON cat.id = a.catid');
		$query->where('(a.catid = 0 OR (cat.access IN ('.$groups.') AND cat.published = 1))');
		// Others
		if ($speaker = (int)$params->get('speaker', 0))
		{
			$query->where('a.speaker_id = '.$speaker);
		}
		if ($series = (int)$params->get('series', 0))
		{
			$query->where('a.series_id = '.$series);
		}
		if ($idlist = $params->get('idlist', 0))
		{
			$id_array = explode(',', $idlist);
			JArrayHelper::toInteger($id_array);
			$query->where('a.id IN ('.implode(',', $id_array).')');
		}
		// SmartFilter
		if ($params->get('smartfilter', 0))
		{
			$view = JRequest::getCmd('view');
			if ($view == 'speaker')
			{
				$query->where('a.speaker_id = '.JRequest::getInt('id'));
			}
			elseif ($view == 'serie')
			{
				$query->where('a.series_id = '.JRequest::getInt('id'));
			}
		}
		// Filetype filter
		if ($filetype = $params->get('filetype', 0))
		{
			if ($filetype == 2)
			{
				$query->where('a.videofile != ""');
			}
			else
			{
				$query->where('a.audiofile != ""');
			}
		}

		$db->setQuery($query, 0, (int)$params->get('ls_count', 3));
		$items	= $db->loadObjectList();

		return $items;
	}
}