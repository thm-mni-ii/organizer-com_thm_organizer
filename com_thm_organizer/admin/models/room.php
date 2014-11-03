<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelRoom
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_COMPONENT_ADMINISTRATOR . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class THM_OrganizerModelLecturer for component com_thm_organizer
 *
 * Class provides methods to deal with lecturer
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelRoom extends JModelLegacy
{
    private $_scheduleModel = null;

    /**
     * Attempts to save a room entry, updating schedule data as necessary.
     *
     * @return true on success, otherwise false
     */
    public function save()
    {
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');
        $this->_db->transactionStart();
        $scheduleSuccess = $this->updateScheduleData($data, "'" . $data['id'] . "'");
        if ($scheduleSuccess)
        {
            $table = JTable::getInstance('rooms', 'thm_organizerTable');
            $roomSuccess = $table->save($data);
            if ($roomSuccess)
            {
                $this->_db->transactionCommit();
                return true;
            }
        }
        $this->_db->transactionRollback();
        return false;
    }

    /**
     * Attempts an iterative merge of all room entries. Due to the attempted
     * merge of multiple entries with individual success codes no return value
     * is given.
     *
     * @return void
     */
    public function autoMergeAll()
    {
        $query = $this->_db->getQuery(true);
        $query->select('*')->from('#__thm_organizer_rooms')->order('longname, id ASC');
        $this->_db->setQuery((string) $query);
        
        try
        {
            $roomEntries = $this->_db->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        if (empty($roomEntries))
        {
            return;
        }

        $deletedIDs = array();
        for ($index = 0; $index < count($roomEntries); $index++)
        {
            $currentEntry = $roomEntries[$index];
            if (in_array($currentEntry['id'], $deletedIDs))
            {
                continue;
            }

            $nextIndex = $index + 1;
            $nextEntry = $roomEntries[$nextIndex];
            while ($nextEntry != false
                AND $currentEntry['longname'] == $nextEntry['longname'])
            {
                $entries = array($currentEntry, $nextEntry);
                $merged = $this->autoMerge($entries);
                if ($merged)
                {
                    $deletedIDs[] = $nextEntry['id'];
                }
                $nextIndex++;
                $nextEntry = $roomEntries[$nextIndex];
            }
        }
    }

    /**
     * Performs an automated merge of room entries, in as far as this is
     * possible according to plausibility constraints.
     *
     * @param   array  $roomEntries  entries to be compared
     *
     * @return  boolean  true on success, otherwise false
     */
    public function autoMerge($roomEntries = null)
    {
        if (empty($roomEntries))
        {
            $query = $this->_db->getQuery(true);
            $query->select('r.id, r.gpuntisID, r.name, r.longname, r.typeID');
            $query->from('#__thm_organizer_rooms AS r');

            $cids = JFactory::getApplication()->input->get('cid', array(), 'array');
            $selectedRooms = "'" . implode("', '", $cids) . "'";
            $query->where("r.id IN ( $selectedRooms )");

            $query->order('r.id ASC');

            $this->_db->setQuery((string) $query);
            
            try
            {
                $roomEntries = $this->_db->loadAssocList();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }
        }

        $data = array();
        $otherIDs = array();
        foreach ($roomEntries as $entry)
        {
            foreach ($entry as $property => $value)
            {
                // Property value is not set for DB Entry
                if (empty($value))
                {
                    continue;
                }
 
                if ($property == 'gpuntisID' OR $property == 'name')
                {
                    if (preg_match('/\.[0-9]{3}$/', $value))
                    {
                        $building = substr($value, 0, strlen($value) - 4);
                        $floor = substr($value, strlen($building) + 1, 1);
                        $room = substr($value, strlen($building) + 2, 2);
                        $value = "$building.$floor.$room";
                    }
                }
 
                // Initial set of data property
                if (!isset($data[$property]))
                {
                    $data[$property] = $value;
                }
 
                // Propery already set and a value differentiation exists => manual merge
                elseif ($data[$property] != $value)
                {
                    if ($property == 'id')
                    {
                        $otherIDs[] = $value;
                        continue;
                    }
                    if ($property == 'gpuntisID')
                    {
                        $data[$property] = str_replace('RM_', '', $data[$property]);
                        $value = str_replace('RM_', '', $value);
                        if ($data[$property] == $value)
                        {
                            continue;
                        }
                    }
                    return false;
                }
            }
        }
        $data['otherIDs'] = "'" . implode("', '", $otherIDs) . "'";
        return $this->merge($data);
    }

    /**
     * Merges resource entries and cleans association tables.
     *
     * @param   array  $data  array used by the automerge function to
     *                        automatically set room values
     *
     * @return  boolean  true on success, otherwise false
     */
    public function merge($data = null)
    {
        // Clean POST variables
        if (empty($data))
        {
            $data = JFactory::getApplication()->input->get('jform', array(), 'array');
            if (empty($data['typeID']))
            {
                unset($data['typeID']);
            }
            $data['otherIDs'] = "'" . implode("', '", explode(',', $data['otherIDs'])) . "'";
        }

        $this->_db->transactionStart();

        $eventsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'event_rooms');
        if (!$eventsSuccess)
        {
            $this->_db->transactionRollback();
            return false;
        }

        $monitorsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'monitors');
        if (!$monitorsSuccess)
        {
            $this->_db->transactionRollback();
            return false;
        }

        if (!empty($data['gpuntisID']))
        {
            $allIDs = "'{$data['id']}', " . $data['otherIDs'];
            $schedulesSuccess = $this->updateScheduleData($data, $allIDs);
            if (!$schedulesSuccess)
            {
                $this->_db->transactionRollback();
                return false;
            }
        }
 
        // Update entry with lowest ID
        $room = JTable::getInstance('rooms', 'thm_organizerTable');
        $success = $room->save($data);
        if (!$success)
        {
            $this->_db->transactionRollback();
            return false;
        }

        $deleteQuery = $this->_db->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_rooms');
        $deleteQuery->where("id IN ( {$data['otherIDs']} )");
        $this->_db->setQuery((string) $deleteQuery);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exception)
        {
            $this->_db->transactionRollback();
            return false;
        }

        $this->_db->transactionCommit();
        return true;
    }

    /**
     * Replaces old room associations
     *
     * @param   int     $newID      the id onto which the room entries merge
     * @param   string  $oldIDs     a string containing the ids to be replaced
     * @param   string  $tableName  the unique part of the table name
     *
     * @return  boolean  true on success, otherwise false
     */
    private function updateAssociation($newID, $oldIDs, $tableName)
    {
        $query = $this->_db->getQuery(true);
        $query->update("#__thm_organizer_{$tableName}");
        $query->set("roomID = '$newID'");
        $query->where("roomID IN ( $oldIDs )");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exception)
        {
            $this->_db->transactionRollback();
            return false;
        }
        return true;
    }

    /**
     * Updates room data and lesson associations in active schedules
     *
     * @param   array   &$data  room data corrresponding to a table row
     * @param   string  $IDs    a list of ids suitable for retrieval of room
     *                          gpuntisIDs to be replaced in saved schedules
     *
     * @return bool  true on success, otherwise false
     */
    public function updateScheduleData(&$data, $IDs)
    {
        if (empty($data['gpuntisID']))
        {
            return true;
        }

        $data['gpuntisID'] = $newName = str_replace('RM_', '', $data['gpuntisID']);

        $scheduleQuery = $this->_db->getQuery(true);
        $scheduleQuery->select('id, schedule');
        $scheduleQuery->from('#__thm_organizer_schedules');
        $this->_db->setQuery((string) $scheduleQuery);
        
        try
        {
            $schedules = $this->_db->loadAssocList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
        
        if (empty($schedules))
        {
            return true;
        }

        $description = '';
        if (!empty($data['typeID']))
        {
            $typeQuery = $this->_db->getQuery(true);
            $typeQuery->select('gpuntisID');
            $typeQuery->from('#__thm_organizer_room_types');
            $typeQuery->where("id = '{$data['typeID']}'");
            $this->_db->setQuery((string) $typeQuery);
            
            try 
            {
                $description .= str_replace('DS_', '', $this->_db->loadResult());
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return false;
            }
        }

        $oldNameQuery = $this->_db->getQuery(true);
        $oldNameQuery->select('gpuntisID');
        $oldNameQuery->from('#__thm_organizer_rooms');
        $oldNameQuery->where("id IN ( $IDs )");
        $oldNameQuery->where("gpuntisID IS NOT NULL");
        $this->_db->setQuery((string) $oldNameQuery);
        
        try
        {
            $oldNames = $this->_db->loadColumn();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        $scheduleTable = JTable::getInstance('schedules', 'thm_organizerTable');
        foreach ($schedules as $schedule)
        {
            $scheduleObject = json_decode($schedule['schedule']);
            $this->processSchedule($scheduleObject, $data, $oldNames, $newName, $description);
            $schedule['schedule'] = json_encode($scheduleObject);
            $success = $scheduleTable->save($schedule);
            if (!$success)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Processes the data for an individual schedule
     * 
     * @param   object  &$schedule    the schedule being processed
     * @param   array   &$data        the data for the schedule db entry
     * @param   array   &$oldNames    the deprecated room names
     * @param   string  $newName      the new name for the room
     * @param   string  $description  the room description
     * 
     * @return  void
     */
    private function processSchedule(&$schedule, &$data, &$oldNames, $newName, $description)
    {
        foreach ($oldNames AS $oldName)
        {
            $this->replaceReferences($schedule, $oldName, $newName);
        }

        if (!isset($schedule->rooms->$newName))
        {
            $schedule->rooms->$newName = new stdClass;
        }

        $schedule->rooms->$newName->gpuntisID = $newName;
        $schedule->rooms->$newName->name = $data['name'];
        $schedule->rooms->$newName->longname = $data['longname'];

        if (!empty($data['typeID']))
        {
            $schedule->rooms->$newName->typeID = $data['typeID'];
            if (!empty($description))
            {
                $schedule->rooms->$newName->description = $description;
            }
        }
    }

    /**
     * Replaces the references using the old room name
     * 
     * @param   object  &$schedule  the schedule being processed
     * @param   string  $oldName    the old name of the room
     * @param   string  $newName    the new name for the room
     * 
     * @return  void
     */
    private function replaceReferences(&$schedule, $oldName, $newName)
    {
        if (isset($schedule->rooms->$oldName))
        {
            unset($schedule->rooms->$oldName);
        }
        foreach ($schedule->calendar as $date => $blocks)
        {
            $this->processDateReferences($schedule, $date, $blocks, $oldName, $newName);
        }
    }

    /**
     * Processes the references for a single date
     * 
     * @param   object  &$schedule  the schedule being processed
     * @param   string  $date       the date being currently iterated
     * @param   object  &$blocks    the block being currently iterated
     * @param   string  $oldName    the old name of the room
     * @param   string  $newName    the new name for the room
     * 
     * @return  void
     */
    private function processDateReferences(&$schedule, $date, &$blocks, $oldName, $newName)
    {
        if (is_object($blocks))
        {
            foreach ($blocks as $block => $lessons)
            {
                $lessonIDs = array_keys((array) $lessons);
                foreach ($lessonIDs as $lessonID)
                {
                    $this->replaceRoomReference($schedule, $date, $block, $lessonID, $oldName, $newName);
                }
            }
        }
    }

    /**
     * Replaces references to a deprecated room name
     * 
     * @param   object  &$schedule  the schedule being processed
     * @param   string  $date       the date being currently iterated
     * @param   int     $block      the block being currently iterated
     * @param   int     $lessonID   the id of the lesson being currently iterated
     * @param   string  $oldName    the old name of the room
     * @param   string  $newName    the new name for the room
     * 
     * @return  void
     */
    private function replaceRoomReference(&$schedule, $date, $block, $lessonID, $oldName, $newName)
    {
        if (isset($schedule->calendar->$date->$block->$lessonID->$oldName))
        {
            $delta = $schedule->calendar->$date->$block->$lessonID->$oldName;
            unset($schedule->calendar->$date->$block->$lessonID->$oldName);
            $schedule->calendar->$date->$block->$lessonID->$newName = $delta;
        }
    }

    /**
     * Deletes room resource entries. Related entries in the event rooms table
     * are deleted automatically due to fk reference.
     *
     * @return boolean
     */
    public function delete()
    {
        return THM_OrganizerHelper::delete('rooms');
    }

    /**
     * Checks whether room nodes have the expected structure and required
     * information
     *
     * @param   object  &$scheduleModel  the validating schedule model
     * @param   object  &$roomNode       the room node to be validated
     *
     * @return void
     */
    public function validate(&$scheduleModel, &$roomNode)
    {
        $this->_scheduleModel = $scheduleModel;

        $warningString = '';
        $gpuntisID = $this->validateUntisID($roomNode, $warningString);
        if (!$gpuntisID)
        {
            return;
        }

        $roomID = str_replace('RM_', '', $gpuntisID);
        $this->_scheduleModel->schedule->rooms->$roomID = new stdClass;
        $this->_scheduleModel->schedule->rooms->$roomID->name = $roomID;
        $this->_scheduleModel->schedule->rooms->$roomID->gpuntisID = $roomID;
        $this->_scheduleModel->schedule->rooms->$roomID->localUntisID
            = str_replace('RM_', '', trim((string) $roomNode[0]['id']));

        $longname = $this->validateLongname($roomNode, $roomID);
        if (!$longname)
        {
            return;
        }

        $capacity = trim((int) $roomNode->capacity);
        $this->_scheduleModel->schedule->rooms->$roomID->capacity = (empty($capacity))? '' : $capacity;

        $this->validateDescription($roomNode, $roomID, $warningString);
        
        if (!empty($warningString))
        {
            $warning = JText::sprintf("COM_THM_ORGANIZER_ERROR_ROOM_PROPERTY_MISSING", $longname, $roomID, $warningString);
            $this->_scheduleModel->scheduleWarnings[] = $warning;
        }
    }

    /**
     * Validates the room's untis id
     * 
     * @param   object  &$roomNode       the room node object
     * @param   string  &$warningString  a string with missing fields
     * 
     * @return  mixed  string untis id if valid, otherwise false
     */
    private function validateUntisID(&$roomNode, &$warningString)
    {
        $externalID = trim((string) $roomNode->external_name);
        $internalID = trim((string) $roomNode[0]['id']);
        if (empty($internalID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_ROOM_ID_MISSING"), $this->_scheduleModel->scheduleErrors))
            {
                $this->_scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_ROOM_ID_MISSING");
            }
            return false;
        }
        if (empty($externalID))
        {
            $warningString .= empty($warningString)? '' : ', ';
            $warningString .= JText::_('COM_THM_ORGANIZER_EXTERNAL_ID');
        }
        $gpuntisID = empty($externalID)? $internalID : $externalID;
        return $gpuntisID;
    }

    /**
     * Validates the room's longname
     * 
     * @param   object  &$roomNode  the room node object
     * @param   string  $roomID     the room's id
     * 
     * @return  mixed  string longname if valid, otherwise false
     */
    private function validateLongname(&$roomNode, $roomID)
    {
        $longname = trim((string) $roomNode->longname);
        if (empty($longname))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_ERROR_ROOM_LONGNAME_MISSING', $roomID);
            return false;
        }
        $this->_scheduleModel->schedule->rooms->$roomID->longname = $longname;
        return $longname;
    }

    /**
     * Validates the room's description attribute
     * 
     * @param   object  &$roomNode       the room node object
     * @param   string  $roomID          the room's id
     * @param   string  &$warningString  a string with missing fields
     * 
     * @return  void
     */
    private function validateDescription(&$roomNode, $roomID, &$warningString)
    {
        $descriptionID = str_replace('DS_', '', trim((string) $roomNode->room_description[0]['id']));
        if (empty($descriptionID)
         OR empty($this->_scheduleModel->schedule->roomtypes->$descriptionID))
        {
            $warningString .= empty($warningString)? '' : ', ';
            $warningString .= JText::_('COM_THM_ORGANIZER_ERROR_ROOM_TYPE');
        }
        $this->_scheduleModel->schedule->rooms->$roomID->description
            = empty($descriptionID)? '' : $descriptionID;
    }
}
