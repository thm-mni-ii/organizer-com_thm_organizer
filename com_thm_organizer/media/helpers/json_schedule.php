<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class which models, validates and compares schedule data to and from json objects.
 */
class THM_OrganizerModelJSONSchedule extends JModelLegacy
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
     * THM_OrganizerModelJSONSchedule constructor.
     *
     * @param object &$schedule the schedule object for direct processing
     *
     * @param null   $schedule
     *
     * @throws Exception
     */
    public function __construct(&$schedule = null)
    {
        $config = [];
        parent::__construct($config);

        if (!empty($schedule)) {
            $this->schedule = $schedule;
        }
    }

    /**
     * Adds a configuration to the configurations array, and adds it's index from that array to the array of
     * configurations for the active instance.
     *
     * @param string $configuration   the configuration object as a string
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
     * @param int   $lessonID        the id of the lesson in the database
     * @param int   $lessonGPUntisID the id of the lesson in the json schedule
     * @param array $calendarEntry   the the calendar entry being currently iterated
     * @param array $lessonSubjects  an array containing the plan subject id (subjectID) and lesson subject id (id), indexed by the plan subject id
     *
     * @return array
     */
    private function getInstanceConfigurations($lessonID, $calendarEntry, $lessonSubjects)
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
             * subjectID => the db / plan subject id
             * teachers & rooms => the teachers and rooms for this configuration
             */
            $rawConfig     = $this->schedule->configurations[$configIndex];
            $configuration = json_decode($rawConfig);

            // TODO: find out where these values are coming from
            if ($configuration->lessonID != $lessonID) {
                unset($this->schedule->calendar->$date->$timeKey->$lessonID->configurations[$instanceIndex]);
                continue;
            }

            $lessonSubjectID = $lessonSubjects[$configuration->subjectID]['id'];
            $pullConfig      = $configuration;
            unset($pullConfig->lessonID, $pullConfig->subjectID);
            $pullConfig   = json_encode($pullConfig);
            $configData   = ['lessonID' => $lessonSubjectID, 'configuration' => $pullConfig];
            $configsTable = JTable::getInstance('lesson_configurations', 'thm_organizerTable');
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
     * @throws Exception
     */
    private function mapConfigurations()
    {
        foreach ($this->schedule->lessons as $lessonGPUntisID => $lesson) {
            $lessonsData                     = [];
            $lessonsData['gpuntisID']        = $lessonGPUntisID;
            $lessonsData['departmentID']     = $this->schedule->departmentID;
            $lessonsData['planningPeriodID'] = $this->schedule->planningPeriodID;

            $lessonsTable = JTable::getInstance('lessons', 'thm_organizerTable');
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
            $calendarEntries = $this->_db->loadAssocList('id');

            // Occurs when the planner left the room blank
            if (empty($calendarEntries)) {
                continue;
            }

            $lessonSubjectsQuery = $this->_db->getQuery(true);
            $lessonSubjectsQuery->select('id, subjectID')->from('#__thm_organizer_lesson_subjects')->where("lessonID = '$lessonID'");
            $this->_db->setQuery($lessonSubjectsQuery);
            $lessonSubjects = $this->_db->loadAssocList('subjectID');

            // Should not occur
            if (empty($lessonSubjects)) {
                return false;
            }

            foreach ($calendarEntries as $calendarID => $calendarEntry) {
                $instanceConfigs = $this->getInstanceConfigurations($lessonGPUntisID, $calendarEntry, $lessonSubjects);

                $configIDs = [];

                foreach ($instanceConfigs as $configID) {
                    $mapData  = ['calendarID' => $calendarID, 'configurationID' => $configID];
                    $mapTable = JTable::getInstance('calendar_configuration_map', 'thm_organizerTable');
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
                        ->where("configurationID NOT IN ('" . implode("', '", $configIDs) . "')");
                    $this->_db->setQuery($deprecatedQuery);

                    try {
                        $success = $this->_db->execute();
                    } catch (Exception $exc) {
                        JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

                        return false;
                    }

                    if (empty($success)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Resolves the subject ids to their module numbers, if available
     *
     * @param object $subjects the lesson subjects
     *
     * @return array the id => delta mapping of the deprecated format lesson, empty if resolution failed
     */
    private function mapSubjectNos($subjects)
    {
        $return = [];
        if (empty($subjects)) {
            return $return;
        }

        foreach ($subjects as $gpuntisID => $value) {
            if (empty($this->refSchedule->subjects->$gpuntisID)) {
                continue;
            }

            $subjectID = $this->refSchedule->subjects->$gpuntisID->id;
            if (empty($subjectID)) {
                continue;
            }

            $return[$subjectID] = empty($this->refSchedule->subjects->$gpuntisID->subjectNo) ?
                '' : $this->refSchedule->subjects->$gpuntisID->subjectNo;
        }

        return $return;
    }

    /**
     * Creates complete instance configurations with lessonID, subjectID, teacher and room IDs => deltas
     *
     * @param string $lessonCode    the reference string used in the deprecated schedules
     * @param object $instanceRooms the room gpuntis ids with their corresponding deltas
     *
     * @return array the ids of the configurations
     */
    private function migrateConfigurations($lessonCode, $instanceRooms)
    {
        $rooms = [];
        foreach ($instanceRooms as $gpuntisID => $delta) {
            $invalidRoom = ($gpuntisID == 'delta' or empty($this->refSchedule->rooms->$gpuntisID));
            if ($invalidRoom) {
                continue;
            }
            $rooms[$this->refSchedule->rooms->$gpuntisID->id] = $this->resolveDelta($delta);
        }

        $configurations = [];

        if (!empty($this->refSchedule->lessons->$lessonCode->configurations)) {
            foreach ($this->refSchedule->lessons->$lessonCode->configurations as $rawBaseConfig) {
                // lesson, subject & teachers
                $config        = json_decode($rawBaseConfig);
                $config->rooms = $rooms;
                $jsonConfig    = json_encode($config);

                $configExists = in_array($jsonConfig, $this->schedule->configurations);
                if (!$configExists) {
                    $this->schedule->configurations[] = $jsonConfig;
                }

                $configIndex = array_search($jsonConfig, $this->schedule->configurations);

                $referenceExists = in_array($configIndex, $configurations);
                if (!$referenceExists) {
                    $configurations[] = $configIndex;
                }
            }
        }

        return $configurations;
    }

    /**
     * Removes calendar entries with the same base data
     *
     * @param array $calData    the data used to find matching calendar entries
     * @param int   $calendarID the valid calendar entry id
     *
     * @return bool true on success, otherwise false
     * @throws Exception
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

        try {
            $this->_db->execute();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return false;
        }

        return true;
    }

    /**
     * Resolves the resource delta
     *
     * @param mixed $resource the resource being checked (object) or the value of a dynamic field typically string
     *
     * @return string empty if the schedule is inactive or the resource had no changes, otherwise new/removed
     */
    private function resolveDelta($resource)
    {
        if (is_object($resource)) {
            $value = empty($resource->delta) ? '' : $resource->delta;
        } else {
            $value = $resource;
        }

        return (empty($this->schedule->active) or empty($value)) ? '' : $value;
    }

    /**
     * Resolves the collection id strings to the numerical values from the database
     *
     * @param object $collection the collection being processed
     * @param string $collection the name of the collection being resolved
     *
     * @return array the id => delta mapping of the deprecated format lesson, empty if resolution failed
     */
    private function resolveCollection($collection, $collectionName)
    {
        $return = [];
        if (empty($collection)) {
            return $return;
        }

        foreach ($collection as $gpuntisID => $value) {
            if (empty($this->refSchedule->$collectionName->$gpuntisID)) {
                continue;
            }

            $resourceID = $this->refSchedule->$collectionName->$gpuntisID->id;
            if (empty($resourceID)) {
                continue;
            }

            $return[$resourceID] = $this->resolveDelta($value);
        }

        return $return;
    }

    /**
     * Removes delta information from a schedule
     *
     * @param object &$schedule the schedule being processed
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
        // TODO: this is sometimes not an object. where does this come from?
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

            // If any of the subordinate nodes/collections are empty after sanitization, the node being processed must be removed
            if (isset($object->subjects)) {
                $this->sanitizeObjectNodes($object->subjects);
                $empty = empty((array)$object->subjects);
                if ($empty) {
                    unset($objectNodes->$objectID);
                    continue;
                }
            }

            if (isset($object->pools)) {
                $this->sanitizeNumericCollection($object->pools);
                $empty = empty($object->pools);
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
     * @throws Exception
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

        try {
            $lessonsSaved         = $this->saveLessons();
            $configurationsSaved  = $this->saveConfigurations();
            $calendarSaved        = $this->saveCalendar();
            $configurationsMapped = $this->mapConfigurations();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->_db->transactionRollback();

            return false;
        }

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
     * @throws Exception
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
                    $lessonsData                     = [];
                    $lessonsData['gpuntisID']        = $lessonID;
                    $lessonsData['departmentID']     = $this->schedule->departmentID;
                    $lessonsData['planningPeriodID'] = $this->schedule->planningPeriodID;

                    $lessonsTable = JTable::getInstance('lessons', 'thm_organizerTable');
                    $lessonsTable->load($lessonsData);

                    if (empty($lessonsTable->id)) {
                        return false;
                    }

                    $calData['lessonID'] = $lessonsTable->id;
                    $calendarTable       = JTable::getInstance('calendar', 'thm_organizerTable');

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

            try {
                $this->_db->execute();
            } catch (Exception $exc) {
                JFactory::getApplication()->enqueueMessage(
                    JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'),
                    'error'
                );

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

            $lessonsData                     = [];
            $lessonsData['gpuntisID']        = $config->lessonID;
            $lessonsData['departmentID']     = $this->schedule->departmentID;
            $lessonsData['planningPeriodID'] = $this->schedule->planningPeriodID;

            $lessonsTable = JTable::getInstance('lessons', 'thm_organizerTable');
            $lessonsTable->load($lessonsData);

            if (empty($lessonsTable->id)) {
                return false;
            }

            $lSubjectsData              = [];
            $lSubjectsData['lessonID']  = $lessonsTable->id;
            $lSubjectsData['subjectID'] = $config->subjectID;

            $lSubjectsTable = JTable::getInstance('lesson_subjects', 'thm_organizerTable');
            $lSubjectsTable->load($lSubjectsData);

            if (empty($lSubjectsTable->id)) {
                return false;
            }

            // Information would be redundant in the db
            unset($config->lessonID, $config->subjectID);

            $configData    = ['lessonID' => $lSubjectsTable->id, 'configuration' => json_encode($config)];
            $lConfigsTable = JTable::getInstance('lesson_configurations', 'thm_organizerTable');
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
     * @throws Exception
     */
    private function saveLessons()
    {
        $departmentID     = $this->schedule->departmentID;
        $planningPeriodID = $this->schedule->planningPeriodID;
        foreach ($this->schedule->lessons as $gpuntisID => $lesson) {
            // If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
            $table = JTable::getInstance('lessons', 'thm_organizerTable');

            $data                     = [];
            $data['gpuntisID']        = $gpuntisID;
            $data['departmentID']     = $departmentID;
            $data['planningPeriodID'] = $planningPeriodID;

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

            $subjectsSaved = $this->saveLessonSubjects($table->id, $lesson->subjects);

            if (!$subjectsSaved) {
                return false;
            }
        }

        $lessonIDs = array_keys((array)$this->schedule->lessons);

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_lessons')->set("delta = 'removed'")
            ->where("departmentID = '$departmentID'")->where("planningPeriodID = '$planningPeriodID'")
            ->where("gpuntisID NOT IN ('" . implode("', '", $lessonIDs) . "')")
            ->where("delta != 'removed'");
        $this->_db->setQuery($query);
        $deprecatedSuccess = $this->_db->execute();

        return empty($deprecatedSuccess) ? false : true;
    }

    /**
     * Saves the lesson pools from the schedule object to the database and triggers functions for saving lesson associations.
     *
     * @param string $lessonSubjectID the db id of the lesson subject association
     * @param object $pools           the pools associated with the subject
     * @param string $subjectNo       the subject's id in documentation
     *
     * @return boolean true if the save process was successful, otherwise false
     * @throws Exception
     */
    private function saveLessonPools($lessonSubjectID, $pools, $subjectID, $subjectNo)
    {
        $processedIDs = [];

        foreach ($pools as $poolID => $delta) {
            // If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
            $table = JTable::getInstance('lesson_pools', 'thm_organizerTable');

            $data              = [];
            $data['subjectID'] = $lessonSubjectID;
            $data['poolID']    = $poolID;
            $table->load($data);

            $data['delta'] = $delta;

            $success = $table->save($data);

            if (!$success) {
                JFactory::getApplication()->enqueueMessage(
                    JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'),
                    'error'
                );
                continue;
            }

            $processedIDs[] = $table->id;

            if (!empty($subjectNo)) {
                $this->savePlanSubjectMapping($subjectID, $poolID, $subjectNo);
            }
        }

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_lesson_pools')
            ->set("delta = 'removed'")
            ->where("id NOT IN ('" . implode("', '", $processedIDs) . "')")
            ->where("subjectID = '$lessonSubjectID'")
            ->where("delta != 'removed'");
        $this->_db->setQuery($query);
        $deprecatedSuccess = $this->_db->execute();

        return empty($deprecatedSuccess) ? false : true;
    }

    /**
     * Saves the lesson subjects from the schedule object to the database and triggers functions for saving lesson
     * associations.
     *
     * @param string $lessonID the db id of the lesson subject association
     * @param object $subjects the subjects associated with the lesson
     *
     * @return boolean true if the save process was successful, otherwise false
     * @throws Exception
     */
    private function saveLessonSubjects($lessonID, $subjects)
    {
        $processedIDs = [];

        foreach ($subjects as $subjectID => $subjectData) {
            // If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
            $table = JTable::getInstance('lesson_subjects', 'thm_organizerTable');

            $data              = [];
            $data['lessonID']  = $lessonID;
            $data['subjectID'] = $subjectID;
            $table->load($data);

            $data['delta'] = $subjectData->delta;

            $success = $table->save($data);

            if (!$success) {
                JFactory::getApplication()->enqueueMessage(
                    JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'),
                    'error'
                );
                continue;
            }

            $processedIDs[] = $table->id;
            $subjectNo      = empty($subjectData->subjectNo) ? null : $subjectData->subjectNo;

            $poolsSaved = $this->saveLessonPools($table->id, $subjectData->pools, $subjectID, $subjectNo);

            if (!$poolsSaved) {
                return false;
            }

            $teachersSaved = $this->saveLessonTeachers($table->id, $subjectData->teachers);

            if (!$teachersSaved) {
                return false;
            }
        }

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_lesson_subjects')
            ->set("delta = 'removed'")
            ->where("id NOT IN ('" . implode("', '", $processedIDs) . "')")
            ->where("lessonID = '$lessonID'")
            ->where("delta != 'removed'");
        $this->_db->setQuery($query);
        $deprecatedSuccess = $this->_db->execute();

        return empty($deprecatedSuccess) ? false : true;
    }

    /**
     * Saves the lesson pools from the schedule object to the database and triggers functions for saving lesson associations.
     *
     * @param string $subjectID the db id of the lesson subject association
     * @param object $teachers  the teachers associated with the subject
     *
     * @return boolean true if the save process was successful, otherwise false
     * @throws Exception
     */
    private function saveLessonTeachers($subjectID, $teachers)
    {
        $processedIDs = [];

        foreach ($teachers as $teacherID => $delta) {
            // If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
            $table = JTable::getInstance('lesson_teachers', 'thm_organizerTable');

            $data              = [];
            $data['subjectID'] = $subjectID;
            $data['teacherID'] = $teacherID;
            $table->load($data);

            // Delta will be 'calculated' later but explicitly overwritten now irregardless
            $data['delta'] = '';

            $success = $table->save($data);

            if (!$success) {
                JFactory::getApplication()->enqueueMessage(
                    JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'),
                    'error'
                );

                continue;
            }

            $processedIDs[] = $table->id;
        }

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_lesson_teachers')
            ->set("delta = 'removed'")
            ->where("id NOT IN ('" . implode("', '", $processedIDs) . "')")
            ->where("subjectID = '$subjectID'")
            ->where("delta != 'removed'");
        $this->_db->setQuery($query);
        $deprecatedSuccess = $this->_db->execute();

        return empty($deprecatedSuccess) ? false : true;
    }

    /**
     * Attempts to associate subjects used in scheduling with their documentation
     *
     * @param string $planSubjectID the id of the subject in the plan_subjects table
     * @param string $poolID        the id of the pool in the plan_pools table
     * @param string $subjectNo     the subject id used in documentation
     *
     * @return void saves/updates a database entry
     * @throws Exception
     */
    private function savePlanSubjectMapping($planSubjectID, $poolID, $subjectNo)
    {
        // Get the mapping boundaries for the program
        $boundariesQuery = $this->_db->getQuery(true);
        $boundariesQuery->select('lft, rgt')
            ->from('#__thm_organizer_mappings as m')
            ->innerJoin('#__thm_organizer_programs as prg on m.programID = prg.id')
            ->innerJoin('#__thm_organizer_plan_programs as p_prg on prg.id = p_prg.programID')
            ->innerJoin('#__thm_organizer_plan_pools as p_pool on p_prg.id = p_pool.programID')
            ->where("p_pool.id = '$poolID'");
        $this->_db->setQuery($boundariesQuery);

        try {
            $boundaries = $this->_db->loadAssoc();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

            return;
        }

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

        try {
            $subjectID = $this->_db->loadResult();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

            return;
        }

        if (empty($subjectID)) {
            return;
        }

        $data  = ['subjectID' => $subjectID, 'plan_subjectID' => $planSubjectID];
        $table = JTable::getInstance('subject_mappings', 'thm_organizerTable');
        $table->load($data);
        $table->save($data);
    }

    /**
     * Creates the delta to the chosen reference schedule
     *
     * @param object $reference the reference schedule
     * @param object $active    the active schedule
     *
     * @return boolean true on successful delta creation, otherwise false
     * @throws Exception
     */
    public function setReference($reference, $active)
    {
        $this->refSchedule = json_decode($reference->schedule);
        $this->schedule    = json_decode($active->schedule);

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
        }

        $this->_db->transactionCommit();

        $dbSuccess = $this->save();
        if (!$dbSuccess) {
            JFactory::getApplication()->enqueueMessage(
                JText::_('COM_THM_ORGANIZER_MESSAGE_SCHEDULE_SAVE_FAIL'),
                'notice'
            );

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
                    $this->setConfigurationReferences($referenceInstance, $instance, $configurations);
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
     * @param object &$instance      the instance being iterated
     * @param array  $configurations the array holding the configurations
     * @param string $source         [schedule|refSchedule]
     */
    private function setConfigurations(&$instance, &$configurations, $source)
    {
        $instanceConfigurations = [];
        foreach ($instance->configurations as $instanceIndex => $globalIndex) {
            $instanceConfigurations[] = $this->$source->configurations[$globalIndex];
            unset($instance->configurations[$instanceIndex]);
        }

        foreach ($instanceConfigurations as $configuration) {
            $this->addConfiguration($configuration, $configurations, $instance);
        }
    }

    /**
     * Sets the configuration references for the instance being iterated
     *
     * @param object $referenceInstance the old instance data
     * @param object &$activeInstance   the active instance data
     * @param array  $configurations    the array holding the configurations
     *
     * @return void modifies $activeInstance and $configurations
     */
    private function setConfigurationReferences($referenceInstance, &$activeInstance, &$configurations)
    {
        $referenceConfigurations = [];
        foreach ($referenceInstance->configurations as $refConfigurationIndex) {
            if (!empty($this->refSchedule->configurations[$refConfigurationIndex])) {
                $referenceConfigurations[] = $this->refSchedule->configurations[$refConfigurationIndex];
            }
        }

        $activeConfigurations = [];
        foreach ($activeInstance->configurations as $activeConfigurationIndex) {
            $activeConfigurations[] = $this->schedule->configurations[$activeConfigurationIndex];
        }

        // These will be renumbered in the following
        $activeInstance->configurations = [];

        $unchangedConfigurations = array_intersect($referenceConfigurations, $activeConfigurations);

        foreach ($unchangedConfigurations as $unchangedConfiguration) {
            $this->addConfiguration($unchangedConfiguration, $configurations, $activeInstance);
        }

        $oldConfigurations = array_diff($referenceConfigurations, $activeConfigurations);
        $newConfigurations = array_diff($activeConfigurations, $referenceConfigurations);

        foreach ($newConfigurations as $ncIndex => $newConfiguration) {
            $newConfigObject = json_decode($newConfiguration);
            $teachers        = array_keys((array)$newConfigObject->teachers);
            $rooms           = array_keys((array)$newConfigObject->rooms);
            $comparisonFound = false;

            foreach ($oldConfigurations as $dcIndex => $oldConfiguration) {
                $oldConfigObject = json_decode($oldConfiguration);

                // Changes of subject are handled at the lesson subjects level and deprecated subjects don't need config deltas.
                if ($oldConfigObject->subjectID != $newConfigObject->subjectID) {
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
                    $newConfigObject->rooms->$removedRoomID = 'removed';
                }

                $newRooms = array_diff($rooms, $oldRooms);
                foreach ($newRooms as $newRoomID) {
                    $newConfigObject->rooms->$newRoomID = 'new';
                }
            }

            // Subject was newly added to the lesson
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
            $referenceSubjectIDs = array_keys((array)$this->refSchedule->lessons->$carriedLessonID->subjects);
            $activeSubjectIDs    = array_keys((array)$this->schedule->lessons->$carriedLessonID->subjects);

            $carriedSubjectIDs = array_intersect($referenceSubjectIDs, $activeSubjectIDs);

            foreach ($carriedSubjectIDs as $carriedSubjectID) {
                $referencePoolIDs = array_keys((array)$this->refSchedule->lessons->$carriedLessonID->subjects->$carriedSubjectID->pools);
                $activePoolIDs    = array_keys((array)$this->schedule->lessons->$carriedLessonID->subjects->$carriedSubjectID->pools);

                $removedPoolIDs = array_diff($referencePoolIDs, $activePoolIDs);

                foreach ($removedPoolIDs as $removedPoolID) {
                    $this->refSchedule->lessons->$carriedLessonID->subjects->$carriedSubjectID->pools->$removedPoolID = 'removed';
                }

                $newPoolIDs = array_diff($activePoolIDs, $referencePoolIDs);

                foreach ($newPoolIDs as $newPoolID) {
                    $this->refSchedule->lessons->$carriedLessonID->subjects->$carriedSubjectID->pools->$newPoolID = 'new';
                }

                $referenceTeacherIDs = array_keys((array)$this->refSchedule->lessons->$carriedLessonID->subjects->$carriedSubjectID->teachers);
                $activeTeacherIDs    = array_keys((array)$this->schedule->lessons->$carriedLessonID->subjects->$carriedSubjectID->teachers);

                $removedTeacherIDs = array_diff($referenceTeacherIDs, $activeTeacherIDs);

                foreach ($removedTeacherIDs as $removedTeacherID) {
                    $this->refSchedule->lessons->$carriedLessonID->subjects->$carriedSubjectID->teachers->$removedTeacherID = 'removed';
                }

                $newTeacherIDs = array_diff($activeTeacherIDs, $referenceTeacherIDs);

                foreach ($newTeacherIDs as $newTeacherID) {
                    $this->refSchedule->lessons->$carriedLessonID->subjects->$carriedSubjectID->teachers->$newTeacherID = 'new';
                }
            }

            $removedSubjectIDs = array_diff($referenceSubjectIDs, $activeSubjectIDs);

            foreach ($removedSubjectIDs as $removedSubjectID) {
                $removedSubject                                                         = $this->refSchedule->lessons->$carriedLessonID->subjects->$removedSubjectID;
                $removedSubject->delta                                                  = 'removed';
                $this->schedule->lessons->$carriedLessonID->subjects->$removedSubjectID = $removedSubject;
            }

            $newSubjectIDs = array_diff($activeSubjectIDs, $referenceSubjectIDs);

            foreach ($newSubjectIDs as $newSubjectID) {
                $this->schedule->lessons->$carriedLessonID->subjects->$newSubjectID->delta = 'new';
            }
        }

        $removedLessonIDs = array_diff($referenceLessonIDs, $activeLessonIDs);

        foreach ($removedLessonIDs as $removedLessonID) {
            $this->schedule->lessons->$removedLessonID        = $this->refSchedule->lessons->$removedLessonID;
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
        }
    }

    /**
     * Transfers time intervals which need no configuration reference processing. (The time interval itself is new or removed.)
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
            $this->schedule->calendar->$date = new stdClass;
        }

        foreach ($times as $time) {
            if (empty($this->schedule->calendar->$date->$time)) {
                $this->schedule->calendar->$date->$time = new stdClass;
            }
            $lessonIDs = array_keys((array)$this->$source->calendar->$date->$time);
            $this->transferInstances($lessonIDs, $status, $date, $time, $configurations);
        }
    }
}
