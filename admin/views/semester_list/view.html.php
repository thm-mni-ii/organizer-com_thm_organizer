<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the Giessen Scheduler Monitors screen
 *
 * @package Joomla
 * @subpackage Giessen Scheduler
 */
class thm_organizersViewSemester_List extends JView {

    function display($tpl = null)
    {
        JToolBarHelper::title( JText::_( 'Semester Manager' ), 'generic.png' );
        JToolBarHelper::addNewX();
        JToolBarHelper::editListX();
        JToolBarHelper::deleteList();
        JToolBarHelper::back();

        //Create Submenu
	$model = $this->getModel();
        foreach($model->data->links as $link)
        {
            JSubMenuHelper::addEntry( JText::_( $link['name'] ), 'index.php?'.$link['link']);
        }

        // Get data from the model
        $items =& $model->data->data;

        $this->assignRef( 'items', $items );

        parent::display($tpl);
    }
}
	