<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMethod
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/merge.php';

/**
 * Class provides methods for method database abstraction
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelMethod extends THM_OrganizerModelMerge
{
    /**
     * Updates key references to the entry being merged.
     *
     * @param int   $newDBID  the id onto which the room entries merge
     * @param array $oldDBIDs the ids to be replaced
     *
     * @return  boolean  true on success, otherwise false
     */
    protected function updateAssociations($newDBID, $oldDBIDs)
    {
        return $this->updateAssociation('method', $newDBID, $oldDBIDs, 'lessons');
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
    protected function updateSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs)
    {
        foreach ($schedule->lessons as $lessonIndex => $lesson) {
            $update = (in_array($lesson->methodID, $allDBIDs));
            if ($update) {
                $schedule->lessons->$lessonIndex->methodID = $newDBID;
            }
        }
    }
}
