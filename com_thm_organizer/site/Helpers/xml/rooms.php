<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/departments.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/rooms.php';

/**
 * Provides functions for XML room validation and modeling.
 */
class THM_OrganizerHelperXMLRooms
{
    /**
     * Validates the rooms node
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the xml object being validated
     *
     * @return void
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->rooms)) {
            $scheduleModel->scheduleErrors[] = \JText::_('COM_THM_ORGANIZER_ERROR_ROOMS_MISSING');

            return;
        }

        $scheduleModel->schedule->rooms = new \stdClass;

        foreach ($xmlObject->rooms->children() as $resourceNode) {
            self::validateIndividual($scheduleModel, $resourceNode);
        }

        if (!empty($scheduleModel->scheduleWarnings['ROOM-EXTERNALID'])) {
            $warningCount = $scheduleModel->scheduleWarnings['ROOM-EXTERNALID'];
            unset($scheduleModel->scheduleWarnings['ROOM-EXTERNALID']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(\JText::_('COM_THM_ORGANIZER_WARNING_ROOM_EXTID_MISSING'), $warningCount);
        }

        if (!empty($scheduleModel->scheduleWarnings['ROOM-TYPE'])) {
            $warningCount = $scheduleModel->scheduleWarnings['ROOM-TYPE'];
            unset($scheduleModel->scheduleWarnings['ROOM-TYPE']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(\JText::_('COM_THM_ORGANIZER_WARNING_TYPE_MISSING'), $warningCount);
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
            if (!in_array(\JText::_('COM_THM_ORGANIZER_ERROR_ROOM_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = \JText::_('COM_THM_ORGANIZER_ERROR_ROOM_ID_MISSING');
            }

            return;
        }

        $internalID = strtoupper(str_replace('RM_', '', $internalID));
        $roomID     = $internalID;

        $displayName = trim((string)$roomNode->longname);
        if (empty($displayName)) {
            $scheduleModel->scheduleErrors[]
                = sprintf(\JText::_('COM_THM_ORGANIZER_ERROR_ROOM_DISPLAY_NAME_MISSING'), $internalID);

            return;
        }

        $externalID = trim((string)$roomNode->external_name);
        if (empty($externalID)) {
            $scheduleModel->scheduleWarnings['ROOM-EXTERNALID'] = empty($scheduleModel->scheduleWarnings['ROOM-EXTERNALID']) ?
                1 : $scheduleModel->scheduleWarnings['ROOM-EXTERNALID'] + 1;
        } else {
            $externalID = strtoupper(str_replace('RM_', '', $externalID));
        }

        $room = new \stdClass;
        if (empty($externalID)) {
            $room->name      = $internalID;
            $room->gpuntisID = $internalID;
        } else {
            $room->name      = $externalID;
            $room->gpuntisID = $externalID;
        }

        // This must be called after the name property has been set
        $room->id           = THM_OrganizerHelperRooms::getID($roomID, $room);
        $room->localUntisID = $internalID;
        $room->longname     = $displayName;

        $descriptionID      = str_replace('DS_', '', trim((string)$roomNode->room_description[0]['id']));
        $invalidDescription = (empty($descriptionID) or empty($scheduleModel->schedule->room_types->$descriptionID));
        if ($invalidDescription) {
            $scheduleModel->scheduleWarnings['ROOM-TYPE'] = empty($scheduleModel->scheduleWarnings['ROOM-TYPE']) ?
                1 : $scheduleModel->scheduleWarnings['ROOM-TYPE'] + 1;

            $room->description = '';
            $room->typeID      = null;
        } else {
            $room->description = $descriptionID;
            $room->typeID      = $scheduleModel->schedule->room_types->{$descriptionID}->id;
        }

        $capacity       = trim((int)$roomNode->capacity);
        $room->capacity = (empty($capacity)) ? '' : $capacity;

        $scheduleModel->schedule->rooms->$roomID = $room;
    }
}
