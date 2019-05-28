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
 * Class which manages stored group data.
 */
class Group extends MergeModel
{
    protected $fkColumn = 'groupID';

    protected $tableName = 'groups';

    /**
     * Provides resource specific user access checks
     *
     * @return boolean  true if the user may edit the given resource, otherwise false
     */
    protected function allowEdit()
    {
        return Groups::allowEdit($this->selected);
    }

    /**
     * Performs batch processing of groups, specifically their publication per period and their associated grids.
     *
     * @return bool true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function batch()
    {
        $groupIDs = OrganizerHelper::getSelectedIDs();
        if (empty($groupIDs)) {
            return false;
        }

        $groupIDs = ArrayHelper::toInteger($groupIDs);
        if (!Groups::allowEdit($groupIDs)) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        return $this->savePublishing();
    }

    /**
     * Merges group entries and cleans association tables.
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

        return $this->savePublishing();
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
        $this->selected = OrganizerHelper::getSelectedIDs();

        if (empty(parent::save($data))) {
            return false;
        }

        if (empty($this->savePublishing())) {
            return false;
        }

        return reset($this->selected);
    }

    /**
     * Saves the publishing data for a group.
     *
     * @return bool true on success, otherwise false
     */
    private function savePublishing()
    {
        $formData = OrganizerHelper::getFormInput();
        if (empty($formData['publishing'])) {
            return true;
        }

        foreach ($this->selected as $groupID) {
            foreach ($formData['publishing'] as $termID => $publish) {
                $table = OrganizerHelper::getTable('Group_Publishing');
                $data  = ['groupID' => $groupID, 'termID' => $termID];
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
        $lpsUpdated = $this->updateAssociation('lesson_groups');
        if (!$lpsUpdated) {
            return false;
        }

        $mergeID = reset($this->selected);
        $query   = $this->_db->getQuery(true);
        $query->select('*')->from('#__thm_organizer_lesson_groups')->where("groupID = $mergeID");
        $this->_db->setQuery($query);

        $assocs = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($assocs)) {
            return true;
        }

        $uniqueLessonCourses = [];
        $duplicateIDs        = [];

        foreach ($assocs as $assoc) {
            if (!isset($uniqueLessonCourses[$assoc['lessonCourseID']])) {
                $uniqueLessonCourses[$assoc['lessonCourseID']] = ['id' => $assoc['id'], 'delta' => $assoc['delta']];
                continue;
            } // Duplicate
            else {
                // An already iterated duplicate has the removed flag => replace and remove it
                if ($uniqueLessonCourses[$assoc['lessonCourseID']]['delta'] == 'removed') {
                    $duplicateIDs[]                                = $uniqueLessonCourses[$assoc['subjectID']]['id'];
                    $uniqueLessonCourses[$assoc['lessonCourseID']] = ['id' => $assoc['id'], 'delta' => $assoc['delta']];
                } // The other duplicate is sufficient => remove this one
                else {
                    $duplicateIDs[] = $assoc['id'];
                }
            }
        }

        if (count($duplicateIDs)) {
            $idsToDelete = "('" . implode("', '", $duplicateIDs) . "')";
            $query       = $this->_db->getQuery(true);
            $query->delete('#__thm_organizer_lesson_groups')->where("id IN $idsToDelete");
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
        $updateIDs = $this->selected;
        $mergeID   = array_shift($updateIDs);

        $lessons = (array)$schedule->lessons;
        foreach ($lessons as $lessonIndex => $lesson) {
            $courses = (array)$lesson->courses;
            foreach ($courses as $courseID => $courseConfig) {
                $groups = (array)$courseConfig->groups;
                foreach ($groups as $groupID => $delta) {
                    if (in_array($groupID, $updateIDs)) {
                        unset($schedule->lessons->$lessonIndex->courses->$courseID->groups->$groupID);
                        $schedule->lessons->$lessonIndex->courses->$courseID->groups->$mergeID = $delta;
                    }
                }
            }
        }
    }
}