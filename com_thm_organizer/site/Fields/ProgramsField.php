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
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Tables\Participants;

/**
 * Class creates a select box for (degree) programs.
 */
class ProgramsField extends OptionsField
{
    use DepartmentFilters;

    /**
     * @var  string
     */
    protected $type = 'Programs';

    /**
     * Returns a select box where stored degree programs can be chosen
     *
     * @return array  the available degree programs
     */
    protected function getOptions()
    {
        $shortTag = Languages::getShortTag();
        $dbo      = Factory::getDbo();
        $query    = $dbo->getQuery(true);

        $query->select("dp.id AS value, dp.name_$shortTag AS name, d.abbreviation AS degree, dp.version");
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON dp.id = m.programID');
        $query->order('name ASC, degree ASC, version DESC');

        $access = $this->getAttribute('access');
        if (!empty($access)) {
            $this->addDeptAccessFilter($query, 'dp', $access);
        }
        $this->addDeptSelectionFilter($query, 'dp');

        $useCurrent = $this->useCurrent();
        if ($useCurrent) {
            $subQuery = $dbo->getQuery(true);
            $subQuery->select("dp2.name_$shortTag, dp2.degreeID, MAX(dp2.version) AS version")
                ->from('#__thm_organizer_programs AS dp2')
                ->group("dp2.name_$shortTag, dp2.degreeID");
            $conditions = "grouped.name_$shortTag = dp.name_$shortTag ";
            $conditions .= "AND grouped.degreeID = dp.degreeID ";
            $conditions .= "AND grouped.version = dp.version ";
            $query->innerJoin("($subQuery) AS grouped ON $conditions");
        }

        $dbo->setQuery($query);

        $options  = parent::getOptions();
        $programs = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($programs)) {
            return $options;
        }

        foreach ($programs as $program) {
            $text = "{$program['name']} ({$program['degree']},  {$program['version']})";

            $options[] = HTML::_('select.option', $program['value'], $text);
        }

        return $options;
    }

    /**
     * Determines whether only the latest version of a program should be displayed in the list.
     *
     * @return bool
     */
    private function useCurrent()
    {
        $useCurrent = false;
        $view       = OrganizerHelper::getInput()->getCmd('view');
        $selected   = OrganizerHelper::getSelectedIDs();
        if ($view === 'participant_edit') {
            $participantID = empty($selected) ? Factory::getUser() : $selected[0];
            $table         = new Participants;
            $exists        = $table->load($participantID);

            if (!$exists) {
                $useCurrent = true;
            }
        }

        return $useCurrent;
    }
}
