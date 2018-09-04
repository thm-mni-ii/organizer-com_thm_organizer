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
    protected $fkColumn = 'poolID';

    protected $tableName = 'plan_pools';

    /**
     * Provides resource specific user access checks
     *
     * @return boolean  true if the user may edit the given resource, otherwise false
     */
    protected function allowEdit()
    {
        $allIDs = [$this->data['id']];
        if (!empty($this->data['otherIDs'])) {
            $allIDs = $allIDs + $this->data['otherIDs'];
        }

        return THM_OrganizerHelperPlan_Pools::allowEdit($allIDs);
    }


    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string $name    The table name. Optional.
     * @param   string $prefix  The class prefix. Optional.
     * @param   array  $options Configuration array for model. Optional.
     *
     * @return  \JTable  A \JTable object
     *
     * @throws  \Exception
     */
    public function getTable($name = 'plan_pools', $prefix = 'thm_organizerTable', $options = [])
    {
        return JTable::getInstance($name, $prefix);
    }

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
            if (empty($this->savePublishing($pPoolID))) {
                return false;
            }
        }

        return true;
    }


    /**
     * Merges plan pool entries and cleans association tables.
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception
     */
    public function merge()
    {
        $success = parent::merge();
        if (!$success) {
            return false;
        }

        $formData = JFactory::getApplication()->input->get('jform', [], 'array');

        return $this->savePublishing($formData['id']);
    }

    /**
     * Attempts to save a resource entry, updating schedule data as necessary.
     *
     * @return mixed  integer on success, otherwise false
     * @throws Exception
     */
    public function save()
    {
        if (empty(parent::save())) {
            return false;
        }

        $formData = JFactory::getApplication()->input->get('jform', [], 'array');

        if (empty($this->savePublishing($formData['id']))) {
            return false;
        }

        return $formData['id'];
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
     * @return boolean  true on success, otherwise false
     */
    protected function updateAssociations()
    {
        return $this->updateAssociation('lesson_pools');
    }

    /**
     * Processes the data for an individual schedule
     *
     * @param object &$schedule the schedule being processed
     *
     * @return void
     */
    protected function updateSchedule(&$schedule)
    {
        $lessons = (array)$schedule->lessons;
        foreach ($lessons as $lessonIndex => $lesson) {
            $subjects = (array)$lesson->subjects;
            foreach ($subjects as $subjectID => $subjectConfig) {
                $pools = (array)$subjectConfig->pools;
                foreach ($pools as $poolID => $delta) {
                    if (in_array($poolID, $this->data['otherIDs'])) {
                        unset($schedule->lessons->$lessonIndex->subjects->$subjectID->pools->$poolID);
                        $schedule->lessons->$lessonIndex->subjects->$subjectID->pools->{$this->data['id']} = $delta;
                    }
                }
            }
        }
    }
}
