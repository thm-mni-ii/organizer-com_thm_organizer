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

use Joomla\CMS\Table\Table;
use Organizer\Helpers;
use Organizer\Tables;

/**
 * Class which manages stored person data.
 */
class Person extends MergeModel implements ScheduleResource
{
	protected $deptResource = 'personID';

	protected $fkColumn = 'personID';

	/**
	 * Aggregates the attributes/resources associated with a person for a particular instance.
	 *
	 * @param   array  $assocs  the attributes/resources associated with the persons before the merge process.
	 *
	 * @return array the aggregated associations
	 */
	private function aggregateAssocs($assocs)
	{
		if (count($assocs) === 1)
		{
			return $assocs[0];
		}

		$groups  = [];
		$roleIDs = [];
		$rooms   = [];
		foreach ($assocs as $assoc)
		{
			foreach ($assoc['groups'] as $groupID)
			{
				$groups[$groupID] = $groupID;
			}

			if ($roleIDs[$assoc['roleID']])
			{
				$roleIDs[$assoc['roleID']]++;
			}
			else
			{
				$roleIDs[$assoc['roleID']] = 1;
			}

			foreach ($assoc['rooms'] as $roomID)
			{
				$rooms[$roomID] = $roomID;
			}
		}

		$roleID = array_keys($roleIDs, max($roleIDs))[0];

		return ['groups' => array_values($groups), 'roleID' => $roleID, 'rooms' => array_values($rooms)];
	}

	/**
	 * Provides user access checks to persons
	 *
	 * @return boolean  true if the user may edit the given resource, otherwise false
	 */
	protected function allowEdit()
	{
		return Helpers\Can::edit('persons', $this->selected);
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
		return new Tables\Persons;
	}

	/**
	 * Updates key references to the entry being merged.
	 *
	 * @return boolean  true on success, otherwise false
	 */
	protected function updateAssociations()
	{
		if (!$this->updateDRAssociation())
		{
			return false;
		}

		if (!$this->updateEventCoordinators())
		{
			return false;
		}

		if (!$this->updateInstancePersons())
		{
			return false;
		}

		return $this->updateSubjectPersons();
	}

	/**
	 * Updates the event coordinators table to reflect the merge of the persons.
	 *
	 * @return bool true on success, otherwise false;
	 */
	private function updateEventCoordinators()
	{
		if (!$relevantEvents = $this->getAssociatedResourceIDs('eventID', 'event_coordinators'))
		{
			return true;
		}

		$mergeID = reset($this->selected);

		foreach ($relevantEvents as $eventID)
		{
			$existing    = new Tables\EventCoordinators;
			$entryExists = $existing->load(['eventID' => $eventID, 'personID' => $mergeID]);

			foreach ($this->selected as $personID)
			{
				$ecTable        = new Tables\EventCoordinators;
				$loadConditions = ['eventID' => $eventID, 'personID' => $personID];
				if ($ecTable->load($loadConditions))
				{
					if ($entryExists)
					{
						if ($existing->id !== $ecTable->id)
						{
							$ecTable->delete();
						}

						continue;
					}

					$ecTable->personID = $mergeID;
					if ($ecTable->store())
					{
						$entryExists = true;
						continue;
					}

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Updates the instance persons table to reflect the merge of the persons.
	 *
	 * @return bool true on success, otherwise false;
	 */
	private function updateInstancePersons()
	{
		if (!$relevantInstances = $this->getAssociatedResourceIDs('instanceID', 'instance_persons'))
		{
			return true;
		}

		$mergeID = reset($this->selected);

		foreach ($relevantInstances as $instanceID)
		{
			$delta    = '';
			$modified = '';
			$roleID   = '';

			$existing    = new Tables\InstancePersons;
			$entryExists = $existing->load(['instanceID' => $instanceID, 'personID' => $mergeID]);

			foreach ($this->selected as $personID)
			{
				$ipTable        = new Tables\InstancePersons;
				$loadConditions = ['instanceID' => $instanceID, 'personID' => $personID];
				if (!$ipTable->load($loadConditions))
				{
					continue;
				}

				if ($ipTable->modified > $modified)
				{
					$delta    = $ipTable->delta;
					$modified = $ipTable->modified;
					$roleID   = $ipTable->roleID;
				}

				if ($entryExists)
				{
					if ($existing->id !== $ipTable->id)
					{
						$ipTable->delete();
					}

					continue;
				}

				$ipTable->delta    = $delta;
				$ipTable->modified = $modified;
				$ipTable->personID = $mergeID;
				$ipTable->roleID   = $roleID;
				if (!$ipTable->store())
				{
					return false;
				}

				$entryExists = true;
				$existing    = $ipTable;
			}

		}

		return true;
	}

	/**
	 * Updates the subject persons table to reflect the merge of the persons.
	 *
	 * @return bool true on success, otherwise false;
	 */
	private function updateSubjectPersons()
	{
		$mergeIDs = implode(', ', $this->selected);
		$query    = $this->_db->getQuery(true);
		$query->select("DISTINCT subjectID, role")
			->from("#__thm_organizer_subject_persons")
			->where("personID IN ($mergeIDs)");
		$this->_db->setQuery($query);
		if (!$relevantAssocs = Helpers\OrganizerHelper::executeQuery('loadAssocList', []))
		{
			return true;
		}

		$mergeID          = reset($this->selected);
		$responsibilities = [];

		foreach ($relevantAssocs as $assoc)
		{
			$subjectID = $assoc['subjectID'];
			if (empty($responsibilities[$subjectID]))
			{
				$responsibilities[$subjectID] = [];
			}

			$responsibilities[$subjectID][$assoc['role']] = $assoc['role'];
		}

		foreach ($responsibilities as $subjectID => $roles)
		{
			foreach ($roles as $role)
			{
				$existing    = new Tables\SubjectPersons;
				$entryExists = $existing->load(['personID' => $mergeID, 'role' => $role, 'subjectID' => $subjectID]);

				foreach ($this->selected as $personID)
				{
					$spTable        = new Tables\SubjectPersons;
					$loadConditions = ['personID' => $personID, 'role' => $role, 'subjectID' => $subjectID];
					if (!$spTable->load($loadConditions))
					{
						continue;
					}

					if ($entryExists)
					{
						if ($existing->id !== $spTable->id)
						{
							$spTable->delete();
						}

						continue;
					}

					$entryExists = true;
					$existing    = $spTable;
				}

				$existing->personID = $mergeID;
				if (!$existing->store())
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Processes the data for an individual schedule
	 *
	 * @param   Tables\Schedules  $schedule  the schedule being processed
	 *
	 * @return bool true if the schedule was changed, otherwise false
	 */
	public function updateSchedule($schedule)
	{
		$instances = json_decode($schedule->schedule, true);
		$mergeID   = reset($this->selected);
		$relevant  = false;

		foreach ($instances as $instanceID => $persons)
		{
			if (!$relevantPersons = array_intersect(array_keys($persons), $this->selected))
			{
				continue;
			}

			$relevant = true;

			$assocs = [];
			foreach ($relevantPersons as $personID)
			{
				$assocs[] = $instances[$instanceID][$personID];
				unset($instances[$instanceID][$personID]);
			}

			$instances[$instanceID][$mergeID] = $this->aggregateAssocs($assocs);
		}

		if ($relevant)
		{
			$schedule->schedule = json_encode($instances);

			return true;
		}

		return false;
	}
}
