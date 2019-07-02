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

namespace Organizer\Models;

use Organizer\Helpers\Languages;

/**
 * Class retrieves the data regarding a filtered set of courses.
 */
class Courses extends ListModelMenu
{
    /**
     * Filters out form inputs which should not be displayed due to menu settings.
     *
     * @param Form $form the form to be filtered
     *
     * @return void modifies $form
     */
    protected function filterFilterForm(&$form)
    {
        // TODO: Implement filterFilterForm(&$form) method.
    }

    /**
     * Method to get a \JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return \JDatabaseQuery  A \JDatabaseQuery object to retrieve the data set.
     */
    protected function getListQuery()
    {
        $tag = Languages::getTag();

        $courseQuery = $this->_db->getQuery(true);
        $subQuery    = $this->_db->getQuery(true);

        $subQuery->select('lessonID, MIN(schedule_date) as start, MAX(schedule_date) as end')
            ->select('(MAX(schedule_date) < CURRENT_DATE()) as expired')
            ->from('#__thm_organizer_calendar')
            ->where("delta != 'removed'")
            ->group('lessonID');

        $linkParts = ["'index.php?option=com_thm_organizer&view=course_edit&id='", 'lcrs.lessonID'];
        $courseQuery->select('DISTINCT lcrs.id AS id')
            ->select($courseQuery->concatenate($linkParts, '') . ' AS link')
            ->select("co.id as courseID, co.name AS name")
            ->select('l.id AS lessonID, l.campusID AS campusID')
            ->select("m.id AS methodID, m.abbreviation_$tag AS method")
            ->select("d.id AS departmentID, d.short_name_$tag AS department")
            ->select('term.id AS termID, term.name AS term')
            ->select('sq.start, sq.end, sq.expired')
            ->select("s.id as subjectID, s.name_$tag as subject, s.campusID AS abstractCampusID");

        $courseQuery->from('#__thm_organizer_lesson_courses AS lcrs')
            ->innerJoin('#__thm_organizer_courses AS co ON co.id = lcrs.courseID')
            ->innerJoin('#__thm_organizer_subject_mappings AS sm on sm.courseID = co.id')
            ->innerJoin('#__thm_organizer_lessons as l on l.id = lcrs.lessonID')
            ->innerJoin('#__thm_organizer_methods as m on m.id = l.methodID')
            ->innerJoin('#__thm_organizer_departments as d on d.id = l.departmentID')
            ->innerJoin('#__thm_organizer_terms as term on term.id = l.termID')
            ->innerJoin("($subQuery) as sq on sq.lessonID = lcrs.lessonID")
            ->leftJoin('#__thm_organizer_subjects AS s on s.id = sm.subjectID');

        // Prep Course Filter
        if (!empty($this->state->get('filter.onlyPrepCourses'))) {
            $courseQuery->where("s.is_prep_course = 1");
            $courseQuery->where("lcrs.courseID is not null and sq.start is not null");
        }

        // Status filter
//        switch ($this->state->get('list.status')) {
//            case 'pending':
//                $courseQuery->where("sq.expired = '0'");
//                break;
//            case 'expired':
//                $courseQuery->where("sq.expired = '1'");
//                break;
//        }

        // Plan subject filter
//        if (!empty($this->state->subjectID)) {
//            $courseQuery->where("s.id = '{$this->state->subjectID}'");
//        }

        // Campus Filter
//        if (!empty($this->state->campusID)) {
//            $campusID = $this->state->campusID;
//            $courseQuery->leftJoin('#__thm_organizer_campuses as lc on l.campusID = lc.id');
//            $courseQuery->leftJoin('#__thm_organizer_campuses as sc on s.campusID = sc.id');
//
//            // lesson has a specific campus id
//            $conditions = "(lc.id = '$campusID' OR  lc.parentID = '$campusID' OR ";
//
//            // lesson has no specific campus id, but subject does
//            $conditions .= "(l.campusID IS NULL AND (sc.id = '$campusID' OR  sc.parentID = '$campusID')))";
//
//            $courseQuery->where($conditions);
//        }

        return $courseQuery;
    }

    /**
     * Overrides state properties with menu settings values.
     *
     * @return void sets state properties
     */
    protected function populateStateFromMenu()
    {
        // TODO: Implement populateStateFromMenu() method.
    }
}
