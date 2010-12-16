<?php
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the SermonSpeaker Component
 */
class SermonspeakerViewSpeakers extends JView
{
	function display($tpl = null)
	{
		// Applying CSS file
		JHTML::stylesheet('sermonspeaker.css', 'components/com_sermonspeaker/');

		$app		= JFactory::getApplication();
		$params		= $app->getParams();

		// Get some data from the models
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Set Meta
		$document =& JFactory::getDocument();
		$document->setTitle(JText::_('COM_SERMONSPEAKER_SPEAKERS_TITLE').' | '.$document->getTitle());
		$document->setMetaData("description",JText::_('COM_SERMONSPEAKER_SPEAKERS_TITLE'));
		$document->setMetaData("keywords",JText::_('COM_SERMONSPEAKER_SPEAKERS_TITLE'));

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

 		$cat = NULL;
		if($params->get('series_cat') || $params->get('speaker_cat') || $params->get('sermon_cat')){
			$cat	=& $this->get('Cat');
			$cat	= ': '.$cat;
		}

		// Support for Content Plugins
		$dispatcher	= &JDispatcher::getInstance();
		$item->params = clone($params);
		JPluginHelper::importPlugin('content');
		foreach($items as $item){
			// Trigger Event for `intro`
			$item->text	= &$item->intro;
			$dispatcher->trigger('onPrepareContent', array(&$item, &$item->params, 0));
			// Trigger Event for `bio`
			$item->text	= &$item->bio;
			$dispatcher->trigger('onPrepareContent', array(&$item, &$item->params, 0));
		}

		// push data into the template
		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('params',		$params);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('av',			$av);			// for Avatars
		$this->assignRef('cat',			$cat);			// for Category title

		parent::display($tpl);
	}
}