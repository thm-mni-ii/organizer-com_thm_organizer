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

require_once 'merge.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/plan_pools.php';

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
        $allIDs = [];
        if (!empty($this->data['id'])) {
            $allIDs = $allIDs + [$this->data['id']];
        }
        if (!empty($this->data['otherIDs'])) {
            $allIDs = $allIDs + $this->data['otherIDs'];
        }

        return THM_OrganizerHelperPlan_Pools::allowEdit($allIDs);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param string $name    The table name. Optional.
     * @param string $prefix  The class prefix. Optional.
     * @param array  $options Configuration array for model. Optional.
     *
     * @return  \JTable  A \JTable object
     */
    public function getTable($name = 'plan_pools', $prefix = 'thm_organizerTable', $options = [])
    {
        return \JTable::getInstance($name, $prefix, $options);
    }

    /**
     * Performs batch processing of plan_pools, specifically their publication per period and their associated grids.
     *
     * @return bool true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function batch()
    {
        $input    = OrganizerHelper::getInput();
        $pPoolIDs = $input->get('cid', [], 'array');
        if (empty($pPoolIDs)) {
            return false;
        }

        $pPoolIDs = Joomla\Utilities\ArrayHelper::toInteger($pPoolIDs);
        if (!THM_OrganizerHelperPlan_Pools::allowEdit($pPoolIDs)) {
            throw new \Exception(Languages::_('THM_ORGANIZER_403'), 403);
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
     * @throws Exception => unauthorized access
     */
    public function merge()
    {
        $success = parent::merge();
        if (!$success) {
            return false;
        }

        $formData = OrganizerHelper::getInput()->get('jform', [], 'array');

        return $this->savePublishing($formData['id']);
    }

    /**
     * Attempts to save a resource entry, updating schedule data as necessary.
     *
     * @return mixed  integer on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function save()
    {
        if (empty(parent::save())) {
            return false;
        }

        $formData = OrganizerHelper::getInput()->get('jform', [], 'array');

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
     */
    private function savePublishing($pPoolID)
    {
        $formData = OrganizerHelper::getInput()->get('jform', [], 'array');
        if (!empty($formData['publishing'])) {
            foreach ($formData['publishing'] as $periodID => $publish) {
                $table = \JTable::getInstance('plan_pool_publishing', 'thm_organizerTable');
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
        $lpsUpdated = $this->updateAssociation('lesson_pools');
        if (!$lpsUpdated) {
            return false;
        }

        $query = $this->_db->getQuery(true);
        $query->select('*')->from('#__thm_organizer_lesson_pools')->where("poolID = {$this->data['id']}");
        $this->_db->setQuery($query);

        $assocs = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($assocs)) {
            return true;
        }

        $uniqueLessonSubjects = [];
        $duplicateIDs         = [];

        foreach ($assocs as $assoc) {
            if (!isset($uniqueLessonSubjects[$assoc['subjectID']])) {
                $uniqueLessonSubjects[$assoc['subjectID']] = ['id' => $assoc['id'], 'delta' => $assoc['delta']];
                continue;
            } // Duplicate
            else {
                // An already iterated duplicate has the removed flag => replace and remove it
                if ($uniqueLessonSubjects[$assoc['subjectID']]['delta'] == 'removed') {
                    $duplicateIDs[]                            = $uniqueLessonSubjects[$assoc['subjectID']]['id'];
                    $uniqueLessonSubjects[$assoc['subjectID']] = ['id' => $assoc['id'], 'delta' => $assoc['delta']];
                } // The other duplicate is sufficient => remove this one
                else {
                    $duplicateIDs[] = $assoc['id'];
                }
            }
        }

        if (count($duplicateIDs)) {
            $idsToDelete = "('" . implode("', '", $duplicateIDs) . "')";
            $query       = $this->_db->getQuery(true);
            $query->delete('#__thm_organizer_lesson_pools')->where("id IN $idsToDelete");
            $this->_db->setQuery($query);
            $success = (bool)OrganizerHelper::executeQuery('execute');
            if (!$success) {
                return false;
            }
        }

        return true;
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
