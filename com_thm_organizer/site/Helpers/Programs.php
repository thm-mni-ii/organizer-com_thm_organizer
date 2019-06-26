<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Exception;
use Joomla\CMS\Factory;
use Organizer\Models\Program;
use Organizer\Tables\Participants;

/**
 * Provides general functions for program access checks, data retrieval and display.
 */
class Programs implements Selectable
{
    use Filtered;

    /**
     * Attempts to get the real program's id, creating the stub if non-existent.
     *
     * @param array  $programData the program data
     * @param string $initialName the name to be used if no entry already exists
     *
     * @return mixed int on success, otherwise null
     * @throws Exception
     */
    public static function getID($programData, $initialName)
    {
        $programTable = OrganizerHelper::getTable('Programs');
        $exists       = $programTable->load($programData);
        if ($exists) {
            return $programTable->id;
        }

        if (empty($initialName)) {
            return null;
        }

        $formData                    = OrganizerHelper::getFormInput();
        $programData['departmentID'] = $formData['departmentID'];
        $programData['name_de']      = $initialName;
        $programData['name_en']      = $initialName;

        $model     = new Program;
        $programID = $model->save($programData);

        return empty($programID) ? null : $programID;
    }

    /**
     * Retrieves the program name
     *
     * @param int $programID the table id for the program
     *
     * @return string the name of the (plan) program, otherwise empty
     */
    public static function getName($programID)
    {
        if (empty($programID)) {
            return Languages::_('THM_ORGANIZER_NO_PROGRAM');
        }

        $dbo         = Factory::getDbo();
        $languageTag = Languages::getShortTag();

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$languageTag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
        $query->select('cat.name AS catName, ' . $query->concatenate($nameParts, "") . ' AS name');

        $query->from('#__thm_organizer_programs AS p');
        $query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $query->leftJoin('#__thm_organizer_categories AS cat ON cat.programID = p.id');
        $query->where("p.id = '$programID'");

        $dbo->setQuery($query);
        $names = OrganizerHelper::executeQuery('loadAssoc', []);

        return empty($names) ? '' : empty($names['name']) ? $names['catName'] : $names['name'];
    }

    /**
     * Retrieves the selectable options for the resource.
     *
     * @param string $access any access restriction which should be performed
     *
     * @return array the available options
     */
    public static function getOptions($access = '')
    {
        $options = [];
        foreach (self::getResources($access) as $program) {
            $name = "{$program['name']} ({$program['degree']},  {$program['version']})";

            $options[] = HTML::_('select.option', $program['id'], $name);
        }

        return $options;
    }

    /**
     * Retrieves the resource items.
     *
     * @param string $access any access restriction which should be performed
     *
     * @return array the available resources
     */
    public static function getResources($access = '')
    {
        $shortTag = Languages::getShortTag();
        $dbo      = Factory::getDbo();
        $query    = $dbo->getQuery(true);

        $query->select("dp.*, dp.name_$shortTag AS name, d.abbreviation AS degree");
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON dp.id = m.programID');
        $query->order('name ASC, degree ASC, version DESC');

        if (!empty($access)) {
            self::addAccessFilter($query, 'dp', $access);
        }

        self::addResourceFilter($query, 'department', 'dept', 'dp');

        $useCurrent = self::useCurrent();
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

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

    /**
     * Determines whether only the latest version of a program should be displayed in the list.
     *
     * @return bool
     */
    private static function useCurrent()
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
