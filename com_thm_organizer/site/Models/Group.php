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

use Exception;
use Organizer\Helpers\Groups;
use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Terms;

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
        $this->selected = Input::getSelectedIDs();
        if (empty($this->selected)) {
            return false;
        }

        if (!Groups::allowEdit($this->selected)) {
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
     * Sets all expired group / term associations to published.
     *
     * @return bool true on success, otherwise false.
     */
    public function publishPast()
    {
        $terms = Terms::getResources();
        $today = date('Y-m-d');

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_group_publishing')->set('published = 1');

        foreach ($terms as $term) {
            if ($term['endDate'] >= $today) {
                continue;
            }

            $query->clear('where');
            $query->where("termID = {$term['id']}");

            $this->_db->setQuery($query);
            $success = OrganizerHelper::executeQuery('execute');
            if (!$success) {
                return false;
            }
        }

        return true;
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
        $this->selected = Input::getSelectedIDs();

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
        $publishing = Input::getFormItems()->get('publishing');
        if (empty($publishing)) {
            return true;
        }

        foreach ($this->selected as $groupID) {
            foreach ($publishing as $termID => $publish) {
                $table = OrganizerHelper::getTable('GroupPublishing');
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
