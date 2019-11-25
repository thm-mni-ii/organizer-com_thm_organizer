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

use Joomla\CMS\Table\Table;
use Organizer\Helpers\Can;
use Organizer\Tables as Tables;

/**
 * Class which manages stored room data.
 */
class Room extends MergeModel implements ScheduleResource
{
	protected $fkColumn = 'roomID';

	protected $tableName = 'rooms';

	/**
	 * Provides user access checks to rooms
	 *
	 * @return boolean  true if the user may edit the given resource, otherwise false
	 */
	protected function allowEdit()
	{
		return Can::manage('facilities');
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
		return new Tables\Rooms;
	}

	/**
	 * Updates key references to the entry being merged.
	 *
	 * @return boolean  true on success, otherwise false
	 */
	protected function updateAssociations()
	{
		if (!$this->updateDirectAssociation('monitors'))
		{
			return false;
		}

		return $this->updateInstanceRooms();
	}

	/**
	 * Updates the instance groups table to reflect the merge of the groups.
	 *
	 * @return bool true on success, otherwise false;
	 */
	private function updateInstanceRooms()
	{
		if (!$relevantAssocs = $this->getAssociatedResourceIDs('assocID', 'instance_rooms'))
		{
			return true;
		}

		$mergeID = reset($this->selected);

		foreach ($relevantAssocs as $assocID)
		{
			$delta       = '';
			$modified    = '';
			$existing    = new Tables\InstanceRooms;
			$entryExists = $existing->load(['assocID' => $assocID, 'roomID' => $mergeID]);

			foreach ($this->selected as $roomID)
			{
				$irTable        = new Tables\InstanceRooms;
				$loadConditions = ['assocID' => $assocID, 'roomID' => $roomID];
				if (!$irTable->load($loadConditions))
				{
					continue;
				}

				if ($irTable->modified > $modified)
				{
					$delta    = $irTable->delta;
					$modified = $irTable->modified;
				}

				if ($entryExists)
				{
					if ($existing->id !== $irTable->id)
					{
						$irTable->delete();
					}

					continue;
				}

				$entryExists = true;
				$existing    = $irTable;
			}

			$existing->delta    = $delta;
			$existing->roomID   = $mergeID;
			$existing->modified = $modified;
			if (!$existing->store())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Processes the data for an individual schedule
	 *
	 * @param   object &$schedule  the schedule being processed
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
			foreach ($persons as $personID => $data)
			{
				if (!$relevantRooms = array_intersect($data['rooms'], $this->selected))
				{
					continue;
				}

				$relevant = true;

				// Unset all relevant to avoid conditional and unique handling
				foreach (array_keys($relevantRooms) as $relevantIndex)
				{
					unset($instances[$instanceID][$personID]['rooms'][$relevantIndex]);
				}

				// Put the merge id in/back in
				$instances[$instanceID][$personID]['rooms'][] = $mergeID;

				// Resequence to avoid JSON encoding treating the array as associative (object)
				$instances[$instanceID][$personID]['rooms']
					= array_values($instances[$instanceID][$personID]['rooms']);
			}
		}

		if ($relevant)
		{
			$schedule->schedule = json_encode($instances);

			return true;
		}

		return false;
	}
}
