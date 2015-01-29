<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        ICSBauer
 * @description ICSBauer file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once dirname(__FILE__) . "/AbstractBuilder.php";
error_reporting(0);

jimport('PHPExcel.PHPExcel');

/**
 * Class ICSBauer for component com_thm_organizer
 * Class provides methods to create a schedule in excel format
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THMICSBuilder extends THMAbstractBuilder
{
    /**
     * Joomla data abstraction
     *
     * @var    DataAbstraction
     */
    private $_JDA = null;

    /**
     * Config
     *
     * @var    Object
     */
    private $_cfg = null;

    /**
     * Excel
     *
     * @var    Object
     */
    private $_objPHPExcel = null;

    /**
     * Active Schedule
     *
     * @var    Object
     */
    private $_activeSchedule = null;

    /**
     * Active Schedule data
     *
     * @var    Object
     */
    private $_activeScheduleData = null;

    /**
     * Constructor with the joomla data abstraction object and configuration object
     *
     * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
     * @param   Object           $cfg  A object which has configurations including
     */
    public function __construct($JDA, $cfg)
    {
        $this->_JDA = $JDA;
        $this->_cfg = $cfg;
    }

    /**
     * Method to create a excel schedule
     *
     * @param   Object  $arr       The event object
     * @param   String  $username  The current logged in username
     * @param   String  $title     The schedule title
     *
     * @return Array An array with information about the status of the creation
     */
    public function createSchedule($arr, $username, $title)
    {
        $success = true;

        $arr = $arr[0];

        try
        {
            $this->_objPHPExcel = new PHPExcel;

            if ($title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE"))
            {
                $title = $username . " - " . $title;
            }

            $this->_objPHPExcel->getProperties()->setCreator($username)
                ->setLastModifiedBy($username)
                ->setTitle($title)
                ->setSubject($title);

            $this->_objPHPExcel->getActiveSheet()->setTitle(JText::_("COM_THM_ORGANIZER_SCHEDULER_CYCLIC_EVENTS"));

            if (isset($arr->session->semesterID))
            {
                $this->_activeSchedule = $this->getActiveSchedule((int) $arr->session->semesterID);
            }
            else
            {
                return array("success" => false, "data" => JText::_("COM_THM_ORGANIZER_SCHEDULER_NO_FILE_CREATED"));
            }

            if ($this->_activeSchedule == false)
            {
                return array("success" => false, "data" => JText::_("COM_THM_ORGANIZER_SCHEDULER_NO_FILE_CREATED"));
            }

            if (is_object($this->_activeSchedule) && is_string($this->_activeSchedule->schedule))
            {
                $this->_activeScheduleData = json_decode($this->_activeSchedule->schedule);

                // To save memory unset schedule
                unset($this->_activeSchedule->schedule);

                if ($this->_activeScheduleData == null)
                {
                    // Cant decode json
                    return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_DATA_FLAWED'));
                }
            }
            else
            {
                return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_NO_ACTIVE_SCHEDULE'));
            }

            $this->setLessonHead();
            $this->setLessonContent($arr);

            $this->_objPHPExcel->createSheet();
            $this->_objPHPExcel->setActiveSheetIndex(1);
            $this->_objPHPExcel->getActiveSheet()->setTitle(JText::_("COM_THM_ORGANIZER_SCHEDULER_SPORADIC_EVENTS"));

            $this->setEventHead();
            $this->setEventContent($arr);

            $this->_objPHPExcel->setActiveSheetIndex(0);
            $objWriter = PHPExcel_IOFactory::createWriter($this->_objPHPExcel, 'Excel5');
            $objWriter->save($this->_cfg['pdf_downloadFolder'] . $title . ".xls");
        }
        catch (Exception $e)
        {
            $success = false;
        }

        if ($success)
        {
            return array("success" => true, "data" => JText::_("COM_THM_ORGANIZER_SCHEDULER_FILE_CREATED"));
        }
        else
        {
            return array("success" => false, "data" => JText::_("COM_THM_ORGANIZER_SCHEDULER_NO_FILE_CREATED"));
        }
    }

    /**
     * Method to set the excel header
     *
     * @return  void
     */
    private function setEventHead()
    {
        $this->_objPHPExcel->getActiveSheet()
            ->setCellValue('A1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TITLE"))
            ->setCellValue('B1', JText::_("COM_THM_ORGANIZER_SCHEDULER_DESCRIPTION"))
            ->setCellValue('C1', JText::_("COM_THM_ORGANIZER_SCHEDULER_AFFECTED_RESOURCE"))
            ->setCellValue('D1', JText::_("COM_THM_ORGANIZER_SCHEDULER_CATEGORY"))
            ->setCellValue('E1', JText::_("COM_THM_ORGANIZER_SCHEDULER_DATE_OF"))
            ->setCellValue('F1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TO_DATE"))
            ->setCellValue('G1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TIME_OF"))
            ->setCellValue('H1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TO_TIME"));

        $this->_objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
    }

    /**
     * Method to create a ical schedule
     *
     * @param   Object  $arr  The event object
     *
     * @return  void
     */
    private function setEventContent($arr)
    {
        $row = 2;
        foreach ($arr->events as $item)
        {
            $resourceIDs = "'" . implode("', '", (array) $item->data->objects) . "'";
            $resString = "";

            $select = 'name as oname';

            // Get a db connection.
            $dbo = JFactory::getDbo();

            // Create a new query object.
            $query = $dbo->getQuery(true);

            // Select all records from the user profile table where key begins with "custom.".
            // Order it by the ordering field.
            $query->select($select);
            $query->from('#__thm_organizer_classes');
            $query->where('id IN ( $resourceIDs )');

            // Reset the query using our newly populated query object.
            $dbo->setQuery((string) $query);

            try
            {
                // Load the results as a list of stdClass objects.
                $classes = $dbo->loadObjectList();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }

            // Create a new query object.
            $query = $dbo->getQuery(true);

            // Select all records from the user profile table where key begins with "custom.".
            // Order it by the ordering field.
            $query->select($select);
            $query->from('#__thm_organizer_teachers');
            $query->where("gpuntisID IN( $resourceIDs )");

            // Reset the query using our newly populated query object.
            $dbo->setQuery((string) $query);

            try
            {
                // Load the results as a list of stdClass objects.
                $teachers = $dbo->loadObjectList();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }

            // Create a new query object.
            $query = $dbo->getQuery(true);

            // Select all records from the user profile table where key begins with "custom.".
            // Order it by the ordering field.
            $query->select($select);
            $query->from('#__thm_organizer_rooms');
            $query->where("gpuntisID IN( $resourceIDs )");

            // Reset the query using our newly populated query object.
            $dbo->setQuery((string) $query);

            try
            {
                // Load the results as a list of stdClass objects.
                $rooms = $dbo->loadObjectList();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }

            $resources = array_merge($classes, array_merge($teachers, $rooms));
            if (count($resources) > 0)
            {
                $resString = implode(", ", $resources);
            }

            $this->_objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $row, $item->data->title)
                ->setCellValue('B' . $row, $item->data->edescription)
                ->setCellValue('C' . $row, $resString)
                ->setCellValue('D' . $row, $item->data->category)
                ->setCellValue('E' . $row, $item->data->startdate)
                ->setCellValue('F' . $row, $item->data->enddate)
                ->setCellValue('G' . $row, $item->data->starttime)
                ->setCellValue('H' . $row, $item->data->endtime);
            $row++;
        }
    }

    /**
     * Method to set the lesson header
     *
     * @return void
     */
    private function setLessonHead()
    {
        $this->_objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', JText::_("COM_THM_ORGANIZER_SCHEDULER_LESSON_TITLE"))
        ->setCellValue('B1', JText::_("COM_THM_ORGANIZER_SCHEDULER_ABBREVIATION"))
        ->setCellValue('C1', JText::_("COM_THM_ORGANIZER_SCHEDULER_COMMENT"))
        ->setCellValue('D1', JText::_("COM_THM_ORGANIZER_SCHEDULER_MODULE_NUMBER"))
        ->setCellValue('E1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TYPE"))
        ->setCellValue('F1', JText::_("COM_THM_ORGANIZER_SCHEDULER_WEEKDAY"))
        ->setCellValue('G1', JText::_("COM_THM_ORGANIZER_SCHEDULER_BLOCK"))
        ->setCellValue('H1', JText::_("COM_THM_ORGANIZER_SCHEDULER_ROOM"))
        ->setCellValue('I1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TEACHER"))
        ->setCellValue('J1', JText::_("COM_THM_ORGANIZER_SCHEDULER_FIRST_DATE"));

        $this->_objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);
    }

    /**
     * Method to set the lesson content
     *
     * @param   Object  $arr  The event object
     *
     * @return  void
     */
    private function setLessonContent($arr)
    {
        foreach ($arr->lessons as $item)
        {
            if (isset($item->pools) && isset($item->teachers) && isset($item->calendar))
            {
                if (isset($item->block) && $item->block > 0)
                {
                    $times       = $this->blocktotime($item->block);
                    $item->stime = $times[0];
                    $item->etime = $times[1];
                }
                $item->sdate = $arr->session->sdate;
                $item->edate = $arr->session->edate;

                $teacherNames = array();
                foreach ($item->teachers as $teacherID => $teacherStatus)
                {
                    if ($teacherStatus != "removed")
                    {
                        $teacherNames[] = $this->getTeacherName($teacherID);
                    }
                }
                $item->teachers = implode(", ", $teacherNames);

                $moduleNames = array();
                foreach ($item->pools as $moduleID => $moduleStatus)
                {
                    if ($moduleStatus != "removed")
                    {
                        $moduleNames[] = $this->getModuleName($moduleID);
                    }
                }
                $item->pools = implode(", ", $moduleNames);

                $roomNames = array();
                foreach ($item->calendar as $block)
                {
                    foreach ($block->{$item->block}->lessonData as $roomID => $roomStatus)
                    {
                        if ($roomStatus != "removed")
                        {
                            $roomNames[] = $this->getRoomName($roomID);
                        }
                    }
                    break;
                }
                $item->rooms = implode(", ", $roomNames);

                $subjectNo = array();
                $subjectName = array();
                $subjectLongname = array();
                foreach ($item->subjects as $subjectID => $subjectStatus)
                {
                    if ($subjectStatus != "removed")
                    {
                        $subjectNo[] = $this->getSubjectNo($subjectID);
                        $subjectName[] = $this->getSubjectName($subjectID);
                        $subjectLongname[] = $this->getSubjectLongname($subjectID);
                    }
                }
                $item->subjectNo = implode(", ", $subjectNo);
                $item->name = implode(", ", $subjectName);
                $item->longname = implode(", ", $subjectLongname);
            }
        }

        $row = 2;

        /**
         * Function to custom sort the lesson array by their teachers
         *
         * @param   object  $thingOne  An object in the array
         * @param   object  $thingTwo  Another object in the array
         *
         * @return Integer Return 0 if the lesson teachers are the same
         *                           +1 if the $thingOne lesson string is greater than the $thingTwo lesson string
         *                           -1 if the $thingOne lesson string is lesser than the $thingTwo lesson string
         */
        $lessonsByTeacher = function ($thingOne, $thingTwo)
        {
            if ($thingOne->teachers == $thingTwo->teachers)
            {
                return 0;
            }
            return ($thingOne->teachers < $thingTwo->teachers) ? -1 : 1;
        };

        uasort($arr->lessons, $lessonsByTeacher);

        foreach ($arr->lessons as $item)
        {
            if (isset($item->pools) && isset($item->teachers) && isset($item->rooms))
            {
                if (!isset($item->longname))
                {
                    $item->longname = "";
                }
                if (!isset($item->category))
                {
                    $item->category = "";
                }

                $dayName = strtoupper($item->dow);
                reset($item->calendar);
                $firstDate = key($item->calendar);
                $dateFormat = "d.m.Y";

                $this->_objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $row, $item->longname)
                ->setCellValue('B' . $row, $item->name)
                ->setCellValue('C' . $row, $item->comment)
                ->setCellValue('D' . $row, $item->subjectNo)
                ->setCellValue('E' . $row, $item->description)
                ->setCellValue('F' . $row, JText::_($dayName))
                ->setCellValue('G' . $row, $item->block)
                ->setCellValue('H' . $row, $item->rooms)
                ->setCellValue('I' . $row, $item->teachers)
                ->setCellValue('J' . $row, date($dateFormat, strtotime($firstDate)));
                $row++;
            }
        }
    }

    /**
     * Method to get the subject number by $subjectID
     *
     * @param   object  $subjectID  A subject id
     *
     * @return   object  The requested subject number
     */
    private function getSubjectNo($subjectID)
    {
        return $this->_activeScheduleData->subjects->{$subjectID}->subjectNo;
    }

    /**
     * Method to get the subject name by $subjectID
     *
     * @param   object  $subjectID  A subject id
     *
     * @return   object  The requested subject name
     */
    private function getSubjectName($subjectID)
    {
        return $this->_activeScheduleData->subjects->{$subjectID}->name;
    }

    /**
     * Method to get the subject longname by $subjectID
     *
     * @param   object  $subjectID  A subject id
     *
     * @return   object  The requested subject longname
     */
    private function getSubjectLongname($subjectID)
    {
        return $this->_activeScheduleData->subjects->{$subjectID}->longname;
    }

    /**
     * Method to get the module name by $moduleID
     *
     * @param   object  $moduleID  A subject id
     *
     * @return   object  The requested module name
     */
    private function getModuleName($moduleID)
    {
        return $this->_activeScheduleData->pools->{$moduleID}->name;
    }

    /**
     * Method to get the room name by $roomID
     *
     * @param   object  $roomID  A subject id
     *
     * @return   object  The requested room name
     */
    private function getRoomName($roomID)
    {
        return $this->_activeScheduleData->rooms->{$roomID}->longname;
    }

    /**
     * Method to get the teacher name by $teacherID
     *
     * @param   object  $teacherID  A subject id
     *
     * @return   object  The requested teacher name
     */
    private function getTeacherName($teacherID)
    {
        $teachers = $this->_activeScheduleData->teachers;
        $name = $teachers->{$teacherID}->surname;
        if (strlen($teachers->{$teacherID}->firstname) > 0)
        {
            $name .= ", " . $teachers->{$teacherID}->firstname{0} . ".";
        }
        return $name;
    }

    /**
     * Method to get the active schedule
     *
     * @param   String  $semesterID  The department semester selection
     *
     * @return   mixed  The active schedule or false
     */
    private function getActiveSchedule($semesterID)
    {
        if (!is_int($semesterID))
        {
            return false;
        }

        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_schedules');
        $query->where('id = ' . $semesterID);
        $dbo->setQuery((string) $query);

        if ($dbo->getErrorMsg())
        {
            return false;
        }

        try
        {
            $result = $dbo->loadObject();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        if ($result === null)
        {
            return false;
        }
        return $result;
    }

    /**
     * Method to transform a block number to the block time (starttime and endtime)
     *
     * @param   Integer  $block  The block number
     *
     * @return Array An array which includes the block time (starttime and endtime)
     */
    private function blocktotime($block)
    {
        $times = array(
            1 => array(
                0 => "8:00",
                1 => "9:30"
            ),
            2 => array(
                0 => "9:50",
                1 => "11:20"
            ),
            3 => array(
                0 => "11:30",
                1 => "13:00"
            ),
            4 => array(
                0 => "14:00",
                1 => "15:30"
            ),
            5 => array(
                0 => "15:45",
                1 => "17:15"
            ),
            6 => array(
                0 => "17:30",
                1 => "19:00"
            )
        );
        return $times[$block];
    }
}
