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
use Organizer\Helpers\OrganizerHelper;
use Organizer\Tables\Rooms as RoomsTable;

/**
 * Class which manages stored room data.
 */
class Room extends MergeModel
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
		return new RoomsTable;
	}

	/**
	 * Updates key references to the entry being merged.
	 *
	 * @return boolean  true on success, otherwise false
	 */
	protected function updateAssociations()
	{
		if (!$this->updateAssociation('monitors'))
		{
			return false;
		}

		if (!$this->updateStoredConfigurations())
		{
			return false;
		}

		return true;
	}

	/**
	 * Processes the data for an individual schedule
	 *
	 * @param   object &$schedule  the schedule being processed
	 *
	 * @return void
	 */
	protected function updateSchedule(&$schedule)
	{
		$updateIDs = $this->selected;
		$mergeID   = array_shift($updateIDs);

		foreach ($schedule->configurations as $index => $configuration)
		{
			$inConfig      = false;
			$configuration = json_decode($configuration);

			foreach ($configuration->rooms as $roomID => $delta)
			{
				if (in_array($roomID, $updateIDs))
				{
					$inConfig = true;
					unset($configuration->rooms->$roomID);
					$configuration->rooms->$mergeID = $delta;
				}
			}

			if ($inConfig)
			{
				$schedule->configurations[$index] = json_encode($configuration, JSON_UNESCAPED_UNICODE);
			}
		}
	}

	/**
	 * Updates the lesson configurations table with the room id changes.
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

		$updateIDs = $this->selected;
		$mergeID   = array_shift($updateIDs);

		foreach ($updateIDs as $updateID)
		{
			$selectQuery->clear('where');
			$regexp = '"rooms":\\{("[0-9]+":"[\w]*",)*"' . $updateID . '"';
			$selectQuery->where("configuration REGEXP '$regexp'");
			$this->_db->setQuery($selectQuery);

			$storedConfigurations = OrganizerHelper::executeQuery('loadAssocList');
			if (empty($storedConfigurations))
			{
				continue;
			}

			foreach ($storedConfigurations as $storedConfiguration)
			{
				$configuration = json_decode($storedConfiguration['configuration'], true);

				$oldDelta = $configuration['rooms'][$updateID];
				unset($configuration['rooms'][$updateID]);

				// The new id is not yet an index, or it is, but has no delta value and the old id did
				if (!isset($configuration['rooms'][$mergeID])
					or (empty($configuration['rooms'][$mergeID]) and !empty($oldDelta)))
				{
					$configuration['rooms'][$mergeID] = $oldDelta;
				}

				$configuration = json_encode($configuration, JSON_UNESCAPED_UNICODE);
				$updateQuery->clear('set');
				$updateQuery->set("configuration = '$configuration'");
				$updateQuery->clear('where');
				$updateQuery->where("id = '{$storedConfiguration['id']}'");
				$this->_db->setQuery($updateQuery);
				$success = (bool) OrganizerHelper::executeQuery('execute');
				if (!$success)
				{
					return false;
				}
			}
		}

		return true;
	}
}
