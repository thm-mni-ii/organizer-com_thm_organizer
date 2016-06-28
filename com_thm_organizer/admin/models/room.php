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
require_once JPATH_ROOT . '/media/com_thm_organizer/models/merge.php';

/**
 * Class provides methods for room database abstraction
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelRoom extends THM_OrganizerModelMerge
{
    /**
     * Removes the resource from the schedule
     *
     * @param   object  &$schedule   the schedule from which the resource will be removed
     * @param   int     $resourceID  the id of the resource in the db
     * @param   string  $gpuntisID   the gpuntis ID for the given resource
     *
     * @return  void  modifies the schedule
     */
    protected function removeFromSchedule(&$schedule, $resourceID, $gpuntisID)
    {
        // Room not used in schedule
        if (empty($schedule->rooms->$gpuntisID))
        {
            return;
        }

        unset($schedule->rooms->$gpuntisID);

        foreach ($schedule->calendar as $date => $blocks)
        {
            $this->iterateDateReferences($schedule, $date, $blocks, array($gpuntisID));
        }
    }

    /**
     * Updates key references to the entry being merged.
     *
     * @param   int    $newDBID   the id onto which the room entries merge
     * @param   array  $oldDBIDs  an array containing the ids to be replaced
     *
     * @return  boolean  true on success, otherwise false
     */
    protected function updateAssociations($newDBID, $oldDBIDs)
    {
        $drUpdated = $this->updateAssociation('room', $newDBID, $oldDBIDs, 'department_resources');
        if (!$drUpdated)
        {
            return false;
        }

        $monitorsUpdated = $this->updateAssociation('room', $newDBID, $oldDBIDs, 'monitors');
        if (!$monitorsUpdated)
        {
            return false;
        }

        $prUpdated = $this->updateAssociation('room', $newDBID, $oldDBIDs, 'plan_rooms');
        if (!$prUpdated)
        {
            return false;
        }

        return $this->updateAssociation('room', $newDBID, $oldDBIDs, 'room_features_map');
    }

    /**
     * Processes the data for an individual schedule
     * 
     * @param   object  &$schedule      the schedule being processed
     * @param   array   &$data          the data for the schedule db entry
     * @param   int     $newDBID        the new id to use for the merged resource in the database (and schedules)
     * @param   string  $newGPUntisID   the new gpuntis ID to use for the merged resource in the schedule
     * @param   array   $allGPUntisIDs  all gpuntis IDs for the resources to be merged
     * @param   array   $allDBIDs       all db IDs for the resources to be merged
     * 
     * @return  void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function updateSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs)
    {
        if (!empty($data['typeID']))
        {
            $typeDBID = $data['typeID'];
            $typeGPUntisID = $this->getDescriptionGPUntisID('room_types', $data['typeID']);
        }
        else
        {
            $typeDBID = $typeGPUntisID = '';
        }

        foreach ($schedule->rooms as $gpuntisID => $room)
        {
            if (in_array($gpuntisID, $allGPUntisIDs))
            {
                // Whether old or new high probability of having to overwrite an attribute this enables standard handling.
                unset($schedule->rooms->$gpuntisID);

                $schedule->rooms->$newGPUntisID = new stdClass;
                $schedule->rooms->$newGPUntisID->id = $newDBID;
                $schedule->rooms->$newGPUntisID->gpuntisID = $newGPUntisID;
                $schedule->rooms->$newGPUntisID->name = $data['name'];
                $schedule->rooms->$newGPUntisID->longname = $data['longname'];
                $schedule->rooms->$newGPUntisID->typeID = $typeDBID;
                $schedule->rooms->$newGPUntisID->description = $typeGPUntisID;
                $schedule->rooms->$newGPUntisID->capacity = $data['capacity'];
            }
        }

        foreach ($schedule->calendar as $date => $blocks)
        {
            $this->iterateDateReferences($schedule, $date, $blocks, $allGPUntisIDs, $newGPUntisID);
        }
    }

    /**
     * Processes the references for a single date
     * 
     * @param   object  &$schedule      the schedule being processed
     * @param   string  $date           the date being currently iterated
     * @param   object  $blocks         the block being currently iterated
     * @param   array   $allGPUntisIDs  all gpuntis IDs for the resources to be merged
     * @param   string  $gpuntisID      the gpuntis ID to use for the resource in the schedule, empty during deletion
     * 
     * @return  void
     */
    private function iterateDateReferences(&$schedule, $date, $blocks, $allGPUntisIDs, $gpuntisID = null)
    {
        if (is_object($blocks))
        {
            foreach ($blocks as $block => $lessons)
            {
                $lessonIDs = array_keys((array) $lessons);
                foreach ($lessonIDs as $lessonID)
                {
                    $this->updateRoomReference($schedule, $date, $block, $lessonID, $allGPUntisIDs, $gpuntisID);
                }
            }
        }
    }

    /**
     * Updates lesson references to rooms. If gpuntisID is empty the reference will be deleted.
     * 
     * @param   object  &$schedule      the schedule being processed
     * @param   string  $date           the date being currently iterated
     * @param   int     $block          the block being currently iterated
     * @param   int     $lessonID       the id of the lesson being currently iterated
     * @param   array   $allGPUntisIDs  all gpuntis IDs for the resources to be merged
     * @param   string  $gpuntisID      the gpuntis ID to use for the resource in the schedule, empty during deletion
     * 
     * @return  void
     */
    private function updateRoomReference(&$schedule, $date, $block, $lessonID, $allGPUntisIDs, $gpuntisID = null)
    {
        foreach ($schedule->calendar->$date->$block->$lessonID as $roomID => $delta)
        {
            if ($roomID == 'delta')
            {
                continue;
            }
            if (in_array($roomID, $allGPUntisIDs))
            {
                unset($schedule->calendar->$date->$block->$lessonID->$roomID);
            }
            if (!empty($gpuntisID))
            {
                $schedule->calendar->$date->$block->$lessonID->$gpuntisID = $delta;
            }
        }
    }
}
