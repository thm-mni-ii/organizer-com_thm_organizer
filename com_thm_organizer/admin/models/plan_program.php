<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelRoom
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/merge.php';

/**
 * Class provides methods for room database abstraction
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPlan_Program extends THM_OrganizerModelMerge
{
	/**
	 * Removes the resource from the schedule
	 *
	 * @param object &$schedule  the schedule from which the resource will be removed
	 * @param int    $resourceID the id of the resource in the db
	 * @param string $gpuntisID  the gpuntis ID for the given resource
	 *
	 * @return  void  modifies the schedule
	 */
	protected function removeFromSchedule(&$schedule, $resourceID, $gpuntisID)
	{
		return;
	}

	/**
	 * Updates key references to the entry being merged.
	 *
	 * @param int   $newDBID  the id onto which the room entries merge
	 * @param array $oldDBIDs an array containing the ids to be replaced
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	protected function updateAssociations($newDBID, $oldDBIDs)
	{
		$drUpdated = $this->updateDRAssociation('program', $newDBID, $oldDBIDs);
		if (!$drUpdated)
		{
			return false;
		}

		return $this->updateAssociation('program', $newDBID, $oldDBIDs, 'plan_pools');
	}

	/**
	 * Processes the data for an individual schedule
	 *
	 * @param object &$schedule     the schedule being processed
	 * @param array  &$data         the data for the schedule db entry
	 * @param int    $newDBID       the new id to use for the merged resource in the database (and schedules)
	 * @param string $newGPUntisID  the new gpuntis ID to use for the merged resource in the schedule
	 * @param array  $allGPUntisIDs all gpuntis IDs for the resources to be merged
	 * @param array  $allDBIDs      all db IDs for the resources to be merged
	 *
	 * @return  void
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function updateOldSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs)
	{
		foreach ($schedule->degrees as $gpuntisID => $program)
		{
			if (in_array($gpuntisID, $allGPUntisIDs))
			{
				// Whether old or new high probability of having to overwrite an attribute this enables standard handling.
				unset($schedule->degrees->$gpuntisID);

				$schedule->degrees->$newGPUntisID              = new stdClass;
				$schedule->degrees->$newGPUntisID->id          = $newDBID;
				$schedule->degrees->$newGPUntisID->gpuntisID   = $newGPUntisID;
				$schedule->degrees->$newGPUntisID->name        = $data['name'];
			}
		}

		foreach ($schedule->pools as $gpuntisID => $pool)
		{
			if (in_array($pool->degree, $allGPUntisIDs))
			{
				$schedule->pools->$gpuntisID->degree = $newGPUntisID;
			}
		}
	}

	/**
	 * Degree programs are not in the new
	 *
	 * @param object &$schedule     the schedule being processed
	 * @param array  &$data         the data for the schedule db entry
	 * @param int    $newDBID       the new id to use for the merged resource in the database (and schedules)
	 * @param string $newGPUntisID  the new gpuntis ID to use for the merged resource in the schedule
	 * @param array  $allGPUntisIDs all gpuntis IDs for the resources to be merged
	 * @param array  $allDBIDs      all db IDs for the resources to be merged
	 *
	 * @return  void
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function updateSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs)
	{
		return;
	}
}
