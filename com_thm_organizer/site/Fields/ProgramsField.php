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

        $defaultOptions = parent::getOptions();
        $programs       = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($programs)) {
            return $defaultOptions;
        }

        // Whether or not the program display should be prefiltered according to user resource access
        $access = $this->getAttribute('access', 'false') == 'true';

        // Unique will only use the most recently accredited program for a specific name and degree
        $unique      = $this->getAttribute('unique', 'false') == 'true';
        $uniqueNames = [];
        $options     = [];

        foreach ($programs as $program) {
            $index = "{$program['name']} {$program['degree']}";

            if ($unique and in_array($index, $uniqueNames)) {
                continue;
            } else {
                $uniqueNames[$index] = $index;
            }

            $text = "{$program['name']}, {$program['degree']} ({$program['version']})";

            if (!$access or Access::allowDocumentAccess('program', $program['value'])) {
                $options[] = HTML::_('select.option', $program['value'], $text);
            }
        }

        return array_merge($defaultOptions, $options);
    }
}
