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

use Organizer\Helpers\Access;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Persons;

/**
 * Class which manages stored person data.
 */
class Person extends MergeModel
{
    protected $deptResource = 'personID';

    protected $fkColumn = 'personID';

    protected $tableName = 'persons';

    /**
     * Provides user access checks to persons
     *
     * @return boolean  true if the user may edit the given resource, otherwise false
     */
    protected function allowEdit()
    {
        if (Access::allowHRAccess()) {
            return true;
        }

        $plannerFor = Access::getAccessibleDepartments('schedule');

        foreach ($this->selected as $selected) {
            $personDepartments = Persons:: getDepartmentIDs($selected);
            foreach ($personDepartments as $personDepartment) {
                if (in_array($personDepartment, $plannerFor)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Removes potential duplicates before the subject person associations are updated.
     *
     * @return bool true if no error occurred, otherwise false
     */
    private function removeDuplicateRoles()
    {
        $updateIDs = $this->selected;
        $mergeID   = array_shift($updateIDs);
        $updateIDs = "'" . implode("', '", $updateIDs) . "'";
        $table     = '#__thm_organizer_subject_persons';

        $selectQuery = $this->_db->getQuery(true);
        $selectQuery->select('DISTINCT subjectID, role')
            ->from($table)
            ->where("personID = $mergeID");
        $this->_db->setQuery($selectQuery);

        $existingRoles = OrganizerHelper::executeQuery('loadAssocList');

        if (!empty($existingRoles)) {
            $potentialDuplicates = [];
            foreach ($existingRoles as $role) {
                $potentialDuplicates[]
                    = "(subjectID = '{$role['subjectID']}' AND role = '{$role['role']}')";
            }
            $potentialDuplicates = '(' . implode(' OR ', $potentialDuplicates) . ')';

            $deleteQuery = $this->_db->getQuery(true);
            $deleteQuery->delete($table)->where("personID IN ( $updateIDs )")->where($potentialDuplicates);
            $this->_db->setQuery($deleteQuery);
            $success = (bool)OrganizerHelper::executeQuery('execute');
            if (!$success) {
                return false;
            }
        }

        return true;
    }

    /**
     * Updates key references to the entry being merged.
     *
     * @return boolean  true on success, otherwise false
     */
    protected function updateAssociations()
    {
        $drUpdated = $this->updateDRAssociation();
        if (!$drUpdated) {
            return false;
        }

        $ipUpdated = $this->updateAssociation('instance_persons');
        if (!$ipUpdated) {
            return false;
        }

        $duplicatesRemoved = $this->removeDuplicateRoles();
        if (!$duplicatesRemoved) {
            return false;
        }

        $spUpdated = $this->updateAssociation('subject_persons');
        if (!$spUpdated) {
            return false;
        }

        $configsUpdated = $this->updateStoredConfigurations();
        if (!$configsUpdated) {
            return false;
        }

        return true;
    }

    /**
     * Processes the data for an individual schedule
     *
     * @param object &$schedule the schedule being processed
     *
     * @return void
     */
    protected function updateSchedule(&$schedule)
    {
        $updateIDs = $this->selected;
        $mergeID   = array_shift($updateIDs);

        foreach ($schedule->lessons as $lessonIndex => $lesson) {
            foreach ($lesson->events as $subjectID => $subjectConfig) {
                foreach ($subjectConfig->persons as $personID => $delta) {
                    if (in_array($personID, $updateIDs)) {
                        unset($schedule->lessons->$lessonIndex->events->$subjectID->persons->$personID);
                        $schedule->lessons->$lessonIndex->events->$subjectID->persons->$mergeID = $delta;
                    }
                }
            }
        }

        foreach ($schedule->configurations as $index => $configuration) {
            $inConfig      = false;
            $configuration = json_decode($configuration);

            foreach ($configuration->persons as $personID => $delta) {
                if (in_array($personID, $updateIDs)) {
                    $inConfig = true;
                    unset($configuration->persons->$personID);
                    $configuration->persons->$mergeID = $delta;
                }
            }

            if ($inConfig) {
                $schedule->configurations[$index] = json_encode($configuration);
            }
        }
    }

    /**
     * Updates the lesson configurations table with the person id changes.
     *
     * @return bool
     */
    private function updateStoredConfigurations()
    {
        $updateIDs = $this->selected;
        $mergeID   = array_shift($updateIDs);

        $table       = '#__thm_organizer_lesson_configurations';
        $selectQuery = $this->_db->getQuery(true);
        $selectQuery->select('id, configuration')
            ->from($table);

        $updateQuery = $this->_db->getQuery(true);
        $updateQuery->update($table);

        foreach ($updateIDs as $updateID) {
            $selectQuery->clear('where');
            $regexp = '"persons":\\{("[0-9]+":"[\w]*",)*"' . $updateID . '"';
            $selectQuery->where("configuration REGEXP '$regexp'");
            $this->_db->setQuery($selectQuery);

            $storedConfigurations = OrganizerHelper::executeQuery('loadAssocList');
            if (empty($storedConfigurations)) {
                continue;
            }

            foreach ($storedConfigurations as $storedConfiguration) {
                $configuration = json_decode($storedConfiguration['configuration'], true);

                $oldDelta = $configuration['persons'][$updateID];
                unset($configuration['persons'][$updateID]);

                // The new id is not yet an index, or it is, but has no delta value and the old id did
                if (!isset($configuration['persons'][$mergeID])
                    or (empty($configuration['persons'][$mergeID]) and !empty($oldDelta))) {
                    $configuration['persons'][$mergeID] = $oldDelta;
                }

                $configuration = json_encode($configuration);
                $updateQuery->clear('set');
                $updateQuery->set("configuration = '$configuration'");
                $updateQuery->clear('where');
                $updateQuery->where("id = '{$storedConfiguration['id']}'");
                $this->_db->setQuery($updateQuery);
                $success = (bool)OrganizerHelper::executeQuery('execute');
                if (!$success) {
                    return false;
                }
            }
        }

        return true;
    }
}
