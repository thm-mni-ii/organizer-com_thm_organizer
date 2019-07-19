<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers\Validators;

use Organizer\Helpers as Helpers;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use stdClass;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class Categories extends Helpers\ResourceHelper implements UntisXMLValidator
{
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
        $pieces          = explode('.', $untisID);
        $plausibleNumber = count($pieces) === 3;
        if ($plausibleNumber) {
            $plausibleCode    = preg_match('/^[A-Z]+[0-9]*$/', $pieces[0]);
            $plausibleVersion = (ctype_digit($pieces[2]) and preg_match('/^[2]{1}[0-9]{3}$/', $pieces[2]));
            $plausibleDegree  = (ctype_upper($pieces[1])
                and preg_match('/^[B|M]{1}[A-Z]{1,2}$/', $pieces[1]));
            if ($plausibleDegree) {
                $degreeTable    = OrganizerHelper::getTable('Degrees');
                $degreePullData = ['code' => $pieces[1]];
                $exists         = $degreeTable->load($degreePullData);
                $degreeID       = $exists ? $degreeTable->id : null;
            }
            if ($plausibleCode and !empty($degreeID) and $plausibleVersion) {
                $container['code']     = $pieces[0];
                $container['degreeID'] = $degreeID;
                $container['version']  = $pieces[2];
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
        $table          = self::getTable();
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
        $programID     = empty($plausibleData) ? null : Helpers\Programs::getID($plausibleData, $tempName);

        $category            = new stdClass;
        $category->untisID   = $untisID;
        $category->name      = $name;
        $category->programID = $programID;

        $scheduleModel->schedule->degrees->$untisID = $category;

        self::setID($scheduleModel, $untisID);
        Helpers\Departments::setDepartmentResource($category->id, 'categoryID');
    }
}
