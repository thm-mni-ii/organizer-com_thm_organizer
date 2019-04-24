<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/campuses.php';

use THM_OrganizerHelperLanguages as Languages;

/**
 * Class retrieves the data regarding a filtered set of courses.
 */
class THM_OrganizerModelCourse_List extends \Joomla\CMS\MVC\Model\ListModel
{
    /**
     * Method to get an array of data items.
     *
     * @return mixed  An array of data items on success, false on failure.
     */
    public function getItems()
    {
        $courses = parent::getItems();

        $maxValues = [];

        foreach ($courses as $index => &$course) {

            $campusID = empty($course->campusID) ? $course->abstractCampusID : $course->campusID;

            if ($this->state->get('status') == 'current') {

                if (isset($maxValues[$course->subjectID]) and isset($maxValues[$course->subjectID][$campusID])) {
                    if ($maxValues[$course->subjectID][$campusID]['start'] > $course->start) {
                        unset($courses[$index]);
                        continue;
                    } else {
                        $oldIndex = $maxValues[$course->subjectID][$campusID]['index'];
                        unset($courses[$oldIndex]);
                    }
                }
            }

            $course->campus = THM_OrganizerHelperCourses::getCampus($course);

            $maxValues[$course->subjectID][$campusID] = ['start' => $course->start, 'index' => $index];
        }

        return $courses;
    }

    /**
     * Method to get a \JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return \JDatabaseQuery  A \JDatabaseQuery object to retrieve the data set.
     */
    protected function getListQuery()
    {
        $tag = Languages::getShortTag();

        $courseQuery = $this->_db->getQuery(true);

        $subQuery = $this->_db->getQuery(true);

        $subQuery->select('lessonID, MIN(schedule_date) as start, MAX(schedule_date) as end')
            ->select('(MAX(schedule_date) < CURRENT_DATE()) as expired')
            ->from('#__thm_organizer_calendar')
            ->where("delta != 'removed'")
            ->group('lessonID');

        $courseQuery->select("s.id as subjectID, ls.lessonID, s.name_$tag as name, sq.start, sq.end, sq.expired");
        $courseQuery->select('l.campusID AS campusID, s.campusID AS abstractCampusID');
        $courseQuery->from('#__thm_organizer_subjects as s');
        $courseQuery->innerJoin('#__thm_organizer_subject_mappings as sm on sm.subjectID = s.id');
        $courseQuery->innerJoin('#__thm_organizer_lesson_subjects as ls on ls.subjectID = sm.plan_subjectID');
        $courseQuery->innerJoin('#__thm_organizer_lessons as l on ls.lessonID = l.id');
        $courseQuery->innerJoin("($subQuery) as sq on sq.lessonID = ls.lessonID");
        $courseQuery->where("is_prep_course = '1' and ls.subjectID is not null and sq.start is not null");
        $courseQuery->order('end DESC, name ASC');

        switch ($this->state->status) {
            case 'pending':
                $courseQuery->where("sq.expired = '0'");
                break;
            case 'expired':
                $courseQuery->where("sq.expired = '1'");
                break;
        }

        if (!empty($this->state->subjectID)) {
            $courseQuery->where("s.id = '{$this->state->subjectID}'");
        }

        if (!empty($this->state->campusID)) {
            $campusID = $this->state->campusID;
            $courseQuery->leftJoin('#__thm_organizer_campuses as lc on l.campusID = lc.id');
            $courseQuery->leftJoin('#__thm_organizer_campuses as sc on s.campusID = sc.id');

            // lesson has a specific campus id
            $conditions = "(lc.id = '$campusID' OR  lc.parentID = '$campusID' OR ";

            // lesson has no specific campus id, but subject does
            $conditions .= "(l.campusID IS NULL AND (sc.id = '$campusID' OR  sc.parentID = '$campusID')))";

            $courseQuery->where($conditions);
        }

        return $courseQuery;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param string $ordering  An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $formData = THM_OrganizerHelperComponent::getInput()->get('jform', [], 'array');

        if (empty($formData)) {
            $params    = THM_OrganizerHelperComponent::getParams();
            $campusID  = $params->get('campusID', 0);
            $status    = 'current';
            $subjectID = 0;
        } else {
            $campusID  = $formData['campusID'];
            $status    = empty($formData['status']) ? 'current' : $formData['status'];
            $subjectID = empty($formData['subjectID']) ? 0 : $formData['subjectID'];
        }

        $this->setState('campusID', $campusID);
        $this->setState('status', $status);
        $this->setState('subjectID', $subjectID);
    }
}
