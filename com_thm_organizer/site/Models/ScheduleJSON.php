<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Organizer\Helpers\Courses;
use Organizer\Helpers\Input;
use Organizer\Helpers\Rooms;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which models, validates and compares schedule data to and from json objects.
 */
class ScheduleJSON extends BaseDatabaseModel
{
    /**
     * Object containing information from the actual schedule
     *
     * @var object
     */
    private $schedule = null;

    /**
     * Object containing information from a comparison schedule
     *
     * @var object
     */
    private $refSchedule = null;

    /**
     * Boolean which represents if the users should be notified
     *
     * @var boolean
     */
    private $shouldNotify = false;

    /**
     * All changes to the schedule are saved in this array
     *
     * @var array
     */
    private $scheduleChanges = [];

    /**
     * JSONSchedule constructor.
     *
     * @param object &$schedule the schedule object for direct processing
     */
    public function __construct(&$schedule = null)
    {
        try {
            parent::__construct([]);
        } catch (Exception $exception) {
            OrganizerHelper::message($exception->getMessage(), 'error');

            return;
        }

        if (!empty($schedule)) {
            $this->schedule = $schedule;
        }
    }

    /**
     * Adds a configuration to the configurations array, and adds it's index from that array to the array of
     * configurations for the active instance.
     *
     * @param string  $configuration  the configuration object as a string
     * @param array  &$configurations the array holding configurations
     * @param object &$activeInstance the instance object currently being processed
     *
     * @return void  modifies $configurations and the configurations property of $activeInstance
     */
    private function addConfiguration($configuration, &$configurations, &$activeInstance)
    {
        if (!in_array($configuration, $configurations)) {
            $configurations[] = $configuration;
        }

        $configurationID = array_search($configuration, $configurations);

        if (!in_array($configurationID, $activeInstance->configurations)) {
            $activeInstance->configurations[] = $configurationID;
        }
    }

    /**
     * Ensures that deprecated configurations are no longer referenced from the calendar.
     *
     * @param array &$instanceConfigs the configuration indexes referenced by the lesson instance
     *
     * @return void removes deprecated references from the instance configurations
     */
    private function checkConfigurationIntegrity(&$instanceConfigs)
    {
        foreach ($instanceConfigs as $instanceConfigIndex => $configIndex) {
            if (!isset($this->schedule->configurations[$configIndex])) {
                if (is_object($instanceConfigs)) {
                    unset($instanceConfigs->$instanceConfigIndex);
                }
                if (is_array($instanceConfigs)) {
                    unset($instanceConfigs[$instanceConfigIndex]);
                }
            }
        }
    }

    /**
     * Retrieves the configurations associated with the lesson instance
     *
     * @param int   $lessonID      the id of the lesson in the database
     * @param array $calendarEntry the the calendar entry being currently iterated
     * @param array $lessonCourses an array containing the course id and lesson course id (id), indexed by the course id
     *
     * @return array
     */
    private function getInstanceConfigurations($lessonID, $calendarEntry, $lessonCourses)
    {
        $date      = $calendarEntry['schedule_date'];
        $startTime = date('Hi', strtotime($calendarEntry['startTime']));
        $endTime   = date('Hi', strtotime($calendarEntry['endTime']));
        $timeKey   = $startTime . '-' . $endTime;

        $configurations = [];

        if (empty($this->schedule->calendar->$date->$timeKey->$lessonID)) {
            return $configurations;
        }

        $configIndexes = $this->schedule->calendar->$date->$timeKey->$lessonID->configurations;

        foreach ($configIndexes as $instanceIndex => $configIndex) {
            /**
             * lessonID => the untis lesson id
             * courseID => the course id
             * teachers & rooms => the teachers and rooms for this configuration
             */
            $rawConfig     = $this->schedule->configurations[$configIndex];
            $configuration = json_decode($rawConfig);

            // TODO: find out where these values are coming from
            if ($configuration->lessonID != $lessonID) {
                unset($this->schedule->calendar->$date->$timeKey->$lessonID->configurations[$instanceIndex]);
                continue;
            }

            $lessonCourseID = $lessonCourses[$configuration->courseID]['id'];
            $pullConfig     = $configuration;
            unset($pullConfig->lessonID, $pullConfig->courseID);
            $pullConfig   = json_encode($pullConfig);
            $configData   = ['lessonCourseID' => $lessonCourseID, 'configuration' => $pullConfig];
            $configsTable = OrganizerHelper::getTable('LessonConfigurations');
            $exists       = $configsTable->load($configData);

            if ($exists) {
                $configurations[] = $configsTable->id;
            }
        }

        return $configurations;
    }

    /**
     * Maps configurations to calendar entries
     *
     * @return bool true on success, otherwise false
     */
    private function mapConfigurations()
    {
        foreach (array_keys((array)$this->schedule->lessons) as $lessonUntisID) {
            $lessonsData                 = [];
            $lessonsData['untisID']      = $lessonUntisID;
            $lessonsData['departmentID'] = $this->schedule->departmentID;
            $lessonsData['termID']       = $this->schedule->termID;

            $lessonsTable = OrganizerHelper::getTable('Lessons');
            $lessonExists = $lessonsTable->load($lessonsData);

            // Should not occur
            if (!$lessonExists) {
                return false;
            }

            $lessonID = $lessonsTable->id;

            // Get the calendar entries which reference the lesson
            $calendarQuery = $this->_db->getQuery(true);
            $calendarQuery->select('id, schedule_date, startTime, endTime')
                ->from('#__thm_organizer_calendar')
                ->where("lessonID = '$lessonID'");
            $this->_db->setQuery($calendarQuery);

            $calendarEntries = OrganizerHelper::executeQuery('loadAssocList', [], 'id');

            // Occurs when the planner left the room blank
            if (empty($calendarEntries)) {
                continue;
            }

            $lessonCoursesQuery = $this->_db->getQuery(true);
            $lessonCoursesQuery->select('id, courseID')
                ->from('#__thm_organizer_lesson_courses')
                ->where("lessonID = '$lessonID'");
            $this->_db->setQuery($lessonCoursesQuery);

            $lessonCourses = OrganizerHelper::executeQuery('loadAssocList', [], 'courseID');

            // Should not occur
            if (empty($lessonCourses)) {
                return false;
            }

            foreach ($calendarEntries as $calendarID => $calendarEntry) {
                $instanceConfigs = $this->getInstanceConfigurations($lessonUntisID, $calendarEntry, $lessonCourses);

                $configIDs = [];

                foreach ($instanceConfigs as $configID) {
                    $mapData  = ['calendarID' => $calendarID, 'configurationID' => $configID];
                    $mapTable = OrganizerHelper::getTable('CalendarConfigurationMap');
                    $mapTable->load($mapData);
                    $success = $mapTable->save($mapData);

                    if (!$success) {
                        return false;
                    }

                    $configIDs[$configID] = $configID;
                }

                if (!empty($configIDs)) {
                    $deprecatedQuery = $this->_db->getQuery(true);
                    $deprecatedQuery->delete('#__thm_organizer_calendar_configuration_map')
                        ->where("calendarID = '$calendarID'")
                        ->where('configurationID NOT IN (' . implode(', ', $configIDs) . ')');
                    $this->_db->setQuery($deprecatedQuery);

                    $success = (bool)OrganizerHelper::executeQuery('execute');
                    if (empty($success)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Removes calendar entries with the same base data
     *
     * @param array $calData    the data used to find matching calendar entries
     * @param int   $calendarID the valid calendar entry id
     *
     * @return bool true on success, otherwise false
     */
    private function removeCalendarDuplicates($calData, $calendarID)
    {
        $query = $this->_db->getQuery(true);
        $query->delete('#__thm_organizer_calendar')
            ->where("schedule_date = '{$calData['schedule_date']}'")
            ->where("startTime = '{$calData['startTime']}'")
            ->where("endTime = '{$calData['endTime']}'")
            ->where("lessonID = '{$calData['lessonID']}'")
            ->where("id != '$calendarID'");

        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }

    /**
     * Removes delta information from a schedule
     *
     * @param string $source the schedule being processed
     *
     * @return void
     */
    public function sanitize($source)
    {
        $this->sanitizeObjectNodes($this->$source->lessons);
        $this->sanitizeConfigurations($this->$source->configurations);
        $this->sanitizeCalendar($this->$source->calendar);
        if (isset($this->$source->referenceID)) {
            unset($this->$source->referenceID);
        }
    }

    /**
     * Removes delta information from the calendar
     *
     * @param object &$calendar the schedule configurations
     *
     * @return void removes delta information and unsets removed schedule entries
     */
    public function sanitizeCalendar(&$calendar)
    {
        foreach ($calendar as $date => $blocks) {
            foreach ($blocks as $blockTimes => $lessons) {
                $this->sanitizeObjectNodes($lessons);
                $empty = empty((array)$lessons);
                if ($empty) {
                    unset($calendar->$date->$blockTimes);
                    continue;
                }
            }
        }
    }

    /**
     * Removes delta information from array collections
     *
     * @param object &$numericCollection the array collection being currently iterated
     *
     * @return void removes delta information and unsets removed schedule entries
     */
    private function sanitizeNumericCollection(&$numericCollection)
    {
        if (!is_object($numericCollection) or empty($numericCollection)) {
            $numericCollection = null;

            return;
        }

        foreach ($numericCollection as $resourceID => $delta) {
            if (!empty($delta) and $delta == 'removed') {
                unset($numericCollection->$resourceID);
            } else {
                $numericCollection->$resourceID = '';
            }
        }
    }

    /**
     * Removes delta information from the configurations
     *
     * @param object &$configurations the schedule configurations
     *
     * @return void removes delta information and unsets removed schedule entries
     */
    private function sanitizeConfigurations(&$configurations)
    {
        foreach ($configurations as $index => $rawConfiguration) {
            // Decodes and converts to assoc arrays
            $configuration = json_decode($rawConfiguration);

            $this->sanitizeNumericCollection($configuration->teachers);
            $noInstanceTeachers = empty($configuration->teachers);
            if ($noInstanceTeachers) {
                unset($configurations[$index]);
                continue;
            }

            $this->sanitizeNumericCollection($configuration->rooms);
            $noInstanceRooms = empty($configuration->rooms);
            if ($noInstanceRooms) {
                unset($configurations[$index]);
                continue;
            }

            $configurations[$index] = json_encode($configuration);
        }
    }

    /**
     * Removes delta information from object collections
     *
     * @param object &$objectNodes the object collection being currently iterated
     *
     * @return void removes delta information and unsets removed schedule entries
     */
    private function sanitizeObjectNodes(&$objectNodes)
    {
        foreach ($objectNodes as $objectID => $object) {
            if (!empty($object->delta) and $object->delta == 'removed') {
                unset($objectNodes->$objectID);
            } else {
                $objectNodes->$objectID->delta = '';
            }

            // If subordinate nodes/collections are empty after sanitization => remove.
            if (isset($object->courses)) {
                $this->sanitizeObjectNodes($object->courses);
                $empty = empty((array)$object->courses);
                if ($empty) {
                    unset($objectNodes->$objectID);
                    continue;
                }
            }

            if (isset($object->groups)) {
                $this->sanitizeNumericCollection($object->groups);
                $empty = empty($object->groups);
                if ($empty) {
                    unset($objectNodes->$objectID);
                    continue;
                }
            }

            if (isset($object->teachers)) {
                $this->sanitizeNumericCollection($object->teachers);
                $empty = empty($object->teachers);
                if ($empty) {
                    unset($objectNodes->$objectID);
                    continue;
                }
            }

            if (isset($object->configurations)) {
                $this->checkConfigurationIntegrity($object->configurations);
                $empty = empty($object->configurations);
                if ($empty) {
                    unset($objectNodes->$objectID);
                    continue;
                }
            }
        }
    }

    /**
     * Saves dynamic schedule information to the database.
     *
     * @param object &$schedule the schedule being processed
     *
     * @return boolean true on success, otherwise false
     */
    public function save(&$schedule = null)
    {
        if (!empty($schedule)) {
            $this->schedule = $schedule;
        }

        if (empty($this->schedule)) {
            return false;
        }

        $this->_db->transactionStart();

        $lessonsSaved         = $this->saveLessons();
        $configurationsSaved  = $this->saveConfigurations();
        $calendarSaved        = $this->saveCalendar();
        $configurationsMapped = $this->mapConfigurations();

        $success = ($lessonsSaved and $configurationsSaved and $calendarSaved and $configurationsMapped);
        if ($success) {
            $this->_db->transactionCommit();

            return true;
        }

        $this->_db->transactionRollback();

        return false;
    }

    /**
     * Creates calendar entries in the database
     *
     * @return bool true on success, otherwise false
     */
    private function saveCalendar()
    {
        $lessonEntries = [];
        foreach ($this->schedule->calendar as $date => $times) {
            $calData                  = [];
            $calData['schedule_date'] = $date;

            foreach ($times as $startEnd => $lessons) {
                list($startTime, $endTime) = explode('-', $startEnd);
                $calData['startTime'] = $startTime . '00';
                $calData['endTime']   = $endTime . '00';

                foreach ($lessons as $lessonID => $instanceData) {
                    $lessonsData                 = [];
                    $lessonsData['untisID']      = $lessonID;
                    $lessonsData['departmentID'] = $this->schedule->departmentID;
                    $lessonsData['termID']       = $this->schedule->termID;

                    $lessonsTable = OrganizerHelper::getTable('Lessons');
                    $lessonsTable->load($lessonsData);

                    if (empty($lessonsTable->id)) {
                        return false;
                    }

                    $calData['lessonID'] = $lessonsTable->id;
                    $calendarTable       = OrganizerHelper::getTable('Calendar');

                    $calendarTable->load($calData);
                    $calData['delta'] = $instanceData->delta;
                    $success          = $calendarTable->save($calData);

                    if (!$success) {
                        return false;
                    }

                    $duplicatesRemoved = $this->removeCalendarDuplicates($calData, $calendarTable->id);

                    if (!$duplicatesRemoved) {
                        return false;
                    }

                    if (empty($lessonEntries[$lessonID])) {
                        $lessonEntries[$lessonID] = [];
                    }

                    $lessonEntries[$lessonsTable->id][$calendarTable->id] = $calendarTable->id;
                }
            }
        }

        // Set deprecated moves to removed
        $deprecatedQuery = $this->_db->getQuery(true);
        $deprecatedQuery->update('#__thm_organizer_calendar');
        $deprecatedQuery->set("delta = 'removed'");

        foreach ($lessonEntries as $lessonID => $calendarIDArray) {
            $calendarIDs = "'" . implode("', '", $calendarIDArray) . "'";
            $deprecatedQuery->clear('where');
            $deprecatedQuery->where("lessonID = '$lessonID'");
            $deprecatedQuery->where("id NOT IN ($calendarIDs)");
            $deprecatedQuery->where("delta != 'removed'");
            $this->_db->setQuery($deprecatedQuery);

            $success = (bool)OrganizerHelper::executeQuery('execute');
            if (!$success) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates lesson configuration entries in the database
     *
     * @return bool true on success, otherwise false
     */
    private function saveConfigurations()
    {
        foreach ($this->schedule->configurations as $json) {
            $config = json_decode($json);

            $lessonsData                 = [];
            $lessonsData['untisID']      = $config->lessonID;
            $lessonsData['departmentID'] = $this->schedule->departmentID;
            $lessonsData['termID']       = $this->schedule->termID;

            $lessonsTable = OrganizerHelper::getTable('Lessons');
            $lessonsTable->load($lessonsData);

            if (empty($lessonsTable->id)) {
                return false;
            }

            $lCourseData             = [];
            $lCourseData['lessonID'] = $lessonsTable->id;
            $lCourseData['courseID'] = $config->courseID;

            $lCoursesTable = OrganizerHelper::getTable('LessonCourses');
            $lCoursesTable->load($lCourseData);

            if (empty($lCoursesTable->id)) {
                return false;
            }

            // Information would be redundant in the db
            unset($config->lessonID, $config->courseID);

            $configData    = ['lessonCourseID' => $lCoursesTable->id, 'configuration' => json_encode($config)];
            $lConfigsTable = OrganizerHelper::getTable('LessonConfigurations');
            $lConfigsTable->load($configData);
            $success = $lConfigsTable->save($configData);
            if (!$success) {
                return false;
            }
        }

        return true;
    }

    /**
     * Saves the lessons from the schedule object to the database and triggers functions for saving lesson associations.
     *
     * @return boolean true if the save process was successful, otherwise false
     */
    private function saveLessons()
    {
        $departmentID = $this->schedule->departmentID;
        $termID       = $this->schedule->termID;
        foreach ($this->schedule->lessons as $untisID => $lesson) {
            // If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
            $table = OrganizerHelper::getTable('Lessons');

            $data                 = [];
            $data['untisID']      = $untisID;
            $data['departmentID'] = $departmentID;
            $data['termID']       = $termID;

            $table->load($data);

            if (!empty($lesson->methodID)) {
                $data['methodID'] = $lesson->methodID;
            }

            $data['delta']   = empty($lesson->delta) ? '' : $lesson->delta;
            $data['comment'] = empty($lesson->comment) ? '' : $lesson->comment;

            $success = $table->save($data);

            if (!$success) {
                return false;
            }

            $coursesSaved = $this->saveLessonCourses($table->id, $lesson->courses);

            if (!$coursesSaved) {
                return false;
            }
        }

        $lessonIDs = array_keys((array)$this->schedule->lessons);

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_lessons')->set("delta = 'removed'")
            ->where("departmentID = '$departmentID'")->where("termID = '$termID'")
            ->where("untisID NOT IN ('" . implode("', '", $lessonIDs) . "')")
            ->where("delta != 'removed'");
        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }

    /**
     * Saves the lesson groups from the schedule object to the database and triggers functions for saving lesson
     * associations.
     *
     * @param string $lessonCourseID the db id of the lesson course association
     * @param object $groups         the groups associated with the course
     * @param int    $courseID       the id of the course
     * @param string $subjectNo      the module number of the subject
     *
     * @return boolean true if the save process was successful, otherwise false
     */
    private function saveLessonGroups($lessonCourseID, $groups, $courseID, $subjectNo)
    {
        $processedIDs = [];

        foreach ($groups as $groupID => $delta) {
            // If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
            $table = OrganizerHelper::getTable('LessonGroups');

            $data                   = [];
            $data['lessonCourseID'] = $lessonCourseID;
            $data['groupID']        = $groupID;
            $table->load($data);

            $data['delta'] = $delta;

            $success = $table->save($data);

            if (!$success) {
                OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
                continue;
            }

            $processedIDs[] = $table->id;

            if (!empty($subjectNo)) {
                $this->saveCourseMapping($courseID, $groupID, $subjectNo);
            }
        }

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_lesson_groups')
            ->set("delta = 'removed'")
            ->where("id NOT IN ('" . implode("', '", $processedIDs) . "')")
            ->where("lessonCourseID = '$lessonCourseID'")
            ->where("delta != 'removed'");
        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }

    /**
     * Saves the lesson courses from the schedule object to the database and triggers functions for saving lesson
     * associations.
     *
     * @param string $lessonID the db id of the lesson
     * @param object $courses  the courses associated with the lesson
     *
     * @return boolean true if the save process was successful, otherwise false
     */
    private function saveLessonCourses($lessonID, $courses)
    {
        $processedIDs = [];

        foreach ($courses as $courseID => $courseData) {
            // If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
            $table = OrganizerHelper::getTable('LessonCourses');

            $data             = [];
            $data['lessonID'] = $lessonID;
            $data['courseID'] = $courseID;
            $table->load($data);

            $data['delta'] = $courseData->delta;

            $success = $table->save($data);

            if (!$success) {
                OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');

                continue;
            }

            $processedIDs[] = $table->id;
            $subjectNo      = empty($courseData->subjectNo) ? null : $courseData->subjectNo;

            $groupSaved = $this->saveLessonGroups($table->id, $courseData->groups, $courseID, $subjectNo);

            if (!$groupSaved) {
                return false;
            }

            $teachersSaved = $this->saveLessonTeachers($table->id, $courseData->teachers);

            if (!$teachersSaved) {
                return false;
            }
        }

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_lesson_courses')
            ->set("delta = 'removed'")
            ->where("id NOT IN ('" . implode("', '", $processedIDs) . "')")
            ->where("lessonID = '$lessonID'")
            ->where("delta != 'removed'");
        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }

    /**
     * Saves the lesson groups from the schedule object to the database and triggers functions for saving lesson
     * associations.
     *
     * @param string $lessonCourseID the id of the lesson => course association
     * @param object $teachers       the teachers associated with the course
     *
     * @return boolean true if the save process was successful, otherwise false
     */
    private function saveLessonTeachers($lessonCourseID, $teachers)
    {
        $processedIDs = [];

        foreach (array_keys((array)$teachers) as $teacherID) {
            // If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
            $table = OrganizerHelper::getTable('LessonTeachers');

            $data                   = [];
            $data['lessonCourseID'] = $lessonCourseID;
            $data['teacherID']      = $teacherID;
            $table->load($data);

            // Delta will be 'calculated' later but explicitly overwritten now irregardless
            $data['delta'] = '';

            $success = $table->save($data);

            if (!$success) {
                OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
                continue;
            }

            $processedIDs[] = $table->id;
        }

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_lesson_teachers')
            ->set("delta = 'removed'")
            ->where("id NOT IN ('" . implode("', '", $processedIDs) . "')")
            ->where("lessonCourseID = '$lessonCourseID'")
            ->where("delta != 'removed'");
        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }

    /**
     * Attempts to associate courses with subjects
     *
     * @param string $courseID  the id of the event
     * @param string $groupID   the id of the group
     * @param string $subjectNo the module number of the subject
     *
     * @return void saves/updates a database entry
     */
    private function saveCourseMapping($courseID, $groupID, $subjectNo)
    {
        // Get the mapping boundaries for the program
        $boundariesQuery = $this->_db->getQuery(true);
        $boundariesQuery->select('lft, rgt')
            ->from('#__thm_organizer_mappings as m')
            ->innerJoin('#__thm_organizer_programs as prg on m.programID = prg.id')
            ->innerJoin('#__thm_organizer_categories as cat on cat.programID = prg.id')
            ->innerJoin('#__thm_organizer_groups as gr on gr.categoryID = cat.id')
            ->where("gr.id = '$groupID'");
        $this->_db->setQuery($boundariesQuery);
        $boundaries = OrganizerHelper::executeQuery('loadAssoc', []);

        if (empty($boundaries)) {
            return;
        }

        // Get the id for the subject documentation
        $subjectQuery = $this->_db->getQuery(true);
        $subjectQuery->select('subjectID')
            ->from('#__thm_organizer_mappings as m')
            ->innerJoin('#__thm_organizer_subjects as s on m.subjectID = s.id')
            ->where("m.lft > '{$boundaries['lft']}'")
            ->where("m.rgt < '{$boundaries['rgt']}'")
            ->where("s.externalID = '$subjectNo'");
        $this->_db->setQuery($subjectQuery);

        $subjectID = OrganizerHelper::executeQuery('loadResult');
        if (empty($subjectID)) {
            return;
        }

        $data  = ['subjectID' => $subjectID, 'courseID' => $courseID];
        $table = OrganizerHelper::getTable('SubjectMappings');
        $table->load($data);
        $table->save($data);
    }

    /**
     * Creates the delta to the chosen reference schedule
     *
     * @param object  $reference    the reference schedule
     * @param object  $active       the active schedule
     * @param boolean $shouldNotify true if users should be notified
     *
     * @return boolean true on successful delta creation, otherwise false
     */
    public function setReference($reference, $active, $shouldNotify = false)
    {
        $this->shouldNotify = $shouldNotify;
        $this->refSchedule  = json_decode($reference->schedule);
        $this->schedule     = json_decode($active->schedule);

        $this->sanitize('refSchedule');
        $this->sanitize('schedule');

        // Protect the active delta in case of fail
        $this->_db->transactionStart();

        $this->schedule->referenceID = $reference->id;
        $reference->set('schedule', json_encode($this->refSchedule));
        $reference->set('active', 0);
        $refSuccess = $reference->store();

        if (!$refSuccess) {
            $this->_db->transactionRollback();

            return false;
        }

        $this->setLessonReference();
        $this->setCalendarReference();

        $active->set('schedule', json_encode($this->schedule));
        $active->set('active', 1);
        $activeSuccess = $active->store();

        if (!$activeSuccess) {
            $this->_db->transactionRollback();

            return false;
        } elseif ($shouldNotify) {
            $this->notify();
        }

        $this->_db->transactionCommit();

        $dbSuccess = $this->save();
        if (!$dbSuccess) {
            OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SCHEDULE_SAVE_FAIL', 'notice');

            return false;
        }

        return true;
    }

    /**
     * Compares the lessons of the reference schedule with the active one and sets delta information
     *
     * @return void modifies information in the schedule lessons collection
     */
    private function setCalendarReference()
    {
        // This will later overwrite the current active schedule configurations
        $configurations = [];

        $refCalendarDates    = array_keys((array)$this->refSchedule->calendar);
        $activeCalendarDates = array_keys((array)$this->schedule->calendar);

        $dates = array_intersect($refCalendarDates, $activeCalendarDates);

        foreach ($dates as $date) {
            $referenceTimes = array_keys((array)$this->refSchedule->calendar->$date);
            $activeTimes    = array_keys((array)$this->schedule->calendar->$date);

            $times = array_intersect($referenceTimes, $activeTimes);

            foreach ($times as $time) {
                $referenceLessonIDs = array_keys((array)$this->refSchedule->calendar->$date->$time);
                $activeLessonIDs    = array_keys((array)$this->schedule->calendar->$date->$time);

                $lessonIDs = array_intersect($referenceLessonIDs, $activeLessonIDs);

                foreach ($lessonIDs as $lessonID) {
                    $referenceInstance = $this->refSchedule->calendar->$date->$time->$lessonID;
                    $instance          = $this->schedule->calendar->$date->$time->$lessonID;
                    $changeAttributes  = ['lessonID' => $lessonID, 'date' => $date, 'time' => $time];
                    $this->setConfigurationReferences(
                        $referenceInstance,
                        $instance,
                        $configurations,
                        $changeAttributes
                    );
                    $this->schedule->calendar->$date->$time->$lessonID = $instance;
                }

                $removedLessonIDs = array_diff($referenceLessonIDs, $activeLessonIDs);
                $this->transferInstances($removedLessonIDs, 'removed', $date, $time, $configurations);

                $newLessonIDs = array_diff($activeLessonIDs, $referenceLessonIDs);
                $this->transferInstances($newLessonIDs, 'new', $date, $time, $configurations);
            }

            $removedTimes = array_diff($referenceTimes, $activeTimes);
            $this->transferTimes('removed', $date, $removedTimes, $configurations);

            $newTimes = array_diff($activeTimes, $referenceTimes);
            $this->transferTimes('new', $date, $newTimes, $configurations);
        }

        $removedDates = array_diff($refCalendarDates, $activeCalendarDates);

        foreach ($removedDates as $date) {
            $times = array_keys((array)$this->refSchedule->calendar->$date);
            $this->transferTimes('removed', $date, $times, $configurations);
        }

        $newDates = array_diff($activeCalendarDates, $refCalendarDates);

        foreach ($newDates as $date) {
            $times = array_keys((array)$this->schedule->calendar->$date);
            $this->transferTimes('new', $date, $times, $configurations);
        }

        $this->schedule->configurations = $configurations;
    }

    /**
     * Sets the configurations for the instance being iterated
     *
     * @param object &$instance       the instance being iterated
     * @param array   $configurations the array holding the configurations
     * @param string  $source         [schedule|refSchedule]
     */
    private function setConfigurations(&$instance, &$configurations, $source)
    {
        $instanceConfigs = [];
        foreach ($instance->configurations as $instanceIndex => $globalIndex) {
            $instanceConfigs[] = $this->$source->configurations[$globalIndex];
            unset($instance->configurations[$instanceIndex]);
        }

        foreach ($instanceConfigs as $configuration) {
            $this->addConfiguration($configuration, $configurations, $instance);
        }
    }

    /**
     * Sets the configuration references for the instance being iterated
     *
     * @param object  $referenceInstance the old instance data
     * @param object &$activeInstance    the active instance data
     * @param array   $configurations    the array holding the configurations
     *
     * @return void modifies $activeInstance and $configurations
     */
    private function setConfigurationReferences(
        $referenceInstance,
        &$activeInstance,
        &$configurations,
        $changeAttributes
    ) {
        $referenceConfigs = [];
        foreach ($referenceInstance->configurations as $refConfigIndex) {
            if (!empty($this->refSchedule->configurations[$refConfigIndex])) {
                $referenceConfigs[] = $this->refSchedule->configurations[$refConfigIndex];
            }
        }

        $activeConfigs = [];
        foreach ($activeInstance->configurations as $activeConfigIndex) {
            $activeConfigs[] = $this->schedule->configurations[$activeConfigIndex];
        }

        // These will be renumbered in the following
        $activeInstance->configurations = [];

        $unchangedConfigs = array_intersect($referenceConfigs, $activeConfigs);

        foreach ($unchangedConfigs as $unchangedConfig) {
            $this->addConfiguration($unchangedConfig, $configurations, $activeInstance);
        }

        $oldConfigurations = array_diff($referenceConfigs, $activeConfigs);
        $newConfigurations = array_diff($activeConfigs, $referenceConfigs);

        foreach ($newConfigurations as $newConfiguration) {
            $newConfigObject = json_decode($newConfiguration);
            $teachers        = array_keys((array)$newConfigObject->teachers);
            $rooms           = array_keys((array)$newConfigObject->rooms);
            $comparisonFound = false;

            foreach ($oldConfigurations as $oldConfiguration) {
                $oldConfigObject = json_decode($oldConfiguration);

                /**
                 * Changes of to courses are handled at the lesson courses level.
                 * Deprecated courses associations don't need config deltas.
                 */
                if ($oldConfigObject->courseID != $newConfigObject->courseID) {
                    continue;
                }

                $comparisonFound = true;

                $oldTeachers = array_keys((array)$oldConfigObject->teachers);

                // Teachers which are not in either diff should have blank values

                $removedTeachers = array_diff($oldTeachers, $teachers);
                foreach ($removedTeachers as $removedTeacherID) {
                    $newConfigObject->teachers->$removedTeacherID = 'removed';
                }

                $newTeachers = array_diff($teachers, $oldTeachers);
                foreach ($newTeachers as $newTeacherID) {
                    $newConfigObject->teachers->$newTeacherID = 'new';
                }

                $oldRooms = array_keys((array)$oldConfigObject->rooms);

                // Rooms which are not in either diff should have blank values
                $removedRooms = array_diff($oldRooms, $rooms);
                foreach ($removedRooms as $removedRoomID) {
                    $this->addChangedRoom(
                        $removedRoomID,
                        $changeAttributes['lessonID'],
                        $changeAttributes['date'],
                        $changeAttributes['time'],
                        "removed"
                    );
                    $newConfigObject->rooms->$removedRoomID = 'removed';
                }

                $newRooms = array_diff($rooms, $oldRooms);
                foreach ($newRooms as $newRoomID) {
                    $this->addChangedRoom(
                        $newRoomID,
                        $changeAttributes['lessonID'],
                        $changeAttributes['date'],
                        $changeAttributes['time'],
                        "new"
                    );
                    $newConfigObject->rooms->$newRoomID = 'new';
                }


            }

            // Course was newly added to the lesson
            if (!$comparisonFound) {
                foreach ($teachers as $teacherID) {
                    $newConfigObject->teachers->$teacherID = 'new';
                }

                foreach ($rooms as $roomID) {
                    $newConfigObject->rooms->$roomID = 'new';
                }
            }

            $diffConfig = json_encode($newConfigObject);

            $this->addConfiguration($diffConfig, $configurations, $activeInstance);
        }
    }

    /**
     * Compares the lessons of the reference schedule with the active one and sets delta information
     *
     * @return void modifies information in the schedule lessons collection
     */
    private function setLessonReference()
    {
        $referenceLessonIDs = array_keys((array)$this->refSchedule->lessons);
        $activeLessonIDs    = array_keys((array)$this->schedule->lessons);

        $carriedLessons = array_intersect($referenceLessonIDs, $activeLessonIDs);

        foreach ($carriedLessons as $carriedLessonID) {
            $activeCourses      = $this->schedule->lessons->$carriedLessonID->courses;
            $activeCourseIDs    = array_keys((array)$activeCourses);
            $refCourses         = $this->refSchedule->lessons->$carriedLessonID->courses;
            $referenceCourseIDs = array_keys((array)$refCourses);

            $carriedCourseIDs = array_intersect($referenceCourseIDs, $activeCourseIDs);

            foreach ($carriedCourseIDs as $carriedCourseID) {
                $activeCourse = $activeCourses->$carriedCourseID;
                $refCourse    = $refCourses->$carriedCourseID;

                $referenceGroupIDs = array_keys((array)$refCourse->groups);
                $activeGroupIDs    = array_keys((array)$activeCourse->groups);

                $removedGroupIDs = array_diff($referenceGroupIDs, $activeGroupIDs);

                foreach ($removedGroupIDs as $removedGroupID) {
                    $refCourse->groups->$removedGroupID = 'removed';
                }

                $newGroupIDs = array_diff($activeGroupIDs, $referenceGroupIDs);

                foreach ($newGroupIDs as $newGroupID) {
                    $refCourse->groups->$newGroupID = 'new';
                }

                $referenceTeacherIDs = array_keys((array)$refCourse->teachers);
                $activeTeacherIDs    = array_keys((array)$activeCourse->teachers);

                $removedTeacherIDs = array_diff($referenceTeacherIDs, $activeTeacherIDs);

                foreach ($removedTeacherIDs as $removedTeacherID) {
                    $refCourse->teachers->$removedTeacherID = 'removed';
                }

                $newTeacherIDs = array_diff($activeTeacherIDs, $referenceTeacherIDs);

                foreach ($newTeacherIDs as $newTeacherID) {
                    $refCourse->teachers->$newTeacherID = 'new';
                }
            }

            $removedCourseIDs = array_diff($referenceCourseIDs, $activeCourseIDs);

            foreach ($removedCourseIDs as $removedCourseID) {
                $removedCourse                   = $refCourses->$removedCourseID;
                $removedCourse->delta            = 'removed';
                $activeCourses->$removedCourseID = $removedCourse;
            }

            $newCourseIDs = array_diff($activeCourseIDs, $referenceCourseIDs);

            foreach ($newCourseIDs as $newCourseID) {
                $activeCourses->$newCourseID->delta = 'new';
            }
        }

        $removedLessonIDs = array_diff($referenceLessonIDs, $activeLessonIDs);

        foreach ($removedLessonIDs as $removedLessonID) {
            $this->schedule->lessons->$removedLessonID = $this->refSchedule->lessons->$removedLessonID;

            $this->schedule->lessons->$removedLessonID->delta = 'removed';
        }

        $newLessonIDs = array_diff($activeLessonIDs, $referenceLessonIDs);

        foreach ($newLessonIDs as $newLessonID) {
            $this->schedule->lessons->$newLessonID->delta = 'new';
        }
    }

    /**
     * Transfers instances which need no configuration reference processing. (The instance itself is new or removed.)
     *
     * @param array  $lessonIDs      the lessonIDs for the instances to be transferred
     * @param string $status         the batch instance status [new|removed]
     * @param string $date           the date when the instance occurs
     * @param string $time           the time interval object when the instance occurs
     * @param array  $configurations the array holding the configurations
     *
     * @return void modifies the schedule time interval object
     */
    private function transferInstances($lessonIDs, $status, $date, $time, &$configurations)
    {
        $source = $status == 'new' ? 'schedule' : 'refSchedule';
        foreach ($lessonIDs as $lessonID) {
            $instance = $this->$source->calendar->$date->$time->$lessonID;

            $instance->delta          = $status;
            $instance->configurations = (array)$instance->configurations;

            $this->setConfigurations($instance, $configurations, $source);
            $this->schedule->calendar->$date->$time->$lessonID = $instance;
            $this->addChange($lessonID, $status, $date, $time);
        }
    }

    /**
     * Transfers time intervals which need no configuration reference processing, because the time interval itself is
     * new or removed.
     *
     * @param string $status         the batch instance status [new|removed]
     * @param string $date           the date when the times occur
     * @param array  $times          the time intervals to be transferred
     * @param array  $configurations the array holding the configurations
     *
     * @return void modifies the schedule date object
     */
    private function transferTimes($status, $date, $times, &$configurations)
    {
        $source = $status == 'new' ? 'schedule' : 'refSchedule';

        if (empty($this->schedule->calendar->$date)) {
            $this->schedule->calendar->$date = new \stdClass;
        }

        foreach ($times as $time) {
            if (empty($this->schedule->calendar->$date->$time)) {
                $this->schedule->calendar->$date->$time = new \stdClass;
            }
            $lessonIDs = array_keys((array)$this->$source->calendar->$date->$time);
            $this->transferInstances($lessonIDs, $status, $date, $time, $configurations);
        }
    }

    /**
     * Notifies all users which are subscribed to the changed lessons about the change via mail
     *
     *
     * @return void modifies the schedule date object
     */
    private function notify()
    {
        return;
        foreach ($this->scheduleChanges as $lessonID => $attributes) {
            for ($i = 0; $i < count($attributes); $i++) {
                if (count($attributes) > $i + 1) {
                    for ($j = $i; $j < count($attributes); $j++) {
                        if ($attributes[$i]['status'] != $attributes[$j]['status']) {
                            if ($attributes[$i]['status'] == 'new') {
                                $attributes[$i]['oldDate']  = $attributes[$j]['oldDate'];
                                $attributes[$i]['oldTime']  = $attributes[$j]['oldTime'];
                                $attributes[$i]['oldRooms'] = array_merge(
                                    $attributes[$i]['oldRooms'],
                                    $attributes[$j]['oldRooms']
                                );
                            } else {
                                $attributes[$i]['newDate']  = $attributes[$j]['newDate'];
                                $attributes[$i]['newTime']  = $attributes[$j]['newTime'];
                                $attributes[$i]['newRooms'] = array_merge(
                                    $attributes[$i]['newRooms'],
                                    $attributes[$j]['newRooms']
                                );
                            }
                            $attributes[$i]['status'] = "moved";
                            array_splice($this->scheduleChanges[$lessonID], $j, 1);
                            array_splice($attributes, $j, 1);
                            break;
                        }
                    }
                    $this->scheduleChanges[$lessonID][$i] = $attributes[$i];
                }
            }
        }

        foreach ($this->scheduleChanges as $lessonID => $attributes) {
            $participants = Courses::getFullParticipantData($lessonID);
            foreach ($participants as $participant) {
                $participantID = $participant['id'];
                if (!$this->getNotify($participantID)) {
                    continue;
                }

                $mailer = Factory::getMailer();

                $user       = Factory::getUser($participantID);
                $userParams = json_decode($user->params, true);
                $mailer->addRecipient($user->email);

                if (!empty($userParams['language'])) {
                    Input::getInput()->set('languageTag', explode('-', $userParams['language'])[0]);
                }

                $params = Input::getParams();
                $sender = Factory::getUser($params->get('mailSender'));

                if (empty($sender->id)) {
                    return;
                }

                $mailer->setSender([$sender->email, $sender->name]);

                $course = Courses::getCourse($lessonID);
                if (empty($course)) {
                    return;
                }

                $campus     = Courses::getCampus($lessonID);
                $courseName = (empty($campus) or empty($campus['name'])) ?
                    $course['name'] : "{$course['name']} ({$campus['name']})";
                $mailer->setSubject($courseName);
                $body           = Languages::_('THM_ORGANIZER_GREETING') . ',\n\n';
                $newLessons     = [];
                $removedLessons = [];
                $movedLessons   = [];
                for ($i = 0; $i < count($attributes); $i++) {
                    $oldDate = $attributes[$i]['oldDate'];
                    if ($oldDate != "") {
                        $strModArr = explode('-', $oldDate);
                        $oldDate   = $strModArr[2] . "." . $strModArr[1] . "." . $strModArr[0];
                    }

                    $newDate = $attributes[$i]['newDate'];
                    if ($newDate != "") {
                        $strModArr = explode('-', $newDate);
                        $newDate   = $strModArr[2] . "." . $strModArr[1] . "." . $strModArr[0];
                    }

                    $oldTime = $attributes[$i]['oldTime'];
                    if ($oldTime != "") {
                        $oldTime = substr($oldTime, 0, 2) . ":" . substr($oldTime, 2, 5) . ":" .
                            substr($oldTime, 7, 2);
                    }

                    $newTime = $attributes[$i]['newTime'];
                    if ($newTime != "") {
                        $newTime = substr($newTime, 0, 2) . ":" . substr($newTime, 2, 5) . ":" .
                            substr($newTime, 7, 2);
                    }
                    $attributes[$i]['newDate'] = $newDate;
                    $attributes[$i]['newTime'] = $newTime;
                    $attributes[$i]['oldDate'] = $oldDate;
                    $attributes[$i]['oldTime'] = $oldTime;

                    switch ($attributes[$i]['status']) {
                        case "new":
                            array_push($newLessons, $attributes[$i]);
                            break;
                        case "removed":
                            array_push($removedLessons, $attributes[$i]);
                            break;
                        case "moved":
                            array_push($movedLessons, $attributes[$i]);
                            break;
                    }
                }
                $statusText = '';

                if (count($newLessons) == 1) {
                    $statusText .= sprintf(
                        Languages::_('THM_ORGANIZER_NOTIFICATION_NEW_SINGLE') . '\n',
                        $courseName,
                        $newLessons[0]['newDate'],
                        $newLessons[0]['newTime']
                    );
                } else {
                    if (count($newLessons) > 0) {
                        $statusText .= sprintf(
                            Languages::_('THM_ORGANIZER_NOTIFICATION_NEW_MULTIPLE_HEADER') . '\n\n',
                            $courseName
                        );
                        foreach ($newLessons as $attribute) {
                            $statusText .= sprintf(
                                Languages::_('THM_ORGANIZER_NOTIFICATION_NEW_MULTIPLE') . '\n',
                                $attribute['newDate'],
                                $attribute['newTime']
                            );
                        }
                    }
                }

                if (count($removedLessons) == 1) {
                    $statusText .= sprintf(
                        Languages::_('THM_ORGANIZER_NOTIFICATION_REMOVED_SINGLE') . '\n',
                        $courseName,
                        $removedLessons[0]['oldDate'],
                        $removedLessons[0]['oldTime']
                    );
                } elseif (count($removedLessons) > 0) {
                    $statusText .= sprintf(
                        Languages::_('THM_ORGANIZER_NOTIFICATION_REMOVED_MULTIPLE') . '\n',
                        $courseName
                    );
                }

                if (count($movedLessons) > 0) {
                    $statusText .= sprintf(
                        Languages::_('THM_ORGANIZER_NOTIFICATION_MOVED_HEADER') . '\n\n',
                        $courseName
                    );
                    if (count($movedLessons) < 10) {
                        for ($i = 0; $i < count($movedLessons); $i++) {
                            if ($movedLessons[$i]['oldTime'] != $movedLessons[$i]['newTime'] &&
                                $movedLessons[$i]['oldDate'] == $movedLessons[$i]['newDate']) {
                                $statusText .= sprintf(
                                    Languages::_('THM_ORGANIZER_NOTIFICATION_MOVED_SINGLE_TIME') . '\n',
                                    $movedLessons[$i]['oldDate'],
                                    $movedLessons[$i]['oldTime'],
                                    $movedLessons[$i]['newTime']
                                );
                            } elseif ($movedLessons[$i]['oldTime'] == $movedLessons[$i]['newTime'] &&
                                $movedLessons[$i]['oldDate'] != $movedLessons[$i]['newDate']) {
                                $statusText .= sprintf(
                                    Languages::_('THM_ORGANIZER_NOTIFICATION_MOVED_SINGLE_DATE') . '\n',
                                    $movedLessons[$i]['oldDate'],
                                    $movedLessons[$i]['newDate'],
                                    $movedLessons[$i]['oldTime']
                                );
                            } elseif (count($movedLessons[0]['oldRooms']) > 0) {
                                for ($j = 0; $j < count($movedLessons[$i]['oldRooms']); $j++) {
                                    $statusText .= sprintf(
                                        Languages::_('THM_ORGANIZER_NOTIFICATION_MOVED_ROOM_CHANGED') . '\n',
                                        $movedLessons[$i]['oldDate'],
                                        $movedLessons[$i]['oldTime'],
                                        Rooms::getName($movedLessons[$i]['oldRooms'][$j]),
                                        Rooms::getName($movedLessons[$i]['newRooms'][$j])
                                    );
                                }
                            } else {
                                $statusText .= sprintf(
                                    Languages::_('THM_ORGANIZER_NOTIFICATION_MOVED_SINGLE_BOTH') . '\n',
                                    $movedLessons[0]['oldDate'],
                                    $movedLessons[0]['newDate'],
                                    $movedLessons[0]['oldTime'],
                                    $movedLessons[0]['newTime']
                                );
                            }
                        }
                    } else {
                        $weekdays   = explode('-', Languages::_('THM_ORGANIZER_WEEKDAYS'));
                        $oldWeekday = $weekdays[date('w', strtotime($movedLessons[0]['oldDate']))];
                        $newWeekday = $weekdays[date('w', strtotime($movedLessons[0]['newDate']))];
                        $statusText .= sprintf(
                            Languages::_('THM_ORGANIZER_NOTIFICATION_MOVED_MULTIPLE') . '\n',
                            $oldWeekday,
                            $newWeekday,
                            $movedLessons[0]['newTime']
                        );
                    }
                }
                $body .= ' => ' . $statusText . '\n\n';
                $body .= Languages::_('THM_ORGANIZER_CLOSING') . ',\n';
                $body .= $sender->name . '\n\n';
                $body .= $sender->email . '\n';

                $addressParts = explode(' - ', $params->get('address'));

                foreach ($addressParts as $aPart) {
                    $body .= $aPart . '\n';
                }

                $contactParts = explode(' - ', $params->get('contact'));

                foreach ($contactParts as $cPart) {
                    $body .= $cPart . '\n';
                }

                $mailer->setBody($body);
                $mailer->Send();
            }
        }
    }

    /**
     * adds a change to the scheduleChanges array,
     *
     * @param $lessonID int ID of the lesson
     * @param $status   string status of the change (new|removed)
     * @param $date     string date of change
     * @param $time     string time of the change
     */
    private function addChange($lessonID, $status, $date, $time)
    {
        if (!array_key_exists($lessonID, $this->scheduleChanges)) {
            $this->scheduleChanges[$lessonID] = [];
        }
        if ($status == 'new') {
            $oldDate = "";
            $newDate = $date;
            $oldTime = "";
            $newTime = $time;
        } else {
            $oldDate = $date;
            $newDate = "";
            $oldTime = $time;
            $newTime = "";
        }
        $this->scheduleChanges[$lessonID][] = [
            'oldDate'  => $oldDate,
            'newDate'  => $newDate,
            'oldTime'  => $oldTime,
            'newTime'  => $newTime,
            'status'   => $status,
            'oldRooms' => [],
            'newRooms' => []
        ];
    }

    /**
     * adds a change to the scheduleChanges array, and additionally adds changed rooms
     *
     * @param $lessonID int ID of the lesson
     * @param $status   string status of the change (new|removed)
     * @param $date     string date of change
     * @param $time     string time of the change
     * @param $roomID   ID of the room which was changed
     */
    private function addChangedRoom($lessonID, $status, $date, $time, $roomID)
    {
        $this->addChange($lessonID, $status, $date, $time);
        if ($status == "new") {
            $roomKey = 'newRooms';
        } else {
            $roomKey = 'oldRooms';
        }
        $arrayIndex = count($this->scheduleChanges[$lessonID]) - 1;

        if (!array_key_exists($roomKey, $this->scheduleChanges[$lessonID][$arrayIndex])) {
            $this->scheduleChanges[$lessonID][$arrayIndex][$roomKey] = [];
        }
        array_push($this->scheduleChanges[$lessonID][$arrayIndex][$roomKey], $roomID);
    }

    /**
     * gets notification value in user_profile table depending on user selection
     *
     * @param userID int ID of user
     *
     * @return bool if user should be notified
     */
    private function getNotify($userID)
    {
        $query = $this->_db->getQuery(true);

        $query->select('COUNT(*)')
            ->from('#__user_profiles')
            ->where("profile_key = 'organizer_notify'")
            ->where("user_id = '$userID'");
        $this->_db->setQuery($query);

        return OrganizerHelper::executeQuery('loadResult');
    }
}