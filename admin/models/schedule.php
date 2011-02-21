<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        [joomla type]
 * @description [description of the file and/or its purpose]
 * @author      [first name] [last name] [Email]
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     [versionsnr]
 */

defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelschedule extends JModel
{

    function __construct(){ parent::__construct(); }
    
    /**
     * public function upload
     *
     * saves a gp-untis schedule file in the database for later use
     */
    public function uploadGPUntis(&$errors)
    {
        $fileName = $_FILES['file']['name'];

        $tmpName  = $_FILES['file']['tmp_name'];
        $schedule = simplexml_load_file($tmpName);
        $this->validateGPUntis(&$schedule, &$errors);

        $fp = fopen($tmpName, 'r');
        $file = fread($fp, filesize($tmpName));
        fclose($fp);
        $file = addslashes($file);

        $includedate = date('Y-m-d');
        $creationdate = (string)$schedule[0]['date'];
        $startdate = (string)$schedule->general->schoolyearbegindate;
        $enddate = (string)$schedule->general->schoolyearenddate;
        unset($schedule);
        $sid = JRequest::getVar('semesterID');

        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $statement = "#__thm_organizer_schedules( filename, file, includedate, creationdate, startdate, enddate, sid )
                      VALUES ( '$fileName', '$file', '$includedate', '$creationdate', '$startdate', '$enddate', '$sid' )";
        $query->insert($statement);
        $dbo->setQuery((string)$query );
        $dbo->query();
        if ($dbo->getErrorNum())$errors['dberrors'] = true;
    }

    /**
     * public funtion validate
     *
     * checks a given schedule in gp-untis xml format for data completeness and consistency
     */
    public function validateGPUntis(&$file, &$errors)
    {
        $creationdate = (string)$file[0]['date'];
        if(empty($creationdate))
        {
            $error = JText::_("Document creation date missing.");
            if(!in_array($errors['dataerrors'],$error))$errors['dataerrors'][] = $error;
            unset($error);
        }
        $startdate = (string)$file->general->schoolyearbegindate;
        if(empty($startdate)) $errors['dataerrors'][] = JText::_("Schedule startdate is missing.");
        $enddate = (string)$file->general->schoolyearenddate;
        if(empty($enddate)) $errors['dataerrors'][] = JText::_("Schedule enddate is missing.");
        $header1 = (string)$file->general->header1;
        if(empty($header1))
            $errors['dataerrors'][] = JText::_("Creating department information is missing.");
        else
        {
            $details = explode(",", $header1);
            if(count($details) < 3)  $errors['dataerrors'][] = JText::_("Header is missing information (institution/campus/department).");
        }

        $timeperiods = array();
        $timeperiodsnode = $file->timeperiods;
        if(empty($timeperiodsnode))
            $errors['dataerrors'][] = JText::_("Time period information is completely missing.");
        else
        {
            foreach( $timeperiodsnode->children() as $timeperiod )
            {
                $id = (string)$timeperiod[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more timeperiods are missing their id attribute.");
                    if(!in_array($error, $errors['dataerrors'])) $errors['dataerrors'][] = $error;
                    unset($error);
                    continue;
                }
                $day = (string)$timeperiod->day;
                if(empty($day))
                {
                    $errors['dataerrors'][] = JText::_("Timeperiod")." $id ".JText::_("does not have a day.");
                    continue;
                }
                $period = (string)$timeperiod->period;
                if(empty($period))
                {
                    $errors['dataerrors'][] = JText::_("Timeperiod")." $id ".JText::_("does not have a period.");
                    continue;
                }
                $timeperiods[$day][$period]['id'] = $id;
                $starttime = (string)$timeperiod->starttime;
                if(empty($starttime))
                    $errors['dataerrors'][] = JText::_("Timeperiod")." $id ".JText::_("does not have a starttime.");
                else $timeperiods[$day][$period]['starttime'] = $starttime;
                $endtime = (string)$timeperiod->endtime;
                if(empty($endtime))
                    $errors['dataerrors'][] = JText::_("Timeperiod")." $id ".JText::_("does not have an endtime.");
                else $timeperiods[$day][$period]['endtime'] = $endtime;
                unset($id, $day, $period, $starttime, $endtime);
            }
        }
        unset($timeperiodsnode);

        $descriptions = array();
        $descriptionsnode = $file->descriptions;
        if(empty($descriptionsnode))
            $errors['dataerrors'][] = JText::_("Room description information is completely missing.");
        else
        {
            foreach($descriptionsnode->children() as $description)
            {
                $id = (string)$description[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more descriptions are missing their id attribute.");
                    if(!in_array($error, $errors['dataerrors'])) $errors['dataerrors'][] = $error;
                    unset($error);
                    continue;
                }
                $name = (string)$description->longname;
                if(empty($name))
                {
                    $errors['dataerrors'][] =
                        JText::_("Description")." $id ".JText::_("does not have a description.");
                    continue;
                }
                else $descriptions[$id] = $name;
                unset($id, $name);
            }
        }
        unset($descriptionsnode);

        $departments = array();
        $departmentsnode = $file->departments;
        if(empty($departmentsnode))
            $errors['dataerrors'][] = JText::_("Department information is completely missing.");
        else
        {
            foreach($departmentsnode->children() as $dptnode)
            {
                $id = (string)$dptnode[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more departments are missing their id attributes.");
                    if(!in_array($error, $errors['dataerrors']))$errors['dataerrors'][] = $error;
                    unset($error);
                    continue;
                }
                $details = explode(",",(string)$dptnode->longname);
                if(count($details) < 3)
                {
                    $errors['dataerrors'][] = JText::_("Department")." $id ".JText::_("does not have all its required information.");
                    continue;
                }
                $departments[$id] = array();
                $departments[$id]['institution'] = trim($details [0]);
                $departments[$id]['campus'] = trim($details [1]);
                $departments[$id]['department'] = trim($details [2]);
                if(isset($details [3])) $departments[$id]['curriculum'] = trim($details [3]);
                unset($id, $details);
            }
        }
        unset($departmentsnode);

        $rooms = array();
        $roomsnode = $file->rooms;
        if(empty($roomsnode))
            $errors['dataerrors'][] = JText::_("Room information is completely missing.");
        else
        {
            foreach($roomsnode->children() as $room)
            {
                $id = (string)$room[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more rooms are missing their id attributes.");
                    if(!in_array($error, $errors['dataerrors']))$errors['dataerrors'][] = $error;
                    unset($error);
                    continue;
                }
                $name = str_replace("RM_","",$id);
                $rooms[$id]['name'] = $name;
                $longname = trim($room->longname);
                if(empty($longname))
                    $errors['dataerrors'][] = JText::_("Room")." $name ($id) ".JText::_("does not have a longname.");
                else $rooms[$id]['longname'] = $longname;
                $capacity = trim($room->capacity);
                if(empty($capacity))
                    $errors['dataerrors'][] = JText::_("Room")." $name ($id) ".JText::_("does not have a capacity.");
                else $rooms[$id]['capacity'] = $capacity;
                $descid = trim($room->description);
                if(empty($descid))
                    $errors['dataerrors'][] = JText::_("Room")." $name ($id) ".JText::_("does not reference a description.");
                else if(empty($descriptions[$descid]))
                    $errors['dataerrors'][] = JText::_("Room")." $name ($id) ".JText::_("references the missing or incomplete description")." $descid.";
                else $rooms[$id]['description'] = $descriptions[$descid];
                $dptid = trim($room->department);
                if(empty($dptid))
                    $errors['dataerrors'][] = JText::_("Room")." $name ($id) ".JText::_("does not reference a department.");
                else if(empty($departments[$dptid]) or count($departments[$dptid]) < 3)
                    $errors['dataerrors'][] =
                        JText::_("Room")." $name ($id) ".JText::_("references the missing or incomplete department")." $dptid.";
                else $rooms['department'] = $departments[$dptid];
                unset($id, $longname, $capacity, $descid, $dptid);
            }
        }
        unset($roomsnode, $descriptions);

        $subjects = array();
        $subjectsnode = $file->subjects;
        if(empty($subjectsnode))
            $errors['dataerrors'][] = JText::_("Subject information is completely missing.");
        else
        {
            foreach($subjectsnode->children() as $subject)
            {
                $id = (string)$subject[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more subjects are missing their id attributes.");
                    if(!in_array($error, $errors['dataerrors']))$errors['dataerrors'][] = $error;
                    unset($error);
                    continue;
                }
                $longname = trim($subject->longname);
                if(empty($longname))
                {
                    $errors['dataerrors'][] = JText::_("Subject")." $id ".JText::_("does not have a longname.");
                    continue;
                }
                else $subjects[$id]['longname'] = $longname;
                $subjectgroup = trim($subject->subjectgroup);
                if(empty($subjectgroup))
                    $errors['dataerrors'][] = JText::_("Subject")." $longname ($id) ".JText::_("does not have a subjectgroup/module number.");
                else $subjects[$id]['subjectgroup'] = $subjectgroup;
                unset($id, $longname, $subjectgroup);
            }
        }
        unset($subjectsnode);

        $teachers = array();
        $teachersnode = $file->teachers;
        if(empty($teachersnode))
            $errors['dataerrors'][] = JText::_("Teacher information is completely missing.");
        else
        {
            foreach($teachersnode->children() as $teacher)
            {
                $id = (string)$teacher[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more teachers are missing their id attributes.");
                    if(!in_array($error, $errors['dataerrors']))$errors['dataerrors'][] = $error;
                    unset($error);
                    continue;
                }
                $surname = trim($teacher->surname);
                if(empty($surname))
                {
                    $errors['dataerrors'][] = JText::_("Teacher")." $id ".JText::_("does not have a surname.");
                    continue;
                }
                else $teachers[$id]['surname'] = $surname;
                $userid = trim($teacher->payrollnumber);
                if(empty($userid))
                    $errors['dataerrors'][] = JText::_("Teacher")." $surname ($id) ".JText::_("does not have a username(payrollnumber).");
                else $teachers[$id]['userid'] = $userid;
                $dptid = trim($teacher->teacher_department[0]['id']);
                if(empty($dptid))
                    $errors['dataerrors'][] = JText::_("Teacher")." $surname ($id) ".JText::_("does not reference a department.");
                else if(empty($departments[$dptid]) or count($departments[$dptid]) < 3)
                    $errors['dataerrors'][] =
                        JText::_("Teacher")." $surname ($id) ".JText::_("references the missing or incomplete department")." $dptid.";
                else $teachers['department'] = $departments[$dptid];
                unset($id, $surname, $userid, $dptid);
            }
        }
        unset($teachersnode);

        $classes = array();
        $classesnode = $file->classes;
        if(empty($classesnode))
            $errors['dataerrors'][] = JText::_("Class(Semester) information is completely missing.");
        else
        {
            foreach($classesnode->children() as $class)
            {
                $id = (string)$class[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more classes(semesters) are missing their id attributes.");
                    if(!in_array($error, $errors['dataerrors']))$errors['dataerrors'][] = $error;
                    unset($error);
                    continue;
                }
                $longname = trim($class->longname);
                if(empty($longname))
                {
                    $errors['dataerrors'][] = JText::_("Class")." $id ".JText::_("does not have a longname.");
                    continue;
                }
                else
                {
                    $details = explode(",", $longname);
                    if(count($details) < 2)
                        $errors['dataerrors'][] = JText::_("The longname attribute of class")." $id ".JText::_("is missing information.");
                    else
                    {
                        $classes[$id]['major'] = $details[0];
                        $classes[$id]['semester'] = $details[1];
                    }
                }
                $teacherid = trim($class->class_teacher[0]['id']);
                if(empty($teacherid))
                    $errors['dataerrors'][] = JText::_("Class")." $longname ($id) ".JText::_("does not reference a teacher.");
                else if(empty($teachers[$teacherid]) or count($teachers[$teacherid]) < 3)
                    $errors['dataerrors'][] = JText::_("Class")." $longname ($id) ".JText::_("references the missing or incomplete teacher")." $teacherid.";
                else $classes[$id]['teacher'] = $teachers[$teacherid];
            }
        }
        unset($classesnode);

        $lessonsnode = $file->lessons;
        if(empty($lessonsnode))
            $errors['dataerrors'][] = JText::_("Lesson information is completely missing.");
        else
        {
            foreach($lessonsnode->children() as $lesson)
            {
                $id = (string)$lesson[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more lessons are missing their id attributes.");
                    if(!in_array($error, $errors['dataerrors']))$errors['dataerrors'][] = $error;
                    unset($error);
                    continue;
                }
                $subjectid = (string)$lesson->lesson_subject[0]['id'];
                if(empty($subjectid))
                {
                    $errors['dataerrors'][] = JText::_("Lesson")." $id ".JText::_("does not have an associated subject.");
                    continue;
                }
                else if(empty($subjects[$subjectid]))
                {
                    $errors['dataerrors'][] = JText::_("Lesson")." $id ".JText::_("references the missing or incomplete subject")." $subjectid.";
                    continue;
                }
                else $name = $subjects[$subjectid]['longname'];
                $lerrorstart = JText::_("Lesson")." $name ($id) ";
                $teacherid = (string)$lesson->lesson_teacher[0]['id'];
                if(empty($teacherid))
                    $errors['dataerrors'][] = $lerrorstart.JText::_("does not have an associated teacher.");
                else if(empty($teachers[$teacherid]))
                {
                    $errors['dataerrors'][] = $lerrorstart.JText::_("references the missing or incomplete teacher")." $teacherid.";
                    continue;
                }
                $classids = (string)$lesson->lesson_classes[0]['id'];
                if(empty($classids))
                    $errors['dataerrors'][] = $lerrorstart.JText::_("does not have any associated classes(semesters).");
                else
                {
                    $classids = explode(" ", $classids);
                    foreach($classids as $classid)
                    {
                        if(!in_array($classid, $classes))
                            $errors['dataerrors'][] = $lerrorstart.JText::_("references the missing or incomplete class")." $classid.";
                    }
                }
                $lessontype = $lesson->text1;
                if(empty($lessontype))
                    $errors['dataerrors'][] = $lerrorstart.JText::_("does not have a type.");
                $periods = trim($lesson->periods);
                if(empty($periods))
                    $errors['dataerrors'][] = $lerrorstart.JText::_("does not have a periods attribute.");
                $times = $lesson->times;
                $timescount = count($times->children());
                if(isset($periods) and $periods != $timescount)
                    $errors['dataerrors'][] = $lerrorstart.JText::_("allocates")." $periods ".JText::_("instances").", $times ".JText::_("were found");
                foreach($times->children() as $instance)
                {
                    $day = (string)$instance->assigned_day;
                    if(empty($day))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which does not have a day attribute.");
                        if(!in_array($error, $errors['dataerrors']))$errors['dataerrors'][] = $error;
                        unset($error);
                    }
                    $period = (string)$instance->assigned_period;
                    if(empty($period))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which does not have a period attribute.");
                        if(!in_array($error, $errors['dataerrors']))$errors['dataerrors'][] = $error;
                        unset($error);
                    }
                    if(isset($day) and isset($period) and empty($timeperiods[$day][$period]))
                        $errors['dataerrors'][] =
                            $lerrorstart.JText::_("contains a time period which is missing or incomplete. Day:")." $day ".JText::_("Period:")." $period";
                    $roomid = (string)$instance->assigned_room[0]['id'];
                    if(empty($roomid))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which does not have a room attribute.");
                        if(!in_array($error, $errors['dataerrors']))$errors['dataerrors'][] = $error;
                        unset($error);
                    }
                    else if(!in_array($roomid, $rooms))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which references the missing or incomplete room")." $roomid.";
                        if(!in_array($error, $errors['dataerrors']))$errors['dataerrors'][] = $error;
                        unset($error);
                    }
                    unset($day, $period, $roomid);
                }
                unset($id, $subjectid, $name, $lerrorstart, $teacherid, $classids, $lessontype, $periods, $times);
            }
        }
        unset($lessonsnode);
    }


    /**
     * public funtion activate
     *
     * earmarks an gp-untis schedule as being active for the given planning period
     */
    public function activate()
    {
        $this->delta();
    }

    /**
     * private funtion delta
     *
     * creates a change set between the currently active schedule and the schedule to
     * become active, and saves this data as a json string in the structure used by
     * the scheduler rich internet application
     */
    private function delta()
    {
        return true;
    }


    /**
    * public function deactivate
    *
    * sets the current active schedule to inactive. this entails the deletion
    * of the delta, and the removal of schedule specific data from the db.
    */
    public function deactivate($id = null)
    {
        $semesterID = JRequest::getVar('semesterID');

        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->update("#__thm_organizer_schedules");
        //set active to false
        $query = "UPDATE #__giessen_scheduler_schedules SET active = NULL WHERE active IS NOT NULL AND sid = '$sid';";
        $dbo->setQuery( $query );
        $dbo->query();

        $query = "DELETE FROM #__giessen_scheduler_user_schedules WHERE username = 'delta' AND sid = '$sid';";
        $dbo->setQuery( $query );
        $dbo->query();//no error check there may be no delta in the db


        //remove active data
        $query = "DELETE FROM #__giessen_scheduler_objects WHERE sid = '$sid';";
        $dbo->setQuery($query);
        $dbo->query();
        $query = "DELETE FROM #__giessen_scheduler_lessons WHERE sid = '$sid';";
        $dbo->setQuery($query);
        $dbo->query();
        $query = "DELETE FROM #__giessen_scheduler_lessonperiods WHERE sid = '$sid';";
        $dbo->setQuery($query);
        $dbo->query();
        $query = "DELETE FROM #__giessen_scheduler_timeperiods WHERE sid = '$sid';";
        $dbo->setQuery($query);
        $dbo->query();

        $this->setRedirect($link, JText::_("Die Datei wurde inaktive gestellt").".");
    }

    /**
     * public function delete
     *
     * removes the selected schedules
     */
    public function delete()
    {
        $scheduleIDs = JRequest::getVar('cid', array(), 'post', 'array');

 	$dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('id');
        $query->from("#__thm_organizer_schedules");
        $query->where("id IN ( '".implode("', '", $scheduleIDs)."' )");
        $query->where("active != NULL");
        $dbo->setQuery((string) $query);
        $activeIDs = $dbo->loadResultArray();
        unset($query);

        if(!empty($activeIDs))
            foreach($activeIDS as $active)
                $this->deactivate ($active);

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_schedules");
        $query->where("id IN ( '".implode("', '", $scheduleIDs)."' )");
        $dbo->setQuery((string)$query);
        $dbo->query();

        if ($dbo->getErrorNum())return false;
        else return true;
    }

    /**
     * public function updateText
     *
     * Adds or updates the description of the schedule.
     */
    function updateText()
    {
        $semesterID = JRequest::getVar('semesterID');
        $scheduleID = JRequest::getVar('schedule_id');
        $description = JRequest::getVar('description');

        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->update("#__thm_organizer_schedules");
        $query->set("description = '$description'");
        $query->where("id = '$id'");
        $dbo->setQuery((string)$query);
        $dbo->query();

        if ($dbo->getErrorNum())return false;
        else return $semesterID;
    }
}
?>
