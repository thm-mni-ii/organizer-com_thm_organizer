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

use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\ResourceHelper;
use stdClass;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class Groups extends ResourceHelper implements UntisXMLValidator
{
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
        $group = $scheduleModel->schedule->groups->$untisID;

        $table  = self::getTable();
        $data   = ['untisID' => $group->untisID];
        $exists = $table->load($data);

        if ($exists) {
            $altered = false;
            foreach ($group as $key => $value) {
                if (property_exists($table, $key) and empty($table->$key) and !empty($value)) {
                    $table->set($key, $value);
                    $altered = true;
                }
            }

            if ($altered) {
                $table->store();
            }

            $scheduleModel->schedule->groups->$untisID->id = $table->id;

            return;
        }
        $table->save($data);
        $scheduleModel->schedule->groups->$untisID->id = $table->id;

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
        if (empty($xmlObject->classes)) {
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_GROUPS_MISSING');

            return;
        }

        $scheduleModel->schedule->groups = new stdClass;

        foreach ($xmlObject->classes->children() as $groupNode) {
            self::validateIndividual($scheduleModel, $groupNode);
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
        $internalID = trim((string)$node[0]['id']);
        if (empty($internalID)) {
            if (!in_array(Languages::_('THM_ORGANIZER_GROUP_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_GROUP_ID_MISSING');
            }

            return;
        }

        $internalID = str_replace('CL_', '', $internalID);
        $externalID = trim((string)$node->external_name);
        $untisID    = empty($externalID) ? $internalID : str_replace('CL_', '', $externalID);

        $full_name = trim((string)$node->longname);
        if (empty($full_name)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_GROUP_LONGNAME_MISSING'),
                $internalID
            );

            return;
        }

        $name = trim((string)$node->classlevel);
        if (empty($name)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_ERROR_NODE_NAME'),
                $full_name,
                $internalID
            );

            return;
        }

        $degreeID = str_replace('DP_', '', trim((string)$node->class_department[0]['id']));
        if (empty($degreeID)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_GROUP_MISSING_CATEGORY'),
                $full_name,
                $internalID
            );

            return;
        } elseif (empty($scheduleModel->schedule->degrees->$degreeID)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_GROUP_CATEGORY_LACKING'),
                $full_name,
                $internalID,
                $degreeID
            );

            return;
        }

        $grid = (string)$node->timegrid;
        if (empty($grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_GROUP_MISSING_GRID'),
                $full_name,
                $internalID
            );

            return;
        } elseif (empty($scheduleModel->schedule->periods->$grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_GROUP_GRID_LACKING'),
                $full_name,
                $internalID,
                $grid
            );

            return;
        }

        $group            = new stdClass;
        $group->degree    = $degreeID;
        $group->untisID   = $untisID;
        $group->full_name = $full_name;
        $group->name      = $name;
        $group->grid      = $grid;
        $group->gridID    = Grids::getID($grid);

        $scheduleModel->schedule->groups->$internalID = $group;
        self::setID($scheduleModel, $internalID);
    }
}
