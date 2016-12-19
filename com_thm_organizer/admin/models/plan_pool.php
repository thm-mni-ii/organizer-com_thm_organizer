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
class THM_OrganizerModelPlan_Pool extends THM_OrganizerModelMerge
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
		$drUpdated = $this->updateDRAssociation('pool', $newDBID, $oldDBIDs);
		if (!$drUpdated)
		{
			return false;
		}

		return $this->updateAssociation('pool', $newDBID, $oldDBIDs, 'lesson_pools');
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
		$programUntisID = $this->getDescriptionGPUntisID('plan_programs', $data['programID']);

		foreach ($schedule->pools as $gpuntisID => $pool)
		{
			if (in_array($gpuntisID, $allGPUntisIDs))
			{
				// Whether old or new high probability of having to overwrite an attribute this enables standard handling.
				unset($schedule->pools->$gpuntisID);

				$schedule->pools->$newGPUntisID               = new stdClass;
				$schedule->pools->$newGPUntisID->id           = $newDBID;
				$schedule->pools->$newGPUntisID->name         = $newGPUntisID;
				$schedule->pools->$newGPUntisID->gpuntisID    = $newGPUntisID;
				$schedule->pools->$newGPUntisID->localUntisID = $newGPUntisID;
				$schedule->pools->$newGPUntisID->longname     = $data['full_name'];
				$schedule->pools->$newGPUntisID->restriction  = $data['name'];
				$schedule->pools->$newGPUntisID->degree       = $programUntisID;

				// This will play little role later due to the times in the calendar table
				$schedule->pools->$newGPUntisID->grid = $pool->grid;

				if (!empty($pool->gridID))
				{
					$schedule->pools->$newGPUntisID->gridID = $pool->gridID;
				}
			}
		}

		foreach ($schedule->lessons as $lessonIndex => $lesson)
		{
			foreach ($lesson->pools as $gpuntisID => $delta)
			{
				if (in_array($gpuntisID, $allGPUntisIDs))
				{
					unset($schedule->lessons->$lessonIndex->pools->$gpuntisID);
					$schedule->lessons->$lessonIndex->pools->$newGPUntisID = $delta;
				}
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
		foreach ($schedule->lessons as $lessonIndex => $lesson)
		{
			foreach ($lesson->subjects as $subjectID => $subjectConfig)
			{
				foreach ($subjectConfig->pools as $poolID => $delta)
				{
					if (in_array($poolID, $allDBIDs))
					{
						unset($schedule->lessons->$lessonIndex->subjects->$subjectID->pools->$poolID);
						$schedule->lessons->$lessonIndex->subjects->$subjectID->pools->$newDBID = $delta;
					}
				}
			}
		}
	}
}
