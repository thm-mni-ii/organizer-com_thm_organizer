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

use Organizer\Helpers\OrganizerHelper;

/**
 * Class which sets permissions for the view.
 */
class Organizer extends BaseModel
{
    // This class only exists to be conform with the standard structure. The view does not access the database.

    /**
     * Checks whether the given id exists in the table.
     *
     * @param string $tableClass the name of the table class
     * @param int    $resourceID the id of the resource being checked
     *
     * @return bool true if the id exists in the table, otherwise false
     */
    private function checkResourceID($tableClass, $resourceID)
    {
        $resources = OrganizerHelper::getTable($tableClass);
        $resources->load($resourceID);

        return empty($resources->id) ? false : $resourceID;
    }

    /**
     * Retrieves the id of the block matching the parameters
     *
     * @param string $date       the date of the block
     * @param string $blockTimes the start and end times of the block aggregated with a - character
     *
     * @return bool
     */
    private function getBlockID($date, $blockTimes)
    {
        list($startTime, $endTime) = explode('-', $blockTimes);
        $startTime = preg_replace('/([\d]{2})$/', ':${1}:00', $startTime);
        $endTime   = preg_replace('/([\d]{2})$/', ':${1}:00', $endTime);
        $blocks    = OrganizerHelper::getTable('Blocks');
        $blocks->load(['date' => $date, 'startTime' => $startTime, 'endTime' => $endTime]);

        return empty($blocks->id) ? false : $blocks->id;
    }

    /**
     * Retrieves the defining information for the given term id.
     *
     * @param int $termID the id of the term sought
     *
     * @return array the term
     */
    private function getTerm($termID)
    {
        $terms = OrganizerHelper::getTable('Terms');
        $terms->load($termID);

        return empty($terms->id) ?
            [] : ['id' => $terms->id, 'startDate' => $terms->startDate, 'endDate' => $terms->endDate];
    }

    /**
     * Retrieves the unit id for the unit with the given identifiers.
     *
     * @param array $unit containing the department, term and untis ids
     *
     * @return bool
     */
    private function getUnitID($unit)
    {
        $units = OrganizerHelper::getTable('Units');
        $units->load($unit);

        return empty($units->id) ? false : $units->id;
    }

    /**
     * Migrates a configuration.
     *
     * @param int $configurationID the id of the configuration to migrate
     *
     * @return bool true on success, otherwise false.
     */
    private function migrateConfiguration($configurationID)
    {
        // blockID, eventID, unitID => instanceID => personID => roomIDs
        $blocksConditions = 'b.date = cal.schedule_date AND b.startTime = cal.startTime AND b.endTime = cal.endTime';
        $configQuery      = $this->_db->getQuery(true);
        $configQuery->select('lc.configuration, i.id AS instanceID, lc.modified')
            ->from('#__thm_organizer_lesson_configurations AS lc')
            ->innerJoin('#__thm_organizer_calendar_configuration_map AS ccm ON ccm.configurationID = lc.id')
            ->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = ccm.calendarID')
            ->innerJoin("#__thm_organizer_blocks AS b ON $blocksConditions")
            ->innerJoin('#__thm_organizer_units AS u ON u.id = cal.lessonID')
            ->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.id = lc.lessonID AND ls.lessonID = u.id')
            ->innerJoin('#__thm_organizer_events AS e ON e.id = ls.subjectID')
            ->innerJoin('#__thm_organizer_instances AS i ON i.blockID = b.id AND i.eventID = e.id AND i.unitID = u.id')
            ->where("ccm.id = $configurationID");

        $this->_db->setQuery($configQuery);

        if ($key = OrganizerHelper::executeQuery('loadAssoc', [])) {

            $configuration = json_decode($key['configuration'], true);

            foreach ($configuration['teachers'] as $personID => $personDelta) {

                $instancePersons = OrganizerHelper::getTable('InstancePersons');
                $instancePersons->load(['instanceID' => $key['instanceID'], 'personID' => $personID]);

                if ($assocID = $instancePersons->id) {
                    foreach ($configuration['rooms'] as $roomID => $roomDelta) {
                        $instanceRooms = OrganizerHelper::getTable('InstanceRooms');
                        $data          = ['assocID' => $assocID, 'roomID' => $roomID];
                        $instanceRooms->load($data);

                        if (empty($instanceRooms->id)) {
                            $prQuery = $this->_db->getQuery(true);
                            $prQuery->insert('#__thm_organizer_instance_rooms')
                                ->columns('assocID, roomID, delta, modified')
                                ->values("$assocID, $roomID, '$roomDelta', '{$key['modified']}'");
                            $this->_db->setQuery($prQuery);
                            OrganizerHelper::executeQuery('execute');
                        }
                    }
                }
            }
        }

        $deleteQuery = $this->_db->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_calendar_configuration_map')->where("id = $configurationID");

        $this->_db->setQuery($deleteQuery);
        OrganizerHelper::executeQuery('execute');

        return true;
    }

    /**
     * Migrates configurations.
     *
     * @return bool true on success, otherwise false.
     */
    public function migrateConfigurations()
    {
        $selectQuery = $this->_db->getQuery(true);
        $selectQuery->select('DISTINCT id')->from('#__thm_organizer_calendar_configuration_map');
        $this->_db->setQuery($selectQuery);

        $mapIDs = OrganizerHelper::executeQuery('loadColumn', []);
        foreach ($mapIDs as $mapID) {
            $this->migrateConfiguration($mapID);
        }

        return true;
    }

    /**
     * Migrates associations with a given participant id.
     *
     * @param int $participantID the id of the participant whose date should be migrated
     *
     * @return bool true on success, otherwise false.
     */
    private function migrateParticipantAssociations($participantID)
    {
        $userLessonsQuery = $this->_db->getQuery(true);
        $userLessonsQuery->select('*')->from('#__thm_organizer_user_lessons')->where("userID = $participantID");
        $this->_db->setQuery($userLessonsQuery);

        if (!$userLessons = OrganizerHelper::executeQuery('loadAssocList', [])) {
            return true;
        }

        $blocksConditions = 'b.date = cal.schedule_date AND b.startTime = cal.startTime AND b.endTime = cal.endTime';
        $dataQuery        = $this->_db->getQuery(true);
        $dataQuery->select('cor.id AS courseID, i.id AS instanceID')
            ->from('#__thm_organizer_calendar_configuration_map AS ccm')
            ->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = ccm.calendarID')
            ->innerJoin("#__thm_organizer_blocks AS b ON $blocksConditions")
            ->innerJoin('#__thm_organizer_units AS u ON u.id = cal.lessonID')
            ->innerJoin('#__thm_organizer_lesson_configurations AS lc ON lc.id = ccm.configurationID')
            ->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.id = lc.lessonID AND ls.lessonID = u.id')
            ->innerJoin('#__thm_organizer_events AS e ON e.id = ls.subjectID')
            ->innerJoin('#__thm_organizer_courses AS cor ON cor.eventID = e.id AND cor.termID = u.termID')
            ->innerJoin('#__thm_organizer_instances AS i ON i.blockID = b.id AND i.eventID = e.id AND i.unitID = u.id');

        $deleteQuery = $this->_db->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_user_lessons');

        foreach ($userLessons as $userLesson) {
            $unitID = $userLesson['lessonID'];
            $ccmIDs = str_replace(['[', '"', ']'], '', $userLesson['configuration']);

            $dataQuery->clear('where');
            $dataQuery->where("u.id = $unitID");
            $dataQuery->where("ccm.id IN ($ccmIDs)");
            $this->_db->setQuery($dataQuery);

            if ($results = OrganizerHelper::executeQuery('loadAssocList', [])) {
                $this->saveCourseParticipant($results[0]['courseID'], $participantID, $userLesson);
                foreach ($results as $result) {
                    $this->saveInstanceParticipant($result['instanceID'], $participantID, $userLesson);
                }
            }


            $deleteQuery->clear('where');
            $deleteQuery->where("id = {$userLesson['id']}");
            $this->_db->setQuery($deleteQuery);
            OrganizerHelper::executeQuery('execute');
        }

    }

    /**
     * Migrates a schedule.
     *
     * @param int $scheduleID the id of the schedule to migrate
     *
     * @return bool true on success, otherwise false.
     */
    private function migrateSchedule($scheduleID)
    {
        $schedules = OrganizerHelper::getTable('Schedules');
        $schedules->load($scheduleID);

        if (empty($schedules->id)) {
            return false;
        }

        if ($schedules->migrated) {
            return true;
        }

        $schedule = json_decode($schedules->schedule, true);

        unset($schedule['creationDate']);
        unset($schedule['creationTime']);
        unset($schedule['departmentID']);
        unset($schedule['endDate']);
        unset($schedule['referenceID']);
        unset($schedule['startDate']);
        unset($schedule['termID']);

        $term = $this->getTerm($schedules->termID);
        if (!$termID = $term['id']) {
            return false;
        }

        $termStart = $term['startDate'];
        $termEnd   = $term['endDate'];
        $unit      = ['departmentID' => $schedules->departmentID, 'termID' => $termID];

        foreach ($schedule['calendar'] as $date => $times) {

            // Remove empty dates and dates beyond the scope of the terms they were created for
            if (empty($times) or $date < $termStart or $date > $termEnd) {
                unset($schedule['calendar'][$date]);
                continue;
            }

            foreach ($times as $blockTimes => $units) {
                if (!$blockID = $this->getBlockID($date, $blockTimes)) {
                    unset($schedule['calendar'][$date][$times]);
                    continue;
                }

                foreach ($units as $untisID => $unitData) {
                    $unit['untisID'] = $untisID;
                    if (!$unitID = $this->getUnitID($unit)) {
                        unset($schedule['calendar'][$date][$blockTimes][$untisID]);
                        continue;
                    }

                    $unitConfiguration = $schedule['lessons'][$untisID];

                    foreach ($unitData['configurations'] as $key => $configurationIndex) {
                        if (empty($schedule['configurations'][$configurationIndex])) {
                            continue;
                        }

                        $instanceConfiguration = json_decode($schedule['configurations'][$configurationIndex], true);
                        $rooms                 = array_keys($instanceConfiguration['rooms']);

                        // The event (plan subject) no longer exists or is no longer associated with the unit
                        if (!$eventID = $this->checkResourceID('Events', $instanceConfiguration['subjectID'])
                            or !$eventConfiguration = $unitConfiguration['subjects'][$eventID]
                        ) {
                            unset($schedule['configurations'][$configurationIndex]);
                            continue;
                        }
                        if (!$groups = $eventConfiguration['pools']) {
                            unset($unitConfiguration['subjects'][$eventID]);
                            continue;
                        }

                        $groups = array_keys($groups);

                        $instance = ['blockID' => $blockID, 'unitID' => $unitID, 'eventID' => $eventID];

                        $instancesTable = OrganizerHelper::getTable('Instances');
                        $instancesTable->load($instance);
                        if (!$instanceID = $instancesTable->id) {
                            $instance['methodID'] = empty($unitConfiguration['methodID']) ?
                                null : $unitConfiguration['methodID'];
                            $instance['delta']    = $unitData['delta'];
                            $instancesTable->save($instance);
                            if (!$instanceID = $instancesTable->id) {
                                continue;
                            }
                        }

                        $persons = [];
                        foreach ($instanceConfiguration['teachers'] as $personID => $instancePersonDelta) {
                            if (!$this->checkResourceID('Persons', $personID)
                                or !array_key_exists($personID, $eventConfiguration['teachers'])) {
                                unset($instanceConfiguration['teachers'][$personID]);
                                continue;
                            }
                            $persons[$personID] = ['groups' => $groups, 'rooms' => $rooms];
                        }

                        if (empty($persons)) {
                            continue;
                        }

                        $schedule[$instanceID] = $persons;
                    }
                }
            }
        }

        unset($schedule['calendar']);
        unset($schedule['configurations']);
        unset($schedule['lessons']);

        $schedules->schedule = json_encode($schedule);
        $schedules->migrated = 1;

        return $schedules->store();
    }

    /**
     * Migrates schedules.
     *
     * @return bool true on success, otherwise false.
     */
    public function migrateSchedules()
    {
        $query = $this->_db->getQuery(true);
        $query->select('id')->from('#__thm_organizer_schedules')->where('migrated = 0');
        $this->_db->setQuery($query);

        $scheduleIDs = OrganizerHelper::executeQuery('loadColumn', []);
        foreach ($scheduleIDs as $scheduleID) {
            $this->migrateSchedule($scheduleID);
        }

        return true;
    }

    /**
     * Migrates user lessons.
     *
     * @return bool true on success, otherwise false.
     */
    public function migrateUserLessons()
    {
        $selectQuery = $this->_db->getQuery(true);
        $selectQuery->select('DISTINCT userID')->from('#__thm_organizer_user_lessons');
        $this->_db->setQuery($selectQuery);

        $participantIDs = OrganizerHelper::executeQuery('loadColumn', []);
        foreach ($participantIDs as $participantID) {
            $this->migrateParticipantAssociations($participantID);
        }

        return true;
    }

    /**
     * Creates or modifies a course participant table entry.
     *
     * @param int   $courseID      the id of the course
     * @param int   $participantID the id of the participant
     * @param array $userLesson    the data from the previously save entry
     *
     * @return void
     */
    private function saveCourseParticipant($courseID, $participantID, $userLesson)
    {
        $cParticipants = OrganizerHelper::getTable('CourseParticipants');
        $cParticipant  = ['courseID' => $courseID, 'participantID' => $participantID];
        $cParticipants->load($cParticipant);
        if (empty($cParticipants->id)) {
            $cParticipant['participantDate'] = $userLesson['user_date'];
            $cParticipant['status']          = $userLesson['status'];
            $cParticipant['statusDate']      = $userLesson['status_date'];
            $cParticipants->save($cParticipant);
        } else {
            $altered = false;
            if ($cParticipants->participantDate < $userLesson['user_date']) {
                $cParticipants->participantDate = $userLesson['user_date'];
                $altered                        = true;
            }
            if ($cParticipants->statusDate < $userLesson['status_date']) {
                $cParticipants->statusDate = $userLesson['status_date'];
                $cParticipants->status     = $userLesson['status'];
                $altered                   = true;
            }

            if ($altered) {
                $cParticipants->store();
            }
        }
    }

    /**
     * Creates or modifies an instance participant table entry.
     *
     * @param int   $instanceID    the id of the instance
     * @param int   $participantID the id of the participant
     * @param array $userLesson    the data from the previously save entry
     *
     * @return void
     */
    private function saveInstanceParticipant($instanceID, $participantID, $userLesson)
    {
        $iParticipants = OrganizerHelper::getTable('InstanceParticipants');
        $iParticipant  = ['instanceID' => $instanceID, 'participantID' => $participantID];
        $iParticipants->load($iParticipant);
        if (empty($iParticipants->id)) {
            $iParticipant['delta']    = '';
            $iParticipant['modified'] = $userLesson['user_date'];
            $iParticipants->save($iParticipant);
        } elseif ($iParticipants->modified < $userLesson['user_date']) {
            $iParticipants->modified = $userLesson['user_date'];
            $iParticipants->store();
        }
    }

    /**
     * Checks whether user lessons or lesson configurations exist which yet need to be migrated. Provides buttons to
     * trigger migration as necessary. Drops the corresponding tables if all data has been migrated.
     *
     * @param Joomla\CMS\Toolbar\Toolbar $toolbar the toolbar to add the button to as necessary.
     *
     * @return void
     */
    public function showConfigurationMigrationButtons($toolbar)
    {
        $prefix = $this->_db->getPrefix();
        $this->_db->setQuery('SHOW TABLES');
        $tables = OrganizerHelper::executeQuery('loadColumn', []);

        $userLessonsTable    = $prefix . 'thm_organizer_user_lessons';
        $userLessonsMigrated = !in_array($userLessonsTable, $tables);
        if (!$userLessonsMigrated) {
            $this->supplementParticipants();

            $lessonCountQuery = $this->_db->getQuery(true);
            $lessonCountQuery->select('COUNT(*)')->from('#__thm_organizer_user_lessons');
            $this->_db->setQuery($lessonCountQuery);

            if (OrganizerHelper::executeQuery('loadResult', 0)) {
                $toolbar->appendButton(
                    'Standard',
                    'users',
                    'Migrate User Lessons',
                    'organizer.migrateUserLessons',
                    false
                );

                return;
            } else {
                $this->_db->setQuery('DROP TABLE `#__thm_organizer_user_lessons`');
                OrganizerHelper::executeQuery('execute');
            }

        }


        $mapTable         = $prefix . 'thm_organizer_calendar_configuration_map';
        $mappingsMigrated = !in_array($mapTable, $tables);
        if (!$mappingsMigrated) {
            $configCountQuery = $this->_db->getQuery(true);
            $configCountQuery->select('COUNT(*)')->from('#__thm_organizer_calendar_configuration_map');
            $this->_db->setQuery($configCountQuery);

            if (OrganizerHelper::executeQuery('loadResult', 0)) {
                $toolbar->appendButton(
                    'Standard',
                    'next',
                    'Migrate Configurations',
                    'organizer.migrateConfigurations',
                    false
                );

                return;
            } else {
                $this->_db->setQuery('DROP TABLE `#__thm_organizer_calendar_configuration_map`');
                OrganizerHelper::executeQuery('execute');

                $this->_db->setQuery('DROP TABLE `#__thm_organizer_calendar`');
                OrganizerHelper::executeQuery('execute');

                $this->_db->setQuery('DROP TABLE `#__thm_organizer_lesson_configurations`');
                OrganizerHelper::executeQuery('execute');

                $this->_db->setQuery('DROP TABLE `#__thm_organizer_lesson_subjects`');
                OrganizerHelper::executeQuery('execute');
            }
        }
    }

    /**
     * Checks whether schedules exist which yet need to be migrated. Provides a button to trigger schedule migration as
     * necessary. Drops the migrated column from the database if all schedules have been migrated.
     *
     * @param Joomla\CMS\Toolbar\Toolbar $toolbar the toolbar to add the button to as necessary.
     *
     * @return void
     */
    public function showScheduleMigrationButton($toolbar)
    {
        $schedules = OrganizerHelper::getTable('Schedules');
        $fields    = $schedules->getFields();
        if (array_key_exists('migrated', $fields)) {
            $countQuery = $this->_db->getQuery(true);
            $countQuery->select('COUNT(*)')->from('#__thm_organizer_schedules')->where('migrated = 0');
            $this->_db->setQuery($countQuery);

            if (OrganizerHelper::executeQuery('loadResult', 0)) {
                $toolbar->appendButton(
                    'Standard',
                    'calendar',
                    'Migrate Schedules',
                    'organizer.migrateSchedules',
                    false
                );

                return;
            }

            $migrationQuery = 'ALTER TABLE #__thm_organizer_schedules DROP COLUMN `migrated`';
            $this->_db->setQuery($migrationQuery);
            OrganizerHelper::executeQuery('execute');

            return;
        }
    }

    /**
     * Adds users who have created schedules to the participants table.
     *
     * @return void
     */
    private function supplementParticipants()
    {
        $participantQuery = $this->_db->getQuery(true);
        $participantQuery->select('DISTINCT id')->from('#__thm_organizer_participants');
        $selectQuery = $this->_db->getQuery(true);
        $selectQuery->select('DISTINCT userID')
            ->from('#__thm_organizer_user_lessons')
            ->where("userID NOT IN ($participantQuery)");
        $this->_db->setQuery($selectQuery);

        if ($missingParticipantIDs = OrganizerHelper::executeQuery('loadColumn', [])) {
            $insertQuery = $this->_db->getQuery(true);
            $insertQuery->insert('#__thm_organizer_participants');
            $insertQuery->columns('id');
            foreach ($missingParticipantIDs as $participantID) {
                $insertQuery->clear('values');
                $insertQuery->values("$participantID");
                $this->_db->setQuery($insertQuery);
                OrganizerHelper::executeQuery('execute');
            }
        }
    }
}
