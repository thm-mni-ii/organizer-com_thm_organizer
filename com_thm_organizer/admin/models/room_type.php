<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelRoom_Type
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/merge.php';

/**
 * Class THM_OrganizerModelRoom_Type for component com_thm_organizer
 * Class provides methods to deal with room type
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelRoom_Type extends THM_OrganizerModelMerge
{
	/**
	 * Removes the resource from the schedule
	 *
	 * @param   object &$schedule  the schedule from which the resource will be removed
	 * @param   int    $resourceID the id of the resource in the db
	 * @param   string $gpuntisID  the gpuntis ID for the given resource
	 *
	 * @return  void  modifies the schedule
	 */
	protected function removeFromSchedule(&$schedule, $resourceID, $gpuntisID)
	{
		foreach ($schedule->rooms AS $roomID => $room)
		{
			$descriptionRelevant = (isset($room->description) AND $room->description == $gpuntisID);
			$idRelevant          = (isset($room->typeID) AND $room->typeID == $resourceID);
			if ($descriptionRelevant OR $idRelevant)
			{
				if (isset($schedule->rooms->$roomID->description))
				{
					unset($schedule->rooms->$roomID->description);
				}
				if (isset($schedule->rooms->$roomID->typeID))
				{
					unset($schedule->rooms->$roomID->typeID);
				}
			}
		}
	}

	/**
	 * Updates key references to the entry being merged.
	 *
	 * @param   int   $newDBID  the id onto which the room entries merge
	 * @param   array $oldDBIDs an array containing the ids to be replaced
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	protected function updateAssociations($newDBID, $oldDBIDs)
	{
		$updated = $this->updateAssociation('type', $newDBID, $oldDBIDs, 'plan_rooms');
		if (!$updated)
		{
			return false;
		}

		return $this->updateAssociation('type', $newDBID, $oldDBIDs, 'rooms');
	}

	/**
	 * Processes the data for an individual schedule
	 *
	 * @param   object &$schedule     the schedule being processed
	 * @param   array  &$data         the data for the schedule db entry
	 * @param   int    $newDBID       the new id to use for the merged resource in the database (and schedules)
	 * @param   string $newGPUntisID  the new gpuntis ID to use for the merged resource in the schedule
	 * @param   array  $allGPUntisIDs all gpuntis IDs for the resources to be merged
	 * @param   array  $allDBIDs      all db IDs for the resources to be merged
	 *
	 * @return  void
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function updateSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs)
	{
		foreach ($schedule->rooms AS $roomGPUntisID => $room)
		{
			$update = (in_array($room->description, $allGPUntisIDs) OR in_array($room->typeID, $allDBIDs));
			if ($update)
			{
				$schedule->rooms->$roomGPUntisID->description = $newGPUntisID;
				$schedule->rooms->$roomGPUntisID->typeID      = $newDBID;
			}
		}
	}
}
