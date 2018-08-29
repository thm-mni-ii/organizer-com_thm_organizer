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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/plan_pools.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/models/merge.php';

/**
 * Class which manages stored plan (subject) pool data.
 */
class THM_OrganizerModelPlan_Pool extends THM_OrganizerModelMerge
{
    /**
     * Performs batch processing of plan_pools, specifically their publication per period and their associated grids.
     *
     * @return void
     */
    public function batch()
    {
        $input    = JFactory::getApplication()->input;
        $pPoolIDs = $input->get('cid', [], 'array');
        if (empty($pPoolIDs)) {
            return false;
        }

        $pPoolIDs = Joomla\Utilities\ArrayHelper::toInteger($pPoolIDs);
        if (!THM_OrganizerHelperPlan_Pools::allowEdit($pPoolIDs)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        foreach ($pPoolIDs as $pPoolID) {
            if (empty($this->savePublishing($pPoolID))){
                return false;
            }
        }

        return true;
    }

    /**
     * Attempts to save a resource entry, updating schedule data as necessary.
     *
     * @return mixed  integer on success, otherwise false
     * @throws Exception
     */
    public function save()
    {
        $formData = JFactory::getApplication()->input->get('jform', [], 'array');
        if (empty($formData['id']) or !is_numeric($formData['id'])) {
            return false;
        }

        $pPoolID  = $formData['id'];
        $pPoolIDs = [$pPoolID];
        if (!THM_OrganizerHelperPlan_Pools::allowEdit($pPoolIDs)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        if (empty(parent::save())) {
            return false;
        }

        if (empty($this->$this->savePublishing($pPoolID))) {
            return false;
        }

        return $pPoolID;
    }

    /**
     * Saves the publishing data for a plan pool.
     *
     * @param int $pPoolID the id of the plan pool
     *
     * @return bool true on success, otherwise false
     * @throws Exception
     */
    private function savePublishing($pPoolID)
    {
        $formData = JFactory::getApplication()->input->get('jform', [], 'array');
        if (!empty($formData['publishing'])) {
            foreach ($formData['publishing'] as $periodID => $publish) {
                $table = JTable::getInstance("plan_pool_publishing", 'thm_organizerTable');
                $data  = ['planPoolID' => $pPoolID, 'planningPeriodID' => $periodID];
                $table->load($data);
                $data['published'] = $publish;

                if (empty($table->save($data))) {
                    return false;
                }
            }
        }

        return true;
    }

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
        $drUpdated = $this->updateDRAssociation('pool', $newDBID, $oldDBIDs);

        if (!$drUpdated) {
            return false;
        }

        $lpUpdated = $this->updateAssociation('pool', $newDBID, $oldDBIDs, 'lesson_pools');

        if (!$lpUpdated) {
            return false;
        }

        return $this->updateAssociation('planPool', $newDBID, $oldDBIDs, 'plan_pool_publishing');
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
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function updateSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs)
    {
        foreach ($schedule->lessons as $lessonIndex => $lesson) {
            foreach ($lesson->subjects as $subjectID => $subjectConfig) {
                foreach ($subjectConfig->pools as $poolID => $delta) {
                    if (in_array($poolID, $allDBIDs)) {
                        unset($schedule->lessons->$lessonIndex->subjects->$subjectID->pools->$poolID);
                        $schedule->lessons->$lessonIndex->subjects->$subjectID->pools->$newDBID = $delta;
                    }
                }
            }
        }
    }
}
