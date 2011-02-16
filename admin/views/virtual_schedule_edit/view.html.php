<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the  Giessen Scheduler Monitors screen
 *
 * @package Joomla
 * @subpackage  Giessen Scheduler
 */
class thm_organizersViewvirtual_schedule_edit extends JView {

	function display($tpl = null)
	{
		JToolBarHelper::save('virtual_schedule_edit.save');
		JToolBarHelper::cancel('virtual_schedule_edit.cancel');

		$model = $this->getModel();
		$responsibles = $model->getResponsibles();

		if(!is_array($responsibles))
			$responsibles = array();
		$types = $model->getTypes();
		if(!is_array($types))
			$types = array();
		$classes = $model->getClasses();
		if(!is_array($classes))
			$classes = array();
		$rooms = $model->getRooms();
		if(!is_array($rooms))
			$rooms = array();
		$teachers = $model->getTeachers();
		if(!is_array($teachers))
			$teachers = array();
		$semesters = $model->getSemesters();
		if(!is_array($semesters))
			$semesters = array();

		$roomDepartments = $model->getRoomDepartments();
		$teacherDepartments = $model->getDepartments("teachers");
		$classesDepartments = $model->getDepartments("classes");

		$tempDepartments = null;
		$tempDepartments[0]["id"] = "none";
		$tempDepartments[0]["name"] = "keins";
		if(is_array($roomDepartments))
		foreach($roomDepartments as $v)
			$tempDepartments[] = $v;

		$roomDepartments = $tempDepartments;


		$tempDepartments = null;
		$tempDepartments[0]["id"] = "none";
		$tempDepartments[0]["name"] = "keins";

		if(is_array($teacherDepartments))
		foreach($teacherDepartments as $v)
			$tempDepartments[] = $v;

		$teacherDepartments = $tempDepartments;


		$tempDepartments = null;
		$tempDepartments[0]["id"] = "none";
		$tempDepartments[0]["name"] = "keins";

		if(is_array($classesDepartments))
		foreach($classesDepartments as $v)
			$tempDepartments[] = $v;

		$classesDepartments = $tempDepartments;

		$session =& JFactory::getSession();
		$oldPost = $session->get('oldPost');
		$session->clear('oldPost');

		if($oldPost != null)
			$_POST = $oldPost;

		$cid = JRequest::getVar('cid', null, 'post','ARRAY');
		$task = JRequest::getVar('task', null, 'post','STRING');
		$cid = $cid[0];

		if($cid == null || $task == "add")
		{
			$vscheduler_name = JRequest::getVar('vscheduler_name', '', 'post','STRING');
			$vscheduler_types = JRequest::getVar('vscheduler_types', null, 'post','STRING');
			$vscheduler_semid = JRequest::getVar('vscheduler_semid', null, 'post','STRING');
			$vscheduler_resps = JRequest::getVar('vscheduler_resps', null, 'post','STRING');
			$vscheduler_classesDepartments = JRequest::getVar('vscheduler_classesDepartments', null, 'post','STRING');
			$vscheduler_teacherDepartments = JRequest::getVar('vscheduler_teacherDepartments', null, 'post','STRING');
			$vscheduler_roomDepartments = JRequest::getVar('vscheduler_roomDepartments', null, 'post','STRING');

			$vscheduler_classes = JRequest::getVar('vscheduler_classes', null, 'post','ARRAY');
			$vscheduler_rooms = JRequest::getVar('vscheduler_rooms', null, 'post','ARRAY');
			$vscheduler_teachers = JRequest::getVar('vscheduler_teachers', null, 'post','ARRAY');
		}
		else
		{
			$data = $model->getData($cid);
			foreach($data as $k=>$v)
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
			$data = array_values($newitem);
			foreach($data as $k=>$v)
			{
				$v->eid = explode(';', $v->eid);
				$vscheduler_name = $v->vname;
				$vscheduler_types = $v->vtype;
				$vscheduler_semid = $v->sid;
				$vscheduler_resps = $v->vresponsible;
				$vscheduler_roomDepartments = null;
				$vscheduler_teacherDepartments = null;
				$vscheduler_classesDepartments = null;
				$vscheduler_classes = null;
				$vscheduler_rooms = null;
				$vscheduler_teachers = null;
				$vscheduler_roomTypes = null;
				$vscheduler_classTypes = null;
				if($vscheduler_types == "room")
				{
					$vscheduler_roomDepartments = $v->department;
					$vscheduler_rooms = $v->eid;
				}
				if($vscheduler_types == "teacher")
				{
					$vscheduler_teacherDepartments = $v->department;
					$vscheduler_teachers = $v->eid;
				}
				if($vscheduler_types == "class")
				{
					$vscheduler_classesDepartments = $v->department;
					$vscheduler_classes = $v->eid;
				}
			}
			$this->assignRef('cid', $cid);
		}

		$this->assignRef('name', $vscheduler_name);
		$this->assignRef('classes', JHTML::_('select.genericlist', $classes, 'vscheduler_classes[]','size="10" class="inputbox" multiple="multiple"', 'id', 'name', $vscheduler_classes));
		$this->assignRef('rooms', JHTML::_('select.genericlist', $rooms, 'vscheduler_rooms[]','size="10" class="inputbox" multiple="multiple"', 'id', 'name', $vscheduler_rooms));
		$this->assignRef('teachers', JHTML::_('select.genericlist', $teachers, 'vscheduler_teachers[]','size="10" class="inputbox" multiple="multiple"', 'id', 'name', $vscheduler_teachers));
		$this->assignRef('resps', JHTML::_('select.genericlist', $responsibles, 'vscheduler_resps','size="1" class="inputbox"', 'id', 'name', $vscheduler_resps));
		$this->assignRef('types', JHTML::_('select.genericlist', $types, 'vscheduler_types','size="1" class="inputbox" onChange="setRessource();"', 'id', 'name', $vscheduler_types));
		$this->assignRef('semesters', JHTML::_('select.genericlist', $semesters, 'vscheduler_semid','size="1" class="inputbox"', 'id', 'name', $vscheduler_semid));
		$this->assignRef('roomDepartments', JHTML::_('select.genericlist', $roomDepartments, 'vscheduler_roomDepartments','size="1" class="inputbox"', 'id', 'name', $vscheduler_roomDepartments));
		$this->assignRef('teacherDepartments', JHTML::_('select.genericlist', $teacherDepartments, 'vscheduler_teacherDepartments','size="1" class="inputbox"', 'id', 'name', $vscheduler_teacherDepartments));
		$this->assignRef('classesDepartments', JHTML::_('select.genericlist', $classesDepartments, 'vscheduler_classesDepartments','size="1" class="inputbox"', 'id', 'name', $vscheduler_classesDepartments));

        parent::display($tpl);
	}

}
