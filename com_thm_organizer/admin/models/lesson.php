<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        data abstraction and business logic class for lessons
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class enapsulating data abstraction and business logic for lessons.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelLesson extends JModel
{
    /**
     * The schedule model
     * 
     * @var object 
     */
    private $_scheduleModel = null;

    /**
     * Whether or not rooms should produce blocking errors.
     * 
     * @var bool
     */
    private $_roomsRequired = true;

    /**
     * The name of the lesson. (subject & type)
     * 
     * @var string
     */
    private $_lessonName = '';

    /**
     * The lesson's id.
     * 
     * @var string 
     */
    private $_lessonID = '';

    /**
     * A unique identifier for the lesson across schedules. (dpt., sem., id)
     * 
     * @var string 
     */
    private $_lessonIndex = '';

    /**
     * Creates the lesson model
     * 
     * @param   object  &$scheduleModel  the model for the schedule
     * @param   bool    $roomsRequired   if rooms should produce blocking errors
     */
    public function __construct(&$scheduleModel, $roomsRequired = true)
    {
        parent::__construct();
        $this->_scheduleModel = $scheduleModel;
        $this->_roomsRequired = $roomsRequired;
    }

    /**
     * Checks whether lesson nodes have the expected structure and required
     * information
     *
     * @param   SimpleXMLNode  &$lessonNode  the lesson node to be validated
     *
     * @return void
     */
    public function validate(&$lessonNode)
    {
        $this->_lessonID;
        $this->_lessonIndex = '';
        $this->_lessonName = '';

        $gpuntisID = $this->validateUntisID(trim((string) $lessonNode[0]['id']));
        if (!$gpuntisID)
        {
            return;
        }

        $department = $this->_scheduleModel->schedule->departmentname;
        $semester = $this->_scheduleModel->schedule->semestername;
        $this->_lessonID = str_replace('LS_', '', $gpuntisID);
        $this->_lessonIndex = $department . $semester . "_" . $this->_lessonID;

        if (!isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}))
        {
            $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex} = new stdClass;
        }
        $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->gpuntisID = $gpuntisID;

        $subjectID = str_replace('SU_', '', trim((string) $lessonNode->lesson_subject[0]['id']));
        $lessonName = $this->validateSubject($subjectID, $department);
        if (!$lessonName)
        {
            return;
        }

        $descriptionID = $this->validateDescription(str_replace('DS_', '', trim((string) $lessonNode->lesson_description)));
        $lessonName .= " - $descriptionID";
        $this->_lessonName = $lessonName;
        $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->name = $lessonName;

        $teacherID = str_replace('TR_', '', trim((string) $lessonNode->lesson_teacher[0]['id']));
        $teacherValid = $this->validateTeacher($teacherID);
        if (!$teacherValid)
        {
            return;
        }

        $poolIDs = (string) $lessonNode->lesson_classes[0]['id'];
        $poolsValid = $this->validatePools($poolIDs);
        if (!$poolsValid)
        {
            return;
        }

        $startDT = strtotime(trim((string) $lessonNode->effectivebegindate));
        $endDT = strtotime(trim((string) $lessonNode->effectiveenddate));
        $datesValid = $this->validateDates($startDT, $endDT);
        if (!$datesValid)
        {
            return;
        }

        $rawOccurences = trim((string) $lessonNode->occurence);
        $occurences = $this->validateRawOccurences($rawOccurences, $startDT, $endDT);

        $comment = trim((string) $lessonNode->text);
        $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->comment = empty($comment)? '' : $comment;

        $periods = intval(trim($lessonNode->periods));
        $times = $lessonNode->times;

        // Cannot produce blocking errors
        $this->validatePeriodsAttribute($periods, $times);
        $this->validateOccurences($occurences, $startDT, $times);
    }

    /**
     * Checks if the untis id is valid
     * 
     * @param   string  $untisID  the untis lesson id
     * 
     * @return  mixed  string if valid, otherwise false
     */
    private function validateUntisID($untisID)
    {
        $untisID = substr($untisID, 0, strlen($untisID) - 2);
        if (empty($untisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_LS_ID_MISSING"), $this->_scheduleModel->scheduleErrors))
            {
                $this->_scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_LS_ID_MISSING");
            }
            return false;
        }
        return $untisID;
    }

    /**
     * Validates the subjectID and builds dependant structural elements
     * 
     * @param   string  $subjectID   the id of the subject
     * @param   string  $department  the name of the department
     * 
     * @return  mixed  string the name of the lesson (subjects) on success,
     *                 otherwise boolean false
     */
    private function validateSubject($subjectID, $department)
    {
        $subjectIndex = $department . "_" . $subjectID;
        if (empty($subjectID)
         AND !isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf("COM_THM_ORGANIZER_LS_SU_MISSING", $this->_lessonID);
            return false;
        }
        elseif (!isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects)
         AND empty($this->_scheduleModel->schedule->subjects->$subjectIndex))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf("COM_THM_ORGANIZER_LS_SU_LACKING", $this->_lessonID, $subjectID);
            return false;
        }

        if (!isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects))
        {
            $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects = new stdClass;
        }

        if (!empty($subjectID)
         AND !key_exists($subjectIndex, $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects))
        {
            $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects->$subjectIndex = '';
        }

        $subjectIndexes = array_keys((array) $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects);
        $lessonName = implode(' / ', $subjectIndexes);
        return str_replace($department . '_', '', $lessonName);
    }

    /**
     * Validates the description
     * 
     * @param   string  $descriptionID  the id of the description
     * 
     * @return  boolean  true on success, otherwise false
     */
    private function validateDescription($descriptionID)
    {
        if (empty($descriptionID))
        {
            $this->_scheduleModel->scheduleErrors[]
                = JText::sprintf("COM_THM_ORGANIZER_LS_TYPE_MISSING", $this->_lessonName, $this->_lessonID);
            return false;
        }
        elseif (empty($this->_scheduleModel->schedule->lessontypes->$descriptionID))
        {
            $this->_scheduleModel->scheduleErrors[]
                = JText::sprintf('COM_THM_ORGANIZER_LS_TYPE_LACKING', $this->_lessonName, $this->_lessonID, $descriptionID);
            return false;
        }

        if (!isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->description))
        {
            $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->description = $descriptionID;
        }
        return $descriptionID;
    }

    /**
     * Validates the teacher attribute and sets corresponding schedule elements
     * 
     * @param   string  $teacherID  the teacher id
     * 
     * @return  boolean  true if valid, otherwise false
     */
    private function validateTeacher($teacherID)
    {
        $teacherFound = false;
        if (empty($teacherID))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_LS_TR_MISSING', $this->_lessonName, $this->_lessonID);
            return false;
        }
        else
        {
            foreach ($this->_scheduleModel->schedule->teachers as $teacherKey => $teacher)
            {
                if ($teacher->localUntisID == $teacherID)
                {
                    $teacherFound = true;
                    $teacherID = $teacherKey;
                    break;
                }
            }
            if (!$teacherFound)
            {
                $this->_scheduleModel->scheduleErrors[]
                    = JText::sprintf('COM_THM_ORGANIZER_LS_TR_LACKING', $this->_lessonName, $this->_lessonID, $teacherID);
                return false;
            }
        }
        if (!isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->teachers))
        {
            $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->teachers = new stdClass;
        }
        if (!key_exists($teacherID, $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->teachers))
        {
            $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->teachers->$teacherID = '';
        }
        return true;
    }

    /**
     * Validates the pools attribute and sets corresponding schedule elements
     * 
     * @param   string  $poolIDs  the ids of the associated pools as string
     *
     * @return  boolean  true if valid, otherwise false
     */
    private function validatePools($poolIDs)
    {
        if (empty($poolIDs) AND !isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->pools))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf("COM_THM_ORGANIZER_LS_CL_MISSING", $this->_lessonName, $this->_lessonID);
            return false;
        }
        elseif (!empty($poolIDs))
        {
            if (!isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->pools))
            {
                $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->pools = new stdClass;
            }
            $poolIDs = explode(" ", $poolIDs);
            foreach ($poolIDs as $poolID)
            {
                $poolID = str_replace('CL_', '', $poolID);
                $poolFound = false;
                foreach ($this->_scheduleModel->schedule->pools as $poolKey => $pool)
                {
                    if ($pool->localUntisID == $poolID)
                    {
                        $poolFound = true;
                        $poolID = $poolKey;
                        break;
                    }
                }
                if (!$poolFound)
                {
                    $this->_scheduleModel->scheduleErrors[]
                        = JText::sprintf('COM_THM_ORGANIZER_LS_CL_LACKING', $this->_lessonName, $this->_lessonID, $poolID);
                    return false;
                }
                $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->pools->$poolID = '';
            }
        }
        return true;
    }

    /**
     * Checks for the validity and consistency of date values
     * 
     * @param   int  $startDT  the startdate as integer
     * @param   int  $endDT    the enddate as integer
     * 
     * @return  boolean  true if dates are valid, otherwise false
     */
    private function validateDates($startDT, $endDT)
    {
        $lessonStartDate = date('Y-m-d', $startDT);
        if (empty($lessonStartDate))
        {
            $this->_scheduleModel->scheduleErrors[]
                = JText::sprintf('COM_THM_ORGANIZER_LS_SD_MISSING', $this->_lessonName, $this->_lessonID);
            return false;
        }
        $startDateExists = array_key_exists($lessonStartDate, get_object_vars($this->_scheduleModel->schedule->calendar));
        if (!$startDateExists)
        {
            $this->_scheduleModel->scheduleErrors[]
                = JText::sprintf('COM_THM_ORGANIZER_LS_SD_OOB', $this->lessonName, $this->_lessonID, $lessonStartDate);
            return false;
        }

        $lessonEndDate = date('Y-m-d', $endDT);
        if (empty($lessonEndDate))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_LS_ED_MISSING', $this->lessonName, $this->_lessonID);
            return false;
        }

        // Checks if startdate is before enddate
        if (strtotime($lessonEndDate) <= $startDT )
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_LS_SDED_INCONSISTANT', $this->lessonName, $this->_lessonID);
            return false;
        }
        return true;
    }

    /**
     * Validates the occurences attribute
     * 
     * @param   string  $raw    the string containing the occurences
     * @param   int     $start  the timestamp of the lesson's begin
     * @param   int     $end    the timestamp of the lesson's end
     * 
     * @return  mixed   array if valid, otherwise false
     */
    private function validateRawOccurences($raw, $start, $end)
    {
        if (empty($raw))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_LS_OCC_MISSING', $this->lessonName, $this->_lessonID);
            return false;
        }
        elseif (strlen($raw) != $this->_scheduleModel->schedule->calendar->sylength)
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_LS_OCC_LEN_BAD', $this->lessonName, $this->_lessonID);
            return false;
        }

        // 86400 is the number of seconds in a day 24 * 60 * 60
        $offset = floor(($start - strtotime($this->_scheduleModel->schedule->startdate)) / 86400);
        $length = floor(($end - $start) / 86400);

        // Change occurences from a string to an array of the appropriate length for iteration
        return str_split(substr($raw, $offset, $length));
    }

    /**
     * Validates the lesson's periods attribute
     * 
     * @param   int     $periods  the number of periods alloted to the lesson
     * @param   object  &$times   the planned occurences of the lesson
     * 
     * @return  void
     */
    private function validatePeriodsAttribute($periods, &$times)
    {
        if (empty($periods))
        {
            $this->_scheduleModel->scheduleWarnings[]
                = JText::sprintf("COM_THM_ORGANIZER_LS_TP_MISSING", $this->_lessonName, $this->_lessonID);
        }
        $timescount = count($times->children());
        if (isset($periods) and $periods != $timescount)
        {
            $this->_scheduleModel->scheduleWarnings[]
                = JText::sprintf('COM_THM_ORGANIZER_LS_TP_INCONSISTANT', $this->_lessonName, $this->_lessonID);
        }
    }

    /**
     * Iterates over possible occurances and validates them
     * 
     * @param   array   $occurences  an array of 'occurences'
     * @param   int     $currentDT   the starting timestamp
     * @param   object  &$instances  the object containing the instances
     * 
     * @return  void
     */
    private function validateOccurences($occurences, $currentDT, &$instances)
    {
        foreach ($occurences as $occurence)
        {
            // Cannot take place on this index
            if ($occurence == '0' OR $occurence == 'F')
            {
                $currentDT = strtotime('+1 day', $currentDT);
                continue;
            }

            foreach ($instances->children() as $instance)
            {
                $valid = $this->validateInstance($instance, $currentDT);
                if (!$valid)
                {
                    return;
                }
            }
            $currentDT = strtotime('+1 day', $currentDT);
        }
        return;
    }

    /**
     * Validates a lesson instance
     * 
     * @param   object  &$instance  the lesson instance
     * @param   int     $currentDT  the current date time in the iteration
     * 
     * @return  boolean  true if valid, otherwise false
     */
    private function validateInstance(&$instance, $currentDT)
    {
        $currentDate = date('Y-m-d', $currentDT);
        $assigned_date = strtotime(trim((string) $instance->assigned_date));
        if (!empty($assigned_date) AND $assigned_date != $currentDT)
        {
            return true;
        }

        $day = trim((string) $instance->assigned_day);
        $validDay = $this->validateInstanceDay($day, $currentDT);
        
        // The day either was not valid, or was not a day on which the lesson occurs
        if ($validDay === true OR $validDay === false)
        {
            return $validDay;
        }

        $period = $this->validatePeriod(trim((string) $instance->assigned_period), $currentDate);
        if (!$period)
        {
            return false;
        }
    
        $roomAttribute = trim((string) $instance->assigned_room[0]['id']);
        if (empty($roomAttribute))
        {
            $throwError = $this->handleMissingRooms($currentDT, $period);
            if ($throwError)
            {
                return false;
            }
        }
        else
        {
            $roomsValid = $this->validateRooms($roomAttribute, $currentDT, $period);
            if (!$roomsValid)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Validates the period attribute of an instance
     * 
     * @param   string  $period       the period attribute
     * @param   string  $currentDate  the date in the current iteration
     * 
     * @return  boolean  true on success, 
     */
    private function validatePeriod($period, $currentDate)
    {
        if (empty($period))
        {
            $error = JText::sprintf('COM_THM_ORGANIZER_LS_TP_PERIOD_MISSING', $this->_lessonName, $this->_lessonID);
            if (!in_array($error, $this->_scheduleModel->scheduleErrors))
            {
                $this->_scheduleModel->scheduleErrors[] = $error;
                return false;
            }
        }

        if (!isset($this->_scheduleModel->schedule->calendar->$currentDate->$period))
        {
            $error
                = JText::sprintf('COM_THM_ORGANIZER_LS_TP_LACKING',
                                 $this->_lessonName, $this->_lessonID,
                                 date('l', strtotime($currentDate)), $period
                                );
            if (!in_array($error, $this->_scheduleModel->scheduleErrors))
            {
                $this->_scheduleModel->scheduleErrors[] = $error;
                return false;
            }
        }

        return $period;
    }

    /**
     * Validates whether the instance day attribute
     * 
     * @param   string  $day        the numeric day of the week
     * @param   int     $currentDT  the current date time in the iteration
     * 
     * @return  mixed  boolean false if the day is missing, true if the lesson
     *                 does not occur on the given day, otherwise the integer dow
     */
    private function validateInstanceDay($day, $currentDT)
    {
        if (empty($day))
        {
            $error = JText::sprintf('COM_THM_ORGANIZER_LS_TP_DAY_MISSING', $this->_lessonName, $this->_lessonID);
            if (!in_array($error, $this->_scheduleModel->scheduleErrors))
            {
                $this->_scheduleModel->scheduleErrors[] = $error;
                return false;
            }
        }
        if ($day != date('w', $currentDT))
        {
            // Does not occur on this date, no error
            return true;
        }
        return $day;
    }

    /**
     * Determines how the missing room attribute will be handled
     * 
     * @param   string  $currentDT  the timestamp of the date being iterated
     * @param   string  $period     the value of the period attribute
     *
     * @return  boolean  true if blocking and not set elsewhere, otherwise false
     */
    private function handleMissingRooms($currentDT, $period)
    {
        $currentDate = date('Y-m-d', $currentDT);

        // Attribute has also not been set by any other lesson
        if (!isset($this->_scheduleModel->schedule->calendar->$currentDate->$period->{$this->_lessonIndex}))
        {
            $error = JText::sprintf('COM_THM_ORGANIZER_LS_TP_ROOM_MISSING', $this->_lessonName, $this->_lessonID, date('l', $currentDT), $period);
            if (!in_array($error, $this->_scheduleModel->scheduleErrors) AND !in_array($error, $this->_scheduleModel->scheduleWarnings))
            {
                if ($this->_roomsRequired)
                {
                    $this->_scheduleModel->scheduleErrors[] = $error;
                    return true;
                }
                else
                {
                    $this->_scheduleModel->scheduleWarnings[] = $error;
                    return false;
                }
            }
        }

        // Attribute has been set by another lesson
        return false;
    }

    /**
     * Validates the room attribute
     * 
     * @param   string  $roomAttribute  the room attribute
     * @param   int     $currentDT      the timestamp of the date being iterated
     * @param   string  $period         the period attribute
     * 
     * @return  boolean  true on success, otherwise false
     */
    private function validateRooms($roomAttribute, $currentDT, $period)
    {
        $currentDate = date('Y-m-d', $currentDT);

        $roomIDs = explode(' ', str_replace('RM_', '', $roomAttribute));
        foreach ($roomIDs as $roomID)
        {
            $roomFound = false;
            foreach ($this->_scheduleModel->schedule->rooms as $roomKey => $room)
            {
                if ($room->localUntisID == $roomID)
                {
                    $roomFound = true;
                    $roomID = $roomKey;
                    break;
                }
            }
            if (!$roomFound)
            {
                $error = JText::sprintf(
                                        'COM_THM_ORGANIZER_LS_TP_ROOM_LACKING',
                                        $this->_lessonName, $this->_lessonID,
                                        date('l', $currentDT), $period, $roomID
                                       );
                if (!in_array($error, $this->_scheduleModel->scheduleErrors))
                {
                    $this->_scheduleModel->scheduleErrors[] = $error;
                }
                return false;
            }
            else
            {
                if (!isset($this->_scheduleModel->schedule->calendar->$currentDate->$period->{$this->_lessonIndex}))
                {
                    $this->_scheduleModel->schedule->calendar->$currentDate->$period->{$this->_lessonIndex} = new stdClass;
                }
                $lessonIndexes = get_object_vars($this->_scheduleModel->schedule->calendar->$currentDate->$period->{$this->_lessonIndex});
                if (!empty($roomID) AND !in_array($roomID, $lessonIndexes))
                {
                    $this->_scheduleModel->schedule->calendar->$currentDate->$period->{$this->_lessonIndex}->$roomID = '';
                }
            }
        }
        return true;
    }
}