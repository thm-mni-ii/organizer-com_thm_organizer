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
     * @param object &$scheduleModel the validating schedule model
     * @param string  $untisID       the id of the resource in Untis
     *
     * @return void modifies the scheduleModel, setting the id property of the resource
     */
    public static function setID(&$scheduleModel, $untisID)
    {
        $room         = $scheduleModel->schedule->rooms->$untisID;
        $table        = self::getTable();
        $loadCriteria = ['untisID' => $room->untisID];
        $exists       = $table->load($loadCriteria);

        if ($exists) {
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
        $scheduleModel->schedule->rooms->$untisID->id = $table->id;

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
        if (empty($xmlObject->rooms)) {
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_ROOMS_MISSING');

            return;
        }

        $scheduleModel->schedule->rooms = new stdClass;

        foreach ($xmlObject->rooms->children() as $node) {
            self::validateIndividual($scheduleModel, $node);
        }

        if (!empty($scheduleModel->scheduleWarnings['ROOM-EXTERNALID'])) {
            $warningCount = $scheduleModel->scheduleWarnings['ROOM-EXTERNALID'];
            unset($scheduleModel->scheduleWarnings['ROOM-EXTERNALID']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(Languages::_('THM_ORGANIZER_WARNING_ROOM_EXTID_MISSING'), $warningCount);
        }

        if (!empty($scheduleModel->scheduleWarnings['ROOM-TYPE'])) {
            $warningCount = $scheduleModel->scheduleWarnings['ROOM-TYPE'];
            unset($scheduleModel->scheduleWarnings['ROOM-TYPE']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(Languages::_('THM_ORGANIZER_WARNING_TYPE_MISSING'), $warningCount);
        }
    }

    /**
     * Checks whether room nodes have the expected structure and required
     * information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$roomNode      the room node to be validated
     *
     * @return void
     */
    public static function validateIndividual(&$scheduleModel, &$roomNode)
    {
        $internalID = trim((string)$roomNode[0]['id']);
        if (empty($internalID)) {
            if (!in_array(Languages::_('THM_ORGANIZER_ERROR_ROOM_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_ROOM_ID_MISSING');
            }

            return;
        }

        $internalID = strtoupper(str_replace('RM_', '', $internalID));
        $externalID = trim((string)$roomNode->external_name);

        if (empty($externalID)) {
            $scheduleModel->scheduleWarnings['ROOM-EXTERNALID'] =
                empty($scheduleModel->scheduleWarnings['ROOM-EXTERNALID']) ?
                    1 : $scheduleModel->scheduleWarnings['ROOM-EXTERNALID']++;
        } else {
            $externalID = strtoupper(str_replace('RM_', '', $externalID));
        }

        $untisID = empty($externalID) ? $internalID : $externalID;

        $typeID      = str_replace('DS_', '', trim((string)$roomNode->room_description[0]['id']));
        $invalidType = (empty($typeID) or empty($scheduleModel->schedule->roomtypes->$typeID));
        if ($invalidType) {
            $scheduleModel->scheduleWarnings['ROOM-TYPE'] = empty($scheduleModel->scheduleWarnings['ROOM-TYPE']) ?
                1 : $scheduleModel->scheduleWarnings['ROOM-TYPE']++;

            $typeID = null;
        } else {
            $typeID = $scheduleModel->schedule->roomtypes->$typeID->id;
        }

        $capacity      = (int)$roomNode->capacity;
        $buildingID    = null;
        $buildingREGEX = Input::getParams()->get('buildingRegex');

        if (!empty($buildingREGEX)) {
            $matchFound = preg_match("/$buildingREGEX/", $untisID, $matches);
            if ($matchFound) {
                $buildingID = Buildings::getID($matches[1]);
            }
        }

        $room             = new stdClass;
        $room->buildingID = $buildingID;
        $room->capacity   = $capacity;
        $room->untisID    = $untisID;
        $room->name       = $untisID;
        $room->typeID     = $typeID;

        $scheduleModel->schedule->rooms->$internalID = $room;
        self::setID($scheduleModel, $internalID);
    }
}
