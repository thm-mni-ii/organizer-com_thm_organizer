<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/models/merge.php';

/**
 * Class which manages stored room type data.
 */
class THM_OrganizerModelRoom_Type extends THM_OrganizerModelMerge
{
    /**
     * Updates key references to the entry being merged.
     *
     * @param int   $newDBID  the id onto which the room entries merge
     * @param array $oldDBIDs an array containing the ids to be replaced
     *
     * @return boolean  true on success, otherwise false
     */
    protected function updateAssociations($newDBID, $oldDBIDs)
    {
        return $this->updateAssociation('type', $newDBID, $oldDBIDs, 'rooms');
    }

    /**
     * The room types are a part of the resource data in the new structure.
     *
     * @param object &$schedule     the schedule being processed
     * @param array  &$data         the data for the schedule db entry
     * @param int    $newDBID       the new id to use for the merged resource in the database (and schedules)
     * @param string $newGPUntisID  the new gpuntis ID to use for the merged resource in the schedule
     * @param array  $allGPUntisIDs all gpuntis IDs for the resources to be merged
     * @param array  $allDBIDs      all db IDs for the resources to be merged
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function updateSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs)
    {
        return;
    }
}
