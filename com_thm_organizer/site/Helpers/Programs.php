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

/**
 * Provides general functions for program access checks, data retrieval and display.
 */
class Programs
{
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
}
