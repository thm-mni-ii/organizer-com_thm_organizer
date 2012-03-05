<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
class thm_organizersControllervirtual_schedule_edit extends JController
{
	function __construct() {
		parent::__construct();
	}

	function save()
	{
	    $model = $this->getModel('virtual_schedule_edit');

	    $vscheduler_id = JRequest::getVar('cid', null, 'post','STRING');
	    $vscheduler_name = JRequest::getVar('vscheduler_name', null, 'post','STRING');
	    $vscheduler_types = JRequest::getVar('vscheduler_types', null, 'post','STRING');

	    if($vscheduler_name == null)
	    {
	      $this->setRedirect( 'index.php?option=com_thm_organizer&view=virtual_schedule_edit', JText::_('Der Name darf nicht leer sein.'), 'error');
	      $session =& JFactory::getSession();
	      $session->set('oldPost', $_POST);
	      return;
	    }
	    $idCheckRet = $model->idExists("VS_".$vscheduler_name);
	    if($idCheckRet == true && $vscheduler_id == null)
	    {
	      $this->setRedirect( 'index.php?option=com_thm_organizer&view=virtual_schedule_edit', JText::_('Der angebene Name existiert bereits.'), 'error');
	      $session =& JFactory::getSession();
	      $session->set('oldPost', $_POST);
	      return;
	    }

	    $vscheduler_semid = JRequest::getVar('vscheduler_semid', null, 'post','STRING');
	    $vscheduler_resps = JRequest::getVar('vscheduler_resps', null, 'post','STRING');
	    $vscheduler_classesDepartments = JRequest::getVar('vscheduler_classesDepartments', null, 'post','STRING');
	    $vscheduler_teacherDepartments = JRequest::getVar('vscheduler_teacherDepartments', null, 'post','STRING');
	    $vscheduler_roomDepartments = JRequest::getVar('vscheduler_roomDepartments', null, 'post','STRING');

	    $vscheduler_classes = JRequest::getVar('vscheduler_classes', null, 'post','ARRAY');
	    $vscheduler_rooms = JRequest::getVar('vscheduler_rooms', null, 'post','ARRAY');
	    $vscheduler_teachers = JRequest::getVar('vscheduler_teachers', null, 'post','ARRAY');

	    if(!isset($vscheduler_name) ||
	       !isset($vscheduler_types) ||
	       !isset($vscheduler_semid) ||
	       !isset($vscheduler_resps) ||
	       !isset($vscheduler_classesDepartments) ||
	       !isset($vscheduler_teacherDepartments) ||
	       !isset($vscheduler_roomDepartments) ||
	       (!isset($vscheduler_classes) && !isset($vscheduler_rooms) && !isset($vscheduler_teachers)))
		{
			$msg = "Folgende Felder haben ungültige Werte:<br/>";
			if(!isset($vscheduler_name))
				$msg .= "vscheduler_name<br/>";
			if(!isset($vscheduler_types))
				$msg .= "vscheduler_types<br/>";
			if(!isset($vscheduler_semid))
				$msg .= "vscheduler_semid<br/>";
			if(!isset($vscheduler_resps))
				$msg .= "vscheduler_resps<br/>";
			if(!isset($vscheduler_classesDepartments))
				$msg .= "vscheduler_classesDepartments<br/>";
			if(!isset($vscheduler_teacherDepartments))
				$msg .= "vscheduler_teacherDepartments<br/>";
			if(!isset($vscheduler_roomDepartments))
				$msg .= "vscheduler_roomDepartments<br/>";
			if(!isset($vscheduler_classes) && $vscheduler_types == "class")
				$msg .= "vscheduler_classes<br/>";
			if(!isset($vscheduler_rooms) && $vscheduler_types == "room")
				$msg .= "vscheduler_rooms<br/>";
			if(!isset($vscheduler_teachers) && $vscheduler_types == "teacher")
				$msg .= "vscheduler_teachers<br/>";

			$this->setRedirect( 'index.php?option=com_thm_organizer&view=virtual_schedule_edit', JText::_($msg), 'error');
	      	$session =& JFactory::getSession();
	      	$session->set('oldPost', $_POST);
	      	return;
	    }
	    else
	    {
	    	//Alles Felder haben gÃ¼ltige Werte
	    	$torf = false;

	    	if($vscheduler_types == "room")
			{
				$vscheduler_Departments = $vscheduler_roomDepartments;
				$vscheduler_elements = $vscheduler_rooms;
			}
			if($vscheduler_types == "class")
			{
				$vscheduler_Departments = $vscheduler_classesDepartments;
				$vscheduler_elements = $vscheduler_classes;
			}
			if($vscheduler_types == "teacher")
			{
				$vscheduler_Departments = $vscheduler_teacherDepartments;
				$vscheduler_elements = $vscheduler_teachers;
			}

			$torf = $model->saveVScheduler($vscheduler_id,
										   $vscheduler_name,
										   $vscheduler_types,
										   $vscheduler_semid,
										   $vscheduler_resps,
										   $vscheduler_Departments,
										   $vscheduler_elements);

			if($torf === "1")
			{
				if($vscheduler_id == null)
					$this->setRedirect( 'index.php?option=com_thm_organizer&view=virtual_schedule_manager', JText::_('Virtuellen Stundenplan '.$vscheduler_name.' erfolgreich angelegt.'));
				else
					$this->setRedirect( 'index.php?option=com_thm_organizer&view=virtual_schedule_manager', JText::_('Virtuellen Stundenplan '.$vscheduler_id.' erfolgreich bearbeitet.'));
				return;
			}
			else
			{
				$this->setRedirect( 'index.php?option=com_thm_organizer&view=virtual_schedule_edit', JText::_("Error: ".$torf), 'error');
		      	$session =& JFactory::getSession();
		      	$session->set('oldPost', $_POST);
		      	return;
			}
	    }
	}

	function cancel()
	{
	    $this->setRedirect( 'index.php?option=com_thm_organizer&view=virtual_schedule_manager');
	}

}
?>
