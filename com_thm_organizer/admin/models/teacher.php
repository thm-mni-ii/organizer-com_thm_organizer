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
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/merge.php';

/**
 * Class which manages stored teacher data.
 */
class THM_OrganizerModelTeacher extends THM_OrganizerModelMerge
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
        $drUpdated = $this->updateDRAssociation('teacher', $newDBID, $oldDBIDs);
        if (!$drUpdated) {
            return false;
        }

        $ltUpdated = $this->updateAssociation('teacher', $newDBID, $oldDBIDs, 'lesson_teachers');
        if (!$ltUpdated) {
            return false;
        }

        return $this->updateAssociation('teacher', $newDBID, $oldDBIDs, 'subject_teachers');
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
     * @return void
     */
    protected function updateSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs)
    {
        foreach ($schedule->lessons as $lessonIndex => $lesson) {
            foreach ($lesson->subjects as $subjectID => $subjectConfig) {
                foreach ($subjectConfig->teachers as $teacherID => $delta) {
                    if (in_array($teacherID, $allDBIDs)) {
                        unset($schedule->lessons->$lessonIndex->subjects->$subjectID->teachers->$teacherID);
                        $schedule->lessons->$lessonIndex->subjects->$subjectID->teachers->$newDBID = $delta;
                    }
                }
            }
        }

        foreach ($schedule->configurations as $index => $configuration) {
            $inConfig      = false;
            $configuration = json_decode($configuration);

            foreach ($configuration->teachers as $teacherID => $delta) {
                if (in_array($teacherID, $allDBIDs)) {
                    // Whether old or new high probability of having to overwrite an attribute this enables standard handling.
                    unset($configuration->teachers->$teacherID);
                    $inConfig                          = true;
                    $configuration->teachers->$newDBID = $delta;
                }
            }

            if ($inConfig) {
                $schedule->configurations[$index] = json_encode($configuration);
            }
        }
    }
}
