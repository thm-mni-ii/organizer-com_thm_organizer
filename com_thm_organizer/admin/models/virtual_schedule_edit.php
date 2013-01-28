<?php
/**
 * @version     v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.model
 * @name        THM_OrganizerModelVirtual_Schedule_Edit
 * @description Class to create and edit a virtual schedule
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizerModelVirtual_Schedule_Edit for component com_thm_organizer
 *
 * Class provides methods to create and edit a virtual schedule
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.model
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class THM_OrganizerModelVirtual_Schedule_Edit extends JModelAdmin
{
	/**
	 * Constructor that calls the parent constructor
	 *
	 * @since   v0.0.1
	 *
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Method to retrieves the jform object for this view
	 *
	 * @param   Array  $data  	  An array with data 		 (Default: Array)
	 * @param   Array  $loadData  An array with data to load (Default: Array)
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_thm_organizer.virtual_schedule_edit', 'virtual_schedule_edit',
				array('control' => 'jform', 'load_data' => $loadData)
		);
		if (empty($form))
		{
			return false;
		}
		else
		{
			return $form;
		}
		 
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed  The data for the form.
	 *
	 * @since	v0.0.1
	 */
	protected function loadFormData()
	{
		if (empty($data))
		{
			$data = $this->getItem();
		}
		else
		{
				
		}
		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	Integer	 $pk  The id of the primary key.  (Default: null)
	 *
	 * @return	mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$cid = $this->getID();

		$virtualSchedule = ($cid) ? parent::getItem($cid) : $this->getTable();

		if ($virtualSchedule->type === "class")
		{
			$virtualSchedule->ClassDepartment = $virtualSchedule->department;
			$virtualSchedule->Classes = $this->getElements($virtualSchedule->id);
		}
		elseif ($virtualSchedule->type === "room")
		{
			$virtualSchedule->RoomDepartment = $virtualSchedule->department;
			$virtualSchedule->Rooms = $this->getElements($virtualSchedule->id);
		}
		else
		{
			$virtualSchedule->TeacherDepartment = $virtualSchedule->department;
			$virtualSchedule->Teachers = $this->getElements($virtualSchedule->id);
		}

		return $virtualSchedule;
	}

	/**
	 * Method to get elements
	 *
	 * @param	Integer	 $id  The id of the virtual schedule.
	 *
	 * @return	Array  An Array with elements
	 */
	private function getElements($id)
	{
		$query = 'SELECT eid FROM #__thm_organizer_virtual_schedules_elements WHERE #__thm_organizer_virtual_schedules_elements.vid = ' . $id;
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$return = array();

		if (!empty($rows))
		{
			foreach ($rows AS $k => $v)
			{
				$return[] = $v->eid;
			}
		}

		return $return;
	}

	/**
	 * Method to get an id
	 *
	 * @return	Integer  Returns the virtual schedule id if set or 0
	 */
	public function getID()
	{
		$cids = JRequest::getVar('cid', null, 'post', 'ARRAY');
		if (isset($cids))
		{
			if (!empty($cids))
			{
				$cid = $cids[0];
			}
		}

		if (!isset($cid))
		{
			$cid = JRequest::getVar('cid', null, 'get', 'ARRAY');
			$cid = base64_decode($cid[0]);
		}

		if (isset($cid))
		{
			return $cid;
		}
		return 0;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	String  $type	 The table type to instantiate  		(Default: 'virtual_schedules)
	 * @param	String	$prefix  A prefix for the table class name. (Default: 'thm_organizerTable')
	 * @param	Array	$config  Configuration array for model. 	(Default: Array)
	 *
	 * @return	JTable	A database object
	 *
	 * @since	v0.0.1
	 */
	public function getTable($type = 'virtual_schedules', $prefix = 'thm_organizerTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the types
	 *
	 * @return	Array	An Array of types
	 *
	 * @since	v0.0.1
	 */
	public function getTypes()
	{
		$types[]["id"] = "class";
		$types[count($types) - 1]["name"] = "Semester";
		$types[]["id"] = "room";
		$types[count($types) - 1]["name"] = "Room";
		$types[]["id"] = "teacher";
		$types[count($types) - 1]["name"] = "Teacher";
		return $types;
	}

	/**
	 * Method to get the responsibles
	 *
	 * @return	Array	An Array of responsibles
	 *
	 * @since	v0.0.1
	 */
	public function getResponsibles()
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$usergroups = array();

		$query = $dbo->getQuery(true);
		$query->select('id');
		$query->from('#__usergroups');
		$dbo->setQuery((string) $query);
		$groups = $dbo->loadObjectList();

		foreach ($groups as $k => $v)
		{
			if (JAccess::checkGroup($v->id, 'core.login.admin') || $v->id == 8)
			{
				$usergroups[] = $v->id;
			}
		}

		$query = "SELECT DISTINCT username as id, name as name
		FROM #__users INNER JOIN #__user_usergroup_map ON #__users.id = user_id INNER JOIN #__usergroups ON group_id = #__usergroups.id WHERE";
		$first = true;
		if (is_array($usergroups))
		{
			foreach ($usergroups as $k => $v)
			{
				if ($first != true)
				{
					$query .= " OR";
				}
				$query .= " #__usergroups.id = " . (int) $v;
				$first = false;
			}
		}
		$query .= " ORDER BY name";
		$dbo->setQuery($query);
		$resps = $dbo->loadObjectList();

		return $resps;
	}

	/**
	 * Method to get the classes
	 *
	 * @return	Array	An Array of classes
	 *
	 * @since	v0.0.1
	 */
	public function getClasses()
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$query = "SELECT gpuntisID as id, CONCAT(major, ' ', semester) as name
		FROM #__thm_organizer_classes
		ORDER BY name";
		$dbo->setQuery($query);
		$classes = $dbo->loadObjectList();
		return $classes;
	}

	/**
	 * Method to get the rooms
	 *
	 * @return	Array	An Array of rooms
	 *
	 * @since	v0.0.1
	 */
	public function getRooms()
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$query = "SELECT gpuntisID as id, alias as name
		FROM #__thm_organizer_rooms
		ORDER BY name";
		$dbo->setQuery($query);
		$rooms = $dbo->loadObjectList();
		return $rooms;
	}

	/**
	 * Method to get the teachers
	 *
	 * @return	Array	An Array of teachers
	 *
	 * @since	v0.0.1
	 */
	public function getTeachers()
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$query = "SELECT gpuntisID as id, name
		FROM #__thm_organizer_teachers
		ORDER BY name";
		$dbo->setQuery($query);
		$teachers = $dbo->loadObjectList();
		return $teachers;
	}

	/**
	 * Method to get the semesters
	 *
	 * @return	Array	An Array of responsibles
	 *
	 * @since	v0.0.1
	 */
	public function getSemesters()
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$query = "SELECT id, Concat(organization, '-', semesterDesc) as name
		FROM #__thm_organizer_semesters
		ORDER BY name";
		$dbo->setQuery($query);
		$semesters = $dbo->loadObjectList();
		return $semesters;
	}

	/**
	 * Method to get the room departments
	 *
	 * @return	Array	An Array of room departments
	 *
	 * @since	v0.0.1
	 */
	public function getRoomDepartments()
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$query = "SELECT DISTINCT id, if (CHAR_LENGTH(description) = 0,category,CONCAT(category, ' (', description, ')')) as name
		FROM #__thm_organizer_descriptions " .
		"ORDER BY name";
		$dbo->setQuery($query);
		$departments = $dbo->loadObjectList();
		return $departments;
	}

	/**
	 * Method to get the teacher departments
	 *
	 * @return	Array	An Array of teacher departments
	 *
	 * @since	v0.0.1
	 */
	public function getTeacherDepartments()
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$query = "SELECT DISTINCT #__thm_organizer_departments.id, CONCAT(#__thm_organizer_departments.department,
		'-', #__thm_organizer_departments.subdepartment) as name
		FROM #__thm_organizer_teachers " .
		"INNER JOIN #__thm_organizer_departments " .
		"WHERE #__thm_organizer_teachers.departmentID = #__thm_organizer_departments.id
		ORDER BY name";
		$dbo->setQuery($query);
		$departments = $dbo->loadObjectList();
		return $departments;
	}

	/**
	 * Method to get the departments by type
	 *
	 * @param   String  $type  The deparment type
	 *
	 * @return	Array	An Array of departments
	 *
	 * @since	v0.0.1
	 */
	public function getDepartments($type)
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$query = "SELECT DISTINCT major as id, major as name
		FROM #__thm_organizer_" . $type . "
		ORDER BY major";
		$dbo->setQuery($query);
		$departments = $dbo->loadObjectList();
		return $departments;
	}

	/**
	 * Method to get the room types
	 *
	 * @return	Array	An Array of room types
	 *
	 * @since	v0.0.1
	 */
	public function getRoomTypes()
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$query = "SELECT DISTINCT rtype as id, rtype as name
		FROM #__thm_organizer_rooms
		ORDER BY name";
		$dbo->setQuery($query);
		$roomType = $dbo->loadObjectList();
		return $roomType;
	}

	/**
	 * Method to get the class types
	 *
	 * @return	Array	An Array of class types
	 *
	 * @since	v0.0.1
	 */
	public function getClassTypes()
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$query = "SELECT DISTINCT semester as id, semester as name
		FROM #__thm_organizer_classes
		ORDER BY name";
		$dbo->setQuery($query);
		$classTypes = $dbo->loadObjectList();
		return $classTypes;
	}

	/**
	 * Method check if the give id exists
	 *
	 * @param   Integer  $id  Virtual schedule id
	 * 
	 * @return	Boolean	 True if the schedule exits, false otherwise
	 * 
	 * @since	v0.0.1
	 */
	public function idExists($id)
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$query = "SELECT count(vid) as id_anz
		FROM #__thm_organizer_virtual_schedules
		WHERE vid = '" . $id . "';";
		$dbo->setQuery($query);
		$id_anz = $dbo->loadObjectList();
		if ($id_anz[0]->id_anz == "0")
		{
			return false;
		}
		return true;
	}

	/**
	 * Method to save a virtual schedule
	 *
	 * @param   Integer  $vscheduler_id  	      Id
	 * @param   String   $vscheduler_vid 		  Virtual schedule id
	 * @param   String   $vscheduler_name  		  Name
	 * @param   Array    $vscheduler_types 	 	  Types
	 * @param   Integer  $vscheduler_semid  	  Semester id
	 * @param   Array    $vscheduler_resps  	  Responsibles
	 * @param   Array    $vscheduler_Departments  Departments
	 * @param   Array    $vscheduler_elements  	  Elements
	 *
	 * @return	Boolean	 True if the schedule was successful saved, false otherwise
	 *
	 * @since	v0.0.1
	 */
	public function saveVirtualSchedule
	(
	 $vscheduler_id,
	 $vscheduler_vid,
	 $vscheduler_name,
	 $vscheduler_types,
	 $vscheduler_semid,
	 $vscheduler_resps,
	 $vscheduler_Departments,
	 $vscheduler_elements)
	{
		 
		$table = JTable::getInstance('virtual_schedules', 'thm_organizerTable');
		$tableElements = JTable::getInstance('virtual_schedules_elements', 'thm_organizerTable');
			
		if ($vscheduler_id === null || $vscheduler_id === 0 || empty($vscheduler_id))
		{
			$vscheduler_vid = "VS_" . $vscheduler_name;
		}

		$data = array();
		$data["vid"] = $vscheduler_vid;
		$data["name"] = $vscheduler_name;
		$data["type"] = $vscheduler_types;
		$data["responsible"] = $vscheduler_resps;
		$data["department"] = $vscheduler_Departments;
		$data["semesterID"] = $vscheduler_semid;

		if ($vscheduler_id != 0 || $vscheduler_id != null)
		{
			$data["id"] = $vscheduler_id;
		}

		$success = $table->save($data);

		if ((bool) $success)
		{
			$this->deleteElements((int) $vscheduler_id);
			$dataElements = array();
			$dataElements["vid"] = $table->id;

			foreach ($vscheduler_elements as $k => $v)
			{
				$tableElements = JTable::getInstance('virtual_schedules_elements', 'thm_organizerTable');
				$dataElements["eid"] = $v;
				$successElements = $tableElements->save($dataElements);
				if (!$successElements)
				{
					return false;
				}
			}
		}
		else
		{
			return false;
		}
		return true;
	}

	/**
	 * Method to delete elements for a given schedule id
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return	Boolean	 True if the element was successful removed, false otherwise
	 *
	 * @since	v0.0.1
	 */
	private function deleteElements($id)
	{
		if (!is_int($id))
		{
			return false;
		}
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();

		$query = 'DELETE FROM #__thm_organizer_virtual_schedules_elements'
		. ' WHERE vid = ' . $id . ';';

		$dbo->setQuery($query);
		$dbo->query();
		return true;
	}

	/**
	 * Method to get the data by a given schedule id
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return	mixed	 The schedule data or "0" if an error occurred
	 *
	 * @since	v0.0.1
	 */
	public function getData($id)
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$query = 'SELECT * FROM #__thm_organizer_virtual_schedules ' .
				'INNER JOIN #__thm_organizer_virtual_schedules_elements ' .
				'ON #__thm_organizer_virtual_schedules.vid = #__thm_organizer_virtual_schedules_elements.vid ' .
				'WHERE #__thm_organizer_virtual_schedules.vid = "' . $id . '"';
		$dbo->setQuery($query);
		$dbo->query();
		if ($dbo->getErrorNum())
		{
			return "0";
		}
		else
		{
			$data = $dbo->loadObjectList();
		}
		return $data;
	}

}
