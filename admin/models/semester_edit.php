<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor model
 * @description db abstraction file for the editing  of semester entries
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelsemester_edit extends JModel
{
    public $id;
    public $organization;
    public $semesterDesc;
    public $manager;
    public $userGroups;
    public $schedules;

    function __construct()
    {
        parent::__construct();
        $this->getData();
        $this->getSchedules();
        $this->getUserGroups();
    }

    function getData()
    {
        $sids = JRequest::getVar('cid',  null, '', 'array');
        if(!empty($sids)) $sid = $sids[0];
        if(!isset($sid)) $sid = JRequest::getVar('semesterID');
        if(is_numeric($sid) and $sid != 0)
        {
            $dbo = JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $query->select("*");
            $query->from("#__thm_organizer_semesters");
            $query->where("id = '$sid'");
            $dbo->setQuery((string)$query);
            $semesterData = $dbo->loadAssoc();
            if(!empty($semesterData))
                foreach($semesterData as $k => $v)$this->$k = $v;
        }
        else
        {
            $this->id = 0;
            $this->manager = 0;
            $this->organization = '';
            $this->semesterDesc = '';
        }
    }

    /**
     * private function getSemesters
     *
     * gets the IDs and names of user groups with admin login priveledges
     */
    private function getUserGroups()
    {
        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('id, title');
        $query->from('#__usergroups');
        $dbo->setQuery((string)$query);
        $usergroups = $dbo->loadAssocList();
        foreach($usergroups as $k => $v)
            if(!JAccess::checkGroup($v['id'], 'core.login.admin'))
                unset($usergroups[$k]);
        $this->userGroups = $usergroups;
    }

    /**
     * private function getSchedules
     * 
     * creates a list of schedules asscociated with the selected semester
     */
    private function getSchedules()
    {		
        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("id, includedate, filename, active, description, startdate, enddate");
        $query->from("#__thm_organizer_schedules");
        $query->where("sid = '{$this->id}'");
        $dbo->setQuery((string)$query);
        $schedules = $dbo->loadAssocList();
        $this->schedules = $schedules;
    }

    function store()
    {
        $id = JRequest::getVar('id');
        $manager =  JRequest::getVar('manager');
        $organization = trim(JRequest::getVar( 'organization', '', 'post','string', JREQUEST_ALLOWRAW ));
        $semester = trim(JRequest::getVar( 'semester', '', 'post','string', JREQUEST_ALLOWRAW ));
        if(empty($organization) or empty($semester)) return 0;

        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__thm_organizer_semesters");
        $query->where("organization = '$organization'");
        $query->where("semesterDesc = '$semester'");
        $dbo->setQuery((string)$query);
        $savedID = $dbo->loadResult();
        unset($query);

        if(empty($savedID))
        {
            $query = $dbo->getQuery(true);
            if(empty($id))
            {
                $query->insert("#__thm_organizer_semesters ( manager, organization, semesterDesc)
                                VALUES ( '$manager', '$organization', '$semester' );");
            }
            else
            {
                $query->update("#__thm_organizer_semesters");
                $query->set("manager = '$manager', organization = '$organization', semesterDesc = '$semester'");
                $query->where("id = '$id';");
            }
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->select("id");
            $query->from("#__thm_organizer_semesters");
            $query->where("organization = '$organization'");
            $query->where("semesterDesc = '$semester'");
            $dbo->setQuery((string)$query);
            $savedID = $dbo->loadResult();
            unset($query);
        }
        if(isset($savedID)) return $savedID;
        else return $id;
    }

    function delete()
    {
        global $mainframe;
        $ids = JRequest::getVar( 'cid' );
        if(count( $ids ))
        {
            $dbo = JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $ids = "'".implode("', '", $ids)."'";

            $query->delete();
            $query->from("#__thm_organizer_semesters");
            $query->where("id IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_schedules");
            $query->where("sid IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_user_schedules");
            $query->where("sid IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_virtual_schedules");
            $query->where("sid IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_virtual_schedules_elements");
            $query->where("sid IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->update("#__thm_organizer_monitors");
            $query->set("semesterID = ''");
            $query->where("semesterID IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();
            unset($query);

            $query = $dbo->getQuery(true);
            $query->select("id");
            $query->from("#__thm_organizer_lessons");
            $query->where("semesterID IN ( $ids )");
            $dbo->setQuery((string)$query);
            $lessonIDs = $dbo->loadResultArray();
            unset($query);

            if(!empty($lessonIDS))
            {
                $lessonIDs = "'".implode("', '", $lessonIDs)."'";

                $query = $dbo->getQuery(true);
                $query->delete();
                $query->from("#__thm_organizer_lessons");
                $query->where("semesterID IN ( $ids )");
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query);

                $query = $dbo->getQuery(true);
                $query->delete();
                $query->from("#__thm_organizer_lesson_times");
                $query->where("lessonID IN ( $lessonIDs )");
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query);

                $query = $dbo->getQuery(true);
                $query->delete();
                $query->from("#__thm_organizer_lesson_teachers");
                $query->where("lessonID IN ( $lessonIDs )");
                $dbo->setQuery((string)$query);
                $dbo->query();
                unset($query);
            }
        }
        return true;
    }
}
?>