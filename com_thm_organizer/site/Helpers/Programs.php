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

defined('_JEXEC') or die;

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

        $formData                    = OrganizerHelper::getForm();
        $programData['departmentID'] = $formData['departmentID'];
        $programData['name_de']      = $initialName;
        $programData['name_en']      = $initialName;

        $model     = new Program;
        $programID = $model->save($programData);

        return empty($programID) ? null : $programID;
    }

    /**
     * Retrieves the (plan) program name
     *
     * @param int    $programID the table id for the program
     * @param string $type      the type of the id (real or plan)
     *
     * @return string the name of the (plan) program, otherwise empty
     */
    public static function getName($programID, $type)
    {
        $dbo         = Factory::getDbo();
        $languageTag = Languages::getShortTag();

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$languageTag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
        $query->select('ppr.name AS ppName, ' . $query->concatenate($nameParts, "") . ' AS name');

        if ($type == 'real') {
            $query->from('#__thm_organizer_programs AS p');
            $query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
            $query->leftJoin('#__thm_organizer_plan_programs AS ppr ON ppr.programID = p.id');
            $query->where("p.id = '$programID'");
        } else {
            $query->from('#__thm_organizer_plan_programs AS ppr');
            $query->leftJoin('#__thm_organizer_programs AS p ON ppr.programID = p.id');
            $query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
            $query->where("ppr.id = '$programID'");
        }

        $dbo->setQuery($query);
        $names = OrganizerHelper::executeQuery('loadAssoc', []);

        return empty($names) ? '' : empty($names['name']) ? $names['ppName'] : $names['name'];
    }

    /**
     * Getter method for schedule programs in database
     *
     * @return array an array of program information
     */
    public static function getPlanPrograms()
    {
        $dbo           = Factory::getDbo();
        $languageTag   = Languages::getShortTag();
        $departmentIDs = OrganizerHelper::getInput()->get('departmentIDs', [], 'raw');

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$languageTag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
        $query->select('DISTINCT ppr.id, ppr.name AS ppName, ' . $query->concatenate($nameParts, "") . ' AS name');
        $query->from('#__thm_organizer_plan_programs AS ppr');
        $query->innerJoin('#__thm_organizer_plan_pools AS ppo ON ppo.programID = ppr.id');
        $query->leftJoin('#__thm_organizer_programs AS p ON ppr.programID = p.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');

        if (!empty($departmentIDs)) {
            $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.programID = ppr.id');
            $query->where("dr.departmentID IN ($departmentIDs)");
        }

        $query->order('ppName');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }
}
