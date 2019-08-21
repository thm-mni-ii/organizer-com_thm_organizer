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
     * @param object &$model   the validating schedule model
     * @param string  $untisID the id of the resource in Untis
     *
     * @return void modifies the model, setting the id property of the resource
     */
    public static function setID(&$model, $untisID)
    {
        $group = $model->schedule->groups->$untisID;

        $table  = self::getTable();
        $exists = $table->load(['untisID' => $group->untisID]);

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
        } else {
            $table->save($group);
        }

        $model->schedule->groups->$untisID->id = $table->id;

        return;
    }

    /**
     * Checks whether nodes have the expected structure and required information
     *
     * @param object &$model     the validating schedule model
     * @param object &$xmlObject the object being validated
     *
     * @return void modifies &$model
     */
    public static function validateCollection(&$model, &$xmlObject)
    {
        $model->schedule->groups = new stdClass;

        foreach ($xmlObject->classes->children() as $node) {
            self::validateIndividual($model, $node);
        }
    }

    /**
     * Checks whether XML node has the expected structure and required
     * information
     *
     * @param object &$model the validating schedule model
     * @param object &$node  the node to be validated
     *
     * @return void
     */
    public static function validateIndividual(&$model, &$node)
    {
        $untisID = str_replace('CL_', '', trim((string)$node[0]['id']));
        $fullName = trim((string)$node->longname);
        if (empty($fullName)) {
            $model->errors[] = sprintf(Languages::_('THM_ORGANIZER_GROUP_FULLNAME_MISSING'), $untisID);

            return;
        }

        $name = trim((string)$node->classlevel);
        if (empty($name)) {
            $model->errors[] = sprintf(Languages::_('THM_ORGANIZER_GROUP_NAME_MISSING'), $fullName, $untisID);

            return;
        }

        $categoryID = str_replace('DP_', '', trim((string)$node->class_department[0]['id']));
        if (empty($categoryID)) {
            $model->errors[] = sprintf(Languages::_('THM_ORGANIZER_GROUP_CATEGORY_MISSING'), $fullName, $untisID);

            return;
        } elseif (empty($model->schedule->categories->$categoryID)) {
            $model->errors[] = sprintf(
                Languages::_('THM_ORGANIZER_GROUP_CATEGORY_INCOMPLETE'),
                $fullName,
                $untisID,
                $categoryID
            );

            return;
        }

        $gridName = (string)$node->timegrid;
        if (empty($gridName)) {
            $model->errors[] = sprintf(Languages::_('THM_ORGANIZER_GROUP_GRID_MISSING'), $fullName, $untisID);

            return;
        } elseif (empty($model->schedule->periods->$gridName)) {
            $model->errors[] = sprintf(
                Languages::_('THM_ORGANIZER_GROUP_GRID_INCOMPLETE'),
                $fullName,
                $untisID,
                $gridName
            );

            return;
        }

        $fieldID      = str_replace('DS_', '', trim($node->class_description[0]['id']));
        $fields       = $model->schedule->fields;
        $invalidField = (empty($fieldID) or empty($fields->$fieldID));

        $group             = new stdClass;
        $group->categoryID = $categoryID;
        $group->untisID    = $untisID;
        $group->fieldID    = $invalidField ? null : $fields->$fieldID->id;
        $group->fullName   = $fullName;
        $group->name       = $name;
        $group->gridID     = Grids::getID($gridName);

        $model->schedule->groups->$untisID = $group;
        self::setID($model, $untisID);
    }
}
