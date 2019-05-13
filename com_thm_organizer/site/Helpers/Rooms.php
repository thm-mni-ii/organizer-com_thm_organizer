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

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use stdClass;

/**
 * Class provides general functions for retrieving room data.
 */
class Rooms implements XMLValidator
{
    /**
     * Checks for the room name for a given room id
     *
     * @param string $roomID the room's id
     *
     * @return string the name if the room could be resolved, otherwise empty
     */
    public static function getName($roomID)
    {
        $roomTable = OrganizerHelper::getTable('Rooms');

        try {
            $success = $roomTable->load($roomID);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');

            return '';
        }

        return $success ? $roomTable->name : '';
    }

    /**
     * Retrieves a list of resources in the form of name => id.
     *
     * @return array the resources, or empty
     */
    public static function getOptions()
    {
        $rooms = self::getRooms(true);

        $result = [];
        foreach ($rooms as $room) {
            $result[$room['name']] = $room['id'];
        }

        return $result;
    }

    /**
     * Retrieves the ids for filtered rooms used in events.
     *
     * @return array the rooms used in actual events which meet the filter criteria
     */
    public static function getPlannedRooms()
    {
        $allRooms = self::getRooms();
        $default  = [];

        if (empty($allRooms)) {
            return $default;
        }

        $app           = OrganizerHelper::getApplication();
        $dbo           = Factory::getDbo();
        $relevantRooms = [];

        $selectedDepartment = $app->input->getInt('departmentIDs');
        $selectedPrograms   = explode(',', $app->input->getString('programIDs'));
        $programIDs         = $selectedPrograms[0] > 0 ?
            implode(',', ArrayHelper::toInteger($selectedPrograms)) : '';

        $query = $dbo->getQuery(true);
        $query->select('COUNT(DISTINCT lc.id)')
            ->from('#__thm_organizer_lesson_configurations AS lc')
            ->innerJoin('#__thm_organizer_lesson_subjects AS ls ON lc.lessonID = ls.id')
            ->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.subjectID = ls.id')
            ->innerJoin('#__thm_organizer_plan_pools AS ppo ON lp.poolID = ppo.id')
            ->innerJoin('#__thm_organizer_department_resources AS dr ON dr.programID = ppo.programID');

        foreach ($allRooms as $room) {

            $query->clear('where');
            // Negative lookaheads are not possible in MySQL and POSIX (e.g. [[:colon:]]) is not in MariaDB
            // This regex is compatible with both
            $regex = '"rooms":\\{("[0-9]+":"[\w]*",)*"' . $room['id'] . '":("new"|"")';
            $query->where("lc.configuration REGEXP '$regex'");

            if (!empty($selectedDepartment)) {
                $query->where("dr.departmentID = $selectedDepartment");

                if (!empty($programIDs)) {
                    $query->where("ppo.programID in ($programIDs)");
                }
            }

            $dbo->setQuery($query);

            $count = OrganizerHelper::executeQuery('loadResult');

            if (!empty($count)) {
                $relevantRooms[$room['name']] = ['id' => $room['id'], 'typeID' => $room['typeID']];
            }
        }

        ksort($relevantRooms);

        return $relevantRooms;
    }

    /**
     * Retrieves all room entries which match the given filter criteria. Ordered by their display names.
     *
     * @return array the rooms matching the filter criteria or empty if none were found
     */
    public static function getRooms()
    {
        $shortTag = Languages::getShortTag();
        $app      = OrganizerHelper::getApplication();
        $input    = $app->input;
        $formData = OrganizerHelper::getForm();

        $menuCampus    = (empty($app->getMenu()) or empty($app->getMenu()->getActive())) ?
            0 : $app->getMenu()->getActive()->params->get('campusID', 0);
        $defaultCampus = $input->getInt('campusID', $menuCampus);

        $buildingID = empty($formData['buildingID']) ? $input->getInt('buildingID') : (int)$formData['buildingID'];
        $campusID   = empty($formData['campusID']) ? $defaultCampus : (int)$formData['campusID'];
        $inputTypes = (array)$input->getInt('typeID', $input->getInt('typeIDs', $input->getInt('roomTypeIDs')));
        $typeIDs    = empty($formData['types']) ? $inputTypes : $formData['types'];
        $inputRooms = (array)$input->getInt('roomID', $input->getInt('roomIDs'));
        $roomIDs    = empty($formData['rooms']) ? $inputRooms : $formData['rooms'];

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT r.id, r.*, rt.name_$shortTag AS typeName, rt.description_$shortTag AS typeDesc")
            ->from('#__thm_organizer_rooms AS r')
            ->innerJoin('#__thm_organizer_room_types AS rt ON rt.id = r.typeID');

        if (!empty($roomIDs)) {
            $roomIDs   = ArrayHelper::toInteger($roomIDs);
            $zeroIndex = array_search(0, $roomIDs);
            if ($zeroIndex !== false) {
                unset($roomIDs[$zeroIndex]);
            }

            // There were more types chosen than the zero index
            if (!empty($roomIDs)) {
                $roomString = "('" . implode("', '", $roomIDs) . "')";
                $query->where("r.id IN $roomString");
            }
        }

        if (!empty($typeIDs)) {
            $typeIDs   = ArrayHelper::toInteger($typeIDs);
            $zeroIndex = array_search(0, $typeIDs);
            if ($zeroIndex !== false) {
                unset($typeIDs[$zeroIndex]);
            }

            // There were more types chosen than the zero index
            if (!empty($typeIDs)) {
                $typeString = "('" . implode("', '", $typeIDs) . "')";
                $query->where("rt.id IN $typeString");
            }
        }

        if (!empty($buildingID) or !empty($campusID)) {
            $query->innerJoin('#__thm_organizer_buildings AS b ON b.id = r.buildingID');

            if (!empty($buildingID)) {
                $query->where("b.id = '$buildingID'");
            }

            if (!empty($campusID)) {
                $query->innerJoin('#__thm_organizer_campuses AS c ON c.id = b.campusID')
                    ->where("(c.id = '$campusID' OR c.parentID = '$campusID')");
            }
        }

        $query->order('name');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
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
        $room         = $scheduleModel->schedule->rooms->$untisID;
        $table        = OrganizerHelper::getTable('Rooms');
        $loadCriteria = ['gpuntisID' => $room->gpuntisID];
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
            $scheduleModel->scheduleWarnings['ROOM-EXTERNALID'] = empty($scheduleModel->scheduleWarnings['ROOM-EXTERNALID']) ?
                1 : $scheduleModel->scheduleWarnings['ROOM-EXTERNALID']++;
        } else {
            $externalID = strtoupper(str_replace('RM_', '', $externalID));
        }

        $untisID = empty($externalID) ? $internalID : $externalID;

        $typeID      = str_replace('DS_', '', trim((string)$roomNode->room_description[0]['id']));
        $invalidType = (empty($typeID) or empty($scheduleModel->schedule->room_types->$typeID));
        if ($invalidType) {
            $scheduleModel->scheduleWarnings['ROOM-TYPE'] = empty($scheduleModel->scheduleWarnings['ROOM-TYPE']) ?
                1 : $scheduleModel->scheduleWarnings['ROOM-TYPE']++;

            $typeID = null;
        } else {
            $typeID = $scheduleModel->schedule->room_types->$typeID->id;
        }

        $capacity      = (int)$roomNode->capacity;
        $buildingID    = null;
        $buildingREGEX = OrganizerHelper::getParams()->get('buildingRegex');

        if (!empty($buildingREGEX)) {
            $matchFound = preg_match("/$buildingREGEX/", $untisID, $matches);
            if ($matchFound) {
                $buildingID = Buildings::getID($matches[1]);
            }
        }

        $room             = new stdClass;
        $room->buildingID = $buildingID;
        $room->capacity   = $capacity;
        $room->gpuntisID  = $untisID;
        $room->name       = $untisID;
        $room->typeID     = $typeID;

        $scheduleModel->schedule->rooms->$internalID = $room;
        self::setID($scheduleModel, $internalID);
    }
}
