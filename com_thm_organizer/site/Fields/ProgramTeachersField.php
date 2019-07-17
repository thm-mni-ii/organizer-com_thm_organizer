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
use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Mappings;
use Organizer\Helpers\Subjects;

/**
 * Class creates a select box for the association of teachers with subject documentation.
 */
class ProgramTeachersField extends OptionsField
{
    protected $type = 'ProgramTeachers';

    /**
     * Method to get the field input markup for a generic list.
     *
     * @return  string  The field input markup.
     */
    protected function getInput()
    {
        if (empty(Input::getInt('programID'))) {
            return '';
        }

        return parent::getInput();
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $options    = parent::getOptions();
        $programID  = Input::getInt('programID');
        $subjectIDs = Mappings::getProgramSubjects($programID);

        if (empty($subjectIDs)) {
            return $options;
        }

        $aggregatedTeachers = [];
        foreach ($subjectIDs as $subjectID) {
            $subjectTeachers = Subjects::getTeachers($subjectID);
            if (empty($subjectTeachers)) {
                continue;
            }

            $aggregatedTeachers = array_merge($aggregatedTeachers, $subjectTeachers);
        }

        ksort($aggregatedTeachers);

        foreach ($aggregatedTeachers as $name => $teacher) {
            $options[] = HTML::_('select.option', $teacher['id'], $name);
        }

        return $options;
    }
}
