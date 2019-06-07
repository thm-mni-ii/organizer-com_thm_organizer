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
use Organizer\Helpers\Teachers;

/**
 * Class which manages stored teacher data.
 */
class Teacher extends MergeModel
{
    protected $deptResource = 'teacherID';

    protected $fkColumn = 'teacherID';

    protected $tableName = 'teachers';

    /**
     * Provides user access checks to teachers
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
            $teacherDepartments = Teachers:: getDepartmentIDs($selected);
            foreach ($teacherDepartments as $teacherDepartment) {
                if (in_array($teacherDepartment, $plannerFor)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Removes potential duplicates before the subject teacher associations are updated.
     *
     * @return bool true if no error occurred, otherwise false
     */
    private function removeDuplicateResponsibilities()
    {
        $updateIDs = $this->selected;
        $mergeID   = array_shift($updateIDs);
        $updateIDs = "'" . implode("', '", $updateIDs) . "'";
        $table     = '#__thm_organizer_subject_teachers';

        $selectQuery = $this->_db->getQuery(true);
        $selectQuery->select('DISTINCT subjectID, teacherResp')
            ->from($table)
            ->where("teacherID = $mergeID");
        $this->_db->setQuery($selectQuery);

        $existingResps = OrganizerHelper::executeQuery('loadAssocList');

        if (!empty($existingResps)) {
            $potentialDuplicates = [];
            foreach ($existingResps as $resp) {
                $potentialDuplicates[]
                    = "(subjectID = '{$resp['subjectID']}' AND teacherResp = '{$resp['teacherResp']}')";
            }
            $potentialDuplicates = '(' . implode(' OR ', $potentialDuplicates) . ')';

            $deleteQuery = $this->_db->getQuery(true);
            $deleteQuery->delete($table)->where("teacherID IN ( $updateIDs )")->where($potentialDuplicates);
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

        $ltUpdated = $this->updateAssociation('lesson_teachers');
        if (!$ltUpdated) {
            return false;
        }

        $duplicatesRemoved = $this->removeDuplicateResponsibilities();
        if (!$duplicatesRemoved) {
            return false;
        }

        $stUpdated = $this->updateAssociation('subject_teachers');
        if (!$stUpdated) {
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
            foreach ($lesson->courses as $subjectID => $subjectConfig) {
                foreach ($subjectConfig->teachers as $teacherID => $delta) {
                    if (in_array($teacherID, $updateIDs)) {
                        unset($schedule->lessons->$lessonIndex->courses->$subjectID->teachers->$teacherID);
                        $schedule->lessons->$lessonIndex->courses->$subjectID->teachers->$mergeID = $delta;
                    }
                }
            }
        }

        foreach ($schedule->configurations as $index => $configuration) {
            $inConfig      = false;
            $configuration = json_decode($configuration);

            foreach ($configuration->teachers as $teacherID => $delta) {
                if (in_array($teacherID, $updateIDs)) {
                    $inConfig = true;
                    unset($configuration->teachers->$teacherID);
                    $configuration->teachers->$mergeID = $delta;
                }
            }

            if ($inConfig) {
                $schedule->configurations[$index] = json_encode($configuration);
            }
        }
    }

    /**
     * Updates the lesson configurations table with the teacher id changes.
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
            $regexp = '"teachers":\\{("[0-9]+":"[\w]*",)*"' . $updateID . '"';
            $selectQuery->where("configuration REGEXP '$regexp'");
            $this->_db->setQuery($selectQuery);

            $storedConfigurations = OrganizerHelper::executeQuery('loadAssocList');
            if (empty($storedConfigurations)) {
                continue;
            }

            foreach ($storedConfigurations as $storedConfiguration) {
                $configuration = json_decode($storedConfiguration['configuration'], true);

                $oldDelta = $configuration['teachers'][$updateID];
                unset($configuration['teachers'][$updateID]);

                // The new id is not yet an index, or it is, but has no delta value and the old id did
                if (!isset($configuration['teachers'][$mergeID])
                    or (empty($configuration['teachers'][$mergeID]) and !empty($oldDelta))) {
                    $configuration['teachers'][$mergeID] = $oldDelta;
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
