<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Organizer\Helpers;
use Organizer\Helpers\Validators\Schedules as SchedulesValidator;
use Organizer\Tables\InstancePersons;
use Organizer\Tables\Schedules as SchedulesTable;

/**
 * Class which manages stored schedule data.
 * Note on access checks: since schedule access rights are set by department, checking the access rights for one
 * schedule is sufficient for any other schedule modified in the same context.
 */
class Schedule extends BaseModel
{
	private $departmentID;
	private $instanceIDs;
	private $instances;
	private $termID;
	private $unitIDs;

	/**
	 * Activates the selected schedule
	 *
	 * @return true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function activate()
	{
		$scheduleID = Helpers\Input::getSelectedIDs()[0];

		if (!Helpers\Can::schedule('schedule', $scheduleID))
		{
			throw new Exception(Helpers\Languages::_('ORGANIZER_403'), 403);
		}

		$table = new SchedulesTable;

		if (!$table->load($scheduleID) or $table->active)
		{
			return true;
		}

		if ($this->setDeltaContext($scheduleID))
		{
			$this->setActive();
			$this->setRemoved();
			$this->authorizedDeactivate(0, $table->departmentID, $table->termID);
			$table->set('active', 1);
			$table->store();

			return true;
		}

		return false;
	}

	/**
	 * Sets the selected schedule to inactive.
	 *
	 * @param   int  $scheduleID    the id of the schedule to deactivate
	 * @param   int  $departmentID  the id of the department context for the schedule to deactivate
	 * @param   int  $termID        the id of the term context for the schedule to deactivate
	 *
	 * @return bool
	 */
	private function authorizedDeactivate($scheduleID = 0, $departmentID = 0, $termID = 0)
	{
		$conditions = empty($scheduleID) ?
			['active' => 1, 'departmentID' => $departmentID, 'termID' => $termID] : $scheduleID;
		$table      = new SchedulesTable;

		if (!$table->load($conditions))
		{
			return false;
		}

		$table->set('active', 0);

		return $table->store();
	}

	/**
	 * Sets the selected schedule to inactive.
	 *
	 * @return bool
	 * @throws Exception Unauthorized Access
	 */
	public function deactivate()
	{
		$scheduleID = Helpers\Input::getSelectedIDs()[0];
		if (!Helpers\Can::schedule('schedule', $scheduleID))
		{
			throw new Exception(Helpers\Languages::_('ORGANIZER_403'), 403);
		}

		return $this->authorizedDeactivate($scheduleID);
	}

	/**
	 * Deletes the selected schedules
	 *
	 * @return boolean true on successful deletion of all selected schedules, otherwise false
	 * @throws Exception Unauthorized Access
	 */
	public function delete()
	{
		if (!Helpers\Can::scheduleTheseDepartments())
		{
			throw new Exception(Helpers\Languages::_('ORGANIZER_403'), 403);
		}

		$scheduleIDs = Helpers\Input::getSelectedIDs();
		foreach ($scheduleIDs as $scheduleID)
		{
			if (!Helpers\Can::schedule('schedule', $scheduleID))
			{
				throw new Exception(Helpers\Languages::_('ORGANIZER_403'), 403);
			}

			$schedule = new SchedulesTable;

			if ($schedule->load($scheduleID) and !$schedule->delete())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return Table A Table object
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function getTable($name = '', $prefix = '', $options = [])
	{
		return new SchedulesTable;
	}

	/**
	 * Retrieves the unit ids associated with the given instanceIDs
	 *
	 * @param   array  $instanceIDs  the ids of the currently active instances
	 *
	 * @return array the unitIDs associated with the instances
	 */
	private function getUnitIDs($instanceIDs)
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('DISTINCT unitID')
			->from('#__thm_organizer_instances')
			->where('id IN (' . implode(',', $instanceIDs) . ')');
		$dbo->setQuery($query);

		return Helpers\OrganizerHelper::executeQuery('loadColumn', []);
	}

	/**
	 * Sets resources to removed which are no longer valid in the context of a recently activated/uploaded schedule.
	 *
	 * @param   int  $activeID  the if of the active schedule
	 *
	 * @return bool
	 */
	private function setActive()
	{
		$this->setActiveInstances();
		$this->setActiveUnits();

		foreach ($this->instances as $instanceID => $persons)
		{
			foreach ($persons as $personID => $associations)
			{
				$instancePersons = new InstancePersons;
				if (!$instancePersons->load(['instanceID' => $instanceID, 'personID' => $personID]))
				{
					continue;
				}

				$roleID = empty($associations['roleID']) ? 1 : $associations['roleID'];
				if ($instancePersons->delta or $instancePersons->roleID != $roleID)
				{
					if ($instancePersons->delta === 'removed')
					{
						$instancePersons->set('delta', 'new');
						$instancePersons->set('roleID', $roleID);
					}
					elseif ($instancePersons->roleID != $roleID)
					{
						$instancePersons->set('delta', 'changed');
						$instancePersons->set('roleID', $roleID);
					}
					else
					{
						// Delta was 'changed' or 'new' both are no longer applicable.
						$instancePersons->set('delta', '');
					}
					$instancePersons->store();
				}

				$assocID = $instancePersons->id;
				$this->setActiveResources($assocID, 'group', $associations['groups']);
				$roomIDs = empty($associations['rooms']) ? [] : $associations['rooms'];
				$this->setActiveResources($assocID, 'room', $roomIDs);
			}
		}

		return true;
	}

	/**
	 * Sets the status of removed instances to new which are a part of the active schedule.
	 *
	 * @return void
	 */
	private function setActiveInstances()
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->update('#__thm_organizer_instances')
			->set("delta = 'new'")
			->where('id IN (' . implode(',', $this->instanceIDs) . ')')
			->where("delta = 'removed'");
		$dbo->setQuery($query);

		Helpers\OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Sets the status of removed resources to removed.
	 *
	 * @param   int     $assocValue    the id value of the superior association
	 * @param   string  $resourceName  the name of the resource to change
	 * @param   array   $resourceIDs   the ids of the currently associated resources
	 *
	 * @return void
	 */
	private static function setActiveResources($assocValue, $resourceName, $resourceIDs)
	{
		$column = $resourceName . 'ID';
		$table  = "#__thm_organizer_instance_{$resourceName}s";
		$dbo    = Factory::getDbo();
		$query  = $dbo->getQuery(true);
		$query->update($table)
			->set("delta = 'new'")
			->where("assocID = $assocValue")
			->where("$column IN (" . implode(',', $resourceIDs) . ")")
			->where("delta = 'removed'");
		$dbo->setQuery($query);

		Helpers\OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Sets the status of units to new which are a part of the active schedule.
	 *
	 * @return void
	 */
	private function setActiveUnits()
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->update('#__thm_organizer_units')
			->set("delta = 'new'")
			->where('id IN (' . implode(',', $this->unitIDs) . ')')
			->where("delta = 'removed'");
		$dbo->setQuery($query);

		Helpers\OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Sets context variables used to set active or removed schedule items.
	 *
	 * @param   int  $scheduleID  the id of the schedule
	 *
	 * @return void sets object properties
	 */
	private function setDeltaContext($scheduleID)
	{
		$table = new SchedulesTable;
		if ($table->load($scheduleID))
		{
			$this->departmentID = $table->departmentID;
			$this->instances    = json_decode($table->schedule, true);
			$this->instanceIDs  = array_keys($this->instances);
			$this->termID       = $table->termID;
			$this->unitIDs      = $this->getUnitIDs($this->instanceIDs);

			return true;
		}

		return false;
	}

	/**
	 * Creates the delta to the chosen reference schedule
	 *
	 * @return boolean true on successful delta creation, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function setReference()
	{
		$referenceID = Helpers\Input::getSelectedIDs()[0];

		$reference = new SchedulesTable;
		if (empty($referenceID) or !$reference->load($referenceID))
		{
			return true;
		}

		if (!Helpers\Can::schedule('schedule', $referenceID))
		{
			throw new Exception(Helpers\Languages::_('ORGANIZER_403'), 403);
		}

		$departmentID = $reference->departmentID;
		$rInstances   = json_decode($reference->schedule, true);
		$termID       = $reference->termID;
		unset($reference);

		$activeID = Helpers\Schedules::getActiveID($departmentID, $termID);
		$active   = new SchedulesTable;
		if (!$active->load($activeID))
		{
			return true;
		}

		$aInstances = json_decode($active->schedule, true);
		unset($active);

		// Truncate to relevant items to save memory
		$nInstanceIDs = array_keys(array_diff_key($aInstances, $rInstances));
		$aInstances   = array_intersect_key($aInstances, $rInstances);
		$rInstances   = array_intersect_key($rInstances, $aInstances);

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->update('#__thm_organizer_instances')
			->set("delta = 'new'")
			->where('id IN (' . implode(',', $nInstanceIDs) . ')');
		$dbo->setQuery($query);

		Helpers\OrganizerHelper::executeQuery('execute');

		foreach ($aInstances as $instanceID => $aInstance)
		{
			$rInstance = $rInstances[$instanceID];
			if ($nPersonIDs = array_keys(array_diff_key($aInstance, $rInstance)))
			{
				$query->clear();
				$query->update('#__thm_organizer_instance_persons')
					->set("delta = 'new'")
					->where('id IN (' . implode(',', $nPersonIDs) . ')');
				$dbo->setQuery($query);

				Helpers\OrganizerHelper::executeQuery('execute');
			}

			if (!$ePersons = array_intersect_key($aInstance, $rInstance))
			{
				continue;
			}

			// Reset the deltas for existing entries before adjusting them manually dependent on the roleID
			$ePersonIDs = array_keys($ePersons);
			$query->clear();
			$query->update('#__thm_organizer_instance_persons')
				->set("delta = ''")
				->where('id IN (' . implode(',', $ePersonIDs) . ')');
			$dbo->setQuery($query);

			Helpers\OrganizerHelper::executeQuery('execute');
			foreach ($ePersons as $personID => $assocs)
			{
				$instancePersons = new InstancePersons;
				if (!$instancePersons->load(['instanceID' => $instanceID, 'personID' => $personID]))
				{
					continue;
				}

				$assocID = $instancePersons->id;
				if (empty($assocs['roleID']) or $assocs['roleID'] != $rInstance[$personID]['roleID'])
				{
					$instancePersons->delta = 'changed';
					$instancePersons->store();
				}

				if ($nGroupIDs = array_keys(array_diff_key($assocs['groups'], $rInstance[$personID]['groups'])))
				{
					$query->clear();
					$query->update('#__thm_organizer_instance_groups')
						->set("delta = 'new'")
						->where("assocID = '$assocID'")
						->where('groupID IN (' . implode(',', $nGroupIDs) . ')');
					$dbo->setQuery($query);

					Helpers\OrganizerHelper::executeQuery('execute');
				}

				if ($eGroupIDs = array_keys(array_intersect_key($assocs['groups'], $rInstance[$personID]['groups'])))
				{
					$query->clear();
					$query->update('#__thm_organizer_instance_groups')
						->set("delta = ''")
						->where("assocID = '$assocID'")
						->where('groupID IN (' . implode(',', $eGroupIDs) . ')');
					$dbo->setQuery($query);

					Helpers\OrganizerHelper::executeQuery('execute');
				}

				if (empty($assocs['rooms']))
				{
					continue;
				}

				if (empty($rInstance[$personID]['rooms']))
				{
					$nRoomIDs = array_keys($assocs['rooms']);
				}
				else
				{
					$nRoomIDs = array_keys(array_diff_key($assocs['rooms'], $rInstance[$personID]['rooms']));
					$eRoomIDs = array_keys(array_intersect_key($assocs['rooms'], $rInstance[$personID]['rooms']));
				}

				if (!empty($nRoomIDs))
				{
					$query->clear();
					$query->update('#__thm_organizer_instance_rooms')
						->set("delta = 'new'")
						->where("assocID = '$assocID'")
						->where('roomID IN (' . implode(',', $nRoomIDs) . ')');
					$dbo->setQuery($query);

					Helpers\OrganizerHelper::executeQuery('execute');
				}

				if (!empty($eRoomIDs))
				{
					$query->clear();
					$query->update('#__thm_organizer_instance_rooms')
						->set("delta = ''")
						->where("assocID = '$assocID'")
						->where('roomID IN (' . implode(',', $eRoomIDs) . ')');
					$dbo->setQuery($query);

					Helpers\OrganizerHelper::executeQuery('execute');
				}
			}
		}

		return true;
	}

	/**
	 * Sets resources to removed which are no longer valid in the context of a recently activated/uploaded schedule.
	 *
	 * @return bool
	 */
	private function setRemoved()
	{
		$this->setRemovedInstances();
		$this->setRemovedUnits();

		foreach ($this->instances as $instanceID => $persons)
		{
			$personIDs = array_keys($persons);
			$this->setRemovedResources('instanceID', $instanceID, 'person', $personIDs);

			foreach ($persons as $personID => $associations)
			{
				$instancePersons = new InstancePersons;
				if (!$instancePersons->load(['instanceID' => $instanceID, 'personID' => $personID]))
				{
					continue;
				}
				$assocID = $instancePersons->id;
				$this->setRemovedResources('assocID', $assocID, 'group', $associations['groups']);
				$roomIDs = empty($associations['rooms']) ? [] : $associations['rooms'];
				$this->setRemovedResources('assocID', $assocID, 'room', $roomIDs);
			}
		}

		return true;
	}

	/**
	 * Sets the status of instances to removed which are not a part of the active schedule.
	 *
	 * @return void
	 */
	private function setRemovedInstances()
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->update('#__thm_organizer_instances')
			->set("delta = 'removed'")
			->where('id NOT IN (' . implode(',', $this->instanceIDs) . ')')
			->where('unitID IN (' . implode(',', $this->unitIDs) . ')')
			->where("delta != 'removed'");
		$dbo->setQuery($query);

		Helpers\OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Sets the status of removed resources to removed.
	 *
	 * @param   string  $assocColumn   the name of the column referencing the superior association
	 * @param   int     $assocValue    the id value of the superior association
	 * @param   string  $resourceName  the name of the resource to change
	 * @param   array   $resourceIDs   the ids of the currently associated resources
	 *
	 * @return void
	 */
	private static function setRemovedResources($assocColumn, $assocValue, $resourceName, $resourceIDs)
	{
		$column = $resourceName . 'ID';
		$table  = "#__thm_organizer_instance_{$resourceName}s";
		$dbo    = Factory::getDbo();
		$query  = $dbo->getQuery(true);
		$query->update($table)
			->set("delta = 'removed'")
			->where("$assocColumn = $assocValue")
			->where("$column NOT IN (" . implode(',', $resourceIDs) . ")")
			->where("delta != 'removed'");
		$dbo->setQuery($query);

		Helpers\OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Sets the status of units to removed which are not a part of the active schedule.
	 *
	 * @return void
	 */
	private function setRemovedUnits()
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->update('#__thm_organizer_units')
			->set("delta = 'removed'")
			->where("departmentID = {$this->departmentID}")
			->where("termID = {$this->termID}")
			->where('id NOT IN (' . implode(',', $this->unitIDs) . ')')
			->where("delta != 'removed'");
		$dbo->setQuery($query);

		Helpers\OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Toggles the schedule's active status. Adjusting referenced resources as appropriate.
	 *
	 * @return boolean  true on success, otherwise false
	 * @throws Exception Unauthorized Access
	 */
	public function toggle()
	{
		$scheduleID = Helpers\Input::getInt('id');
		$table      = new SchedulesTable;

		if (!Helpers\Can::schedule('schedule', $scheduleID))
		{
			throw new Exception(Helpers\Languages::_('ORGANIZER_403'), 403);
		}

		if (empty($scheduleID) or !$table->load($scheduleID))
		{
			return false;
		}

		if ($table->active)
		{
			$table->set('active', 0);

			return $table->store();
		}

		if ($this->setDeltaContext($scheduleID))
		{
			$this->setActive();
			$this->setRemoved();
			$this->authorizedDeactivate(0, $table->departmentID, $table->termID);
			$table->set('active', 1);
			$table->store();

			return true;
		}

		return false;
	}

	/**
	 * Saves a schedule in the database for later use
	 *
	 * @param   bool  $notify  true if affected participants/persons should be notified
	 *
	 * @return  boolean true on success, otherwise false
	 * @throws Exception Invalid Request / Unauthorized Access
	 */
	public function upload($notify = false)
	{
		$departmentID = Helpers\Input::getInt('departmentID');
		$invalidForm  = (empty($departmentID));

		if ($invalidForm)
		{
			throw new Exception(Helpers\Languages::_('ORGANIZER_400'), 400);
		}

		if (!Helpers\Can::schedule('schedule', $departmentID))
		{
			throw new Exception(Helpers\Languages::_('ORGANIZER_403'), 403);
		}

		$validator = new SchedulesValidator();
		$valid     = $validator->validate();

		if (!$valid)
		{
			return false;
		}

		$this->authorizedDeactivate(0, $departmentID, $validator->termID);

		$data = [
			'active'       => 1,
			'creationDate' => $validator->creationDate,
			'creationTime' => $validator->creationTime,
			'departmentID' => $departmentID,
			'schedule'     => json_encode($validator->instances),
			'termID'       => $validator->termID,
			'userID'       => Factory::getUser()->id
		];

		$newTable = new SchedulesTable;
		if (!$newTable->save($data))
		{
			return false;
		}

		$this->setDeltaContext($newTable->id);

		return $this->setRemoved();
	}
}
