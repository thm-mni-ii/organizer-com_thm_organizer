<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

/**
 * View class for the  Giessen Scheduler Monitors screen
 *
 * @package Joomla
 * @subpackage  Giessen Scheduler
 */
class  thm_organizersViewresource_manager extends JView {

	function display($tpl = null)
	{
		//Create Toolbar
        JToolBarHelper::title( JText::_( 'THM - Organizer: Resource Manager' ), 'generic.png' );
		thm_organizerHelper::addSubmenu('resource_manager');

		$mainframe = JFactory::getApplication("administrator");
		$this->option = $mainframe->scope;

		$document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        parent::display($tpl);
	}
}
