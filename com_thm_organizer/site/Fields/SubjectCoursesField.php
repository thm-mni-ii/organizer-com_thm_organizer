<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Factory;
use Organizer\Helpers\HTML;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a select box for explicitly mapping subject documentation to plan subjects. This is also done implicitly
 * during the schedule import process according to degree programs and the subject's module number.
 */
class SubjectCoursesField extends BaseField
{
    protected $type = 'SubjectCourses';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return string  the HTML output
     */
    public function getInput()
    {
        $fieldName = $this->getAttribute('name');
        $subjectID = OrganizerHelper::getInput()->getInt('id', 0);

        $dbo          = Factory::getDbo();
        $subjectQuery = $dbo->getQuery(true);
        $subjectQuery->select('courseID');
        $subjectQuery->from('#__thm_organizer_subject_mappings');
        $subjectQuery->where("subjectID = '$subjectID'");
        $dbo->setQuery($subjectQuery);
        $selected = OrganizerHelper::executeQuery('loadColumn', []);

        $courseQuery = $dbo->getQuery(true);
        $courseQuery->select('id AS value, name');
        $courseQuery->from('#__thm_organizer_courses');
        $courseQuery->order('name');
        $dbo->setQuery($courseQuery);

        $courses = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($courses)) {
            $courses = [];
        }

        $options = [];
        foreach ($courses as $course) {
            $options[$course['value']] = $course['name'];
        }

        $attributes       = ['multiple' => 'multiple', 'size' => '10'];
        $selectedMappings = empty($selected) ? [] : $selected;

        return HTML::selectBox($options, $fieldName, $attributes, $selectedMappings, true);
    }
}
