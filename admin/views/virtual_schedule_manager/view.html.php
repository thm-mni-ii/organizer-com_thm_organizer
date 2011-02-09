<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the  Giessen Scheduler Monitors screen
 *
 * @package Joomla
 * @subpackage  Giessen Scheduler
 */
class  thm_organizersViewvirtualschedule extends JView {

	function display($tpl = null)
	{
		//Create Toolbar
        JToolBarHelper::title( JText::_( 'Giessen Scheduler - Virtual Schedule' ), 'generic.png' );
		JToolBarHelper::addNewX();
        JToolBarHelper::editListX();
        /**
         * ToDo: Virtuelle Stundenpläne sollen kopiert werden können.
         */
        //JToolBarHelper::customX( 'copy', 'copy.png', 'copy_f2.png', JText::_('Copy') );
        JToolBarHelper::deleteListX('Really?');
        JToolBarHelper::back();

        //Create Submenu
        JSubMenuHelper::addEntry( JText::_( 'Main Menu' ), 'index.php?option=com_thm_organizer&view=thm_organizers');
        JSubMenuHelper::addEntry( JText::_( 'Category Manager' ), 'index.php?option=com_thm_organizer&view=category_list');
        JSubMenuHelper::addEntry( JText::_( 'Monitor Manager' ), 'index.php?option=com_thm_organizer&view=monitor_list');
        JSubMenuHelper::addEntry( JText::_( 'Semester Manager' ), 'index.php?option=com_thm_organizer&view=semester_list');
        JSubMenuHelper::addEntry( JText::_( 'Scheduler Application Settings' ), 'index.php?option=com_thm_organizer&view=scheduler_application_settings');

		global $mainframe, $option;
		$db  		= & JFactory::getDBO();

		$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order",		'filter_order',		'#__giessen_scheduler_virtual_schedules.sid', '' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'#__giessen_scheduler_virtual_schedules.vid', '' );
		$filter_type		= $mainframe->getUserStateFromRequest( "$option.filter_type",		'filter_type', 		0,			'string' );
		$filter_logged		= $mainframe->getUserStateFromRequest( "$option.filter_logged",		'filter_logged', 	0,			'int' );
		$filter 			= $mainframe->getUserStateFromRequest( $option.'.filter', 'filter', '', 'int' );
		$search 			= $mainframe->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		$model =& $this->getModel();

		// Get data from the model
		$items =& $this->get('Data');
		$newitem = array();

		foreach($items as $k=>$v)
		{
			if(!isset($newitem[$v->id]))
			{
				$newitem[$v->id] = $v;
			}
			else
			{
				$newitem[$v->id]->eid = $newitem[$v->id]->eid.";".$v->eid;
			}
			if($newitem[$v->id]->type == "CL")
				$newitem[$v->id]->type = "class";
			if($newitem[$v->id]->type == "TR")
				$newitem[$v->id]->type = "teacher";
			if($newitem[$v->id]->type == "RM")
				$newitem[$v->id]->type = "room";
		}
		$items = array_values($newitem);

		$pagination = & $this->get('Pagination');

//		var_dump($pagination);

		// search filter
		$lists['search']= $search;

		//assign data to template
		$this->assignRef('lists'  , $lists);

		$this->assignRef( 'items', $items );
		$this->assignRef('pagination', $pagination);
		$this->assignRef('lists', $lists);
		if(isset($roleFilters_req))
			$this->assignRef('rolesFilters', $roleFilters_req);
		if(isset($groupFilters_req))
		$this->assignRef('groupFilters', $groupFilters_req);

        parent::display($tpl);
	}
}
