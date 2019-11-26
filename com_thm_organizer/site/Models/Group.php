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
use Joomla\CMS\Table\Table;
use Organizer\Helpers as Helpers;
use Organizer\Tables as Tables;

/**
 * Class which manages stored group data.
 */
class Group extends MergeModel implements ScheduleResource
{
	protected $fkColumn = 'groupID';

	/**
	 * Provides resource specific user access checks
	 *
	 * @return boolean  true if the user may edit the given resource, otherwise false
	 */
	protected function allowEdit()
	{
		return Helpers\Can::edit('groups', $this->selected);
	}

	/**
	 * Performs batch processing of groups, specifically their publication per period and their associated grids.
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function batch()
	{
		$this->selected = Helpers\Input::getSelectedIDs();
		if (empty($this->selected))
		{
			return false;
		}

		if (!Helpers\Can::edit('groups', $this->selected))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		return $this->savePublishing();
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
		return new Tables\Groups;
	}

	/**
	 * Merges group entries and cleans association tables.
	 *
	 * @return boolean  true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function merge()
	{
		if (!parent::merge())
		{
			return false;
		}

		return $this->savePublishing();
	}

	/**
	 * Sets all expired group / term associations to published.
	 *
	 * @return bool true on success, otherwise false.
	 */
	public function publishPast()
	{
		$terms = Helpers\Terms::getResources();
		$today = date('Y-m-d');

		$query = $this->_db->getQuery(true);
		$query->update('#__thm_organizer_group_publishing')->set('published = 1');

		foreach ($terms as $term)
		{
			if ($term['endDate'] >= $today)
			{
				continue;
			}

			$query->clear('where');
			$query->where("termID = {$term['id']}");

			$this->_db->setQuery($query);
			$success = Helpers\OrganizerHelper::executeQuery('execute');
			if (!$success)
			{
				return false;
			}
		}

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
		$this->selected = Helpers\Input::getSelectedIDs();

		if (!parent::save($data))
		{
			return false;
		}

		if (empty($this->savePublishing()))
		{
			return false;
		}

		return reset($this->selected);
	}

	/**
	 * Saves the publishing data for a group.
	 *
	 * @return bool true on success, otherwise false
	 */
	private function savePublishing()
	{
		$publishing = Helpers\Input::getFormItems()->get('publishing');
		if (empty($publishing))
		{
			return true;
		}

		foreach ($this->selected as $groupID)
		{
			foreach ($publishing as $termID => $publish)
			{
				$table = new Tables\GroupPublishing;
				$data  = ['groupID' => $groupID, 'termID' => $termID];
				$table->load($data);
				$data['published'] = $publish;

				if (empty($table->save($data)))
				{
					return false;
				}
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
		if (!$this->updateDirectAssociation('pools'))
		{
			return false;
		}

		return $this->updateInstanceGroups();
	}

	/**
	 * Updates the instance groups table to reflect the merge of the groups.
	 *
	 * @return bool true on success, otherwise false;
	 */
	private function updateInstanceGroups()
	{
		if (!$relevantAssocs = $this->getAssociatedResourceIDs('assocID', 'instance_groups'))
		{
			return true;
		}

		$mergeID = reset($this->selected);

		foreach ($relevantAssocs as $assocID)
		{
			$delta       = '';
			$modified    = '';
			$existing    = new Tables\InstanceGroups;
			$entryExists = $existing->load(['assocID' => $assocID, 'groupID' => $mergeID]);

			foreach ($this->selected as $groupID)
			{
				$igTable        = new Tables\InstanceGroups;
				$loadConditions = ['assocID' => $assocID, 'groupID' => $groupID];
				if (!$igTable->load($loadConditions))
				{
					continue;
				}

				if ($igTable->modified > $modified)
				{
					$delta    = $igTable->delta;
					$modified = $igTable->modified;
				}

				if ($entryExists)
				{
					if ($existing->id !== $igTable->id)
					{
						$igTable->delete();
					}

					continue;
				}

				$entryExists = true;
				$existing    = $igTable;
			}

			$existing->delta    = $delta;
			$existing->groupID  = $mergeID;
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
			foreach ($persons as $personID => $data)
			{
				if (!$relevantGroups = array_intersect($data['groups'], $this->selected))
				{
					continue;
				}

				$relevant = true;

				// Unset all relevant to avoid conditional and unique handling
				foreach (array_keys($relevantGroups) as $relevantIndex)
				{
					unset($instances[$instanceID][$personID]['groups'][$relevantIndex]);
				}

				// Put the merge id in/back in
				$instances[$instanceID][$personID]['groups'][] = $mergeID;

				// Resequence to avoid JSON encoding treating the array as associative (object)
				$instances[$instanceID][$personID]['groups']
					= array_values($instances[$instanceID][$personID]['groups']);
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
