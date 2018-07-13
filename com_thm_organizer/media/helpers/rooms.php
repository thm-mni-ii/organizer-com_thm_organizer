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
require_once 'language.php';

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
     * @throws Exception
     */
    public static function getID($gpuntisID, $data)
    {
        $buildingName = '';

        $params        = JComponentHelper::getParams('com_thm_organizer');
        $buildingREGEX = $params->get('buildingRegex');
        $roomREGEX     = $params->get('roomRegex');

        if (!empty($buildingREGEX) and !empty($roomREGEX) and !empty($data->name)) {
            $rMatchFound = preg_match("/$roomREGEX/", $data->name, $rMatches);
            if ($rMatchFound) {
                $bMatchFound = preg_match("/$buildingREGEX/", $rMatches[0], $bMatches);
                if ($bMatchFound) {
                    $buildingName = $bMatches[0];
                }
            }
        }

        $roomTable    = JTable::getInstance('rooms', 'thm_organizerTable');
        $loadCriteria = ['gpuntisID' => $gpuntisID];

        try {
            $success = $roomTable->load($loadCriteria);
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return null;
        }

        $data->buildingID = empty($buildingName) ? null : THM_OrganizerHelperBuildings::getID($buildingName);

        // Room entry already exists
        if ($success) {

            // Fill empty values, but do not overwrite existing
            foreach ($data as $key => $value) {
                if (property_exists($roomTable, $key) and empty($roomTable->$key) and !empty($value)) {
                    $roomTable->set($key, $value);
                }
            }
            $roomTable->store();

            return $roomTable->id;
        }

        $success = $roomTable->save($data);

        return $success ? $roomTable->id : null;
    }

    /**
     * Checks for the room name for a given room id
     *
     * @param string $roomID the room's id
     *
     * @return string the name if the room could be resolved, otherwise empty
     * @throws Exception
     */
    public static function getName($roomID)
    {
        $roomTable = JTable::getInstance('rooms', 'thm_organizerTable');

        try {
            $success = $roomTable->load($roomID);
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return '';
        }

        return $success ? $roomTable->longname : '';
    }

    /**
     * Retrieves the ids for filtered rooms used in events.
     *
     * @return array the rooms used in actual events which meet the filter criteria
     * @throws Exception
     */
    public static function getPlanRooms()
    {
        $allRooms = self::getRooms();
        $default  = [];

        if (empty($allRooms)) {
            return $default;
        }

        $app           = JFactory::getApplication();
        $dbo           = JFactory::getDbo();
        $relevantRooms = [];

        $selectedDepartments = $app->input->getString('departmentIDs');
        $departmentIDs       = "'" . str_replace(',', "', '", $selectedDepartments) . "'";
        $selectedPrograms    = $app->input->getString('programIDs');
        $programIDs          = "'" . str_replace(',', "', '", $selectedPrograms) . "'";

        foreach ($allRooms as $room) {
            $query = $dbo->getQuery(true);
            $query->select('COUNT(DISTINCT lc.id)');
            $query->from('#__thm_organizer_lesson_configurations AS lc');

            // Negative lookaheads are not possible in MySQL and POSIX (e.g. [[:colon:]]) is not in MariaDB
            // This regex is compatible with both
            $regex = '"rooms":\\{[^{}]+"' . $room['id'] . '":("new"|"")';
            $query->where("lc.configuration REGEXP '$regex'");

            /*
            * TODO: Insert JSON function for MySQL >= 5.7. (1.55sec instead of 1.99sec with 1.000.000 executions)
            * $subQuery->where('JSON_UNQUOTE(JSON_EXTRACT(lc.configuration, "$.rooms.' . $room['id'] . '")) NOT LIKE "removed"');
            */

            if (!empty($selectedDepartments)) {
                $query->innerJoin("#__thm_organizer_department_resources AS dr ON dr.roomID = '{$room['id']}'");
                $query->where("dr.departmentID IN ($departmentIDs)");
            }

            if (!empty($selectedPrograms)) {
                $query->innerJoin('#__thm_organizer_lesson_subjects AS ls ON lc.lessonID = ls.id');
                $query->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.subjectID = ls.id');
                $query->innerJoin('#__thm_organizer_plan_pools AS ppo ON lp.poolID = ppo.id');
                $query->where("ppo.programID in ($programIDs)");
            }

            $dbo->setQuery($query);

            try {
                $count = $dbo->loadResult();
            } catch (Exception $exc) {
                $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

                return $default;
            }

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
     * @throws Exception
     */
    public static function getRooms()
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $app      = JFactory::getApplication();
        $input    = $app->input;
        $formData = $input->get('jform', [], 'array');

        $menuCampus    = (empty($app->getMenu()) or empty($app->getMenu()->getActive())) ?
            0 : $app->getMenu()->getActive()->params->get('campusID', 0);
        $defaultCampus = $input->getInt('campusID', $menuCampus);

        $buildingID   = empty($formData['buildingID']) ? $input->getInt('buildingID') : (int)$formData['buildingID'];
        $campusID     = empty($formData['campusID']) ? $defaultCampus : (int)$formData['campusID'];
        $departmentID = $input->getInt('departmentIDs', 0);
        $inputTypes   = (array)$input->getInt('typeID', $input->getInt('typeIDs', $input->getInt('roomTypeIDs')));
        $typeIDs      = empty($formData['types']) ? $inputTypes : $formData['types'];
        $inputRooms   = (array)$input->getInt('roomID', $input->getInt('roomIDs'));
        $roomIDs      = empty($formData['rooms']) ? $inputRooms : $formData['rooms'];

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT r.id, r.*, rt.name_$shortTag AS typeName, rt.description_$shortTag AS typeDesc")
            ->from('#__thm_organizer_rooms AS r')
            ->innerJoin('#__thm_organizer_room_types AS rt ON rt.id = r.typeID');

        if (!empty($departmentID)) {
            $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.roomID = r.id');
            $query->where("dr.departmentID = '$departmentID'");
        }

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

        try {
            $rooms = $dbo->loadAssocList();
        } catch (Exception $exc) {
            return [];
        }

        return empty($rooms) ? [] : $rooms;
    }
}