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

use Organizer\Helpers\Buildings;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\ResourceHelper;
use stdClass;

/**
 * Class provides general functions for retrieving room data.
 */
class Rooms extends ResourceHelper implements UntisXMLValidator
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
        $room  = $model->schedule->rooms->$untisID;
        $table = self::getTable();

        if ($table->load(['untisID' => $room->untisID])) {
            $altered = false;
            foreach ($room as $key => $value) {
                if (property_exists($table, $key) and empty($table->$key) and !empty($value)) {
                    $table->set($key, $value);
                    $altered = true;
                }
            }

            if ($altered) {
                $table->store();
            }
        } else {
            $table->save($room);
        }
        $model->schedule->rooms->$untisID->id = $table->id;

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
        if (empty($xmlObject->rooms)) {
            $model->errors[] = Languages::_('THM_ORGANIZER_ROOMS_MISSING');

            return;
        }

        $model->schedule->rooms = new stdClass;

        foreach ($xmlObject->rooms->children() as $node) {
            self::validateIndividual($model, $node);
        }

        if (!empty($model->warnings['REX'])) {
            $warningCount = $model->warnings['REX'];
            unset($model->warnings['REX']);
            $model->warnings[] = sprintf(Languages::_('THM_ORGANIZER_ROOM_EXTERNAL_IDS_MISSING'), $warningCount);
        }

        if (!empty($model->warnings['RT'])) {
            $warningCount = $model->warnings['RT'];
            unset($model->warnings['RT']);
            $model->warnings[] = sprintf(Languages::_('THM_ORGANIZER_ROOMTYPES_MISSING'), $warningCount);
        }
    }

    /**
     * Checks whether room nodes have the expected structure and required
     * information
     *
     * @param object &$model    the validating schedule model
     * @param object &$roomNode the room node to be validated
     *
     * @return void
     */
    public static function validateIndividual(&$model, &$roomNode)
    {
        $internalID = trim((string)$roomNode[0]['id']);
        if (empty($internalID)) {
            if (!in_array(Languages::_('THM_ORGANIZER_ROOM_IDS_MISSING'), $model->errors)) {
                $model->errors[] = Languages::_('THM_ORGANIZER_ROOM_IDS_MISSING');
            }

            return;
        }

        $internalID = strtoupper(str_replace('RM_', '', $internalID));

        if ($externalID = strtoupper(trim((string)$roomNode->external_name))) {
            $untisID = $externalID;
        } else {
            $model->warnings['REX'] = empty($model->warnings['REX']) ? 1 : $model->warnings['REX']++;
            $untisID                = $internalID;
        }

        $roomTypeID  = str_replace('DS_', '', trim((string)$roomNode->room_description[0]['id']));
        $invalidType = (empty($roomTypeID) or empty($model->schedule->roomtypes->$roomTypeID));
        if ($invalidType) {
            $model->warnings['RT'] = empty($model->warnings['RT']) ? 1 : $model->warnings['RT']++;
            $roomTypeID            = null;
        } else {
            $roomTypeID = $model->schedule->roomtypes->$roomTypeID->id;
        }

        $capacity      = (int)$roomNode->capacity;
        $buildingID    = null;
        $buildingREGEX = Input::getParams()->get('buildingRegex');

        if (!empty($buildingREGEX) and preg_match("/$buildingREGEX/", $untisID, $matches)) {
            $buildingID = Buildings::getID($matches[1]);
        }

        $room             = new stdClass;
        $room->buildingID = $buildingID;
        $room->capacity   = $capacity;
        $room->name       = $untisID;
        $room->roomtypeID = $roomTypeID;
        $room->untisID    = $untisID;

        $model->schedule->rooms->$internalID = $room;
        self::setID($model, $internalID);
    }
}
