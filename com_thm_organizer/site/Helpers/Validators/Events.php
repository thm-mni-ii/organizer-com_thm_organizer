<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers\Validators;

use Organizer\Helpers\Languages;
use stdClass;

/**
 * Provides functions for XML lesson validation and modeling.
 */
class Events implements UntisXMLValidator
{
    /**
     * Processes instance information for the new schedule format
     *
     * @param object &$scheduleModel the validating schedule model
     * @param int     $lessonID      the id of the lesson being iterated
     * @param int     $courseID      the id of the subject associated with this lesson unit
     * @param int     $teacherID     the id of the teacher associated with this lesson unit
     * @param string  $currentDate   the date being iterated
     * @param object  $period        the period information from the grid
     * @param object  $roomIDs       the room ids assigned to the instance
     *
     * @return void
     */
    private static function processInstance(
        &$scheduleModel,
        $lessonID,
        $courseID,
        $teacherID,
        $currentDate,
        $period,
        $roomIDs
    ) {
        // New format calendar items are created as necessary
        if (!isset($scheduleModel->schedule->calendar->$currentDate)) {
            $scheduleModel->schedule->calendar->$currentDate = new stdClass;
        }

        $dateObject = $scheduleModel->schedule->calendar->$currentDate;
        $times      = $period->startTime . '-' . $period->endTime;
        if (!isset($dateObject->$times)) {
            $dateObject->$times = new stdClass;
        }

        if (!isset($dateObject->$times->$lessonID)) {
            $dateObject->$times->$lessonID                 = new stdClass;
            $dateObject->$times->$lessonID->delta          = '';
            $dateObject->$times->$lessonID->configurations = [];
        }

        $config                       = new stdClass;
        $config->lessonID             = $lessonID;
        $config->courseID             = $courseID;
        $config->teachers             = new stdClass;
        $config->teachers->$teacherID = '';
        $config->rooms                = $roomIDs;
        $existingIndex                = null;

        if (!empty($dateObject->$times->$lessonID->configurations)) {
            $compConfig = null;

            foreach ($dateObject->$times->$lessonID->configurations as $configIndex) {
                $tempConfig = json_decode($scheduleModel->schedule->configurations[$configIndex]);

                if ($tempConfig->courseID == $courseID) {
                    $compConfig    = $tempConfig;
                    $existingIndex = $configIndex;
                    break;
                }
            }

            if (!empty($compConfig)) {
                foreach ($compConfig->teachers as $localTeacherID => $emptyDelta) {
                    $config->teachers->$localTeacherID = $emptyDelta;
                }

                foreach ($compConfig->rooms as $roomID => $emptyDelta) {
                    $config->rooms->$roomID = $emptyDelta;
                }
            }
        }

        self::createConfig($scheduleModel, $lessonID, $config, $currentDate, $times, $existingIndex);

        return;
    }

    /**
     * Creates a new configuration
     *
     * @param object &$scheduleModel the validating schedule model
     * @param int     $lessonID      the id of the lesson being iterated
     * @param object  $config        the configuration object
     * @param string  $date          the date to which the configuration should be referenced
     * @param string  $times         the times used for indexing blocks in the calendar
     * @param int     $existingIndex the existing index of the configuration if existent
     *
     * @return void
     */
    private static function createConfig(&$scheduleModel, $lessonID, $config, $date, $times, $existingIndex)
    {
        $jsonConfig = json_encode($config);

        if (!empty($existingIndex)) {
            $scheduleModel->schedule->configurations[$existingIndex] = $jsonConfig;

            return;
        }

        $scheduleModel->schedule->configurations[] = $jsonConfig;
        $configKeys                                = array_keys($scheduleModel->schedule->configurations);
        $configIndex                               = end($configKeys);

        $scheduleModel->schedule->calendar->$date->$times->$lessonID->configurations[] = $configIndex;
    }

    /**
     * Determines how the missing room attribute will be handled
     *
     * @param object &$scheduleModel the validating schedule model
     * @param int     $lessonID      the id of the lesson being iterated
     * @param string  $lessonName    the name of the lesson as used for error reporting
     * @param array   $groups        the groups associated with the lesson unit
     * @param string  $currentDT     the timestamp of the date being iterated
     * @param string  $period        the value of the period attribute
     *
     * @return void adds a message to the scheduleModel scheduleWarnings array
     */
    private static function createMissingRoomMessage(
        &$scheduleModel,
        $lessonID,
        $lessonName,
        $groups,
        $currentDT,
        $period
    ) {
        $groups       = implode(', ', $groups);
        $dow          = strtoupper(date('l', $currentDT));
        $localizedDoW = Languages::_($dow);
        $error        = sprintf(
            Languages::_('THM_ORGANIZER_LESSON_MISSING_ROOM'),
            $lessonName,
            $lessonID,
            $groups,
            $localizedDoW,
            $period
        );

        if (!in_array($error, $scheduleModel->scheduleWarnings)) {
            $scheduleModel->scheduleWarnings[] = $error;
        }
    }

    /**
     * Retrieves the resource id using the Untis ID. Creates the resource id if unavailable.
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $untisID       the id of the resource in Untis
     *
     * @return void modifies the scheduleModel, setting the id property of the resource
     */
    public static function setID(&$scheduleModel, $untisID)
    {
        // Lessons are only saved if the validation completed.
    }

    /**
     * Checks whether nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the object being validated
     *
     * @return void modifies &$scheduleModel
     */
    public static function validateCollection(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->lessons)) {
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_LESSONS_MISSING');

            return;
        }

        $scheduleModel->schedule->configurations = [];
        $scheduleModel->schedule->lessons        = new stdClass;

        foreach ($xmlObject->lessons->children() as $node) {
            self::validateIndividual($scheduleModel, $node);
        }

        if (!empty($scheduleModel->scheduleWarnings['LESSON-METHOD'])) {
            $warningCount = $scheduleModel->scheduleWarnings['LESSON-METHOD'];
            unset($scheduleModel->scheduleWarnings['LESSON-METHOD']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(Languages::_('THM_ORGANIZER_METHOD_ID_WARNING'), $warningCount);
        }
    }

    /**
     * Checks whether XML node has the expected structure and required
     * information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$node          the node to be validated
     *
     * @return void
     */
    public static function validateIndividual(&$scheduleModel, &$node)
    {
        $effBeginDT  = isset($node->begindate) ?
            strtotime(trim((string)$node->begindate)) :
            strtotime(trim((string)$node->effectivebegindate));
        $termBeginDT = strtotime($scheduleModel->schedule->startDate);
        $effEndDT    = isset($node->enddate) ?
            strtotime(trim((string)$node->enddate)) :
            strtotime(trim((string)$node->effectiveenddate));
        $termEndDT   = strtotime($scheduleModel->schedule->endDate);

        // Lesson is not relevant for the uploaded schedule (starts after term ends or ends before term begins)
        if ($effBeginDT > $termEndDT or $effEndDT < $termBeginDT) {
            return;
        }

        // Reset variables passed through the object
        $lessonID = self::validateUntisID($scheduleModel, trim((string)$node[0]['id']));

        if (empty($lessonID)) {
            return;
        }

        if (!isset($scheduleModel->schedule->lessons->$lessonID)) {
            $scheduleModel->schedule->lessons->$lessonID = new stdClass;
        }

        $lessonName = '';
        $courseID   = '';
        if (!self::validateSubject($scheduleModel, $node, $lessonID, $courseID, $lessonName)) {
            return;
        }

        self::validateMethod($scheduleModel, $node, $lessonID, $lessonName);

        $groups = [];
        if (!self::validateGroups($scheduleModel, $node, $lessonID, $lessonName, $courseID, $groups)) {
            return;
        }

        $teacherID = '';
        if (!self::validateTeacher($scheduleModel, $node, $lessonID, $lessonName, $courseID, $teacherID)) {
            return;
        }

        if (!self::validateDates($scheduleModel, $lessonID, $lessonName, $effBeginDT, $effEndDT)) {
            return;
        }

        // Should not have been exported
        if (empty($node->times->count())) {
            return;
        }

        $times   = $node->times->children();
        $comment = trim((string)$node->text);

        if (empty($comment) or $comment == '.') {
            $comment = '';
        }

        $scheduleModel->schedule->lessons->{$lessonID}->comment = $comment;

        $rawInstances = trim((string)$node->occurence);
        $startDT      = $effBeginDT < $termBeginDT ? $termBeginDT : $effBeginDT;
        $endDT        = $termEndDT < $effEndDT ? $termEndDT : $effEndDT;

        // Adjusted dates are used because effective dts are not always accurate for the time frame
        $potentialInstances = self::truncateInstances($scheduleModel, $rawInstances, $startDT, $endDT);

        $gridName = empty((string)$node->timegrid) ? 'Haupt-Zeitraster' : (string)$node->timegrid;

        // Cannot produce blocking errors
        self::validateInstances(
            $scheduleModel,
            $lessonID,
            $lessonName,
            $courseID,
            $teacherID,
            $groups,
            $potentialInstances,
            $startDT,
            $times,
            $gridName
        );
    }

    /**
     * Checks if the untis id is valid
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $rawUntisID    the untis lesson id
     *
     * @return mixed  string if valid, otherwise false
     */
    private static function validateUntisID(&$scheduleModel, $rawUntisID)
    {
        $withoutPrefix = str_replace("LS_", '', $rawUntisID);
        $untisID       = substr($withoutPrefix, 0, strlen($withoutPrefix) - 2);

        if (empty($untisID)) {
            $missingText = Languages::_('THM_ORGANIZER_LESSON_MISSING_ID');
            if (!in_array($missingText, $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = $missingText;
            }

            return false;
        }

        return $untisID;
    }

    /**
     * Validates the subjectID and builds dependant structural elements
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$node          the lesson node
     * @param int     $lessonID      the id of the lesson being iterated
     * @param int     $courseID      the id of the subject associated with this lesson unit
     * @param string &$lessonName    the name of the lesson as used for error reporting
     *
     * @return mixed  string the name of the lesson (subjects) on success,
     *                 otherwise boolean false
     */
    private static function validateSubject(&$scheduleModel, &$node, $lessonID, &$courseID, &$lessonName)
    {
        $lessonName = str_replace('SU_', '', trim((string)$node->lesson_subject[0]['id']));

        if (empty($lessonName)) {
            $scheduleModel->scheduleErrors[] =
                sprintf(Languages::_('THM_ORGANIZER_LESSON_MISSING_SUBJECT'), $lessonID);

            return false;
        }

        $subjectIndex = $scheduleModel->schedule->departmentname . "_" . $lessonName;

        if (empty($scheduleModel->schedule->courses->$subjectIndex)) {
            $scheduleModel->scheduleErrors[] =
                sprintf(
                    Languages::_('THM_ORGANIZER_ERROR_LESSON_SUBJECT_LACKING'),
                    $lessonID,
                    $lessonName
                );

            return false;
        }

        if (!isset($scheduleModel->schedule->lessons->{$lessonID}->courses)) {
            $scheduleModel->schedule->lessons->{$lessonID}->courses = new stdClass;
        }

        // Used in configurations, teachers and groups
        $courseID = $scheduleModel->schedule->courses->$subjectIndex->id;

        if (!isset($scheduleModel->schedule->lessons->$lessonID->courses->$courseID)) {
            $newSubject            = new stdClass;
            $newSubject->delta     = '';
            $newSubject->subjectNo = $scheduleModel->schedule->courses->$subjectIndex->subjectNo;
            $newSubject->groups    = new stdClass;
            $newSubject->teachers  = new stdClass;

            $scheduleModel->schedule->lessons->$lessonID->courses->$courseID = $newSubject;
        }

        return true;
    }

    /**
     * Validates the description
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$node          the lesson node
     * @param int     $lessonID      the id of the lesson being iterated
     * @param string &$lessonName    the name of the lesson as used for error reporting
     *
     * @return void modifies object properties
     */
    private static function validateMethod(&$scheduleModel, &$node, $lessonID, &$lessonName)
    {
        $untisID       = str_replace('DS_', '', trim((string)$node->lesson_description));
        $invalidMethod = (empty($untisID) or empty($scheduleModel->schedule->methods->$untisID));

        if ($invalidMethod) {
            $scheduleModel->scheduleWarnings['LESSON-METHOD'] =
                empty($scheduleModel->scheduleWarnings['LESSON-METHOD']) ?
                    1 : $scheduleModel->scheduleWarnings['LESSON-METHOD']++;

            return;
        }

        $lessonName .= " - $untisID";

        $scheduleModel->schedule->lessons->{$lessonID}->methodID
            = $scheduleModel->schedule->methods->$untisID->id;

        return;
    }

    /**
     * Validates the teacher attribute and sets corresponding schedule elements
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$node          the lesson node
     * @param int     $lessonID      the id of the lesson being iterated
     * @param string  $lessonName    the name of the lesson as used for error reporting
     * @param int     $courseID      the id of the subject associated with this lesson unit
     * @param int    &$teacherID     the id of the teacher associated with this lesson unit
     *
     * @return boolean  true if valid, otherwise false
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private static function validateTeacher(&$scheduleModel, &$node, $lessonID, $lessonName, $courseID, &$teacherID)
    {
        $untisID = str_replace('TR_', '', trim((string)$node->lesson_teacher[0]['id']));

        if (empty($untisID)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_LESSON_MISSING_TEACHER'),
                $lessonName,
                $lessonID
            );

            return false;
        }

        if (empty($scheduleModel->schedule->teachers->$untisID)
            or empty($scheduleModel->schedule->teachers->$untisID->id)) {
            $scheduleModel->scheduleErrors[] =
                sprintf(
                    Languages::_('THM_ORGANIZER_ERROR_LESSON_TEACHER_LACKING'),
                    $lessonName,
                    $lessonID,
                    $teacherID
                );

            return false;
        }

        if (!empty($courseID)) {
            $teacherID = $scheduleModel->schedule->teachers->$untisID->id;

            $scheduleModel->schedule->lessons->$lessonID->courses->$courseID->teachers->$teacherID = '';
        }

        return true;
    }

    /**
     * Validates the groups attribute and sets corresponding schedule elements
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$node          the lesson node
     * @param int     $lessonID      the id of the lesson being iterated
     * @param string  $lessonName    the name of the lesson as used for error reporting
     * @param int     $courseID      the id of the subject associated with this lesson unit
     * @param array  &$groups        the untis ids of the groups associated with the lesson for error reporting
     *
     * @return boolean  true if valid, otherwise false
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private static function validateGroups(&$scheduleModel, &$node, $lessonID, $lessonName, $courseID, &$groups)
    {
        $rawUntisIDs = str_replace('CL_', '', (string)$node->lesson_classes[0]['id']);

        if (empty($rawUntisIDs)) {
            $scheduleModel->scheduleErrors[] =
                sprintf(Languages::_('THM_ORGANIZER_LESSON_MISSING_GROUP'), $lessonName, $lessonID);

            return false;
        }

        $untisIDs = explode(" ", $rawUntisIDs);

        foreach ($untisIDs as $untisID) {
            if (empty($scheduleModel->schedule->groups->$untisID)
                or empty($scheduleModel->schedule->groups->$untisID->id)) {
                $scheduleModel->scheduleErrors[] =
                    sprintf(
                        Languages::_('THM_ORGANIZER_LESSON_GROUP_LACKING'),
                        $lessonName,
                        $lessonID,
                        $untisID
                    );

                continue;
            }

            $groupID = $scheduleModel->schedule->groups->$untisID->id;

            $scheduleModel->schedule->lessons->$lessonID->courses->$courseID->groups->$groupID = '';
            $groups[$untisID]                                                                  = $untisID;
        }

        return empty($groups) ? false : true;
    }

    /**
     * Checks for the validity and consistency of date values
     *
     * @param object &$scheduleModel the validating schedule model
     * @param int     $lessonID      the id of the lesson being iterated
     * @param string  $lessonName    the name of the lesson as used for error reporting
     * @param int     $startDT       the start date as integer
     * @param int     $endDT         the end date as integer
     *
     * @return boolean  true if dates are valid, otherwise false
     */
    private static function validateDates(&$scheduleModel, $lessonID, $lessonName, $startDT, $endDT)
    {
        if (empty($startDT)) {
            $scheduleModel->scheduleErrors[] =
                sprintf(
                    Languages::_('THM_ORGANIZER_LESSON_MISSING_START_DATE'),
                    $lessonName,
                    $lessonID
                );

            return false;
        }

        $syStartTime     = strtotime($scheduleModel->schedule->syStartDate);
        $syEndTime       = strtotime($scheduleModel->schedule->syEndDate);
        $lessonStartDate = date('Y-m-d', $startDT);

        $validStartDate = ($startDT >= $syStartTime and $startDT <= $syEndTime);
        if (!$validStartDate) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_ERROR_LESSON_START_DATE_INVALID'),
                $lessonName,
                $lessonID,
                $lessonStartDate
            );

            return false;
        }

        if (empty($endDT)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_LESSON_MISSING_END_DATE'),
                $lessonName,
                $lessonID
            );

            return false;
        }

        $lessonEndDate = date('Y-m-d', $endDT);

        $validEndDate = ($endDT >= $syStartTime and $endDT <= $syEndTime);
        if (!$validEndDate) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_ERROR_LESSON_END_DATE_INVALID'),
                $lessonName,
                $lessonID,
                $lessonEndDate
            );

            return false;
        }

        // Checks if start date is before end date
        if ($endDT < $startDT) {
            $scheduleModel->scheduleErrors[] =
                sprintf(
                    Languages::_('THM_ORGANIZER_ERROR_LESSON_DATES_INCONSISTENT'),
                    $lessonName,
                    $lessonID,
                    $lessonStartDate,
                    $lessonEndDate
                );

            return false;
        }

        return true;
    }

    /**
     * Validates the occurrences attribute
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $raw           the string containing the occurrences
     * @param int     $start         the timestamp of the lesson's begin
     * @param int     $end           the timestamp of the lesson's end
     *
     * @return mixed   array if valid, otherwise false
     */
    private static function truncateInstances(&$scheduleModel, $raw, $start, $end)
    {
        // Increases the end value one day (Untis uses inclusive dates)
        $end = strtotime('+1 day', $end);

        // 86400 is the number of seconds in a day 24 * 60 * 60
        $offset = floor(($start - strtotime($scheduleModel->schedule->syStartDate)) / 86400);
        $length = floor(($end - $start) / 86400);

        $validOccurrences = substr($raw, $offset, $length);

        // Change occurrences from a string to an array of the appropriate length for iteration
        return empty($validOccurrences) ? [] : str_split($validOccurrences);
    }

    /**
     * Iterates over possible occurrences and validates them
     *
     * @param object &$scheduleModel      the validating schedule model
     * @param int     $lessonID           the id of the lesson being iterated
     * @param string  $lessonName         the name of the lesson as used for error reporting
     * @param int     $courseID           the id of the subject associated with this lesson unit
     * @param int     $teacherID          the id of the teacher associated with this lesson unit
     * @param array   $groups             the groups associated with the lesson unit
     * @param array   $potentialInstances an array of 'occurrences'
     * @param int     $currentDT          the starting timestamp
     * @param array  &$instances          the object containing the instances
     * @param string  $grid               the grid used by the lesson
     *
     * @return void
     */
    private static function validateInstances(
        $scheduleModel,
        $lessonID,
        $lessonName,
        $courseID,
        $teacherID,
        $groups,
        $potentialInstances,
        $currentDT,
        &$instances,
        $grid
    ) {
        if (count($instances) == 0) {
            return;
        }

        foreach ($potentialInstances as $potentialInstance) {
            // Untis uses F for vacation days and 0 for any other date restriction
            $notAllowed = ($potentialInstance == '0' or $potentialInstance == 'F');

            if ($notAllowed) {
                $currentDT = strtotime('+1 day', $currentDT);
                continue;
            }

            foreach ($instances as $instance) {
                $valid = self::validateInstance(
                    $scheduleModel,
                    $lessonID,
                    $lessonName,
                    $courseID,
                    $teacherID,
                    $groups,
                    $instance,
                    $currentDT,
                    $grid
                );
                if (!$valid) {
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
     * @param object &$scheduleModel the validating schedule model
     * @param int     $lessonID      the id of the lesson being iterated
     * @param string  $lessonName    the name of the lesson as used for error reporting
     * @param int     $courseID      the id of the subject associated with this lesson unit
     * @param int     $teacherID     the id of the teacher associated with this lesson unit
     * @param array   $groups        the untis ids of the groups associated with the lesson for error reporting
     * @param object &$instance      the lesson instance
     * @param int     $currentDT     the current date time in the iteration
     * @param string  $grid          the grid used by the lesson
     *
     * @return boolean  true if valid, otherwise false
     */
    private static function validateInstance(
        &$scheduleModel,
        $lessonID,
        $lessonName,
        $courseID,
        $teacherID,
        $groups,
        &$instance,
        $currentDT,
        $grid
    ) {
        $assigned_day = trim((string)$instance->assigned_day);
        $dow          = date('w', $currentDT);

        if ($assigned_day != $dow) {
            return true;
        }

        // Sporadic events have specific dates assigned to them.
        $assigned_date = strtotime(trim((string)$instance->assigned_date));

        // The event is sporadic and does not occur on the date being currently iterated
        if (!empty($assigned_date) and $assigned_date != $currentDT) {
            return true;
        }

        $periodNo      = trim((string)$instance->assigned_period);
        $roomAttribute = trim((string)$instance->assigned_room[0]['id']);

        if (empty($roomAttribute)) {
            self::createMissingRoomMessage(
                $scheduleModel,
                $lessonID,
                $lessonName,
                $groups,
                $currentDT,
                $periodNo
            );

            return false;
        }

        $roomIDs = self::validateRooms(
            $scheduleModel,
            $lessonID,
            $lessonName,
            $groups,
            $roomAttribute,
            $currentDT,
            $periodNo
        );

        if ($roomIDs === false) {
            return false;
        }

        $currentDate = date('Y-m-d', $currentDT);
        $period      = $scheduleModel->schedule->periods->$grid->periods->$periodNo;
        self::processInstance($scheduleModel, $lessonID, $courseID, $teacherID, $currentDate, $period, $roomIDs);

        return true;
    }

    /**
     * Validates the room attribute
     *
     * @param object &$scheduleModel the validating schedule model
     * @param int     $lessonID      the id of the lesson being iterated
     * @param string  $lessonName    the name of the lesson as used for error reporting
     * @param array   $groups        the untis ids of the groups associated with the lesson for error reporting
     * @param string  $roomAttribute the room attribute
     * @param int     $currentDT     the timestamp of the date being iterated
     * @param string  $period        the period attribute
     *
     * @return mixed the roomIDs object on success, otherwise false
     */
    private static function validateRooms(
        &$scheduleModel,
        $lessonID,
        $lessonName,
        $groups,
        $roomAttribute,
        $currentDT,
        $period
    ) {
        $roomIDs      = new stdClass;
        $roomUntisIDs = explode(' ', str_replace('RM_', '', strtoupper($roomAttribute)));

        foreach ($roomUntisIDs as $roomUntisID) {
            if (!isset($scheduleModel->schedule->rooms->$roomUntisID)
                or empty($scheduleModel->schedule->rooms->$roomUntisID->id)) {
                $groups       = implode(', ', $groups);
                $dow          = strtoupper(date('l', $currentDT));
                $localizedDoW = Languages::_($dow);
                $error        = sprintf(
                    Languages::_('THM_ORGANIZER_ERROR_LESSON_ROOM_LACKING'),
                    $lessonName,
                    $lessonID,
                    $groups,
                    $localizedDoW,
                    $period,
                    $roomUntisID
                );
                if (!in_array($error, $scheduleModel->scheduleErrors)) {
                    $scheduleModel->scheduleErrors[] = $error;
                }

                return false;
            }

            $roomID           = $scheduleModel->schedule->rooms->$roomUntisID->id;
            $roomIDs->$roomID = '';
        }

        return $roomIDs;
    }
}
