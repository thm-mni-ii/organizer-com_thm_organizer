<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        data abstraction and business logic class for xml schedules
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';
require_once JPATH_COMPONENT . '/models/module.php';
require_once JPATH_COMPONENT . '/models/department.php';
require_once JPATH_COMPONENT . '/models/description.php';
require_once JPATH_COMPONENT . '/models/period.php';
require_once JPATH_COMPONENT . '/models/room.php';
require_once JPATH_COMPONENT . '/models/subject.php';
require_once JPATH_COMPONENT . '/models/teacher.php';
require_once JPATH_COMPONENT . '/models/lesson.php';
/**
 * Class enapsulating data abstraction and business logic for xml schedules
 * generated by Untis software. 
 * 
 * @package  Admin
 * 
 * @since    2.5.4
 *  */
class thm_organizersModelschedule
{
    /**
     * saves a schedule in the database for later use
     *
     * @return   array  $status  ['success']    true on save, false on db error
     *                           ['schedule']   the schedule data coded in json
     *                           ['errors']     critical data inconsistencies
     *                           ['warnings']   minor data inconsistencies 
     */
    public function upload()
    {
        $status = $this->validate();
        if (isset($status['errors']))
        {
            return $status;
        }

        $data = array();
        $data['departmentname'] = $status['schedule']['departmentname'];
        $data['semestername'] = $status['schedule']['semestername'];
        $data['creationdate'] = $status['schedule']['creationdate'];
        $formdata = JRequest::getVar('jform', null, null, null, 4);
        $formdata['description'] = htmlspecialchars($formdata['description']);
        $data['description'] = $formdata['description'];
        $data['schedule'] = $status['schedule'];
        $data['startdate'] = $status['schedule']['startdate'];
        $data['enddate'] = $status['schedule']['enddate'];

        $table = JTable::getInstance('schedules', 'thm_organizerTable');
        $status['success'] = $table->save($data);
        return $status;
    }

    /**
     * validate
     *
     * checks a given schedule in gp-untis xml format for data completeness and
     * consistency
     *
     * @return $status array of strings listing inconsistancies empty if none
     *          were found
     */
    public function validate()
    {
        $xmlSchedule = simplexml_load_file($_FILES['file']['tmp_name']);
        $schedule    = array();
        $errors      = array();
        $warnings    = array();
        $resources   = array();

        // General node
        // Creation Date & Time
        $creationDate = trim((string) $xmlSchedule[0]['date']);
        if (empty($creationDate))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_SCH_CREATION_DATE_MISSING");
        }
        else
        {
            $schedule['creationdate'] = $creationDate;
        }
        $creationTime = trim((string) $xmlSchedule[0]['date']);
        if (empty($creationTime))
        {
            $warnings[] = JText::_("COM_THM_ORGANIZER_SCH_CREATION_TIME_MISSING");
        }
        else
        {
            $schedule['creationtime'] = $creationTime;
        }
        
        // Schoolyear dates
        $syStartDate = trim((string) $xmlSchedule->general->termbegindate);
        if (empty($syStartDate))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_SCH_START_DATE_MISSING");
        }
        else
        {
            $syStartDate = substr($syStartDate, 0, 4) . '-' . substr($syStartDate, 4, 2) . '-' . substr($syStartDate, 6, 2);
        }
        $syEndDate = trim((string) $xmlSchedule->general->termenddate);
        if (empty($syEndDate))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_SCH_END_DATE_MISSING");
        }
        else
        {
            $syEndDate = substr($syEndDate, 0, 4) . '-' . substr($syEndDate, 4, 2) . '-' . substr($syEndDate, 6, 2);
        }

        // Organizational Data
        $departmentname = trim((string) $xmlSchedule->general->header1);
        if (empty($departmentname))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_SCH_ORGANIZATION_MISSING");
        }
        else
        {
            $schedule['departmentname'] = $departmentname;
        }
        $planningperiod = trim((string) $xmlSchedule->general->header2);
        if (empty($planningperiod))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_SCH_SCHOOLYEARNAME_MISSING");
        }
        else
        {
            $schedule['planningperiod'] = $planningperiod;
        }

        // Term Start & Enddates
        $startDate = trim((string) $xmlSchedule->general->termbegindate);
        if (empty($startDate))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_SCH_START_DATE_MISSING");
        }
        else
        {
            $schedule['startdate'] = substr($startDate, 0, 4) . '-' . substr($startDate, 4, 2) . '-' . substr($startDate, 6, 2);
        }
        $endDate = trim((string) $xmlSchedule->general->termenddate);
        if (empty($endDate))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_SCH_END_DATE_MISSING");
        }
        else
        {
            $schedule['enddate'] = substr($endDate, 0, 4) . '-' . substr($endDate, 4, 2) . '-' . substr($endDate, 6, 2);
        }

        // Checks if term and schoolyear dates are consistent
        $syStartDate = strtotime($syStartDate);
        $syEndDate = strtotime($syEndDate);
        $termStartDT = strtotime($schedule['startdate']);
        $termEndDT = strtotime($schedule['enddate']);
        if ($termStartDT < $syStartDate OR $termEndDT > $syEndDate OR $termStartDT >= $termEndDT)
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_SCH_DATES_INCONSISTANT');
        }

        $periods = array();
        $periodsModel = new thm_organizersModelperiod;
        $periodsModel->validate($xmlSchedule->timeperiods, $periods, $errors);
        $resources['periods'] = $periods;
        unset($periodsModel);

        $descriptions = array();
        $descriptionsModel = new thm_organizersModeldescription;
        $descriptionsModel->validate($xmlSchedule->descriptions, $descriptions, $errors);
        $resources['descriptions'] = $descriptions;
        unset($descriptionsModel);

        // Departments node holds degree names
        $degrees = array();
        $degreesModel = new thm_organizersModeldepartment;
        $degreesModel->validate($xmlSchedule->departments, $degrees, $errors);
        $resources['degrees'] = $degrees;
        unset($degreesModel);

        $rooms = array();
        $roomsModel = new thm_organizersModelroom;
        $roomsModel->validate($xmlSchedule->rooms, $rooms, $errors, $warnings, $descriptions);
        $resources['rooms'] = $rooms;
        unset($roomsModel);

        $subjects = array();
        $subjectsModel = new thm_organizersModelsubject;
        $subjectsModel->validate($xmlSchedule->subjects, $subjects, $errors, $warnings, $descriptions);
        $resources['subjects'] = $subjects;
        unset($subjectsModel);

        $teachers = array();
        $teachersModel = new thm_organizersModelteacher;
        $teachersModel->validate($xmlSchedule->teachers, $teachers, $errors, $warnings, $descriptions);
        $resources['teachers'] = $teachers;
        unset($teachersModel);

        // Classes node holds information about modules
        $modules = array();
        $modulesModel = new thm_organizersModelmodule;
        $modulesModel->validate($xmlSchedule->classes, $modules, $errors, $warnings, $resources);
        $resources['modules'] = $modules;
        unset($modulesModel);

        $calendar = empty($errors)?
            $this->initializeCalendar($periods, $schedule['startdate'], $schedule['enddate'], $syStartDate, $syEndDate) : array();
        $lessons = array();
        $lessonsModel = new thm_organizersModellesson;
        $lessonsModel->validate($xmlSchedule->lessons, $lessons, $errors, $warnings, $resources, $calendar);
        unset($lessonsModel);

        $status = array();
        if (count($errors))
        {
            $status['errors'] = "<br />" . implode("<br />", $errors);
        }
        else
        {
            $roomDescriptions = array();
            $this->sortRoomDescriptions($rooms, $descriptions, $roomDescriptions);
        }
        if (count($warnings))
        {
            $status['warnings'] = "<br />" . implode("<br />", $warnings);
        }
        return $status;
    }

    /**
     * Creates an array with dates as indexes for the days of the given planning period
     * 
     * @param   array   &$periods     the periods as defined in the schedule
     * @param   string  $startdate    the date upon which the planning period begins
     * @param   string  $enddate      the date upon which the planning period ends
     * @param   string  $syStartDate  the date upon which the school year begins
     * @param   string  $syEndDate    the date upon which the school year ends
     * 
     * @return   array  $calendar  array containing indies for all of the days
     *                             and periods for a planning period
     *                             [<DATE 'Y-m-d'>][<PERIOD int(1)>] = array()
     */
    private function initializeCalendar(&$periods, $startdate, $enddate, $syStartDate, $syEndDate)
    {
       $calendar = array();
       $startDT = strtotime($startdate);
       $endDT = strtotime($enddate);
       
       // 86400 is the number of seconds in a day 24 * 60 * 60
       // Calculate the days between schoolyear start and term start
       $frontOffset = floor(($startDT - $syStartDate) / 86400);
       $calendar['offset'] = $frontOffset;
       
       // Calculate the schoolyear length
       $syLength = floor(($syEndDate - $syEndDate) / 86400);
       $calendar['sylength'] = $syLength;

       // Calculate the length off the planning period
       $termLength = floor(($endDT - $startDT) / 86400);
       $calendar['termlength'] = $termLength;
       
       for ($currentDT = $startDT; $currentDT <= $endDT; )
       {
           // Create an index for the date
           $currentDate = date('Y-m-d', $currentDT);
           $calendar[$currentDate] = array();

           // Add period indices
           $dow = date('w', $currentDT);
           foreach ($periods as $period)
           {
               if ($period['day'] == $dow)
               {
                   $calendar[$currentDate][$period['period']] = array();
               }
           }
           
           // Raise the iterator
           $currentDT = strtotime('+1 day', $currentDT);
       }
       return $calendar;
    }

    /**
     * Sorts room descriptions out of the descriptions
     * 
     * @param   array  &$rooms             the array containing room data
     * @param   array  &$descriptions      the array containing description data
     * @param   array  &$roomDescriptions  the array to hold room descriptions
     * 
     * @return void
     */
    private function sortRoomDescriptions(&$rooms, &$descriptions, &$roomDescriptions)
    {
        foreach ($rooms as $room)
        {
            if (isset($descriptions[$room['description']]) AND !isset($roomDescriptions[$room['description']]))
            {
                $roomDescriptions[$room['description']] = $descriptions[$room['description']];
                unset($descriptions[$room['description']]);
            }
        }
    }

    /**
     * activate
     *
     * creates a field entry (date) in the database marking a gp-untis schedule
     * as being active for the given planning period (semester)
     *
     * @param   JTable  &$schedule  the schedule row to be activated
     * @param   array   &$return    holdins status messages from individual
     *                             function calls
     * 
     * @return string on error
     */
    public function activate(&$schedule, &$return)
    {
        $dbo = JFactory::getDBO();
        $file = $schedule->file;
        $newScheduleName = $schedule->filename;
        $semesterID = $schedule->sid;

        $query = $dbo->getQuery(true);
        $query->select("CONCAT( organization, ' - ', semester Desc)");
        $query->from("#__thm_organizer_semesters");
        $query->where("id = '$semesterID'");
        $dbo->setQuery((string) $query);
        $semesterName = $dbo->loadResult();

        $query = $dbo->getQuery(true);
        $query->select('filename');
        $query->from("#__thm_organizer_schedules");
        $query->where("active IS NOT NULL");
        $query->where("sid = '$semesterID'");
        $query->where("plantypeID = '1'");
        $dbo->setQuery((string) $query);
        $oldScheduleName = $dbo->loadResult();

        if ($oldScheduleName)
        {
            $oldData = $this->getOldData($semesterID);
            $this->deactivate($semesterID);
        }
        $newData = $this->processNewData($schedule);

        $msg = "";
        if ($oldData and $newData)
        {
            $msg .= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_1') . " $semesterName ";
            $msg .= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_2A') . " $oldScheduleName ";
            $msg .= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_3A') . " $newScheduleName.";
            $return['messages'][] = $msg;
            $this->calculateDelta($oldData, $newData, $semesterID);
        }
        elseif ($newData)
        {
            $msg .= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_1') . " $semesterName ";
            $msg .= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_2B') . " $newScheduleName.";
            $return['messages'][] = $msg;
        }
        else
        {
            $return['errors'][] = JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_DB_FAIL');
        }
    }

    /**
     * processNewData
     *
     * saves and models the data contained in the file
     * 
     * @param   JTable  &$row  jtable object representing the row holding the schedule
     *
     * @return mixed array modeling lesson data if successful, otherwise false
     */
    protected function processNewData(&$row)
    {
        $dbo = $this->getDbo();
        $semesterID = $row->sid;
        $schedule = simplexml_load_string(stripslashes($row->file));

        $periods = array();
        $periodsmodel = new thm_organizersModelperiod;
        $periodsmodel->processData($schedule->timeperiods, $periods, $semesterID);
        unset($periodsmodel);

        $descriptions = array();
        $descriptionsmodel = new thm_organizersModeldescription;
        $descriptionsmodel->processData($schedule->descriptions, $descriptions);
        unset($descriptionsmodel);

        $departments = array();
        $departmentsmodel = new thm_organizersModeldepartment;
        $departmentsmodel->processData($schedule->departments, $departments);
        unset($departmentsmodel);

        $rooms = array();
        $roomsmodel = new thm_organizersModelroom;
        $roomsmodel->processData($schedule->rooms, $rooms, 0, $descriptions);
        unset($roomsmodel);

        $subjects = array();
        $subjectsmodel = new thm_organizersModelsubject;
        $subjectsmodel->processData($schedule->subjects, $subjects);
        unset($subjectsmodel);

        $teachers = array();
        $teachersmodel = new thm_organizersModelteacher;
        $teachersmodel->processData($schedule->teachers, $teachers, 0, $departments);
        unset($teachersmodel);

        $classes = array();
        $classesmodel = new thm_organizersModelclass;
        $classesmodel->processData($schedule->classes, $classes, 0, $teachers);
        unset($classesmodel);

        $lessons = array();
        $resources = array( 'periods' => $periods,
                            'rooms' => $rooms,
                            'classes' => $classes,
                            'teachers' => $teachers,
                            'subjects' => $subjects);
        $lessonsmodel = new thm_organizersModellesson;
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
     * @param   int  $semesterID  the id of the semester
     * 
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
        $dbo->setQuery((string) $query);
        $lessonIDs = "( '" . implode("', '", $dbo->loadResultArray(0)) . "' )";
        $results = $dbo->loadAssocList();

        $lessons = array();
        foreach ($results as $result)
        {
            $lessonID = $result['id'];
            $gpuntisID = $result['gpuntisID'];
            if (!isset($lessons[$gpuntisID]))
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
            $dbo->setQuery((string) $query);
            $lessons[$gpuntisID]['teacherIDs'] = $dbo->loadResultArray();

            $query = $dbo->getQuery(true);
            $query->select("DISTINCT(classID)");
            $query->from("#__thm_organizer_lesson_classes");
            $query->where("lessonID = '$lessonID'");
            $dbo->setQuery((string) $query);
            $lessons[$gpuntisID]['classIDs'] = $dbo->loadResultArray();

            $query = $dbo->getQuery(true);
            $query->select("*");
            $query->from("#__thm_organizer_lesson_times");
            $query->where("lessonID = '$lessonID'");
            $dbo->setQuery((string) $query);
            $instances = $dbo->loadAssocList();
            foreach ($instances as $instance)
            {
                $periodID = $instance['periodID'];
                $roomID = $instance['roomID'];
                if (!isset($lessons[$gpuntisID]['periods'][$periodID]))
                {
                    $lessons[$gpuntisID]['periods'][$periodID] = array();
                }
                if (!isset($lessons[$gpuntisID]['periods'][$periodID]['roomIDs']))
                {
                    $lessons[$gpuntisID]['periods'][$periodID]['roomIDs'] = array();
                }
                if (!in_array($roomID, $lessons[$gpuntisID]['periods'][$periodID]['roomIDs']))
                {
                    $lessons[$gpuntisID]['periods'][$periodID]['roomIDs'][] = $roomID;
                }
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
     * @param   mixed  $oldData     struture modeling the previously active lesson data
     * @param   mixed  $newData     structzure modeling the active lesson data
     * @param   int    $semesterID  the id of the semester
     * 
     * @return void
     */
    private function calculateDelta($oldData, $newData, $semesterID)
    {
        $delta = array();
        foreach ($newData as $gpuntisKey => $lesson)
        {
            // Lesson is new
            if (!key_exists($gpuntisKey, $oldData))
            {
                $delta[$gpuntisKey] = $lesson;
                $delta[$gpuntisKey]['status'] = 'new';
            }
            // Lesson occurs in both plans
            else
            {
                $lesson_changes = false;
                $period_changes = false;
                $newTeachers = array_diff($lesson['teacherIDs'], $oldData[$gpuntisKey]['teacherIDs']);
                $oldTeachers = array_diff($oldData[$gpuntisKey]['teacherIDs'], $lesson['teacherIDs']);
                $newClasses = array_diff($lesson['classIDs'], $oldData[$gpuntisKey]['classIDs']);
                $oldClasses = array_diff($oldData[$gpuntisKey]['classIDs'], $lesson['classIDs']);
                if (count($newTeachers) or count($oldTeachers) or count($newClasses) or count($oldClasses))
                {
                    $lesson_changes = true;
                    $delta[$gpuntisKey]['status'] = "changed";
                    $delta[$gpuntisKey]['changes'] = array();
                    if (count($newTeachers) or count($oldTeachers))
                    {
                        $delta[$gpuntisKey]['changes']['teacherIDs'] = array();
                        if (count($newTeachers))
                        {
                            foreach ($newTeachers as $newTeacher)
                            {
                                $delta[$gpuntisKey]['changes']['teacherIDs'][$newTeacher] = "new";
                            }
                        }
                        if (count($oldTeachers))
                        {
                            foreach ($oldTeachers as $oldTeacher)
                            {
                                $delta[$gpuntisKey]['changes']['teacherIDs'][$oldTeacher] = "removed";
                            }
                        }
                    }
                    if (count($newClasses) or count($oldClasses))
                    {
                        $delta[$gpuntisKey]['changes']['classIDs'] = array();
                        if (count($newClasses))
                        {
                            foreach ($newClasses as $newClass)
                            {
                                $delta[$gpuntisKey]['changes']['classIDs'][$newClass] = "new";
                            }
                        }
                        if (count($oldClasses))
                        {
                            foreach ($oldClasses as $oldClass)
                            {
                                $delta[$gpuntisKey]['changes']['classIDs'][$oldClass] = "removed";
                            }
                        }
                    }
                }
                // If the number of periods has remained the same than any new keys are moves
                $moved = (count($lesson['periods']) == count($oldData[$gpuntisKey]['periods']))? true : false;
                foreach ($lesson['periods'] as $periodID => $instance)
                {
                    // Period has been added
                    if (!key_exists($periodID, $oldData[$gpuntisKey]['periods']))
                    {
                        $period_changes = true;
                        if (!isset($delta[$gpuntisKey]['periods']))
                        {
                            $delta[$gpuntisKey]['periods'] = array();
                        }
                        $delta[$gpuntisKey]['periods'][$periodID] = $instance;
                        $delta[$gpuntisKey]['periods'][$periodID]['status'] = ($moved)? 'moved': 'new';
                    }
                    // Period is in both plans
                    else
                    {
                        $newRooms = array_diff($instance['roomIDs'], $oldData[$gpuntisKey]['periods'][$periodID]['roomIDs']);
                        $oldRooms = array_diff($oldData[$gpuntisKey]['periods'][$periodID]['roomIDs'], $instance['roomIDs']);
                        if (count($newRooms) or count($oldRooms))
                        {
                            $period_changes = true;
                            if (!isset($delta[$gpuntisKey]['periods']))
                            {
                                $delta[$gpuntisKey]['periods'] = array();
                            }
                            $delta[$gpuntisKey]['periods'][$periodID] = $instance;
                            $delta[$gpuntisKey]['periods'][$periodID]['status'] = 'changed';
                            if (count($newRooms))
                            {
                                foreach ($newRooms as $newRoom)
                                {
                                    $delta[$gpuntisKey]['periods'][$periodID]['changes']['roomIDs'][$newRoom] = "new";
                                }
                            }
                            if (count($oldRooms))
                            {
                                foreach ($oldRooms as $oldRoom)
                                {
                                    $delta[$gpuntisKey]['periods'][$periodID]['changes']['roomIDs'][$oldRoom] = "removed";
                                }
                            }
                        }
                    }
                }
                foreach ($oldData[$gpuntisKey]['periods'] as $periodID => $instance)
                {
                    // Period has been removed
                    if (!key_exists($periodID, $lesson['periods']))
                    {
                        $period_changes = true;
                        if (!isset($delta[$gpuntisKey]['periods']))
                        {
                            $delta[$gpuntisKey]['periods'] = array();
                        }
                        $delta[$gpuntisKey]['periods'][$periodID] = $instance;
                        $delta[$gpuntisKey]['periods'][$periodID]['status'] = 'removed';
                    }
                }
                if ($lesson_changes or $period_changes)
                {
                    $delta[$gpuntisKey]['subjectID'] = $lesson['subjectID'];
                    $delta[$gpuntisKey]['type'] = $lesson['type'];
                    $delta[$gpuntisKey]['comment'] = $lesson['comment'];
                    $delta[$gpuntisKey]['teacherIDs'] = $lesson['teacherIDs'];
                    $delta[$gpuntisKey]['classIDs'] = $lesson['classIDs'];
                    if ($lesson_changes)
                    {
                        foreach ($lesson['periods'] as $periodID => $instance)
                        {
                            if (!isset($delta[$gpuntisKey]['periods']))
                            {
                                $delta[$gpuntisKey]['periods'] = array();
                            }
                            if (!isset($delta[$gpuntisKey]['periods'][$periodID]))
                            {
                                $delta[$gpuntisKey]['periods'][$periodID] = $instance;
                            }
                        }
                    }
                }
            }
        }
        foreach ($oldData as $gpuntisKey => $lesson)
        {
            // Lesson has been removed
            if (!key_exists($gpuntisKey, $newData))
            {
                $delta[$gpuntisKey] = $lesson;
                $delta[$gpuntisKey]['status'] = 'removed';
            }
        }

        // Json_encode does not handle special characters properly
        $delta = json_encode($delta);
        $special_characters = array('\u00d6' => 'Ã–',
                                    '\u00f6' => 'Ã¶',
                                    '\u00c4' => 'Ã„',
                                    '\u00e4' => 'Ã¤',
                                    '\u00dc' => 'Ãœ',
                                    '\u00fc' => 'Ã¼',
                                    '\u00df' => 'ÃŸ');
        foreach ($special_characters as $unicode => $character)
        {
            $delta = str_replace($unicode, $character, $delta);
        }

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
     * @param   int  $semesterID  the id of the semester whose active plan is to be
     *                            deactivated
     * 
     * @return void
     */
    public function deactivate($semesterID)
    {
        $dbo = $this->getDbo();

        $query = $dbo->getQuery(true);
        $query->select("DISTINCT ( id )");
        $query->from("#__thm_organizer_lessons");
        $query->where("semesterID = '$semesterID'");
        $query->where("plantypeID = '1'");
        $dbo->setQuery((string) $query);
        $lessonIDs = "( '" . implode("', '", $dbo->loadResultArray()) . "' )";

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lessons");
        $query->where("id IN $lessonIDs");
        $dbo->setQuery((string) $query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lesson_teachers");
        $query->where("lessonID IN $lessonIDs");
        $dbo->setQuery((string) $query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lesson_classes");
        $query->where("lessonID IN $lessonIDs");
        $dbo->setQuery((string) $query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_lesson_times");
        $query->where("lessonID IN $lessonIDs");
        $dbo->setQuery((string) $query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->update("#__thm_organizer_schedules");
        $query->set("active = NULL");
        $query->where("plantypeID = '1'");
        $query->where("sid = '$semesterID'");
        $dbo->setQuery((string) $query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_deltas");
        $query->where("plantypeID = '1'");
        $query->where("semesterID = '$semesterID'");
        $dbo->setQuery((string) $query);
        $dbo->query();
    }

    /**
     * removes the selected schedule
     *
     * @param   int  $scheduleID  the id of the schedule to be deleted
     * 
     * @return void
     */
    public function delete($scheduleID)
    {
        $schedule = JTable::getInstance('schedules', 'thm_organizerTable');
        if ($schedule->load($scheduleID))
        {
            $schedule->delete($scheduleID);
        }
    }
}
?>
