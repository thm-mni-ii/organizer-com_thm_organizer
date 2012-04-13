<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model schedule manager
 * @description datase abstraction file for the schedules table
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
require_once JPATH_COMPONENT.'/models/class.php';
require_once JPATH_COMPONENT.'/models/department.php';
require_once JPATH_COMPONENT.'/models/description.php';
require_once JPATH_COMPONENT.'/models/period.php';
require_once JPATH_COMPONENT.'/models/room.php';
require_once JPATH_COMPONENT.'/models/subject.php';
require_once JPATH_COMPONENT.'/models/teacher.php';
require_once JPATH_COMPONENT.'/models/lesson.php';
require_once JPATH_COMPONENT.'/models/schedule.php';
class thm_organizersModelschedulexml extends thm_organizersModelschedule
{
    /**
     * upload
     *
     * saves a schedule file in the database for later use
     *
     * @return mixed $result boolean false on db error, or array of
     *         strings
     */
    public function upload()
    {
        $schedule = simplexml_load_file($_FILES['file']['tmp_name']);
        $fp = fopen($_FILES['file']['tmp_name'], 'r');
        $file = fread($fp, filesize($_FILES['file']['tmp_name']));
        fclose($fp);

        $data = JRequest::getVar('jform', null, null, null, 4);
        $data['filename'] = str_replace(".xml", "", $_FILES['file']['name']);
        $data['file'] = addslashes($file);
        $data['plantypeID'] = '1';
        $data['creationdate'] = trim((string)$schedule[0]['date']);
        $data['startdate'] = trim((string)$schedule->general->schoolyearbegindate);
        $data['enddate'] = trim((string)$schedule->general->schoolyearenddate);


        $table = JTable::getInstance('schedules', 'thm_organizerTable');
        $success = $table->save($data);
        return ($success)? $table->id : $success;
    }

    /**
     * validate
     *
     * checks a given schedule in gp-untis xml format for data completeness and
     * consistency
     *
     * @return $problems array of strings listing inconsistancies empty if none
     *          were found
     */
    public function validate()
    {
        $schedule = simplexml_load_file($_FILES['file']['tmp_name']);

        $creationdate = trim((string)$schedule[0]['date']);
        if(empty($creationdate))
            $errors[] = JText::_("COM_THM_ORGANIZER_SCH_CREATION_DATE_MISSING");
        $startdate = trim((string)$schedule->general->schoolyearbegindate);
        if(empty($startdate))
            $errors[] = JText::_("COM_THM_ORGANIZER_SCH_START_DATE_MISSING");
        $enddate = trim((string)$schedule->general->schoolyearenddate);
        if(empty($enddate))
            $errors[] = JText::_("COM_THM_ORGANIZER_SCH_END_DATE_MISSING");
        $header1 = trim((string)$schedule->general->header1);
        if(empty($header1))
            $warnings[] = JText::_("COM_THM_ORGANIZER_SCH_ORGANIZATION_MISSING");
        else
        {
            $details = explode(",", $header1);
            if(count($details) < 3)
                $warnings[] = JText::_("COM_THM_ORGANIZER_SCH_ORGANIZATION_LACKING");
        }

        $errors = array();
        $warnings = array();

        $periods = array();
        $periodsmodel = new thm_organizersModelperiod(array());
        $periodsmodel->validate('xml', $schedule->timeperiods, $periods, $errors, $warnings);
        unset($periodsmodel);

        $descriptions = array();
        $descriptionsmodel = new thm_organizersModeldescription(array());
        $descriptionsmodel->validate('xml', $schedule->descriptions, $descriptions, $errors, $warnings);
        unset($descriptionsmodel);

        $departments = array();
        $departmentsmodel = new thm_organizersModeldepartment(array());
        $departmentsmodel->validate('xml', $schedule->departments, $departments, $errors, $warnings);
        unset($departmentsmodel);

        $rooms = array();
        $roomsmodel = new thm_organizersModelroom(array());
        $roomsmodel->validate('xml', $schedule->rooms, $rooms, $errors, $warnings, $descriptions);
        unset($roomsmodel);

        $subjects = array();
        $subjectsmodel = new thm_organizersModelsubject(array());
        $subjectsmodel->validate('xml', $schedule->subjects, $subjects, $errors, $warnings);
        unset($subjectsmodel);


        $teachers = array();
        $teachersmodel = new thm_organizersModelteacher(array());
        $teachersmodel->validate('xml', $schedule->teachers, $teachers, $errors, $warnings, $departments);
        unset($teachersmodel);
        
        $classes = array();
        $classesmodel = new thm_organizersModelclass(array());
        $classesmodel->validate('xml', $schedule->classes, $classes, $errors, $warnings, $teachers);
        unset($classesmodel);

        $dummy = array();
        $resources = array('subjects' => $subjects,
                           'teachers' => $teachers,
                           'classes' => $classes,
                           'periods' => $periods,
                           'rooms' => $rooms);
        $lessonsmodel = new thm_organizersModellesson(array());
        $lessonsmodel->validate('xml', $schedule->lessons, $dummy, $errors, $warnings, $resources);
        unset($lessonsmodel);

        $problems = array();
        if(count($errors))
            $problems['errors'] = "<br />".implode("<br />", $errors);
        if(count($warnings))
            $problems['warnings'] = "<br />".implode("<br />", $warnings);
        return $problems;
    }

    /**
     * activate
     *
     * creates a field entry (date) in the database marking a gp-untis schedule
     * as being active for the given planning period (semester)
     *
     * @param JTable Object $schedule
     * @param array $return array holding status messages from individual
     *                      function calls
     * @return string on error
     */
    public function activate(&$schedule, &$return)
    {
        $dbo = JFactory::getDBO();
        $newScheduleName = $schedule->filename;
        $semesterID = $schedule->sid;

        $query = $dbo->getQuery(true);
        $query->select("CONCAT( organization, ' - ', semester Desc)");
        $query->from("#__thm_organizer_semesters");
        $query->where("id = '$semesterID'");
        $dbo->setQuery((string)$query);
        $semesterName = $dbo->loadResult();

        $query = $dbo->getQuery(true);
        $query->select('filename');
        $query->from("#__thm_organizer_schedules");
        $query->where("active IS NOT NULL");
        $query->where("sid = '$semesterID'");
        $query->where("plantypeID = '1'");
        $dbo->setQuery((string)$query);
        $oldScheduleName = $dbo->loadResult();

        if($oldScheduleName)
        {
            $oldData = $this->getOldData($semesterID);
            $this->deactivate($semesterID);
        }
        $newData = $this->processNewData($schedule);

        $msg = "";
        if($oldData and $newData)
        {
            $msg .= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_1')." $semesterName ";
            $msg .= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_2A')." $oldScheduleName ";
            $msg .= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_3A')." $newScheduleName.";
            $return['messages'][] = $msg;
            $this->calculateDelta($oldData, $newData, $semesterID);
        }
        else if($newData)
        {
            $msg .= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_1')." $semesterName ";
            $msg .= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_2B')." $newScheduleName.";
            $return['messages'][] = $msg;
        }
        else $return['errors'][]= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_DB_FAIL');
    }

    /**
     * processNewData
     *
     * saves and models the data contained in the file
     *
     * @return mixed array modeling lesson data if successful, otherwise false
     */
    protected function processNewData(&$row)
    {
        $semesterID = $row->sid;
        $schedule = simplexml_load_string(stripslashes($row->file));

        $periods = array();
        $periodsmodel = new thm_organizersModelperiod(array());
        $periodsmodel->processData($schedule->timeperiods, $periods, $semesterID);
        unset($periodsmodel);

        $descriptions = array();
        $descriptionsmodel = new thm_organizersModeldescription(array());
        $descriptionsmodel->processData($schedule->descriptions, $descriptions);
        unset($descriptionsmodel);

        $departments = array();
        $departmentsmodel = new thm_organizersModeldepartment(array());
        $departmentsmodel->processData($schedule->departments, $departments);
        unset($departmentsmodel);
        
        $rooms = array();
        $roomsmodel = new thm_organizersModelroom(array());
        $roomsmodel->processData($schedule->rooms, $rooms, 0, $descriptions);
        unset($roomsmodel);

        $subjects = array();
        $subjectsmodel = new thm_organizersModelsubject(array());
        $subjectsmodel->processData($schedule->subjects, $subjects);
        unset($subjectsmodel);

        $teachers = array();
        $teachersmodel = new thm_organizersModelteacher(array());
        $teachersmodel->processData($schedule->teachers, $teachers, 0, $departments);
        unset($teachersmodel);

        $classes = array();
        $classesmodel = new thm_organizersModelclass(array());
        $classesmodel->processData($schedule->classes, $classes, 0, $teachers);
        unset($classesmodel);

        $lessons = array();
        $resources = array( 'periods' => $periods,
                            'rooms' => $rooms,
                            'classes' => $classes,
                            'teachers' => $teachers,
                            'subjects' => $subjects);
        $lessonsmodel = new thm_organizersModellesson(array());
        $lessonsmodel->processData($schedule->lessons, $lessons, $semesterID, $resources);
        unset($lessonsmodel);

        $row->active = date('Y-m-d');
        $row->store();

        return $lessons;
    }

    /**
     * getOldData
     *
     * retrieves lesson data for the previously active semester & plantype
     * and encapsulates it in an array structure
     *
     * @param int $semesterID the id of the semester
     * @return mixed $lessons a structure modeling the previously active lesson data
     */
    protected function getOldData($semesterID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("*");
        $query->from("#__thm_organizer_lessons");
        $query->where("plantypeID = '1'");
        $query->where("semesterID = '$semesterID'");
        $dbo->setQuery((string)$query);
        $results = $dbo->loadAssocList();

        $lessons = array();
        foreach($results as $result)
        {
            $lessonID = $result['id'];
            $gpuntisID = $result['gpuntisID'];
            if(!isset($lessons[$gpuntisID]))
            {
                $lessons[$gpuntisID] = array();
                $lessons[$gpuntisID]['subjectID'] = $result['subjectID'];
                $lessons[$gpuntisID]['type'] = $result['type'];
                $lessons[$gpuntisID]['comment'] = $comment;
                $lessons[$gpuntisID]['classIDs'] = array();
                $lessons[$gpuntisID]['teacherIDs'] = array();
                $lessons[$gpuntisID]['periods'] = array();
            }

            $query = $dbo->getQuery(true);
            $query->select("DISTINCT(teacherID)");
            $query->from("#__thm_organizer_lesson_teachers");
            $query->where("lessonID = '$lessonID'");
            $dbo->setQuery((string)$query);
            $lessons[$gpuntisID]['teacherIDs'] = $dbo->loadResultArray();

            $query = $dbo->getQuery(true);
            $query->select("DISTINCT(classID)");
            $query->from("#__thm_organizer_lesson_classes");
            $query->where("lessonID = '$lessonID'");
            $dbo->setQuery((string)$query);
            $lessons[$gpuntisID]['classIDs'] = $dbo->loadResultArray();

            $query = $dbo->getQuery(true);
            $query->select("*");
            $query->from("#__thm_organizer_lesson_times");
            $query->where("lessonID = '$lessonID'");
            $dbo->setQuery((string)$query);
            $instances = $dbo->loadAssocList();
            foreach($instances as $instance)
            {
                $periodID = $instance['periodID'];
                $roomID = $instance['roomID'];
                if(!isset($lessons[$gpuntisID]['periods'][$periodID]))
                    $lessons[$gpuntisID]['periods'][$periodID] = array();
                if(!isset($lessons[$gpuntisID]['periods'][$periodID]['roomIDs']))
                    $lessons[$gpuntisID]['periods'][$periodID]['roomIDs'] = array();
                if(!in_array($roomID, $lessons[$gpuntisID]['periods'][$periodID]['roomIDs']))
                    $lessons[$gpuntisID]['periods'][$periodID]['roomIDs'][] = $roomID;
            }
        }
        return $lessons;
    }

    /**
     * calculateDelta
     *
     * creates a change set between the currently active schedule and the schedule to
     * become active, and saves this data as a json string in the structure used by
     * the scheduler rich internet application
     *
     * @param mixed $oldData struture modeling the previously active lesson data
     * @param mixed $newData structzure modeling the active lesson data
     * @param int $semesterID the id of the semester
     */
    private function calculateDelta($oldData, $newData, $semesterID)
    {
        $delta = array();
        foreach($newData as $gpuntisKey => $lesson)
        {
            if(!key_exists($gpuntisKey, $oldData))//lesson is new
            {
                $delta[$gpuntisKey] = $lesson;
                $delta[$gpuntisKey]['status'] = 'new';
            }
            else//lesson occurs in both plans
            {
                $lesson_changes = false;
                $period_changes = false;
                $newTeachers = array_diff($lesson['teacherIDs'], $oldData[$gpuntisKey]['teacherIDs']);
                $oldTeachers = array_diff($oldData[$gpuntisKey]['teacherIDs'], $lesson['teacherIDs']);
                $newClasses = array_diff($lesson['classIDs'], $oldData[$gpuntisKey]['classIDs']);
                $oldClasses = array_diff($oldData[$gpuntisKey]['classIDs'], $lesson['classIDs']);
                if(count($newTeachers) or count($oldTeachers) or count($newClasses) or count($oldClasses))
                {
                    $lesson_changes = true;
                    $delta[$gpuntisKey]['status'] = "changed";
                    $delta[$gpuntisKey]['changes'] = array();
                    if(count($newTeachers) or count($oldTeachers))
                    {
                        $delta[$gpuntisKey]['changes']['teacherIDs'] = array();
                        if(count($newTeachers))
                            foreach($newTeachers as $newTeacher)
                                $delta[$gpuntisKey]['changes']['teacherIDs'][$newTeacher]= "new";
                        if(count($oldTeachers))
                            foreach($oldTeachers as $oldTeacher)
                                $delta[$gpuntisKey]['changes']['teacherIDs'][$oldTeacher]= "removed";
                    }
                    if(count($newClasses) or count($oldClasses))
                    {
                        $delta[$gpuntisKey]['changes']['classIDs'] = array();
                        if(count($newClasses))
                            foreach($newClasses as $newClass)
                                $delta[$gpuntisKey]['changes']['teacherIDs'][$newClass]= "new";
                        if(count($oldClasses))
                            foreach($oldClasses as $oldClass)
                                $delta[$gpuntisKey]['changes']['teacherIDs'][$oldClass]= "removed";
                    }
                }
                //if the number of periods has remained the same than any new keys are moves
                $moved = (count($lesson['periods']) == count($oldData[$gpuntisKey]['periods']))? true : false;
                foreach($lesson['periods'] as $periodID => $instance)
                {
                    if(!key_exists($periodID, $oldData[$gpuntisKey]['periods']))//period has been added
                    {
                        $period_changes = true;
                        if(!isset($delta[$gpuntisKey]['periods']))$delta[$gpuntisKey]['periods'] = array();
                        $delta[$gpuntisKey]['periods'][$periodID] = $instance;
                        $delta[$gpuntisKey]['periods'][$periodID]['status'] = ($moved)? 'moved': 'new';
                    }
                    else//period is in both plans
                    {
                        $newRooms = array_diff($instance['roomIDs'], $oldData[$gpuntisKey]['periods'][$periodID]['roomIDs']);
                        $oldRooms = array_diff($oldData[$gpuntisKey]['periods'][$periodID]['roomIDs'], $instance['roomIDs']);
                        if(count($newRooms) or count($oldRooms))
                        {
                            $period_changes = true;
                            if(!isset($delta[$gpuntisKey]['periods']))$delta[$gpuntisKey]['periods'] = array();
                            $delta[$gpuntisKey]['periods'][$periodID] = $instance;
                            $delta[$gpuntisKey]['periods'][$periodID]['status'] = 'changed';
                            if(count($newRooms))
                                foreach($newRooms as $newRoom)
                                    $delta[$gpuntisKey]['periods'][$periodID]['changes']['roomIDs'][$newRoom]= "new";
                            if(count($oldRooms))
                                foreach($oldRooms as $oldRoom)
                                    $delta[$gpuntisKey]['periods'][$periodID]['changes']['roomIDs'][$oldRoom]= "removed";
                        }
                    }
                }
                foreach($oldData[$gpuntisKey]['periods'] as $periodID => $instance)
                {
                    if(!key_exists($periodID, $lesson['periods']))//period has been removed
                    {
                        $period_changes = true;
                        if(!isset($delta[$gpuntisKey]['periods']))$delta[$gpuntisKey]['periods'] = array();
                        $delta[$gpuntisKey]['periods'][$periodID] = $instance;
                        $delta[$gpuntisKey]['periods'][$periodID]['status'] = 'removed';
                    }
                }
                if($lesson_changes or $period_changes)
                {
                    $delta[$gpuntisKey]['subjectID'] = $lesson['subjectID'];
                    $delta[$gpuntisKey]['type'] = $lesson['type'];
                    $delta[$gpuntisKey]['comment'] = $lesson['comment'];
                    $delta[$gpuntisKey]['teacherIDs'] = $lesson['teacherIDs'];
                    $delta[$gpuntisKey]['classIDs'] = $lesson['classIDs'];
                    if($lesson_changes)
                    {
                        foreach($lesson['periods'] as $periodID => $instance)
                        {
                            if(!isset($delta[$gpuntisKey]['periods']))
                                $delta[$gpuntisKey]['periods'] = array();
                            if(!isset($delta[$gpuntisKey]['periods'][$periodID]))
                                $delta[$gpuntisKey]['periods'][$periodID] = $instance;
                        }
                    }
                }
            }
        }
        foreach($oldData as $gpuntisKey => $lesson)
        {
            if(!key_exists($gpuntisKey, $newData))//lesson has been removed
            {
                $delta[$gpuntisKey] = $lesson;
                $delta[$gpuntisKey]['status'] = 'removed';
            }
        }

        //json_encode does not handle special characters properly
        $delta = json_encode($delta);
        $special_characters = array('\u00d6' => 'Ö',
                                    '\u00f6' => 'ö',
                                    '\u00c4' => 'Ä',
                                    '\u00e4' => 'ä',
                                    '\u00dc' => 'Ü',
                                    '\u00fc' => 'ü',
                                    '\u00df' => 'ß');
        foreach($special_characters as $unicode => $character)
            $delta = str_replace($unicode, $character, $delta);

        $table = JTable::getInstance('deltas', 'thm_organizerTable');
        $loadData = array('semesterID' => $semesterID,
                          'plantypeID' => '1');
        $data = array('semesterID' => $semesterID,
                      'plantypeID' => '1',
                      'delta' => $delta);
        $table->load($loadData);
        $table->save($data);
    }

    /**
     * deactivate
     *
     * sets the current active schedule to inactive. this entails the deletion
     * of the delta, and the removal of schedule specific data from the db.
     *
     * @param int $semesterID the id of the semester whose active plan is to be
     *                        deactivated
     */
    public function deactivate($semesterID)
    {
        $dbo = $this->getDbo();

        $query = $dbo->getQuery(true);
        $query->select("DISTINCT ( id )");
        $query->from("#__thm_organizer_lessons");
        $query->where("semesterID = '$semesterID'");
        $query->where("plantypeID = '1'");
        $dbo->setQuery((string)$query);
        $lessonIDs = "( '".implode("', '", $dbo->loadResultArray())."' )";

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lessons");
        $query->where("id IN $lessonIDs");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lesson_teachers");
        $query->where("lessonID IN $lessonIDs");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lesson_classes");
        $query->where("lessonID IN $lessonIDs");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lesson_times");
        $query->where("lessonID IN $lessonIDs");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->update("#__thm_organizer_schedules");
        $query->set("active = NULL");
        $query->where("plantypeID = '1'");
        $query->where("sid = '$semesterID'");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_deltas");
        $query->where("plantypeID = '1'");
        $query->where("semesterID = '$semesterID'");
        $dbo->setQuery((string)$query);
        $dbo->query();
    }

    /**
     * delete
     *
     * removes the selected schedule
     *
     * @param int $scheduleID the id of the schedule to be deleted
     */
    public function delete($scheduleID)
    {
        $schedule = JTable::getInstance('schedules', 'thm_organizerTable');
        if($schedule->load($scheduleID))$schedule->delete($scheduleID);
    }
}
?>
