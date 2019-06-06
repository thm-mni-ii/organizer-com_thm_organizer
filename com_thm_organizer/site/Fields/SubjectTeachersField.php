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
use Organizer\Helpers\Subjects;

/**
 * Class creates a select box for the association of teachers with subject documentation.
 */
class SubjectTeachersField extends ListField
{
    protected $type = 'SubjectTeachers';

    /**
     * Returns a select box which contains the colors
     *
     * @return string  the HTML for the color select box
     */
    public function getInput()
    {
        $html    = '<select name="' . $this->name . '" multiple>';
        $options = $this->getOptions();
        if (empty($this->value)) {
            $this->value = [0 => ''];
        }
        foreach ($options as $option) {
            $selected = in_array($option->value, $this->value) ? ' selected="selected"' : '';
            $html     .= '<option value="' . $option->value . '"' . $selected . '>' . $option->text . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $subjectIDs     = OrganizerHelper::getSelectedIDs();
        $responsibility = $this->getAttribute('responsibility');
        $invalid        = (empty($subjectIDs) or empty($subjectIDs[0]) or empty($responsibility));

        if ($invalid) {
            return [];
        }

        $existingTeachers = Subjects::getTeachers($subjectIDs[0], $responsibility);
        $this->value      = [];
        foreach ($existingTeachers as $teacher) {
            $this->value[$teacher['id']] = $teacher['id'];
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('t.id, t.surname, t.forename')
            ->from('#__thm_organizer_teachers AS t')
            ->order('surname, forename');

        $departmentID = $this->form->getValue('departmentID');
        if (!empty($departmentID)) {
            if (empty($this->value)) {
                $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.teacherID = t.id');
                $query->where("departmentID = $departmentID");
            } else {
                $query->leftJoin('#__thm_organizer_department_resources AS dr ON dr.teacherID = t.id');
                $teacherIDs  = implode(',', $this->value);
                $extTeachers = "(departmentID != $departmentID AND teacherID IN ($teacherIDs))";
                $query->where("(departmentID = $departmentID OR $extTeachers)");
            }
        }

        $dbo->setQuery($query);
        $teachers = OrganizerHelper::executeQuery('loadAssocList', null, 'id');

        $options = parent::getOptions();
        if (empty($teachers)) {
            return $options;
        }

        foreach ($teachers as $teacher) {
            $text      = empty($teacher['forename']) ?
                $teacher['surname'] : "{$teacher['surname']}, {$teacher['forename']}";
            $options[] = HTML::_('select.option', $teacher['id'], $text);
        }

        return $options;
    }
}
