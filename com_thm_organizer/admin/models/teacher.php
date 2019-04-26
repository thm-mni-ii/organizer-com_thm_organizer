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

require_once 'merge.php';

/**
 * Class which manages stored teacher data.
 */
class THM_OrganizerModelTeacher extends THM_OrganizerModelMerge
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
        return THM_OrganizerHelperAccess::allowHRAccess();
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param string $name    The table name. Optional.
     * @param string $prefix  The class prefix. Optional.
     * @param array  $options Configuration array for model. Optional.
     *
     * @return  \JTable  A \JTable object
     */
    public function getTable($name = 'teachers', $prefix = 'thm_organizerTable', $options = [])
    {
        return \JTable::getInstance($name, $prefix, $options);
    }

    /**
     * Removes potential duplicates before the subject teacher associations are updated.
     *
     * @return bool true if no error occurred, otherwise false
     */
    private function removeDuplicateResponsibilities()
    {
        $table = '#__thm_organizer_subject_teachers';

        $selectQuery = $this->_db->getQuery(true);
        $selectQuery->select('DISTINCT subjectID, teacherResp')
            ->from($table)
            ->where("teacherID = '{$this->data['id']}'");
        $this->_db->setQuery($selectQuery);

        $existingResps = OrganizerHelper::executeQuery('loadAssocList');
        $oldIDString   = "'" . implode("', '", $this->data['otherIDs']) . "'";

        if (!empty($existingResps)) {
            $potentialDuplicates = [];
            foreach ($existingResps as $resp) {
                $potentialDuplicates[]
                    = "(subjectID = '{$resp['subjectID']}' AND teacherResp = '{$resp['teacherResp']}')";
            }
            $potentialDuplicates = '(' . implode(' OR ', $potentialDuplicates) . ')';

            $deleteQuery = $this->_db->getQuery(true);
            $deleteQuery->delete($table)
                ->where("teacherID IN ( $oldIDString )")
                ->where($potentialDuplicates);
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
        foreach ($schedule->lessons as $lessonIndex => $lesson) {
            foreach ($lesson->subjects as $subjectID => $subjectConfig) {
                foreach ($subjectConfig->teachers as $teacherID => $delta) {
                    if (in_array($teacherID, $this->data['otherIDs'])) {
                        unset($schedule->lessons->$lessonIndex->subjects->$subjectID->teachers->$teacherID);
                        $schedule->lessons->$lessonIndex->subjects->$subjectID->teachers->{$this->data['id']} = $delta;
                    }
                }
            }
        }

        foreach ($schedule->configurations as $index => $configuration) {
            $inConfig      = false;
            $configuration = json_decode($configuration);

            foreach ($configuration->teachers as $teacherID => $delta) {
                if (in_array($teacherID, $this->data['otherIDs'])) {
                    // Whether old or new high probability of having to overwrite an attribute this enables standard handling.
                    unset($configuration->teachers->$teacherID);
                    $inConfig                                     = true;
                    $configuration->teachers->{$this->data['id']} = $delta;
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

        $table       = '#__thm_organizer_lesson_configurations';
        $selectQuery = $this->_db->getQuery(true);
        $selectQuery->select('id, configuration')
            ->from($table);

        $updateQuery = $this->_db->getQuery(true);
        $updateQuery->update($table);

        foreach ($this->data['otherIDs'] as $oldID) {
            $selectQuery->clear('where');
            $regexp = '"teachers":\\{("[0-9]+":"[\w]*",)*"' . $oldID . '"';
            $selectQuery->where("configuration REGEXP '$regexp'");
            $this->_db->setQuery($selectQuery);

            $storedConfigurations = OrganizerHelper::executeQuery('loadAssocList');
            if (empty($storedConfigurations)) {
                continue;
            }

            foreach ($storedConfigurations as $storedConfiguration) {
                $configuration = json_decode($storedConfiguration['configuration'], true);

                $oldDelta = $configuration['teachers'][$oldID];
                unset($configuration['teachers'][$oldID]);

                // The new id is not yet an index, or it is, but has no delta value and the old id did
                if (!isset($configuration['teachers'][$this->data['id']])
                    or (empty($configuration['teachers'][$this->data['id']]) and !empty($oldDelta))) {
                    $configuration['teachers'][$this->data['id']] = $oldDelta;
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
