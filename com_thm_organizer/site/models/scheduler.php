<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        scheduler model
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Class THM_OrganizerModelScheduler for component com_thm_organizer
 * Class provides methods to get the neccessary data to display a schedule
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelScheduler extends JModelLegacy
{
    /**
    * Semester id
    *
    * @var    int
    */
    public $semesterID = null;

     /**
      * Message
      *
      * @var    String
      */
     protected $msg;

     /**
      * Constructor
      */
     public function __construct()
     {
         parent::__construct();
     }

    /**
     * Method to get the session id
     *
     * @return  String  The current session id or empty string if the username is null
     */
    public function getSessionID()
    {
        $user = JFactory::getUser();
        if ($user->username == null)
        {
            return "";
        }

        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT s.session_id, s.username, s.usertype, u.email');
        $query->from('#__session AS s');
        $query->leftJoin('#__users AS u ON s.username = u.username');
        $query->where("s.username = '{$user->get('username')}'");
        $query->where("s.guest = '0'");
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $rows = $this->_db->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_SESSION_INFORMATION"), 500);
        }
        
        return $rows['0']->session_id;
    }

    /**
     * Method to check if the component is available
     *
     * @param   String  $com  Component name
     *
     * @return  Boolean true if the component is available, false otherwise
     */
    public function isComAvailable($com)
    {
        $query    = $this->_db->getQuery(true);
        $query->select('extension_id AS "id", element AS "option", params, enabled');
        $query->from('#__extensions');
        $query->where('type = ' . $this->_db->quote('component'));
        $query->where('element = ' . $this->_db->quote($com));
        $this->_db->setQuery((string) $query);
        
        try
        {
        $result = $this->_db->loadObject();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_COMPONENT_CHECK"), 500);
        }

        $error = $this->_db->getErrorMsg();
        if (!empty($error))
        {
            return false;
        }
        if ($result === null)
        {
            return false;
        }
        return true;
    }

    /**
     * Method to get the active schedule
     *
     * @param   String  $deptAndSem  The department semester selection
     *
     * @return   mixed  The active schedule or false
     */
    public function getActiveSchedule($deptAndSem)
    {
        if (!is_string($deptAndSem))
        {
            return false;
        }

        list($department, $semester, $startdate, $enddate) = explode(";", $deptAndSem);
        if (empty($semester))
        {
            return false;
        }

        $query = $this->_db->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_schedules');
        $query->where('departmentname = ' . $this->_db->quote($department));
        $query->where('semestername = ' . $this->_db->quote($semester));
        $query->where('startdate = ' . $this->_db->quote($startdate));
        $query->where('enddate = ' . $this->_db->quote($enddate));
        $query->where('active = 1');
        $this->_db->setQuery((string) $query);
        
        try
        {
            $result = $this->_db->loadObject();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_SCHEDULE"), 500);
        }
        
        $error = $this->_db->getErrorMsg();
        if (!empty($error))
        {
            return false;
        }
        if ($result === null)
        {
            return false;
        }
        return $result;
    }

    /**
     * Method to get the active schedule
     *
     * @param   String  $scheduleID  The schedule ID
     *
     * @return   mixed  The active schedule or false
     */
    public function getActiveScheduleByID($scheduleID)
    {
        if (!is_int($scheduleID))
        {
            return false;
        }

        $query = $this->_db->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_schedules');
        $query->where('id = ' . $scheduleID);
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $result = $this->_db->loadObject();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_SCHEDULE"), 500);
        }

        $error = $this->_db->getErrorMsg();
        if (!empty($error))
        {
            return false;
        }
        if ($result === null)
        {
            return false;
        }
        return $result;
    }

    /**
    * Method to get the color for the modules
    *
    * @return   Array  An Array with the color for the module
    */
    public function getCurriculumModuleColors()
    {
        $query = $this->_db->getQuery(true);

        $query->select('c.color AS hexColorCode, s.name AS semesterName, cm.organizer_major AS organizerMajorName');
        $query->from('#__thm_semesters AS s');
        $query->innerJoin('#__thm_curriculum_semesters_majors AS sm ON s.id = sm.semester_id');
        $query->innerJoin('#__thm_curriculum_majors AS cm ON cm.id = sm.major_id');
        $query->innerJoin('#__thm_curriculum_colors AS c ON c.id = s.color_id');
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $result = $this->_db->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_COLORS"), 500);
        }

         $error = $this->_db->getErrorMsg();
         if (!empty($error))
         {
             return array();
         }

         if ($result === null)
         {
             return array();
         }

         return $result;
    }

    /**
     * Method to get all rooms from database
     *
     * @return   Array  An Array with the rooms
     */
    public function getRooms()
    {
        $query = $this->_db->getQuery(true);

        $query->select('*');
        $query->from('#__thm_organizer_rooms');
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $result = $this->_db->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_ROOMS"), 500);
        }

        $error = $this->_db->getErrorMsg();
        if (!empty($error))
        {
            return array();
        }
        return $result;
    }

    /**
     * Method to get all teachers from database
     *
     * @return   Array  An Array with the teachers
     */
    public function getTeachers()
    {
        $query = $this->_db->getQuery(true);

        $query->select('*');
        $query->from('#__thm_organizer_teachers');
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $result = $this->_db->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_TEACHERS"), 500);
        }

        $error = $this->_db->getErrorMsg();
        if (!empty($error))
        {
            return array();
        }
        return $result;
    }

    /**
     * Method to get all teacher types from database
     *
     * @return   Array  An Array with the teacher types
     */
    public function getTeacherTypes()
    {
        $query = $this->_db->getQuery(true);

        $query->select('*');
        $query->from('#__thm_organizer_teacher_fields');
        $this->_db->setQuery($query);
        
        try 
        {
            $result = $this->_db->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_TEACHERS_TYPES"), 500);
        }

        $error = $this->_db->getErrorMsg();
        if (!empty($error))
        {
            return array();
        }
        return $result;
    }

    /**
     * Method to get all subjects (externalID AND english name) from database
     *
     * @return   Array  An Array with the subejects
     */
    public function getDBData($languageTag)
    {
        $query = $this->_db->getQuery(true);
        $itemID = JFactory::getApplication()->input->getInt('Itemid');

        $select = "name_$languageTag AS name, short_name_$languageTag AS shortname, ";
        $select .= "abbreviation_$languageTag AS abbreviation, externalID, ";
        $select .= "CONCAT('index.php?option=com_thm_organizer&view=subject_details&languageTag=', ";
        $select .= "'$languageTag', '&id=', id, '&Itemid=', '$itemID') AS link";
        $query->select($select);
        $query->from('#__thm_organizer_subjects');
        $query->where('externalID IS NOT NULL AND externalID <> ""');
        $this->_db->setQuery($query);
        
        try 
        {
            $result = $this->_db->loadObjectList("externalID");
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_SUBJECTS_EXTERNALID_ENGLISHNAME"), 500);
        }

        $error = $this->_db->getErrorMsg();
        if (!empty($error))
        {
            return array();
        }
        return $result;
    }
}