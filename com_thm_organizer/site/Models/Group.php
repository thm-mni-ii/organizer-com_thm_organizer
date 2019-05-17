<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Exception;
use Joomla\Utilities\ArrayHelper;
use Organizer\Helpers\Groups;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which manages stored plan (subject) pool data.
 */
class Group extends MergeModel
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

        return Groups::allowEdit($allIDs);
    }

    /**
     * Performs batch processing of groups, specifically their publication per period and their associated grids.
     *
     * @return bool true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function batch()
    {
        $pPoolIDs = OrganizerHelper::getSelectedIDs();
        if (empty($pPoolIDs)) {
            return false;
        }

        $pPoolIDs = ArrayHelper::toInteger($pPoolIDs);
        if (!Groups::allowEdit($pPoolIDs)) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
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

        $formData = OrganizerHelper::getForm();

        return $this->savePublishing($formData['id']);
    }

    /**
     * Attempts to save the resource.
     *
     * @param array $data form data which has been preprocessed by inheriting classes.
     *
     * @return bool true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function save($data = [])
    {
        if (empty(parent::save($data))) {
            return false;
        }

        $data = empty($data) ? OrganizerHelper::getForm() : $data;

        if (empty($this->savePublishing($data['id']))) {
            return false;
        }

        return $data['id'];
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
        $formData = OrganizerHelper::getForm();
        if (!empty($formData['publishing'])) {
            foreach ($formData['publishing'] as $periodID => $publish) {
                $table = OrganizerHelper::getTable('Group_Publishing');
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
