<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Organizer\Helpers\Can;
use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Tables\Schedules as SchedulesTable;

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
	 * Provides resource specific user access checks
	 *
	 * @return boolean  true if the user may edit the given resource, otherwise false
	 */
	protected function allowEdit()
	{
		return Can::administrate();
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

		if (empty($this->selected))
		{
			return false;
		}

		if (!$this->allowEdit())
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$table = $this->getTable();

		foreach ($this->selected as $resourceID)
		{
			try
			{
				$table->load($resourceID);
			}
			catch (Exception $exc)
			{
				OrganizerHelper::message($exc->getMessage(), 'error');

				return false;
			}

			try
			{
				$table->delete($resourceID);
			}
			catch (Exception $exc)
			{
				OrganizerHelper::message($exc->getMessage(), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Get the ids of the resources associated over an association table.
	 *
	 * @param   string  $assocColumn  the name of the column which has the associated ids
	 * @param   string  $assocTable   the unique part of the association table name
	 *
	 * @return array the associated ids
	 */
	protected function getAssociatedResourceIDs($assocColumn, $assocTable)
	{
		$mergeIDs = implode(', ', $this->selected);
		$query    = $this->_db->getQuery(true);
		$query->select("DISTINCT $assocColumn")
			->from("#__thm_organizer_$assocTable")
			->where("$this->fkColumn IN ($mergeIDs)");
		$this->_db->setQuery($query);

		return OrganizerHelper::executeQuery('loadColumn', []);
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
	 * Merges resource entries and cleans association tables.
	 *
	 * @return boolean  true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function merge()
	{
		$this->selected = Input::getSelectedIDs();
		sort($this->selected);

		if (!$this->allowEdit())
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		// Associations have to be updated before entity references are deleted by foreign keys
		if (!$this->updateAssociations())
		{
			return false;
		}

		$data          = empty($this->data) ? Input::getFormItems()->toArray() : $this->data;
		$deprecatedIDs = $this->selected;
		$data['id']    = array_shift($deprecatedIDs);
		$table         = $this->getTable();

		// Remove deprecated entries
		foreach ($deprecatedIDs as $deprecated)
		{
			if (!$table->delete($deprecated))
			{
				return false;
			}
		}

		// Save the merged values of the current entry
		if (!$table->save($data))
		{
			return false;
		}

		if ($this instanceof ScheduleResource and !$this->updateSchedules())
		{
			return false;
		}

		// Any further processing should not iterate over deprecated ids.
		$this->selected = [$data['id']];

		return true;
	}

	/**
	 * Attempts to save the resource.
	 *
	 * @param   array  $data  form data which has been preprocessed by inheriting classes.
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function save($data = [])
	{
		if (empty(Input::getSelectedIDs()))
		{
			return false;
		}

		if (!$this->allowEdit())
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}


		$this->data = empty($data) ? Input::getFormItems()->toArray() : $data;
		$table      = $this->getTable();

		if ($table->save($this->data))
		{
			// Set id for new rewrite for existing.
			$this->data['id'] = $table->id;

			if (!empty($this->deptResource) and !$this->updateDepartments())
			{
				return false;
			}

			return $table->id;
		}

		return false;
	}

	/**
	 * Updates an association where the associated resource itself has a fk reference to the resource being merged.
	 *
	 * @param   string  $tableSuffix  the unique part of the table name
	 *
	 * @return boolean  true on success, otherwise false
	 */
	protected function updateDirectAssociation($tableSuffix)
	{
		$updateIDs = $this->selected;
		$mergeID   = array_shift($updateIDs);
		$updateIDs = "'" . implode("', '", $updateIDs) . "'";

		$query = $this->_db->getQuery(true);
		$query->update("#__thm_organizer_$tableSuffix");
		$query->set("{$this->fkColumn} = '$mergeID'");
		$query->where("{$this->fkColumn} IN ( $updateIDs )");
		$this->_db->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('execute', false);
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
		$existing = OrganizerHelper::executeQuery('loadColumn', []);

		if ($deprecated = array_diff($existing, $this->data['departmentID']))
		{
			$deletionQuery = $this->_db->getQuery(true);
			$deletionQuery->delete('#__thm_organizer_department_resources');
			$deletionQuery->where("{$this->deptResource} = '{$this->data['id']}'");
			$deletionQuery->where("departmentID IN ('" . implode("','", $deprecated) . "')");
			$this->_db->setQuery($deletionQuery);

			$deleted = (bool) OrganizerHelper::executeQuery('execute', false, null, true);
			if (!$deleted)
			{
				return false;
			}
		}

		$new = array_diff($this->data['departmentID'], $existing);

		if (!empty($new))
		{
			$insertQuery = $this->_db->getQuery(true);
			$insertQuery->insert('#__thm_organizer_department_resources');
			$insertQuery->columns("departmentID, {$this->deptResource}");

			foreach ($new as $newID)
			{
				$insertQuery->values("'$newID', '{$this->data['id']}'");
				$this->_db->setQuery($insertQuery);

				$inserted = (bool) OrganizerHelper::executeQuery('execute', false, null, true);
				if (!$inserted)
				{
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

		if (empty($deptIDs))
		{
			return true;
		}

		$deleteQuery = $this->_db->getQuery(true);
		$deleteQuery->delete('#__thm_organizer_department_resources')
			->where("{$this->fkColumn} IN ( $relevantIDs )");
		$this->_db->setQuery($deleteQuery);

		$deleted = (bool) OrganizerHelper::executeQuery('execute', false, null, true);
		if (!$deleted)
		{
			return false;
		}

		$mergeID     = reset($this->selected);
		$insertQuery = $this->_db->getQuery(true);
		$insertQuery->insert('#__thm_organizer_department_resources');
		$insertQuery->columns("departmentID, {$this->fkColumn}");

		foreach ($deptIDs as $deptID)
		{
			$insertQuery->values("'$deptID', $mergeID");
		}

		$this->_db->setQuery($insertQuery);

		return (bool) OrganizerHelper::executeQuery('execute', false, null, true);
	}

	/**
	 * Updates room data and lesson associations in active schedules
	 *
	 * @return bool  true on success, otherwise false
	 */
	private function updateSchedules()
	{
		$scheduleIDs = $this->getSchedulesIDs();
		if (empty($scheduleIDs))
		{
			return true;
		}

		foreach ($scheduleIDs as $scheduleID)
		{
			$scheduleTable = new SchedulesTable;

			if (!$scheduleTable->load($scheduleID))
			{
				continue;
			}

			if ($this->updateSchedule($scheduleTable) and !$scheduleTable->store())
			{
				return false;
			}
		}

		return true;
	}
}
