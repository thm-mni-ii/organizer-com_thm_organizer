<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.controller
 * @name        THM_OrganizerControllervirtual_schedule
 * @description perform tasks that affects virtual schedules
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');

/**
 * Class performs access checks, redirects and model function calls for data persistence
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerControllerVirtual_Schedule extends JControllerForm
{
    /**
     * Performs access checks and redirects to the virtual schedule edit view
     * 
     * @return void 
     */
    public function add()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('view', 'virtual_schedule_edit');
        JRequest::setVar('id', '0');
        parent::display();
    }

	/**
	 * Performs access checks and redirects to the virtual schedule edit view
	 *
	 * @param   Object  $key     Key		   (default: null)
	 * @param   Object  $urlVar  Url variable  (default: null)
	 *
	 * @return void
	 */
	public function edit($key = null, $urlVar = null)
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		JRequest::setVar('view', 'virtual_schedule_edit');
		parent::display();
	}
	/**
	 * Performs access checks, makes call to the models's save function, and
	 * redirects to the virtual schedule manager view
	 *
	 * @param   Object  $key     Key		   (default: null)
	 * @param   Object  $urlVar  Url variable  (default: null)
	 *
	 * @todo clean this up
	 * 
	 * @return void
	 */
	public function save($key = null, $urlVar = null)
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		$model = $this->getModel('virtual_schedule_edit');
		 
		$data = JRequest::getVar('jform', null, null, null);

		$vsID = $data["id"];
		$vid = $data["vid"];
		$name = $data["name"];
		$type = $data["type"];

		if ($name == null)
		{
			$url = 'index.php?option=com_thm_organizer&view=virtual_schedule_edit';
			$this->setRedirect($url, JText::_('COM_THM_ORGANIZER_VSE_NAME_MISSING'), 'error');
			$session = JFactory::getSession();
			$session->set('oldPost', $_POST);
			return;
		}
		$semid = $data["semester"];
		$resps = $data["responsible"];
		$classesDepartments = $data["ClassDepartment"];
		$teacherDepartments = $data["TeacherDepartment"];
		$roomDepartments = $data["RoomDepartment"];
		 
		$classes = null;
		$rooms = null;
		$teachers = null;
		 
		if ($type == "room")
		{
			$rooms = $data["Rooms"];
		}
		if ($type == "class")
		{
			$classes = $data["Classes"];
		}
		if ($type == "teacher")
		{
			$teachers = $data["Teachers"];
		}

		if (!isset($name) OR !isset($type) OR !isset($semid) OR !isset($resps)
		 OR !isset($classesDepartments) OR !isset($teacherDepartments)
		 OR !isset($roomDepartments) OR (!isset($classes) && !isset($rooms) && !isset($teachers)))
		{
			$msg = "Folgende Felder haben ungültige Werte:<br/>";
			if (!isset($name))
			{
				$msg .= "vscheduler_name<br/>";
			}
			if (!isset($type))
			{
				$msg .= "vscheduler_types<br/>";
			}
			if (!isset($semid))
			{
				$msg .= "vscheduler_semid<br/>";
			}
			if (!isset($resps))
			{
				$msg .= "vscheduler_resps<br/>";
			}
			if (!isset($classesDepartments))
			{
				$msg .= "vscheduler_classesDepartments<br/>";
			}
			if (!isset($teacherDepartments))
			{
				$msg .= "vscheduler_teacherDepartments<br/>";
			}
			if (!isset($roomDepartments))
			{
				$msg .= "vscheduler_roomDepartments<br/>";
			}
			if (!isset($classes) && $type == "class")
			{
				$msg .= "vscheduler_classes<br/>";
			}
			if (!isset($rooms) && $type == "room")
			{
				$msg .= "vscheduler_rooms<br/>";
			}
			if (!isset($teachers) && $type == "teacher")
			{
				$msg .= "vscheduler_teachers<br/>";
			}

			$this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_edit', JText::_($msg), 'error');
			$session =& JFactory::getSession();
			$session->set('oldPost', $_POST);
			return;
		}
		else
		{
			if ($type == "room")
			{
				$Departments = $roomDepartments;
				$elements = $rooms;
			}
			if ($type == "class")
			{
				$Departments = $classesDepartments;
				$elements = $classes;
			}
			if ($type == "teacher")
			{
				$Departments = $teacherDepartments;
				$elements = $teachers;
			}

			$torf = $model->saveVirtualSchedule(
					$vsID,
					$vid,
					$name,
					$type,
					$semid,
					$resps,
					$Departments,
					$elements
					);

			if ($torf === true)
			{
				$url = 'index.php?option=com_thm_organizer&view=virtual_schedule_manager';
				if ($vsID == null)
				{
					$msg = JText::sprintf('COM_THM_ORGANIZER_VSE_CREATE_SUCCESS', $name);
				}
				else
				{
					$msg = JText::sprintf('COM_THM_ORGANIZER_VSE_EDIT_SUCCESS', $name);
				}
				$this->setRedirect($url, $msg);
				return;
			}
			else
			{
				$url = 'index.php?option=com_thm_organizer&view=virtual_schedule_edit';
				$this->setRedirect($url, JText::_('COM_THM_ORGANIZER_VSE_EDIT_FAIL'), 'error');
				$session = JFactory::getSession();
				$session->set('oldPost', $_POST);
				return;
			}
		}
	}

	/**
	 * Performs access checks, makes call to the models's delete function, and
	 * redirects to the virtual schedule manager view
	 * 
	 * @todo clean this up
	 *
	 * @return void
	 */
	public function delete()
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		$cid = JRequest::getVar('cid',   array(), 'post', 'array');
		$cids = "'" . implode("', '", $cid) . "'";

		$dbo = JFactory::getDBO();
		$scheduleQuery = $dbo->getQuery(true);
		$scheduleQuery->delete('#__thm_organizer_virtual_schedules');
		$scheduleQuery->where("vid IN ( $cids )");
		$dbo->setQuery((string) $scheduleQuery);
		$dbo->query();

		if ($dbo->getErrorNum())
		{
			$msg = JText::_('COM_THM_ORGANIZER_ERROR_DELETING');
		}
		else
		{
			$elementQuery = $dbo->getQuery(true);
			$elementQuery->delete('#__thm_organizer_virtual_schedules_elements');
			$elementQuery->where("vid IN ( $cids )");
			$dbo->setQuery((string) $elementQuery);
			$dbo->query();
		}

		if (count($cid) > 1)
		{
			$msg = JText::sprintf('COM_THM_ORGANIZER_VSE_DELETE_SUCCESSES', implode(', ', $cid));
		}
		else
		{
			$msg = JText::sprintf('COM_THM_ORGANIZER_VSE_DELETE_SUCCESS', implode(', ', $cid));
		}

		$this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_manager', $msg);

	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  void
	 */
	public function cancel($key = null)
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		$this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_manager');
	}
}
