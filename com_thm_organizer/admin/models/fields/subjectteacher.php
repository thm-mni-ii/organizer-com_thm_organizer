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

use \THM_OrganizerHelperHTML as HTML;

/**
 * Class creates a select box for the association of teachers with subject documentation.
 */
class JFormFieldSubjectTeacher extends JFormField
{
    protected $type = 'subjectTeacher';

    /**
     * Returns a select box where stored teachers can be associated with a subject
     *
     * @return string  the HTML output
     */
    public function getInput()
    {
        $fieldName      = $this->getAttribute('name');
        $subjectID      = THM_OrganizerHelperComponent::getInput()->getInt('id', 0);
        $responsibility = $this->getAttribute('responsibility');

        $dbo           = JFactory::getDbo();
        $selectedQuery = $dbo->getQuery(true);
        $selectedQuery->select('teacherID');
        $selectedQuery->from('#__thm_organizer_subject_teachers');
        $selectedQuery->where("subjectID = '$subjectID' AND teacherResp = '$responsibility'");
        $dbo->setQuery($selectedQuery);
        $selected = THM_OrganizerHelperComponent::executeQuery('loadColumn', []);

        $teachersQuery = $dbo->getQuery(true);
        $teachersQuery->select('id AS value, surname, forename');
        $teachersQuery->from('#__thm_organizer_teachers');
        $teachersQuery->order('surname, forename');
        $dbo->setQuery($teachersQuery);

        $teachers = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
        if (empty($teachers)) {
            $teachers = [];
        }

        $options = [];
        foreach ($teachers as $key => $teacher) {
            $name = empty($teacher['forename']) ? $teacher['surname'] : "{$teacher['surname']}, {$teacher['forename']}";
            $options[$teacher['value']] = $name;
        }

        $attributes       = ['multiple' => 'multiple', 'class' => 'inputbox', 'size' => '10'];
        $selectedTeachers = empty($selected) ? [] : $selected;

        return HTML::selectBox($options, $fieldName, $attributes, $selectedTeachers, true);
    }
}
