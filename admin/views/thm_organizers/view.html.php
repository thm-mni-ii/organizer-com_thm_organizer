<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the Giessen Scheduler Monitors screen
 *
 * @package Joomla
 * @subpackage Giessen Scheduler
 */
class thm_organizersViewthm_organizers extends JView
{
    function display($tpl = null)
    {
        //Load pane behavior
        jimport('joomla.html.pane');

        //initialise variables
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        $pane = & JPane::getInstance('sliders');
        $user = & JFactory::getUser();
        $mainframe = JFactory::getApplication("administrator");
		$this->option = $mainframe->scope;

        //build toolbar
        JToolBarHelper::title( JText::_( 'THM Organizer:' )." ".JText::_( "COM_THM_ORGANIZER_MAIN_TITLE" ), 'home.png' );

        //assign vars to the template
        $this->assignRef('pane', $pane);
        $this->assignRef('user', $user);

	parent::display($tpl);
    }
}
