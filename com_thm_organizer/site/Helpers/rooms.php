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

require_once 'buildings.php';
require_once 'departments.php';
require_once 'languages.php';

use OrganizerHelper;
use THM_OrganizerHelperLanguages as Languages;

/**
 * Class provides general functions for retrieving room data.
 */
class THM_OrganizerHelperRooms
{
    /**
     * Checks for the room entry in the database, creating it as necessary. Adds the id to the room entry in the
     * schedule.
     *
     * @param string $gpuntisID the room's gpuntis ID
     * @param array  $data      the room data to be used for creating a new entry as necessary
     *
     * @return mixed  int the id if the room could be resolved/added, otherwise null
     */
    public static function getID($gpuntisID, $data)
    {
        $roomTable    = \JTable::getInstance('rooms', 'thm_organizerTable');
        $loadCriteria = ['gpuntisID' => $gpuntisID];

        try {
            $roomTable->load($loadCriteria);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');

            return null;
        }

        $buildingREGEX = OrganizerHelper::getParams()->get('buildingRegex');

        if (!empty($buildingREGEX) and !empty($data->name)) {
            $matchFound = preg_match("/$buildingREGEX/", $data->name, $matches);
            if ($matchFound) {
                $data->buildingID = THM_OrganizerHelperBuildings::getID($matches[1]);
            }
        }

        if (empty($roomTable->id)) {
            $success = $roomTable->save($data);

            return $success ? $roomTable->id : null;
        }

        // Fill empty values, but do not overwrite existing
        foreach ($data as $key => $value) {
            if (property_exists($roomTable, $key) and empty($roomTable->$key) and !empty($value)) {
                $roomTable->set($key, $value);
            }
        }
        $roomTable->store();

        return $roomTable->id;
    }

    /**
     * Checks for the room name for a given room id
     *
     * @param string $roomID the room's id
     *
     * @return string the name if the room could be resolved, otherwise empty
     */
    public static function getName($roomID)
    {
        $roomTable = \JTable::getInstance('rooms', 'thm_organizerTable');

        try {
            $success = $roomTable->load($roomID);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');

            return '';
        }

        return $success ? $roomTable->longname : '';
    }

    /**
     * Retrieves the ids for filtered rooms used in events.
     *
     * @return array the rooms used in actual events which meet the filter criteria
     */
    public static function getPlanRooms()
    {
        $allRooms = self::getRooms();
        $default  = [];

        if (empty($allRooms)) {
            return $default;
        }

        $app           = OrganizerHelper::getApplication();
        $dbo           = \JFactory::getDbo();
        $relevantRooms = [];

        $selectedDepartment = $app->input->getInt('departmentIDs');
        $selectedPrograms   = explode(',', $app->input->getString('programIDs'));
        $programIDs         = $selectedPrograms[0] > 0 ?
            implode(',', Joomla\Utilities\ArrayHelper::toInteger($selectedPrograms)) : '';

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
        $formData = $input->get('jform', [], 'array');

        $menuCampus    = (empty($app->getMenu()) or empty($app->getMenu()->getActive())) ?
            0 : $app->getMenu()->getActive()->params->get('campusID', 0);
        $defaultCampus = $input->getInt('campusID', $menuCampus);

        $buildingID = empty($formData['buildingID']) ? $input->getInt('buildingID') : (int)$formData['buildingID'];
        $campusID   = empty($formData['campusID']) ? $defaultCampus : (int)$formData['campusID'];
        $inputTypes = (array)$input->getInt('typeID', $input->getInt('typeIDs', $input->getInt('roomTypeIDs')));
        $typeIDs    = empty($formData['types']) ? $inputTypes : $formData['types'];
        $inputRooms = (array)$input->getInt('roomID', $input->getInt('roomIDs'));
        $roomIDs    = empty($formData['rooms']) ? $inputRooms : $formData['rooms'];

        $dbo   = \JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT r.id, r.*, rt.name_$shortTag AS typeName, rt.description_$shortTag AS typeDesc")
            ->from('#__thm_organizer_rooms AS r')
            ->innerJoin('#__thm_organizer_room_types AS rt ON rt.id = r.typeID');

        if (!empty($roomIDs)) {
            $roomIDs   = Joomla\Utilities\ArrayHelper::toInteger($roomIDs);
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
            $typeIDs   = Joomla\Utilities\ArrayHelper::toInteger($typeIDs);
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

        $query->order('longname');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

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
            $scheduleModel->scheduleErrors[] = \JText::_('THM_ORGANIZER_ERROR_ROOMS_MISSING');

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
                = sprintf(\JText::_('THM_ORGANIZER_WARNING_ROOM_EXTID_MISSING'), $warningCount);
        }

        if (!empty($scheduleModel->scheduleWarnings['ROOM-TYPE'])) {
            $warningCount = $scheduleModel->scheduleWarnings['ROOM-TYPE'];
            unset($scheduleModel->scheduleWarnings['ROOM-TYPE']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(\JText::_('THM_ORGANIZER_WARNING_TYPE_MISSING'), $warningCount);
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
            if (!in_array(\JText::_('THM_ORGANIZER_ERROR_ROOM_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = \JText::_('THM_ORGANIZER_ERROR_ROOM_ID_MISSING');
            }

            return;
        }

        $internalID = strtoupper(str_replace('RM_', '', $internalID));
        $roomID     = $internalID;

        $displayName = trim((string)$roomNode->longname);
        if (empty($displayName)) {
            $scheduleModel->scheduleErrors[]
                = sprintf(\JText::_('THM_ORGANIZER_ERROR_ROOM_DISPLAY_NAME_MISSING'), $internalID);

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

        // This must be called after all properties have been set
        $room->id           = THM_OrganizerHelperRooms::getID($roomID, $room);

        $scheduleModel->schedule->rooms->$roomID = $room;
    }
}
