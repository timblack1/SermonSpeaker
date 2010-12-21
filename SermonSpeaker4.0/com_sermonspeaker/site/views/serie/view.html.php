<?php
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the SermonSpeaker Component
 */
class SermonspeakerViewSerie extends JView
{
	function display($tpl = null)
	{
		// Initialise variables.
		$app		= JFactory::getApplication();
		$params		= $app->getParams();

		// Get some data from the models
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$serie		= $this->get('Serie');
		$pagination	= $this->get('Pagination');

		// Set Meta
		$document =& JFactory::getDocument();
		$document->setTitle(JText::_('COM_SERMONSPEAKER_SERIE_TITLE').' | '.$document->getTitle());
		$document->setMetaData("description",JText::_('COM_SERMONSPEAKER_SERIE_TITLE'));
		$document->setMetaData("keywords",JText::_('COM_SERMONSPEAKER_SERIE_TITLE'));

		// add Breadcrumbs
		$breadcrumbs	= &$app->getPathWay();
		$breadcrumbs->addItem($serie->series_title);


		// Add swfobject-javascript for player
		$document->addScript(JURI::root()."components/com_sermonspeaker/media/player/swfobject.js");
		
		// Applying CSS file
		JHTML::stylesheet('sermonspeaker.css', 'components/com_sermonspeaker/');

		// Update Statistic
		if ($params->get('track_series')) {
			$model = $this->getModel();
			$model->hit();
		}
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Check whether category access level allows access.
/*		$user	= JFactory::getUser();
		$groups	= $user->authorisedLevels();
		if (!in_array($category->access, $groups)) {
			return JError::raiseError(403, JText::_("JERROR_ALERTNOAUTHOR"));
		}
*/

		// Support for Content Plugins
		$dispatcher	= &JDispatcher::getInstance();
		$item->params = clone($params);
		JPluginHelper::importPlugin('content');
		// Trigger Event for `series_description`
		$item->text	= &$serie->series_description;
		$dispatcher->trigger('onPrepareContent', array(&$item, &$item->params, 0));

		// Loop through each item and create links
		$direct_link = $params->get('list_direct_link');
		foreach($items as $row){
			switch ($direct_link){ // direct links to the file instead to the detailpage
				case '00':
					$row->link1 = JRoute::_(SermonspeakerHelperRoute::getSermonRoute($row->slug));
					$row->link2 = $row->link1;
					break;
				case '01':
					$row->link1 = JRoute::_(SermonspeakerHelperRoute::getSermonRoute($row->slug));
					$row->link2 = SermonspeakerHelperSermonspeaker::makelink($row->sermon_path);
					break;
				case '10':
					$row->link1 = SermonspeakerHelperSermonspeaker::makelink($row->sermon_path);
					$row->link2 = JRoute::_(SermonspeakerHelperRoute::getSermonRoute($row->slug));
					break;
				case '11':
					$row->link1 = SermonspeakerHelperSermonspeaker::makelink($row->sermon_path);
					$row->link2 = $row->link1;
					break;
			}
		}

        // push data into the template
		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('params',		$params);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('serie',		$serie);

		parent::display($tpl);
	}
}