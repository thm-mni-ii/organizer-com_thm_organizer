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
class  thm_organizersViewvirtual_schedule_manager extends JView {

    public function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        
        $title = JText::_('COM_THM_ORGANIZER').': '.JText::_('JCATEGORIES');        
        JToolBarHelper::title( $title, 'mni' );
        JToolBarHelper::title( JText::_( 'THM - Organizer: Virtual Schedule Manager' ), 'generic.png' );
        JToolBarHelper::addNewX('virtual_schedule_manager.add');
        JToolBarHelper::editListX('virtual_schedule_manager.edit');
        /**
         * ToDo: Virtuelle Stundenpl�ne sollen kopiert werden k�nnen.
         */
        //JToolBarHelper::customX( 'copy', 'copy.png', 'copy_f2.png', JText::_('Copy') );
		JToolBarHelper::deleteListX('Really?','virtual_schedule_manager.remove' );

		$mainframe = JFactory::getApplication("administrator");
		$option = $mainframe->scope;
		$view = JRequest::getString('view');
		$db  		= & JFactory::getDBO();

		$filter_order		= $mainframe->getUserStateFromRequest( "$option.$view.filter_order",		'filter_order',		'#__thm_organizer_virtual_schedules.sid, #__thm_organizer_virtual_schedules.vid', '' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.$view.filter_order_Dir",	'filter_order_Dir',	'', '' );
		$filter_type		= $mainframe->getUserStateFromRequest( "$option.$view.filter_type",		'filter_type', 		0,			'string' );
		$filter_logged		= $mainframe->getUserStateFromRequest( "$option.$view.filter_logged",		'filter_logged', 	0,			'int' );
		$filter 			= $mainframe->getUserStateFromRequest( $option.$view.'.filter', 'filter', '', 'int' );
		$search 			= $mainframe->getUserStateFromRequest( $option.$view.'.search', 'search', '', 'string' );
		$search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		$model =& $this->getModel();

		// Get data from the model
		$items = & $this->get('Data');
		$newitem = array();

		$elements = $model->getElements();

		foreach($elements as $k=>$v)
		{
			if(!isset($newitem[$v->vid]))
			{
				$newitem[$v->vid] = $v;
			}
			else
			{
				$newitem[$v->vid]->eid = $newitem[$v->vid]->eid.";".$v->eid;
			}
		}
		$elements = array_values($newitem);

		foreach($items as $ik=>$iv)
		{
			foreach($elements as $ek=>$ev)
			{
				if($iv->id == $ev->vid && $iv->sid == $ev->sid)
				{
					if(isset($iv->eid))
						$iv->eid = "";
					$iv->eid = $ev->eid;
				}
			}
		}

		$pagination = & $this->get('Pagination');

		// search filter
		$lists['search']= $search;

		//assign data to template
		$this->assignRef('lists', $lists);

		$this->assignRef('items', $items );
		$this->assignRef('pagination', $pagination);
		$this->assignRef('lists', $lists);
		if(isset($roleFilters_req))
			$this->assignRef('rolesFilters', $roleFilters_req);
		if(isset($groupFilters_req))
		$this->assignRef('groupFilters', $groupFilters_req);

        parent::display($tpl);
	}
}
