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

use Joomla\CMS\Factory;
use Organizer\Models\Program;
use stdClass;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class Categories implements DepartmentAssociated, XMLValidator
{
    /**
     * Checks whether the given plan program is associated with an allowed department
     *
     * @param array $categoryIDs the ids of the plan programs being checked
     *
     * @return bool  true if the plan program is associated with an allowed department, otherwise false
     */
    public static function allowEdit($categoryIDs)
    {
        $user = Factory::getUser();

        if (empty($user->id)) {
            return false;
        }

        if (Access::isAdmin()) {
            return true;
        }

        if (empty($categoryIDs)) {
            return false;
        }

        $categoryIDs        = "'" . implode("', '", $categoryIDs) . "'";
        $allowedDepartments = Access::getAccessibleDepartments('schedule');

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT id')
            ->from('#__thm_organizer_department_resources')
            ->where("categoryID IN ( $categoryIDs )")
            ->where("departmentID IN ('" . implode("', '", $allowedDepartments) . "')");

        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Gets the plan programs with corresponding documented program titles if associated.
     *
     * @return mixed
     */
    public static function getCategories()
    {

        $dbo           = Factory::getDbo();
        $languageTag   = Languages::getShortTag();
        $departmentIDs = OrganizerHelper::getInput()->get('departmentIDs', [], 'raw');

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$languageTag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
        $query->select('DISTINCT cat.id, cat.name AS catName, ' . $query->concatenate($nameParts, "") . ' AS name');
        $query->from('#__thm_organizer_categories AS cat');
        $query->innerJoin('#__thm_organizer_groups AS gr ON gr.categoryID = cat.id');
        $query->leftJoin('#__thm_organizer_programs AS p ON p.id = cat.programID');
        $query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');

        if (!empty($departmentIDs)) {
            $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = cat.id');
            $query->where("dr.departmentID IN ($departmentIDs)");
        }

        $query->order('catName');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

    /**
     * Retrieves the ids of departments associated with the resource
     *
     * @param int $resourceID the id of the resource for which the associated departments are requested
     *
     * @return array the ids of departments associated with the resource
     */
    public static function getDepartmentIDs($resourceID)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('departmentID')
            ->from('#__thm_organizer_department_resources')
            ->where("categoryID = $resourceID");
        $dbo->setQuery($query);
        $departmentIDs = OrganizerHelper::executeQuery('loadColumn', []);

        return empty($departmentIDs) ? [] : $departmentIDs;
    }

    /**
     * Retrieves the category name
     *
     * @param int $categoryID the table id for the program
     *
     * @return string the name of the (plan) program, otherwise empty
     */
    public static function getName($categoryID)
    {
        $dbo         = Factory::getDbo();
        $languageTag = Languages::getShortTag();

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$languageTag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
        $query->select('cat.name AS catName, ' . $query->concatenate($nameParts, "") . ' AS name');

        $query->from('#__thm_organizer_categories AS cat');
        $query->leftJoin('#__thm_organizer_programs AS p ON cat.programID = p.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $query->where("cat.id = '$categoryID'");

        $dbo->setQuery($query);
        $names = OrganizerHelper::executeQuery('loadAssoc', []);

        return empty($names) ? '' : empty($names['name']) ? $names['catName'] : $names['name'];
    }

    /**
     * Retrieves a list of resources in the form of name => id.
     *
     * @return array the resources, or empty
     */
    public static function getOptions()
    {
        $programs = self::getCategories();

        $results = [];
        foreach ($programs as $program) {
            $name           = empty($program['name']) ? $program['catName'] : $program['name'];
            $results[$name] = $program['id'];
        }

        ksort($results);

        return empty($results) ? [] : $results;
    }

    /**
     * Determines whether the data conveyed in the untisID is plausible for finding a real program.
     *
     * @param string $untisID the id used in untis for this program
     *
     * @return array empty if the id is implausible
     */
    private static function parsePlausibleProgramData($untisID)
    {
        $container       = [];
        $programPieces   = explode('.', $untisID);
        $plausibleNumber = count($programPieces) === 3;
        if ($plausibleNumber) {
            $plausibleCode    = preg_match('/^[A-Z]+[0-9]*$/', $programPieces[0]);
            $plausibleVersion = (ctype_digit($programPieces[2]) and preg_match('/^[2]{1}[0-9]{3}$/',
                    $programPieces[2]));
            $plausibleDegree  = (ctype_upper($programPieces[1])
                and preg_match('/^[B|M]{1}[A-Z]{1,2}$/', $programPieces[1]));
            if ($plausibleDegree) {
                $degreeTable    = OrganizerHelper::getTable('Degrees');
                $degreePullData = ['code' => $programPieces[1]];
                $exists         = $degreeTable->load($degreePullData);
                $degreeID       = $exists ? $degreeTable->id : null;
            }
            if ($plausibleCode and !empty($degreeID) and $plausibleVersion) {
                $container['code']     = $programPieces[0];
                $container['degreeID'] = $degreeID;
                $container['version']  = $programPieces[2];
            }
        }

        return $container;
    }

    /**
     * Retrieves the resource id using the Untis ID. Creates the resource id if unavailable.
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $untisID       the id of the resource in Untis
     *
     * @return void modifies the scheduleModel, setting the id property of the resource
     */
    public static function setID(&$scheduleModel, $untisID)
    {
        $program        = $scheduleModel->schedule->degrees->$untisID;
        $table          = OrganizerHelper::getTable('Categories');
        $loadCriteria   = [];
        $loadCriteria[] = ['untisID' => $untisID];
        $loadCriteria[] = ['name' => $program->name];

        foreach ($loadCriteria as $criterion) {
            $exists = $table->load($criterion);
            if ($exists) {
                $altered = false;
                foreach ($program as $key => $value) {
                    if (property_exists($table, $key) and empty($table->$key) and !empty($value)) {
                        $table->set($key, $value);
                        $altered = true;
                    }
                }

                if ($altered) {
                    $table->store();
                }

                $scheduleModel->schedule->degrees->$untisID->id = $table->id;

                return;
            }
        }

        $table->save($program);
        $scheduleModel->schedule->degrees->$untisID->id = $table->id;

        return;
    }

    /**
     * Checks whether nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the object being validated
     *
     * @return void modifies &$scheduleModel
     */
    public static function validateCollection(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->departments)) {
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_PROGRAMS_MISSING');

            return;
        }

        $scheduleModel->schedule->degrees = new stdClass;

        foreach ($xmlObject->departments->children() as $node) {
            self::validateIndividual($scheduleModel, $node);
        }
    }

    /**
     * Checks whether XML node has the expected structure and required
     * information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$node          the node to be validated
     *
     * @return void
     */
    public static function validateIndividual(&$scheduleModel, &$node)
    {
        $untisID = trim((string)$node[0]['id']);
        if (empty($untisID)) {
            if (!in_array(Languages::_('THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING');
            }

            return;
        }

        $untisID = str_replace('DP_', '', $untisID);

        $name = (string)$node->longname;
        if (!isset($name)) {
            $scheduleModel->scheduleErrors[]
                = sprintf(Languages::_('THM_ORGANIZER_ERROR_PROGRAM_NAME_MISSING'), $untisID);

            return;
        }

        $plausibleData = self::parsePlausibleProgramData($untisID);
        $tempArray     = explode('(', $name);
        $tempName      = trim($tempArray[0]);
        $programID     = empty($plausibleData) ? null : Programs::getID($plausibleData, $tempName);

        $category            = new stdClass;
        $category->untisID   = $untisID;
        $category->name      = $name;
        $category->programID = $programID;

        $scheduleModel->schedule->degrees->$untisID = $category;

        self::setID($scheduleModel, $untisID);
        Departments::setDepartmentResource($category->id, 'categoryID');

    }
}
