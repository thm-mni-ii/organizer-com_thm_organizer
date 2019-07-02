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
use Organizer\Helpers\Access;
use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class provides methods for merging resources. Resource specific tasks are implemented in the extending classes.
 */
abstract class MergeModel extends BaseModel
{
    /**
     * @var array the preprocessed form data
     */
    protected $data = [];

    /**
     * @var the column name in the department resources table
     */
    protected $deptResource;

    /**
     * The column name referencing this resource in other resource tables.
     * @var string
     */
    protected $fkColumn = '';

    /**
     * The ids selected by the user
     *
     * @var array
     */
    protected $selected = [];

    /**
     * The name of the table where this resource is stored.
     * @var string
     */
    protected $tableName = '';

    /**
     * Provides resource specific user access checks
     *
     * @return boolean  true if the user may edit the given resource, otherwise false
     */
    protected function allowEdit()
    {
        return Access::isAdmin();
    }

    /**
     * Performs an automated merge of field entries, if allowed by to plausibility constraints. No access checks here
     * because this function is just a preprocessor for the function merge, which does have access checks.
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function autoMerge()
    {
        $this->selected = Input::getSelectedIDs();
        $entries        = $this->getEntries();

        $keyProperties = ['untisID'];
        if ($this->fkColumn == 'teacherID') {
            $keyProperties[] = 'username';
        }

        $updateIDs = $this->selected;

        $data       = [];
        $data['id'] = array_shift($updateIDs);

        foreach ($entries as $entry) {
            foreach ($entry as $property => $value) {
                if ($property == 'id') {
                    continue;
                }

                $value = trim($value);

                if (empty($value)) {
                    continue;
                }

                if (empty($data[$property])) {
                    $data[$property] = $value;
                    continue;
                }

                if ($data[$property] == $value) {
                    continue;
                }

                // Differing key property or numerical values => auto merge impossible
                $isKeyProperty = in_array($property, $keyProperties);
                if ($isKeyProperty or is_int($value)) {
                    return false;
                }

                $leftInRight = (strpos($value, $data[$property]) !== false
                    and strlen($value) > strlen($data[$property]));
                if ($leftInRight) {
                    $data[$property] = $value;
                    continue;
                }

                $rightInLeft = (strpos($data[$property], $value) !== false
                    and strlen($data[$property]) > strlen($value));
                if ($rightInLeft) {
                    $data[$property] = $value;
                    continue;
                }

                // string values are incompatible => auto merge impossible
                return false;
            }
        }

        if (!empty($data['id'])) {
            $this->data = $data;
        }

        return $this->merge();
    }

    /**
     * Attempts to delete resource entries
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception => invalid request or unauthorized access
     */
    public function delete()
    {
        $this->selected = Input::getSelectedIDs();

        if (empty($this->selected)) {
            return false;
        }

        if (!$this->allowEdit()) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $table = $this->getTable();

        foreach ($this->selected as $resourceID) {
            try {
                $table->load($resourceID);
            } catch (Exception $exc) {
                OrganizerHelper::message($exc->getMessage(), 'error');

                return false;
            }

            $this->_db->transactionStart();

            try {
                $table->delete($resourceID);
            } catch (Exception $exc) {
                OrganizerHelper::message($exc->getMessage(), 'error');
                $this->_db->transactionRollback();

                return false;
            }

            $this->_db->transactionCommit();
        }

        return true;
    }

    /**
     * Retrieves resource entries from the database
     *
     * @return mixed  array on success, otherwise null
     */
    protected function getEntries()
    {
        $query = $this->_db->getQuery(true);
        $query->select('*')
            ->from("#__thm_organizer_{$this->tableName}")
            ->where("id IN ('" . implode("', '", $this->selected) . "')")
            ->order('id ASC');

        $this->_db->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList');
    }

    /**
     * Retrieves the ids of all saved schedules
     *
     * @return mixed  array on success, otherwise null
     */
    protected function getSchedulesIDs()
    {
        $query = $this->_db->getQuery(true);
        $query->select('id');
        $query->from('#__thm_organizer_schedules');
        $this->_db->setQuery($query);

        return OrganizerHelper::executeQuery('loadColumn', []);
    }

    /**
     * Retrieves the schedule for the given id.
     *
     * @param int $scheduleID the id of the schedule
     *
     * @return mixed  object on success, otherwise null
     */
    protected function getScheduleObject($scheduleID)
    {
        $query = $this->_db->getQuery(true);
        $query->select('schedule');
        $query->from('#__thm_organizer_schedules');
        $query->where("id = '$scheduleID'");
        $this->_db->setQuery($query);

        $schedule = OrganizerHelper::executeQuery('loadResult');

        return empty($schedule) ? null : json_decode($schedule);
    }

    /**
     * Merges resource entries and cleans association tables.
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function merge()
    {
        $this->selected = Input::getSelectedIDs();

        if (!$this->allowEdit()) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        set_time_limit(0);

        $this->_db->transactionStart();

        // Associations have to be updated before entity references are deleted by foreign keys
        $associationsUpdated = $this->updateAssociations();
        if (!$associationsUpdated) {
            $this->_db->transactionRollback();

            return false;
        }

        $data          = empty($this->data) ? Input::getForm() : $this->data;
        $deprecatedIDs = $this->selected;
        $data['id']    = array_shift($deprecatedIDs);
        $table         = $this->getTable();

        // Remove deprecated entries
        foreach ($deprecatedIDs as $deprecated) {
            $deleted = $table->delete($deprecated);
            if (!$deleted) {
                $this->_db->transactionRollback();

                return false;
            }
        }

        // Save the merged values of the current entry
        $success = $table->save($data);
        if (!$success) {
            $this->_db->transactionRollback();

            return false;
        }

        $schedulesSuccess = $this->updateSchedules();
        if (!$schedulesSuccess) {
            $this->_db->transactionRollback();

            return false;
        }

        $this->_db->transactionCommit();

        // Any further processing should not iterate over deprecated ids.
        $this->selected = [$data['id']];

        return true;
    }

    /**
     * Attempts to save the resource.
     *
     * @param array $data form data which has been preprocessed by inheriting classes.
     *
     * @return bool true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function save($data = [])
    {
        if (empty(Input::getSelectedIDs())) {
            return false;
        }

        if (!$this->allowEdit()) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $this->_db->transactionStart();

        $this->data = empty($data) ? Input::getForm() : $data;
        $table      = $this->getTable();
        $success    = $table->save($this->data);

        if ($success) {
            // Set id for new rewrite for existing.
            $this->data['id'] = $table->id;

            if (!empty($this->deptResource)) {
                $departmentsUpdated = $this->updateDepartments();
                if (!$departmentsUpdated) {
                    $this->_db->transactionRollback();

                    return false;
                }
            }

            $this->_db->transactionCommit();

            return $table->id;
        }

        $this->_db->transactionRollback();

        return false;
    }

    /**
     * Updates an association
     *
     * @param string $tableName the unique part of the table name
     *
     * @return boolean  true on success, otherwise false
     */
    protected function updateAssociation($tableName)
    {
        $updateIDs = $this->selected;
        $mergeID   = array_shift($updateIDs);
        $updateIDs = "'" . implode("', '", $updateIDs) . "'";

        $query = $this->_db->getQuery(true);
        $query->update("#__thm_organizer_$tableName");
        $query->set("{$this->fkColumn} = '$mergeID'");
        $query->where("{$this->fkColumn} IN ( $updateIDs )");
        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute', false, null, true);
    }

    /**
     * Updates the resource dependent associations
     *
     * @return boolean  true on success, otherwise false
     */
    abstract protected function updateAssociations();

    /**
     * Updates the associated departments for a resource
     *
     * @return bool true on success, otherwise false
     */
    private function updateDepartments()
    {
        $existingQuery = $this->_db->getQuery(true);
        $existingQuery->select('DISTINCT departmentID');
        $existingQuery->from('#__thm_organizer_department_resources');
        $existingQuery->where("{$this->deptResource} = '{$this->data['id']}'");
        $this->_db->setQuery($existingQuery);
        $existing   = OrganizerHelper::executeQuery('loadColumn', []);
        $deprecated = array_diff($existing, $this->data['departmentID']);

        if (!empty($deprecated)) {
            $deletionQuery = $this->_db->getQuery(true);
            $deletionQuery->delete('#__thm_organizer_department_resources');
            $deletionQuery->where("{$this->deptResource} = '{$this->data['id']}'");
            $deletionQuery->where("departmentID IN ('" . implode("','", $deprecated) . "')");
            $this->_db->setQuery($deletionQuery);

            $deleted = (bool)OrganizerHelper::executeQuery('execute', false, null, true);
            if (!$deleted) {
                return false;
            }
        }

        $new = array_diff($this->data['departmentID'], $existing);

        if (!empty($new)) {
            $insertQuery = $this->_db->getQuery(true);
            $insertQuery->insert('#__thm_organizer_department_resources');
            $insertQuery->columns("departmentID, {$this->deptResource}");

            foreach ($new as $newID) {
                $insertQuery->values("'$newID', '{$this->data['id']}'");
                $this->_db->setQuery($insertQuery);

                $inserted = (bool)OrganizerHelper::executeQuery('execute', false, null, true);
                if (!$inserted) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Updates department resource associations
     *
     * @return boolean  true on success, otherwise false
     */
    protected function updateDRAssociation()
    {
        $relevantIDs = "'" . implode("', '", $this->selected) . "'";

        $departmentQuery = $this->_db->getQuery(true);
        $departmentQuery->select('DISTINCT departmentID');
        $departmentQuery->from('#__thm_organizer_department_resources');
        $departmentQuery->where("{$this->deptResource} IN ( $relevantIDs )");
        $this->_db->setQuery($departmentQuery);
        $deptIDs = OrganizerHelper::executeQuery('loadColumn', []);

        if (empty($deptIDs)) {
            return true;
        }

        $deleteQuery = $this->_db->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_department_resources')
            ->where("{$this->fkColumn} IN ( $relevantIDs )");
        $this->_db->setQuery($deleteQuery);

        $deleted = (bool)OrganizerHelper::executeQuery('execute', false, null, true);
        if (!$deleted) {
            return false;
        }

        $mergeID     = reset($this->selected);
        $insertQuery = $this->_db->getQuery(true);
        $insertQuery->insert('#__thm_organizer_department_resources');
        $insertQuery->columns("departmentID, {$this->fkColumn}");

        foreach ($deptIDs as $deptID) {
            $insertQuery->values("'$deptID', $mergeID");
        }

        $this->_db->setQuery($insertQuery);

        return (bool)OrganizerHelper::executeQuery('execute', false, null, true);
    }

    /**
     * Processes the data for an individual schedule
     *
     * @param object &$schedule the schedule being processed
     *
     * @return void
     */
    abstract protected function updateSchedule(&$schedule);

    /**
     * Updates room data and lesson associations in active schedules
     *
     * @return bool  true on success, otherwise false
     */
    private function updateSchedules()
    {
        $scheduleIDs = $this->getSchedulesIDs();
        if (empty($scheduleIDs)) {
            return true;
        }

        foreach ($scheduleIDs as $scheduleID) {
            $scheduleObject = $this->getScheduleObject($scheduleID);

            if (empty($scheduleObject)) {
                continue;
            }

            $scheduleObject->configurations = (array)$scheduleObject->configurations;
            $this->updateSchedule($scheduleObject);

            $scheduleTable = OrganizerHelper::getTable('Schedules');
            $scheduleTable->load($scheduleID);
            $scheduleTable->schedule = json_encode($scheduleObject);
            $success                 = $scheduleTable->store();
            if (!$success) {
                return false;
            }
        }

        return true;
    }
}
