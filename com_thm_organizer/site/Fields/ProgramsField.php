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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Tables\Participants;

/**
 * Class creates a select box for (degree) programs.
 */
class ProgramsField extends ListField
{
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
        $dbo->setQuery($query);

        $options  = parent::getOptions();
        $programs = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($programs)) {
            return $options;
        }

        $onlyLatest = false;
        $view = OrganizerHelper::getInput()->getCmd('view');
        $selected = OrganizerHelper::getSelectedIDs();
        if ($view === 'participant_edit') {
            $participantID = empty($selected) ? Factory::getUser() : $selected[0];
            $table = new Participants;
            $exists = $table->load($participantID);

            if (!$exists) {
                $onlyLatest = true;
            }
        }

        // Whether or not the program display should be prefiltered according to user resource access
        $authRequired = $this->getAttribute('auth', '0') === '0' ? false : true;
        $uniqueNames = [];

        foreach ($programs as $program) {
            if ($authRequired and !Access::allowDocumentAccess('program', $program['value'])) {
                continue;
            }

            $index = "{$program['name']}, {$program['degree']}";
            if ($onlyLatest) {
                if (in_array($index, $uniqueNames)) {
                    continue;
                }
                $uniqueNames[$index] = $index;
            }

            $text = "$index ({$program['version']})";

            $options[] = HTML::_('select.option', $program['value'], $text);
        }

        return $options;
    }
}
