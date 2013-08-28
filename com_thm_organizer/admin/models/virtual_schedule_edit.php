<?php
/**
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
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');
/**
 * Class THM_OrganizerModelVirtual_Schedule_Edit for component com_thm_organizer
 * Class provides methods to create and edit a virtual schedule
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.model
 *
 * @todo clean this thing up
 */
class THM_OrganizerModelVirtual_Schedule_Edit extends JModelAdmin
{
    /**
     * Method to retrieves the jform object for this view
     *
     * @param   Array  $data      An array with data (Default: Array)
     * @param   Array  $loadData  An array with data to load (Default: Array)
     *
     * @return    mixed  A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_thm_organizer.virtual_schedule_edit',
                                'virtual_schedule_edit',
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
     * @return    mixed  The data for the form.
     *
     * @since    v0.0.1
     */
    protected function loadFormData()
    {
        $data = $this->getItem();
        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $cid  The id of the primary key.
     *
     * @return    mixed  Object on success, false on failure.
     */
    public function getItem($cid = null)
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
     * @param   Integer  $vid  The id of the virtual schedule.
     *
     * @return    Array  An Array with elements
     */
    private function getElements($vid)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('eid');
        $query->from('#__thm_organizer_virtual_schedules_elements');
        $query->where("vid = '$vid'");
        $dbo->setQuery((string) $query);
        return $dbo->loadResultArray();
    }

    /**
     * Method to get an id
     *
     * @return    Integer  Returns the virtual schedule id if set or 0
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
            $cids = JRequest::getVar('cid', null, 'get', 'ARRAY');
            $cid = base64_decode($cids[0]);
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
     * @param   String  $type    The table type to instantiate (Default: 'virtual_schedules)
     * @param   String  $prefix  A prefix for the table class name. (Default: 'thm_organizerTable')
     * @param   Array   $config  Configuration array for model. (Default: Array)
     *
     * @return    JTable    A database object
     */
    public function getTable($type = 'virtual_schedules', $prefix = 'thm_organizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the types
     *
     * @return    Array    An Array of types
     */
    public function getTypes()
    {
        $types = array();
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
     * @return    Array    An Array of responsibles
     */
    public function getResponsibles()
    {
        $dbo = JFactory::getDBO();
        $groupQuery = $dbo->getQuery(true);
        $groupQuery->select('id');
        $groupQuery->from('#__usergroups');
        $dbo->setQuery((string) $groupQuery);
        $groups = $dbo->loadAssocList();

        $usergroups = array();
        foreach ($groups as $group)
        {
            if (JAccess::checkGroup($group['id'], 'core.login.admin') || $group['id'] == 8)
            {
                $usergroups[] = $group['id'];
            }
        }

        $userQuery = $dbo->getQuery(true);
        $userQuery->select('DISTINCT username as id, name as name');
        $userQuery->from('#__users AS u');
        $userQuery->innerJoin('#__user_usergroup_map AS ug ON u.id = ug.user_id');
        $userQuery->innerJoin('#__usergroups AS g ON group_id = g.id');
        $userQuery->where("ug.id IN ('" . implode("','", $usergroups) . "')");
        $userQuery->order('name');
        $dbo->setQuery((string) $userQuery);
        $resps = $dbo->loadObjectList();

        return $resps;
    }

    /**
     * Method to get the classes
     *
     * @return    Array    An Array of classes
     *
     * @since    v0.0.1
     */
    public function getClasses()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("gpuntisID as id, CONCAT(major, ' ', semester) as name");
        $query->from('#__thm_organizer_classes');
        $query->order('name');
        $dbo->setQuery((string) $query);
        $classes = $dbo->loadObjectList();
        return $classes;
    }

    /**
     * Method to get the rooms
     *
     * @return    Array    An Array of rooms
     *
     * @since    v0.0.1
     */
    public function getRooms()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('gpuntisID as id, alias as name');
        $query->from('#__thm_organizer_rooms');
        $query->order('name');
        $dbo->setQuery((string) $query);
        $rooms = $dbo->loadObjectList();
        return $rooms;
    }

    /**
     * Method to get the teachers
     *
     * @return    Array    An Array of teachers
     *
     * @since    v0.0.1
     */
    public function getTeachers()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('gpuntisID as id, name');
        $query->from('#__thm_organizer_teachers');
        $query->order('name');
        $dbo->setQuery((string) $query);
        $teachers = $dbo->loadObjectList();
        return $teachers;
    }

    /**
     * Method to get the semesters
     *
     * @return    Array    An Array of responsibles
     *
     * @since    v0.0.1
     */
    public function getSemesters()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("id, Concat(organization, '-', semesterDesc) as name");
        $query->from('#__thm_organizer_semesters');
        $query->order('name');
        $dbo->setQuery((string) $query);
        $semesters = $dbo->loadObjectList();
        return $semesters;
    }

    /**
     * Method to get the room departments
     *
     * @return    Array    An Array of room departments
     *
     * @since    v0.0.1
     */
    public function getRoomDepartments()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT id, if (CHAR_LENGTH(description) = 0,category,CONCAT(category, ' (', description, ')')) as name");
        $query->from('#__thm_organizer_descriptions');
        $query->order('name');
        $dbo->setQuery((string) $query);
        $departments = $dbo->loadObjectList();
        return $departments;
    }

    /**
     * Method to get the teacher departments
     *
     * @return    Array    An Array of teacher departments
     *
     * @since    v0.0.1
     */
    public function getTeacherDepartments()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT d.id, CONCAT(d.department, '-', d.subdepartment) as name");
        $query->from('#__thm_organizer_teachers AS t');
        $query->innerJoin('#__thm_organizer_departments AS d ON t.departmentID = d.id');
        $query->order('name');
        $dbo->setQuery((string) $query);
        $departments = $dbo->loadObjectList();
        return $departments;
    }

    /**
     * Method to get the departments by type
     *
     * @param   String  $type  The deparment type
     *
     * @return    Array    An Array of departments
     */
    public function getDepartments($type)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT major as id, major as name');
        $query->from("#__thm_organizer_$type");
        $query->order('major');
        $dbo->setQuery((string) $query);
        $departments = $dbo->loadObjectList();
        return $departments;
    }

    /**
     * Method to get the room types
     *
     * @return    Array    An Array of room types
     */
    public function getRoomTypes()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT rtype as id, rtype as name');
        $query->from('#__thm_organizer_rooms');
        $query->order('name');
        $dbo->setQuery((string) $query);
        $roomType = $dbo->loadObjectList();
        return $roomType;
    }

    /**
     * Method to get the class types
     *
     * @return    Array    An Array of class types
     */
    public function getClassTypes()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT semester as id, semester as name');
        $query->from('#__thm_organizer_classes');
        $query->order('name');
        $dbo->setQuery((string) $query);
        $classTypes = $dbo->loadObjectList();
        return $classTypes;
    }

    /**
     * Method check if the give id exists
     *
     * @param   Integer  $vid  Virtual schedule id
     *
     * @return    Boolean     True if the schedule exits, false otherwise
     */
    public function idExists($vid)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('count(vid) as id_anz');
        $query->from('#__thm_organizer_virtual_schedules');
        $query->where("id = '$vid'");
        $dbo->setQuery((string) $query);
        $vid_anz = $dbo->loadObjectList();
        if ($vid_anz[0]->id_anz == "0")
        {
            return false;
        }
        return true;
    }

    /**
     * Method to save a virtual schedule
     *
     * @param   Integer  $vscheduler_id        Id
     * @param   String   $vscheduler_vid       Virtual schedule id
     * @param   String   $vscheduler_name      Name
     * @param   Array    $vscheduler_types     Types
     * @param   Integer  $vscheduler_semid     Semester id
     * @param   Array    $vscheduler_resps     Responsibles
     * @param   Array    $degrees              Departments
     * @param   Array    $vscheduler_elements  Elements
     *
     * @return    Boolean     True if the schedule was successful saved, false otherwise
     */
    public function saveVirtualSchedule($vscheduler_id, $vscheduler_vid,
        $vscheduler_name, $vscheduler_types, $vscheduler_semid, $vscheduler_resps,
        $degrees, $vscheduler_elements)
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
        $data["department"] = $degrees;
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

            foreach ($vscheduler_elements as $v)
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
     * @param   Integer  $vid  Id
     *
     * @return    Boolean     True if the element was successful removed, false otherwise
     */
    private function deleteElements($vid)
    {
        if (!is_int($vid))
        {
            return false;
        }
        $dbo = JFactory::getDBO();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from('#__thm_organizer_virtual_schedules_elements');
        $query->where("vid = '$vid'");
        $dbo->setQuery((string) $query);
        $dbo->query();
        return true;
    }

    /**
     * Method to get the data by a given schedule id
     *
     * @param   Integer  $vid  Id
     *
     * @return    mixed  The schedule data or "0" if an error occurred
     */
    public function getData($vid)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_virtual_schedules AS vs');
        $query->innerJoin('#__thm_organizer_virtual_schedules_elements AS vse ON vs.id = vse.vid');
        $query->where("vs.id = '$vid'");
        $dbo->setQuery((string) $query);
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
