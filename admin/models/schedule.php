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
    public function uploadGPUntis()
    {
        $fileName = $_FILES['file']['name'];

        $tmpName  = $_FILES['file']['tmp_name'];
        $schedule = simplexml_load_file($tmpName);
        $result = $this->validateGPUntis(&$schedule);

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
        if ($dbo->getErrorNum())return false;
        else return $result;
    }

    /**
     * public funtion validate
     *
     * checks a given schedule in gp-untis xml format for data completeness and consistency
     */
    public function validateGPUntis(&$file)
    {
        $erray = array();
        $creationdate = (string)$file[0]['date'];
        if(empty($creationdate))
        {
            $error = JText::_("Document creation date missing.");
            if(!in_array($erray,$error))$erray[] = $error;
            unset($error);
        }
        $startdate = (string)$file->general->schoolyearbegindate;
        if(empty($startdate)) $erray[] = JText::_("Schedule startdate is missing.");
        $enddate = (string)$file->general->schoolyearenddate;
        if(empty($enddate)) $erray[] = JText::_("Schedule enddate is missing.");
        $header1 = (string)$file->general->header1;
        if(empty($header1))
            $erray[] = JText::_("Creating department information is missing.");
        else
        {
            $details = explode(",", $header1);
            if(count($details) < 3)  $erray[] = JText::_("Header is missing information (institution/campus/department).");
        }

        $timeperiods = array();
        $timeperiodsnode = $file->timeperiods;
        if(empty($timeperiodsnode))
            $erray[] = JText::_("Time period information is completely missing.");
        else
        {
            foreach( $timeperiodsnode->children() as $timeperiod )
            {
                $id = (string)$timeperiod[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more timeperiods are missing their id attribute.");
                    if(!in_array($error, $erray)) $erray[] = $error;
                    unset($error);
                    continue;
                }
                $day = (string)$timeperiod->day;
                if(empty($day))
                {
                    $erray[] = JText::_("Timeperiod")." $id ".JText::_("does not have a day.");
                    continue;
                }
                $period = (string)$timeperiod->period;
                if(empty($period))
                {
                    $erray[] = JText::_("Timeperiod")." $id ".JText::_("does not have a period.");
                    continue;
                }
                $timeperiods[$day][$period]['id'] = $id;
                $starttime = (string)$timeperiod->starttime;
                if(empty($starttime))
                    $erray[] = JText::_("Timeperiod")." $id ".JText::_("does not have a starttime.");
                else $timeperiods[$day][$period]['starttime'] = $starttime;
                $endtime = (string)$timeperiod->endtime;
                if(empty($endtime))
                    $erray[] = JText::_("Timeperiod")." $id ".JText::_("does not have an endtime.");
                else $timeperiods[$day][$period]['endtime'] = $endtime;
                unset($id, $day, $period, $starttime, $endtime);
            }
        }
        unset($timeperiodsnode);

        $descriptions = array();
        $descriptionsnode = $file->descriptions;
        if(empty($descriptionsnode))
            $erray[] = JText::_("Room description information is completely missing.");
        else
        {
            foreach($descriptionsnode->children() as $description)
            {
                $id = (string)$description[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more descriptions are missing their id attribute.");
                    if(!in_array($error, $erray)) $erray[] = $error;
                    unset($error);
                    continue;
                }
                $name = (string)$description->longname;
                if(empty($name))
                {
                    $erray[] =
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
            $erray[] = JText::_("Department information is completely missing.");
        else
        {
            foreach($departmentsnode->children() as $dptnode)
            {
                $id = (string)$dptnode[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more departments are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $details = explode(",",(string)$dptnode->longname);
                if(count($details) < 3)
                {
                    $erray[] = JText::_("Department")." $id ".JText::_("does not have all its required information.");
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
            $erray[] = JText::_("Room information is completely missing.");
        else
        {
            foreach($roomsnode->children() as $room)
            {
                $id = (string)$room[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more rooms are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $name = str_replace("RM_","",$id);
                $rooms[$id]['name'] = $name;
                $longname = trim($room->longname);
                if(empty($longname))
                    $erray[] = JText::_("Room")." $name ($id) ".JText::_("does not have a longname.");
                else $rooms[$id]['longname'] = $longname;
                $capacity = trim($room->capacity);
                if(!empty($capacity)) $rooms[$id]['capacity'] = $capacity;
                $descid = trim($room->room_description[0]['id']);
                if(empty($descid))
                    $erray[] = JText::_("Room")." $name ($id) ".JText::_("does not reference a description.");
                else if(empty($descriptions[$descid]))
                    $erray[] = JText::_("Room")." $name ($id) ".JText::_("references the missing or incomplete description")." $descid.";
                else $rooms[$id]['description'] = $descriptions[$descid];
                $dptid = trim($room->room_department[0]['id']);
                if(empty($dptid))
                    $erray[] = JText::_("Room")." $name ($id) ".JText::_("does not reference a department.");
                else if(empty($departments[$dptid]) or count($departments[$dptid]) < 3)
                    $erray[] =
                        JText::_("Room")." $name ($id) ".JText::_("references the missing or incomplete department")." $dptid.";
                else $rooms['department'] = $dptid;
                unset($id, $longname, $capacity, $descid, $dptid);
            }
        }
        unset($roomsnode, $descriptions);

        $subjects = array();
        $subjectsnode = $file->subjects;
        if(empty($subjectsnode))
            $erray[] = JText::_("Subject information is completely missing.");
        else
        {
            foreach($subjectsnode->children() as $subject)
            {
                $id = (string)$subject[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more subjects are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $longname = trim($subject->longname);
                if(empty($longname))
                {
                    $erray[] = JText::_("Subject")." $id ".JText::_("does not have a longname.");
                    continue;
                }
                else $subjects[$id]['longname'] = $longname;
                $subjectgroup = trim($subject->subjectgroup);
                if(!empty($subjectgroup)) $subjects[$id]['subjectgroup'] = $subjectgroup;
                unset($id, $longname, $subjectgroup);
            }
        }
        unset($subjectsnode);

        $teachers = array();
        $teachersnode = $file->teachers;
        if(empty($teachersnode))
            $erray[] = JText::_("Teacher information is completely missing.");
        else
        {
            foreach($teachersnode->children() as $teacher)
            {
                $id = (string)$teacher[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more teachers are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $surname = trim((string)$teacher->surname);
                if(empty($surname))
                {
                    $erray[] = JText::_("Teacher")." $id ".JText::_("does not have a surname.");
                    continue;
                }
                else $teachers[$id]['surname'] = $surname;
                $userid = trim((string)$teacher->payrollnumber);
                if(!empty($userid)) $teachers[$id]['userid'] = $userid;
//                if(empty($userid))
//                    $erray[] = JText::_("Teacher")." $surname ($id) ".JText::_("does not have a username(payrollnumber).");
//                else $teachers[$id]['userid'] = $userid;
                $dptid = trim((string)$teacher->teacher_department[0]['id']);
                if(empty($dptid))
                    $erray[] = JText::_("Teacher")." $surname ($id) ".JText::_("does not reference a department.");
                else if(empty($departments[$dptid]) or count($departments[$dptid]) < 3)
                    $erray[] =
                        JText::_("Teacher")." $surname ($id) ".JText::_("references the missing or incomplete department")." $dptid.";
                else $teachers[$id]['department'] = $dptid;
                unset($id, $surname, $userid, $dptid);
            }
        }
        unset($teachersnode);

        $classes = array();
        $classesnode = $file->classes;
        if(empty($classesnode))
            $erray[] = JText::_("Class(Semester) information is completely missing.");
        else
        {
            foreach($classesnode->children() as $class)
            {
                $id = (string)$class[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more classes(semesters) are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $longname = trim($class->longname);
                if(empty($longname))
                {
                    $erray[] = JText::_("Class")." $id ".JText::_("does not have a longname.");
                    continue;
                }
                else
                {
                    $details = explode(",", $longname);
                    if(count($details) < 2)
                        $erray[] = JText::_("The longname attribute of class")." $id ".JText::_("is missing information.");
                    else
                    {
                        $classes[$id]['major'] = $details[0];
                        $classes[$id]['semester'] = $details[1];
                    }
                }
                $teacherid = trim((string)$class->class_teacher[0]['id']);
                if(empty($teacherid))
                    $erray[] = JText::_("Class")." $longname ($id) ".JText::_("does not reference a teacher.");
                else if(empty($teachers[$teacherid]) or count($teachers[$teacherid]) < 3)
                    $erray[] = JText::_("Class")." $longname ($id) ".JText::_("references the missing or incomplete teacher")." $teacherid.";
                else $classes[$id]['teacher'] = $teacherid;
                unset($id, $longname, $teacherid);
            }
        }
        unset($classesnode);

        $lessonsnode = $file->lessons;
        if(empty($lessonsnode))
            $erray[] = JText::_("Lesson information is completely missing.");
        else
        {
            foreach($lessonsnode->children() as $lesson)
            {
                $id = (string)$lesson[0]['id'];
                if(empty($id))
                {
                    $error = JText::_("One or more lessons are missing their id attributes.");
                    if(!in_array($error, $erray))$erray[] = $error;
                    unset($error);
                    continue;
                }
                $subjectid = (string)$lesson->lesson_subject[0]['id'];
                if(empty($subjectid))
                {
                    $erray[] = JText::_("Lesson")." $id ".JText::_("does not have an associated subject.");
                    continue;
                }
                else if(empty($subjects[$subjectid]))
                {
                    $erray[] = JText::_("Lesson")." $id ".JText::_("references the missing or incomplete subject")." $subjectid.";
                    continue;
                }
                else $name = $subjects[$subjectid]['longname'];
                $lerrorstart = JText::_("Lesson")." $name ($id) ";
                $teacherid = (string)$lesson->lesson_teacher[0]['id'];
                if(empty($teacherid))
                    $erray[] = $lerrorstart.JText::_("does not have an associated teacher.");
                else if(empty($teachers[$teacherid]))
                {
                    $erray[] = $lerrorstart.JText::_("references the missing or incomplete teacher")." $teacherid.";
                    continue;
                }
                $classids = (string)$lesson->lesson_classes[0]['id'];
                if(empty($classids))
                    $erray[] = $lerrorstart.JText::_("does not have any associated classes(semesters).");
                else
                {
                    $classids = explode(" ", $classids);
                    foreach($classids as $classid)
                    {
                        if(!key_exists($classid, $classes))
                            $erray[] = $lerrorstart.JText::_("references the missing or incomplete class")." $classid.";
                    }
                }
                $lessontype = $lesson->text1;
                if(empty($lessontype))
                    $erray[] = $lerrorstart.JText::_("does not have a type.");
                $periods = trim($lesson->periods);
                if(empty($periods))
                    $erray[] = $lerrorstart.JText::_("does not have a periods attribute.");
                $times = $lesson->times;
                $timescount = count($times->children());
                if(isset($periods) and $periods != $timescount)
                    $erray[] = $lerrorstart.JText::_("allocates")." $periods ".JText::_("instances").", $times ".JText::_("were found");
                foreach($times->children() as $instance)
                {
                    $day = (string)$instance->assigned_day;
                    if(empty($day))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which does not have a day attribute.");
                        if(!in_array($error, $erray))$erray[] = $error;
                        unset($error);
                    }
                    $period = (string)$instance->assigned_period;
                    if(empty($period))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which does not have a period attribute.");
                        if(!in_array($error, $erray))$erray[] = $error;
                        unset($error);
                    }
                    if(isset($day) and isset($period) and empty($timeperiods[$day][$period]))
                        $erray[] =
                            $lerrorstart.JText::_("contains a time period which is missing or incomplete. Day:")." $day ".JText::_("Period:")." $period";
                    $roomid = (string)$instance->assigned_room[0]['id'];
                    if(empty($roomid))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which does not have a room attribute.");
                        if(!in_array($error, $erray))$erray[] = $error;
                        unset($error);
                    }
                    else if(!key_exists($roomid, $rooms))
                    {
                        $error = $lerrorstart.JText::_("contains a time period which references the missing or incomplete room")." $roomid.";
                        if(!in_array($error, $erray))$erray[] = $error;
                        unset($error);
                    }
                    unset($day, $period, $roomid);
                }
                unset($id, $subjectid, $name, $lerrorstart, $teacherid, $classids, $lessontype, $periods, $times);
            }
        }
        unset($lessonsnode);
        $errors = "";
        foreach($erray as $k => $v)
        {
            if($v == "") unset($erray[$k]);
            else $errors .= "<br />".$v;
        }
        if( $errors != "") return $errors;
        else return true;
    }


    /**
     * public funtion activate
     *
     * earmarks an gp-untis schedule as being active for the given planning period
     */
    public function activate()
    {
        $dbo = & JFactory::getDBO();
        $semesterID = JRequest::getVar('semesterID');
        $id = JRequest::getVar('schedule_id');

        //load the schedule to be activated from the file in the database
        $query = "SELECT file, filename FROM #__thm_organizer_schedules WHERE id = '$id'";
        $dbo->setQuery( $query );
        $result = $dbo->query();
        if ($dbo->getErrorNum())
        {
            $this->setRedirect($link, JText::_("Ein Fehler ist aufgetreten."), 'error'  );
        }
        list($file, $to) = mysql_fetch_array($result);

        //create php structures from the xml structures
        if($file)
        {
                $query = "SELECT filename
                                  FROM #__thm_organizer_schedules
                                  WHERE active IS NOT NULL
                                        AND sid = '$sid'";
                $dbo->setQuery( $query );
                $from = $dbo->loadResult();
                if($from)
                {

                        $oldlessons = array();
                        $query = "SELECT l.lid, o.oname AS name, o.oalias AS description
                                          FROM #__thm_organizer_lessons AS l
                                                INNER JOIN #__thm_organizer_objects AS o
                                                        ON l.lid = o.oid
                                          WHERE l.sid = '$sid'
                                                AND o.sid = '$sid'";
                        $dbo->setQuery($query);
                        $lids = $dbo->loadAssocList();
                        foreach($lids as $lk => $lv)
                        {
                                $oldlessons[$lv['lid']]['name'] = $lv['name'];
                                $oldlessons[$lv['lid']]['desc'] = $lv['description'];
                                //classes are independant of the implementing periods
                                $query = "SELECT cid
                                          FROM #__thm_organizer_lessons
                                          WHERE lid = '".$lv['lid']."'
                                                AND sid = '$sid'";
                                $dbo->setQuery($query);
                                $cids = $dbo->loadAssocList();
                                foreach($cids as $ck => $cv)
                                {
                                        $oldlessons[$lv['lid']]['classes'][$cv['cid']] = $cv['cid'];
                                }
                                //teachers and rooms are dependant on the implementing periods
                                //timeperiod data is checked here because is otherwise impossible to display time changes
                                //if the timeperiod stays the same
                                $query = "SELECT lp.rid, lp.tid, tp.tpid, tp.day,
                                                        tp.period, tp.starttime, tp.endtime
                                                  FROM #__thm_organizer_lessonperiods AS lp
                                                        INNER JOIN #__thm_organizer_timeperiods as tp
                                                                ON lp.tpid = tp.tpid
                                                  WHERE lid = '".$lv['lid']."'
                                                        AND lp.sid = '$sid'
                                                        AND tp.sid = '$sid'";
                                $dbo->setQuery($query);
                                $lpdata = $dbo->loadAssocList();
                                foreach($lpdata as $lpdk => $lpdv)
                                {
                                        if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['day'])
                                                $oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['day'] = $lpdv['day'];
                                        if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['period'])
                                                $oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['period'] = $lpdv['period'];
                                        if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['startime'])
                                                $oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['starttime'] = $lpdv['starttime'];
                                        if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['endtime'])
                                                $oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['endtime'] = $lpdv['endtime'];
                                        if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['rooms'][$lpdv['rid']])
                                                $oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['rooms'][$lpdv['rid']] = $lpdv['rid'];
                                        if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['teachers'][$lpdv['tid']])
                                                $oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['teachers'][$lpdv['tid']] = $lpdv['tid'];

                                }
                        }
                        //$dptdump = print_r($oldlessons, true);

                        //remove active data
                        $query = "DELETE FROM #__thm_organizer_objects WHERE sid = '$sid';";
                        $dbo->setQuery($query);
                        $dbo->query();
                        $query = "DELETE FROM #__thm_organizer_lessons WHERE sid = '$sid';";
                        $dbo->setQuery($query);
                        $dbo->query();
                        $query = "DELETE FROM #__thm_organizer_lessonperiods WHERE sid = '$sid';";
                        $dbo->setQuery($query);
                        $dbo->query();
                        $query = "DELETE FROM #__thm_organizer_timeperiods WHERE sid = '$sid';";
                        $dbo->setQuery($query);
                        $dbo->query();
                }

                //arrays to contain the lists of resources from the schedule
                $descriptions = array(); $departments = array();
                $timeperiods = array(); $classes = array();
                $lessonclasses = array(); $subjects = array();
                $subjectobjects = array(); $teachers = array();
                $lessons = array(); $rooms = array();

                $dDoc = new DOMDocument();
                $dDoc->loadXML($file);
                $document = $dDoc->documentElement;

                //descriptions are used to "type" a room
                $descriptionnodes = $document->getElementsByTagName( "description" );
                if($descriptionnodes)
                        foreach( $descriptionnodes as $description )
                        {
                            $descid = trim($description->getAttribute("id"));
                            foreach($description->getElementsByTagName("longname") as $longname)
                            {
                                $desc = trim($longname->textContent);
                            }
                            $descriptions[$descid] = $desc;
                            unset( $longname);
                        }
                unset($description, $descriptionnodes);

                //collects data specific to departments
                //departments are used to assign an org. unit to installations
                //or dept(curricula) to teachers
                $departmentnodes = $document->getElementsByTagName( "department" );
                if(count($departmentnodes) > 0)
                        foreach( $departmentnodes as $department )
                        {
                            $deptid = trim($department->getAttribute("id"));
                            foreach($department->getElementsByTagName("longname") as $longname)
                            {
                                $details = explode(',', $longname->textContent);
                                $departments[$deptid]['school'] = trim($details [0]);
                                $departments[$deptid]['campus'] = trim($details [1]);
                                $departments[$deptid]['department'] = trim($details [2]);
                                $departments[$deptid]['curriculum'] = trim($details [3]);
                            }
                                unset($longname);
                        }
                unset($department, $departmentnodes);

                //collects data specific to time periods
                $timeperiodnodes = $document->getElementsByTagName( "timeperiod" );
                foreach( $timeperiodnodes as $timeperiod )
                {
                    $tpid = trim($timeperiod->getAttribute("id"));
                    foreach($timeperiod->getElementsByTagName("day") as $eday)
                    {
                        $day = trim($eday->textContent);
                    }
                    foreach($timeperiod->getElementsByTagName("period") as $eperiod)
                    {
                        $period = trim($eperiod->textContent);
                    }
                    foreach($timeperiod->getElementsByTagName("starttime") as $estarttime)
                    {
                        $tstarttime = trim($estarttime->textContent);
                        $starttime = substr($tstarttime, 0, 2).":".substr($tstarttime, 2, 2).":00";
                    }
                    foreach($timeperiod->getElementsByTagName("endtime") as $eendtime)
                    {
                        $tendtime = trim($eendtime->textContent);
                        $endtime = substr($tendtime, 0, 2).":".substr($tendtime, 2, 2).":00";
                    }
                    unset($eday, $eperiod, $estarttime, $eendtime);
                    $starttime = $starttime;
                    $endtime = $endtime;
                    $timeperiods[$day][$period] = $tpid;
                    $timeperiods[$tpid]['tpid']= $tpid;
                    $timeperiods[$tpid]['day']= $day;
                    $timeperiods[$tpid]['period']= $period;
                    $timeperiods[$tpid]['starttime']= $starttime;
                    $timeperiods[$tpid]['endtime']= $endtime;

                    $query = "INSERT IGNORE INTO #__thm_organizer_timeperiods
                                                                                (tpid, day, period, starttime, endtime, sid)
                                                                         VALUES('$tpid', '$day', '$period', '$starttime', '$endtime', '$sid');";
                    $dbo->setQuery($query);
                    $dbo->query();
                }
                unset($tpid, $timeperiod, $timeperiodnodes);

                //subjects are abstract guidelines for lessons
                //lessons implement subjects and carry their names
                $subjectnodes = $document->getElementsByTagName( "subject" );
                foreach( $subjectnodes as $subject )
                {
                    $suid = trim($subject->getAttribute("id"));
                        $subjects[$suid]['id'] = $suid;
                    foreach($subject->getElementsByTagName("longname") as $longname)
                    {
                        $subjects[$suid]['name'] = trim($longname->textContent);
                    }
                    foreach($subject->getElementsByTagName("subjectgroup") as $sge)
                    {
                        $subjects[$suid]['module'] = trim($sge->textContent);
                    }
                        unset($longname, $sge);
                }
                unset($subject, $subjectnodes);

                $teachernodes = $document->getElementsByTagName( "teacher" );
                foreach( $teachernodes as $teacher )
                {
                    $oid = trim($teacher->getAttribute("id"));
                        $teachers[$tid]['id'] = $oid;
                    foreach($teacher->getElementsByTagName("surname") as $surname)
                    {
                        $oname = trim($surname->textContent);
                    }
                    $payrollnumbers = $teacher->getElementsByTagName("payrollnumber");
                    if($payrollnumbers)
                            foreach($payrollnumbers as $prn)
                            {
                                $manager = trim($prn->textContent);
                            }
                    foreach($teacher->getElementsByTagName("teacher_department") as $td)
                    {
                        $dept = trim($departments[$td->getAttribute("id")]['curriculum']);
                    }
                        unset($surname, $prn, $td);
                $teachers[$oid]['oid'] = $oid;
                $teachers[$oid]['oname'] = $oname;
                $teachers[$oid]['manager'] = $prno;
                $teachers[$oid]['department'] = $dept;
                    $query = "INSERT IGNORE INTO #__thm_organizer_objects
                                        (oid, oname, otype, manager,  sid)
                                  VALUES('$oid', '$oname', 'teacher', '$manager', '0');";
                    $dbo->setQuery($query);
                    $dbo->query();
                    $query = "INSERT IGNORE INTO #__thm_organizer_teachers
                                        (tid, department)
                                  VALUES('$oid', '$dept');";
                    $dbo->setQuery($query);
                    $lastquery;
                    $dbo->query();
                }
                unset($subject, $subjectnodes);

                //classes are majors divided among their semesters
                //exceptions being other departments using rooms under the management of IT dept
                $classnodes = $document->getElementsByTagName( "class" );
                foreach( $classnodes as $class )
                {
                    $oid = $class->getAttribute("id");
                    $oname = str_replace("CL_", "", $oid);
                    foreach($class->getElementsByTagName("longname") as $ln)
                    {
                        $oalias = trim($ln->textContent);
                        $parts = explode(',', $oalias);
                        $department = trim($parts[0]);
                        $semester = trim($parts[1]);
                    }
                    foreach($class->getElementsByTagName("class_teacher") as $ct)
                    {
                        $tid = trim($ct->textContenttrim);
                    }
                        unset($ln, $ct);
                    $classes[$oid]['oid'] = $oid;
                    $classes[$oid]['oname'] = $oname;
                    $classes[$oid]['oalias'] = $oalias;
                    $manager = $teachers[$tid]['manager'];
                    $classes[$oid]['manager'] = $manager;
                    $classes[$oid]['department'] = $department;
                    $classes[$oid]['semester'] = $semester;
                    $query = "INSERT IGNORE INTO #__thm_organizer_objects
                                        (oid, oname, oalias, otype, manager, sid)
                                  VALUES('$oid', '$oname', '$oalias', 'class', '$manager', '0');";
                    $dbo->setQuery($query);
                    $dbo->query();
                    $query = "INSERT IGNORE INTO #__thm_organizer_classes
                                        (cid, department, semester)
                                  VALUES('$oid', '$department', '$semester');";
                    $dbo->setQuery($query);
                    $dbo->query();
                }
                unset($classnodes, $class);

                $roomnodes = $document->getElementsByTagName( "room" );
                foreach( $roomnodes as $room )
                {
                    $oid = trim($room->getAttribute("id"));
                    $oname = str_replace("RM_","",$oid);
                    foreach($room->getElementsByTagName("longname") as $longname)
                    {
                        $oalias = trim($longname->textContent);
                    }
                    foreach($room->getElementsByTagName("capacity") as $cap)
                    {
                        $capacity = trim($cap->textContent);
                    }
                    foreach($room->getElementsByTagName("room_description") as $rdesc)
                    {
                        $rtype = $descriptions[trim($rdesc->getAttribute("id"))];
                    }
                    foreach($room->getElementsByTagName("room_department") as $rdept)
                    {
                        $department = $departments[trim($rdept->getAttribute("id"))]['department'];
                    }
                    unset($longname, $cap, $rdesc, $rdept);
                    $rooms[$oid]['oid'] = $oid;
                    $rooms[$oid]['oname'] = $oname;
                    $rooms[$oid]['oalias'] = $oalias;
                    $rooms[$oid]['capacity'] = $capacity;
                    $rooms[$oid]['rtype'] = $rtype;
                    $rooms[$oid]['department'] = $department;
                    $query = "INSERT IGNORE INTO #__thm_organizer_objects
                                        (oid, oname, otype, oalias, sid)
                                  VALUES('$oid', '$oname', 'room', '$oalias', '0');";
                    $dbo->setQuery($query);
                    $dbo->query();
                    $query = "INSERT IGNORE INTO #__thm_organizer_rooms
                                        (rid, capacity, rtype, department)
                                  VALUES('$oid', '$capacity', '$rtype', '$department');";
                    $dbo->setQuery($query);
                    $dbo->query();
                }
                unset($roomnodes, $room);

                $lessonnodes = $document->getElementsByTagName( "lesson" );
                foreach( $lessonnodes as $lesson )
                {
                        $oid = $suid = $lessontype = $oname = $oalias = null;
                        $oid = substr($lesson->getAttribute("id"), 0, strlen($lesson->getAttribute("id")) - 2);
                        $lessons[$oid]['oldid'] = $oldid;
                    foreach($lesson->getElementsByTagName("lesson_subject") as $subjectnl)
                    {
                        $suid= $subjectnl->getAttribute("id");
                    }
                    unset($subjectnl);
                    $lessons[$oid]['subjectid'] = $suid;
                $oname = $subjects[$suid]['name'];
                        foreach($lesson->getElementsByTagName("text1") as $t1)
                    {
                        $lessontype = $t1->textContent;
                    }
                    unset($t1);
                    if($lessontype != "V")//V(Vorlesung) does not need to be specially identified in the name
                        $oname = $oname."-".$lessontype;
                $lessons[$oid]['name'] = $oname;
                $oalias = $subjects[$suid]['module'];
                if(!$oalias) $oalias = "";
                $lessons[$oid]['desc'] = $oalias;

                //details common to all resources
                        $query = "INSERT INTO #__thm_organizer_objects (oid, oname, oalias, otype, sid)
                                                VALUES('$oid', '$oname', '$oalias', 'lesson', '$sid');";
                        $dbo->setQuery( $query );
                        $dbo->query();

                        if(!isset($lessoncount[$oid])) $lessoncount[$oid] = 0;
                        else $lessoncount[$oid] = $lessoncount[$oid] + 1;
                        //details specific to a lesson
                        foreach($lesson->getElementsByTagName("lesson_classes") as $classesnl)
                    {
                        $classids = $classesnl->getAttribute("id");
                            $tempclassidarray = explode(" ", $classids);
                            foreach($tempclassidarray as $tempclassid)
                            {
                                $lessons[$oid]['classes'][$tempclassid] = $tempclassid;
                            }
                    }
                    unset($classesnl);
                    foreach($lessons[$oid]['classes'] as $classid)
                {
                        $query = "INSERT INTO #__thm_organizer_lessons (lid, cid, ltype, sid)
                                                VALUES('$oid', '$classid', '$lessontype', '$sid');";
                                $dbo->setQuery( $query );
                                $dbo->query();
                }
                        //details specific to a lesson period
                        foreach($lesson->getElementsByTagName("lesson_teacher") as $teachernl)
                    {
                        $tid = $teachernl->getAttribute("id");
                    }
                    unset($teachernl);
                        foreach($lesson->getElementsByTagName("time") as $time)
                    {
                                foreach($time->getElementsByTagName("assigned_day") as $daynl)
                            {
                                $day = $daynl->textContent;
                            }
                                foreach($time->getElementsByTagName("assigned_period") as $periodnl)
                            {
                                $period = $periodnl->textContent;
                            }
                                foreach($time->getElementsByTagName("assigned_room") as $roomnl)
                            {
                                $rid = $roomnl->getAttribute("id");
                            }
                            $tpid = $timeperiods[$day][$period];
                            $lessons[$oid]['periods'][$tpid]['day'] = $day;
                            $lessons[$oid]['periods'][$tpid]['period'] = $period;
                            $lessons[$oid]['periods'][$tpid]['starttime'] = $timeperiods[$tpid]['starttime'];
                            $lessons[$oid]['periods'][$tpid]['endtime'] = $timeperiods[$tpid]['endtime'];
                            $lessons[$oid]['periods'][$tpid]['teachers'][$tid] = $tid;
                            $lessons[$oid]['periods'][$tpid]['rooms'][$rid] = $rid;
                            $query = "INSERT INTO #__thm_organizer_lessonperiods (lid, rid, tpid, tid, sid)
                                                        VALUES('$oid', '$rid', '$tpid', '$tid', '$sid');";
                                $dbo->setQuery( $query );
                                $dbo->query();
                            unset($rid, $day, $period, $tpid);
                            unset($lid);
                    }
                    unset($time);
                }
                unset($lesson, $lessonnodes, $dDoc);
        }

        //build the delta
        if($from)
        {
                $type = "cyclic";
                $predelta = array();
                $lessonsdelta = array();//holds a running count of movedto/movedfrom lessons
                foreach($lessons as $lessonkey => $lessonvalue)
                {
                        //if a lesson does not exist in the old plan than every period is new
                        if(!array_key_exists($lessonkey, $oldlessons))
                        {
                                foreach($lessons[$lessonkey]['periods'] as $lpkey => $lpvalue)
                                {
                                        $key = $lessonkey." ".$lpkey;
                                        $predelta[$key]['type'] = $type;
                                        $predelta[$key]['dow'] = $lessons[$lessonkey]['periods'][$lpkey]['day'];
                                        $predelta[$key]['block'] = $lessons[$lessonkey]['periods'][$lpkey]['period'];
                                        $predelta[$key]['clas'] = implode(" ", $lessons[$lessonkey]['classes']);
                                        $predelta[$key]['room'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['rooms']);
                                        $predelta[$key]['doz'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['teachers']);
                                        $predelta[$key]['key'] = $lessonkey." ".$lpkey;
                                        $predelta[$key]['name'] = $lessons[$lessonkey]['name'];
                                        $predelta[$key]['desc'] = $lessons[$lessonkey]['desc'];
                                        $predelta[$key]['css'] = 'new';
                                        unset($key);
                                }
                        }
                        //the lesson exists in both plans
                        else
                        {
                                $css = "";
                                $changes = array();
                                foreach($lessons[$lessonkey]['classes'] as $ck => $cv)
                                {
                                        //classes new to the lesson
                                        if(!array_key_exists($ck, $oldlessons[$lessonkey]['classes']))
                                        {
                                                if(!$changes['classes'][$ck])$changes['classes'][$ck] = "new";
                                                $css = "changed";
                                        }
                                }
                                foreach($oldlessons[$lessonkey]['classes'] as $ck => $cv)
                                {
                                        //classes removed from the lesson
                                        if(!array_key_exists($ck, $lessons[$lessonkey]['classes']))
                                        {
                                                if(!$changes['classes'][$ck])$changes['classes'][$ck] = "removed";
                                                $css = "changed";
                                        }
                                }
                                foreach($lessons[$lessonkey]['periods'] as $lpkey => $lpvalue)
                                {
                                        //if the time period does not exist, but the lesson does
                                        //than a period in the old plan was moved here
                                        if(!array_key_exists($lpkey, $oldlessons[$lessonkey]['periods']))
                                        {
                                                $key = $lessonkey." ".$lpkey;
                                                $predelta[$key]['type'] = $type;
                                                $predelta[$key]['dow'] = $lessons[$lessonkey]['periods'][$lpkey]['day'];
                                                $predelta[$key]['block'] = $lessons[$lessonkey]['periods'][$lpkey]['period'];
                                                $predelta[$key]['clas'] = implode(" ", $lessons[$lessonkey]['classes']);
                                                $predelta[$key]['room'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['rooms']);
                                                $predelta[$key]['doz'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['teachers']);
                                                $predelta[$key]['key'] = $key ;
                                                $predelta[$key]['name'] = $lessons[$lessonkey]['name'];
                                                $predelta[$key]['desc'] = $lessons[$lessonkey]['desc'];
                                                if($css == "changed")
                                                {
                                                        $predelta[$key]['changes'] = $changes;
                                                        $predelta[$key]['css'] = 'movedto '.$css;
                                                }
                                                else $predelta[$key]['css'] = 'movedto';
                                                if($lessonsdelta[$lessonkey]['movedto'])
                                                        $lessonsdelta[$lessonkey]['movedto'] = $lessonsdelta[$lessonkey]['movedto'] + 1;
                                                else $lessonsdelta[$lessonkey]['movedto'] = 1;
                                                unset($key);
                                        }
                                        //check if the data represented by the time period has changed
                                        //i.e. the timeperiod are the same, but the day, block, etc. have changed
                                        else
                                        {
                                                $oldperiod = $oldlessons[$lessonkey]['periods'][$lpkey];
                                                if($oldperiod['starttime'] != $lpvalue['starttime'])
                                                {
                                                        $css = "changed";
                                                        $changes['starttime'] = $oldperiod['starttime']." => ".$lpvalue['starttime'];
                                                }
                                                if($oldperiod['endtime'] != $lpvalue['endtime'])
                                                {
                                                        $css = "changed";
                                                        $changes['endtime'] = $oldperiod['endtime']." => ".$lpvalue['endtime'];
                                                }
                                                foreach($lessons[$lessonkey]['periods'][$lpkey]['teachers'] as $lptkey => $lptvalue)
                                                {
                                                        if(!array_key_exists($lptkey, $oldlessons[$lessonkey]['periods'][$lpkey]['teachers']))
                                                        {
                                                                $css = "changed";
                                                                $changes['teachers'][$lptkey] = "new";
                                                        }
                                                        else
                                                                unset($oldlessons[$lessonkey]['periods'][$lpkey]['teachers'][$lptkey]);
                                                }
                                                if(count($oldlessons[$lessonkey]['periods'][$lpkey]['teachers']) > 0)
                                                        foreach($oldlessons[$lessonkey]['periods'][$lpkey]['teachers'] as $optkey => $optvalue)
                                                        {
                                                                $css = "changed";
                                                                $changes['teachers'][$optkey] = "removed";
                                                        }
                                                foreach($lessons[$lessonkey]['periods'][$lpkey]['rooms'] as $lprkey => $lprvalue)
                                                {
                                                        if(!array_key_exists($lprkey, $oldlessons[$lessonkey]['periods'][$lpkey]['rooms']))
                                                        {
                                                                $css = "changed";
                                                                $changes['rooms'][$lprkey] = "new";
                                                        }
                                                        else
                                                                unset($oldlessons[$lessonkey]['periods'][$lpkey]['rooms'][$lprkey]);
                                                }
                                                if(count($oldlessons[$lessonkey]['periods'][$lpkey]['rooms']) > 0)
                                                        foreach($oldlessons[$lessonkey]['periods'][$lpkey]['rooms'] as $oprkey => $oprvalue)
                                                        {
                                                                $css = "changed";
                                                                $changes['rooms'][$oprkey] = "removed";
                                                        }
                                                if($css == "changed")
                                                {
                                                        $key = $lessonkey." ".$lpkey;
                                                        $predelta[$key]['type'] = $type;
                                                        $predelta[$key]['dow'] = $lessons[$lessonkey]['periods'][$lpkey]['day'];
                                                        $predelta[$key]['block'] = $lessons[$lessonkey]['periods'][$lpkey]['period'];
                                                        $predelta[$key]['clas'] = implode(" ", $lessons[$lessonkey]['classes']);
                                                        $predelta[$key]['room'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['rooms']);
                                                        $predelta[$key]['doz'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['teachers']);
                                                        $predelta[$key]['key'] = $key;
                                                        $predelta[$key]['name'] = $lessons[$lessonkey]['name'];
                                                        $predelta[$key]['desc'] = $lessons[$lessonkey]['desc'];
                                                        $predelta[$key]['css'] = 'changed';
                                                        $predelta[$key]['changes'] = $changes;
                                                        unset($key);
                                                }
                                                unset($oldlessons[$lessonkey]['periods'][$lpkey]);
                                        }
                                }
                                //periods that were not in the new plan have been moved
                                if(count($oldlessons[$lessonkey]['periods']) > 0)
                                {
                                        foreach($oldlessons[$lessonkey]['periods'] as $lpkey => $lpvalue)
                                        {
                                                $key = $lessonkey." ".$lpkey;
                                                $predelta[$key]['type'] = $type;
                                                $predelta[$key]['dow'] = $oldlessons[$lessonkey]['periods'][$lpkey]['day'];
                                                $predelta[$key]['block'] = $oldlessons[$lessonkey]['periods'][$lpkey]['period'];
                                                $predelta[$key]['clas'] = implode(" ", $oldlessons[$lessonkey]['classes']);
                                                $predelta[$key]['room'] = implode(" ", $oldlessons[$lessonkey]['periods'][$lpkey]['rooms']);
                                                $predelta[$key]['doz'] = implode(" ", $oldlessons[$lessonkey]['periods'][$lpkey]['teachers']);
                                                $predelta[$key]['key'] = $key;
                                                $predelta[$key]['name'] = $oldlessons[$lessonkey]['name'];
                                                $predelta[$key]['desc'] = $oldlessons[$lessonkey]['desc'];
                                                $predelta[$key]['css'] = 'movedfrom';
                                                if($lessonsdelta[$lessonkey]['movedfrom'])
                                                        $lessonsdelta[$lessonkey]['movedfrom'] = $lessonsdelta[$lessonkey]['movedfrom'] + 1;
                                                else $lessonsdelta[$lessonkey]['movedfrom'] = 1;
                                                unset($key);
                                        }
                                }
                                unset($oldlessons[$lessonkey], $changes);
                        }
                }
                unset($lessons);
                if(count($oldlessons) > 0)
                {
                        foreach($oldlessons as $olk => $olv)
                        {
                                foreach($olv['periods'] as $olpkey => $olpvalue)
                                {
                                        $key = $olk." ".$olpkey;
                                        $predelta[$key]['type'] = $type;
                                        $predelta[$key]['dow'] = $oldlessons[$olk]['periods'][$olpkey]['day'];
                                        $predelta[$key]['block'] = $oldlessons[$olk]['periods'][$olpkey]['period'];
                                        $predelta[$key]['clas'] = implode(" ", $oldlessons[$olk]['classes']);
                                        $predelta[$key]['room'] = implode(" ", $oldlessons[$olk]['periods'][$olpkey]['rooms']);
                                        $predelta[$key]['doz'] = implode(" ", $oldlessons[$olk]['periods'][$olpkey]['teachers']);
                                        $predelta[$key]['key'] = $olk." ".$olpkey;
                                        $predelta[$key]['name'] = $oldlessons[$olk]['name'];
                                        $predelta[$key]['desc'] = $oldlessons[$olk]['desc'];
                                        $predelta[$key]['css'] = 'removed';
                                        unset($key);
                                }
                        }
                }
        }

        //sometimes a lesson block is cancelled or added which leads to uneven numbers of movedfrom and movedto
        $discrepancies = array();
        foreach($lessonsdelta as $ldk => $ldv)
        {
                if(!$ldv['movedto'])$ldv['movedto'] = 0;
                if(!$ldv['movedfrom'])$lkv['movedfrom'] = 0;
                if($ldv['movedto'] > $ldv['movedfrom'])
                {
                        $count = $ldv['movedto'] - $ldv['movedfrom'];
                        $discrepancies[$ldk]['count'] = $count;
                        $discrepancies[$ldk]['delta'] = 'movedto';
                }
                else if($ldv['movedto'] < $ldv['movedfrom'])
                {
                        $count = $ldv['movedfrom'] - $ldv['movedto'];
                        $discrepancies[$ldk]['count'] = $count;
                        $discrepancies[$ldk]['delta'] = 'movedfrom';
                }
        }
        unset($lessonsdelta);
        foreach($discrepancies as $lessonkey => $lessondisc)
        {
                $ld = $lessondisc['delta'];
                foreach($predelta as $dk => $dv)
                {
                        $keyparts = explode(' ', $dk);
                        if($keyparts[0] == $lessonkey)
                        {
                                if($lessondisc['count'] > 0)
                                {
                                        if($dv['css'] == ($ld == 'movedto'))
                                        {
                                                $predelta[$dk]['css'] = 'new';
                                                $lessondisc['count'] = $lessondisc['count'] - 1;
                                        }
                                        if($dv['css'] == ($ld == 'movedfrom'))
                                        {
                                                $predelta[$dk]['css'] = 'removed';
                                                $lessondisc['count'] = $lessondisc['count'] - 1;
                                        }
                                }
                        }
                }
        }
        $index = 0;
        $delta = array();
        foreach($predelta as $pdk => $pdv)
        {
                $delta[$index] = $predelta[$pdk];
                $index++;
        }


        //json_encode does not handle umlaute properly
        $malformedjsondelta = json_encode($delta);
        $jsondelta = str_replace('\u00d6', 'Ö',
                                        str_replace('\u00f6', 'ö',
                                        str_replace('\u00c4', 'Ä',
                                        str_replace('\u00e4', 'ä',
                                        str_replace('\u00dc', 'Ü',
                                        str_replace('\u00fc', 'ü',
                                        str_replace('\u00df', 'ß', $malformedjsondelta)))))));

        //deletes old delta
        $query = "DELETE FROM #__thm_organizer_user_schedules WHERE username = 'delta'";
        $dbo->setQuery( $query );
        $dbo->query();

        //inserts new delta
        $currenttime = time();
        $query = "INSERT INTO #__thm_organizer_user_schedules (username, data, created)
        VALUES ('delta', '$jsondelta', '$currenttime')";
        $dbo->setQuery( $query );
        $dbo->query();
        if ($dbo->getErrorNum())
        {
                $this->setRedirect($link, JText::_("Ein Fehler ist aufgetreten."), 'error'  );
        }

        //save the most recent module catalogue
        //$this->buildModules($subjects);

        //set old active to false
        $query = "UPDATE #__thm_organizer_schedules SET active = NULL WHERE sid = '$sid';";
        $dbo->setQuery( $query );
        $dbo->query();

        $currentdate = date('Y-m-d H:i:s');
        //set new active to true
        $query = "UPDATE #__thm_organizer_schedules SET active = '$currentdate' WHERE id = '$id'";
        $dbo->setQuery( $query );
        $dbo->query();
        $link = JRoute::_('index.php?option=com_thm_organizer&view=schedulelist&semesterid='.$sid);
        $dump = print_r($dptdump, true);
        if ($dbo->getErrorNum())
        {
                        $this->setRedirect($link, JText::_("Ein Fehler ist aufgetreten."), 'error' );
        }
        else
        {
                if($from)
                {
                        $this->setRedirect($link, $return.JText::_('Der aktueller Stundenplan wurde ge&auml;ndert von ').$from.JText::_(' auf ').$to.JText::_(" ge&auml;ndert.") );
                }
            else $this->setRedirect($link, $return.JText::_('Der aktueller Stundenplan wurde auf ').$to.JText::_(" gesetzt." ));
        }
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
    function edit_comment()
    {
        $scheduleIDs = JRequest::getVar('cid', array(), 'post', 'array');
        if(!empty($scheduleIDs))
        {
            $dbo = & JFactory::getDBO();
            foreach($scheduleIDs as $scheduleID)
            {
                $description = JRequest::getString("description$scheduleID");
                if(isset($description))
                {
                    $query = $dbo->getQuery(true);
                    $query->update("#__thm_organizer_schedules");
                    $query->set("description = '$description'");
                    $query->where("id = '$scheduleID'");
                    $dbo->setQuery((string)$query);
                    $dbo->query();
                    unset($query, $description);
                }                
            }
        }
        if ($dbo->getErrorNum())return false;
        else return true;
    }
}
?>
