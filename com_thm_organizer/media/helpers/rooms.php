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
        $dbo           = JFactory::getDbo();
        $default       = [];
        $input         = JFactory::getApplication()->input;
        $selectedRooms = $input->get('roomIDs', [], 'array');
        $selectedTypes = $input->get('typeIDs', [], 'array');

        $allRoomQuery = $dbo->getQuery(true);
        $allRoomQuery->select('DISTINCT r.id, r.name, r.typeID')->from('#__thm_organizer_rooms AS r');

        if (!empty($selectedRooms)) {
            $roomIDs = "'" . implode("', '", $selectedRooms) . "'";
            $allRoomQuery->where("r.id IN ($roomIDs)");
        }

        if (!empty($selectedTypes)) {
            $allRoomQuery->innerJoin("#__thm_organizer_room_types AS rt ON r.typeID = rt.id");
            $typeIDs = "'" . implode("', '", $selectedTypes) . "'";
            $allRoomQuery->where("rt.id IN ($typeIDs)");
        }

        $dbo->setQuery($allRoomQuery);

        try {
            $allRooms = $dbo->loadAssocList();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

            return $default;
        }

        if (empty($allRooms)) {
            return $default;
        }

        $selectedDepartments = $input->getString('departmentIDs');
        $selectedPrograms    = $input->getString('programIDs');
        $relevantRooms       = [];

        foreach ($allRooms as $room) {
            $relevanceQuery = $dbo->getQuery(true);
            $relevanceQuery->select("COUNT(DISTINCT lc.id)");
            $relevanceQuery->from('#__thm_organizer_lesson_configurations AS lc');
            $relevanceQuery->innerJoin('#__thm_organizer_lesson_subjects AS ls ON lc.lessonID = ls.id');
            $relevanceQuery->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.subjectID = ls.id');

            $regex = '[[.quotation-mark.]]rooms[[.quotation-mark.]][[.colon.]][[.{.]]' .
                '([[.quotation-mark.]][[:alnum:]]*[[.quotation-mark.]][[.colon.]]?[[.comma.]]?)*' .
                "[[.quotation-mark.]]{$room['id']}[[.quotation-mark.]][[.colon.]]" .
                '[[.quotation-mark.]][^removed]';
            $relevanceQuery->where("lc.configuration REGEXP '$regex'");

            if (!empty($selectedDepartments)) {
                $relevanceQuery->innerJoin("#__thm_organizer_department_resources AS dr ON dr.roomID = '{$room['id']}'");
                $departmentIDs = "'" . str_replace(',', "', '", $selectedDepartments) . "'";
                $relevanceQuery->where("dr.departmentID IN ($departmentIDs)");
            }

            if (!empty($selectedPrograms)) {
                $programIDs = "'" . str_replace(',', "', '", $selectedPrograms) . "'";
                $relevanceQuery->innerJoin('#__thm_organizer_plan_pools AS ppo ON lp.poolID = ppo.id');
                $relevanceQuery->where("ppo.programID in ($programIDs)");
            }

            $dbo->setQuery($relevanceQuery);

            try {
                $count = $dbo->loadResult();
            } catch (Exception $exc) {
                JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

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
        $defaultCampus = empty($input->getInt('campusID')) ? $menuCampus : $input->getInt('campusID');

        $buildingID = (empty($formData) or empty($formData['buildingID'])) ? $input->getInt('buildingID') : (int)$formData['buildingID'];
        $campusID   = (empty($formData) or empty($formData['campusID'])) ? $defaultCampus : (int)$formData['campusID'];
        $typeIDs    = (empty($formData) or empty($formData['types'])) ? [$input->getInt('typeID')] : $formData['types'];
        $roomIDs    = (empty($formData) or empty($formData['rooms'])) ? [$input->getInt('roomID')] : $formData['rooms'];

        $dbo   = JFactory::getDbo();
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

        try {
            $rooms = $dbo->loadAssocList();
        } catch (Exception $exc) {
            return [];
        }

        return empty($rooms) ? [] : $rooms;
    }
}
