<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        scheduler model
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
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

        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT s.session_id, s.username, s.usertype, u.email');
        $query->from('#__session AS s');
        $query->leftJoin('#__users AS u ON s.username = u.username');
        $query->where("s.username = '{$user->get('username')}'");
        $query->where("s.guest = '0'");
        $dbo->setQuery((string) $query);
        $rows = $dbo->loadObjectList();
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
        $dbo = JFactory::getDBO();
        $query    = $dbo->getQuery(true);
        $query->select('extension_id AS "id", element AS "option", params, enabled');
        $query->from('#__extensions');
        $query->where('type = ' . $dbo->quote('component'));
        $query->where('element = ' . $dbo->quote($com));
        $dbo->setQuery((string) $query);

        try
        {
            $result = $dbo->loadObject();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        return $result === null? false : true;
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

        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_schedules');
        $query->where('departmentname = ' . $dbo->quote($department));
        $query->where('semestername = ' . $dbo->quote($semester));
        $query->where('startdate = ' . $dbo->quote($startdate));
        $query->where('enddate = ' . $dbo->quote($enddate));
        $query->where('active = 1');
        $dbo->setQuery((string) $query);

        try
        {
            $result = $dbo->loadObject();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        return $result === null? false : $result;
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

        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_schedules');
        $query->where('id = ' . $scheduleID);
        $dbo->setQuery((string) $query);

        try
        {
            $result = $dbo->loadObject();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        return $result === null? false : $result;
    }

    /**
    * Method to get the color for the modules
    *
    * @return   Array  An Array with the color for the module
    */
    public function getCurriculumModuleColors()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);

        $query->select('c.color AS hexColorCode, s.name AS semesterName, cm.organizer_major AS organizerMajorName');
        $query->from('#__thm_semesters AS s');
        $query->innerJoin('#__thm_curriculum_semesters_majors AS sm ON s.id = sm.semester_id');
        $query->innerJoin('#__thm_curriculum_majors AS cm ON cm.id = sm.major_id');
        $query->innerJoin('#__thm_curriculum_colors AS c ON c.id = s.color_id');
        $dbo->setQuery((string) $query);

        try
        {
            $result = $dbo->loadObjectList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }

        return $result === null? array() : $result;
    }

    /**
     * Method to get all rooms from database
     *
     * @return   Array  An Array with the rooms
     */
    public function getRooms()
    {
       $dbo = JFactory::getDBO();
       $query = $dbo->getQuery(true);

       $query->select('*');
       $query->from('#__thm_organizer_rooms');
       $dbo->setQuery((string) $query);

        try
        {
            $result = $dbo->loadObjectList();
            return $result;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Method to get all teachers from database
     *
     * @return   Array  An Array with the teachers
     */
    public function getTeachers()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);

        $query->select('*');
        $query->from('#__thm_organizer_teachers');
        $dbo->setQuery((string) $query);

        try
        {
            $result = $dbo->loadObjectList();
            return $result;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Method to get all teacher types from database
     *
     * @return   Array  An Array with the teacher types
     */
    public function getTeacherTypes()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);

        $query->select('*');
        $query->from('#__thm_organizer_teacher_fields');
        $dbo->setQuery($query);

        try
        {
            $result = $dbo->loadObjectList();
            return $result;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }
}
