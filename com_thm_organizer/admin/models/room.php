<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelRoom
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT . DS . 'assets' . DS . 'helpers' . DS . 'thm_organizerHelper.php';

/**
 * Class THM_OrganizerModelLecturer for component com_thm_organizer
 *
 * Class provides methods to deal with lecturer
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelRoom extends JModel
{
    private $_scheduleModel = null;

    /**
     * Attempts to save a room entry, updating schedule data as necessary.
     *
     * @return true on success, otherwise false
     */
    public function save()
    {
        $dbo = JFactory::getDbo();
        $data = JRequest::getVar('jform', null, null, null, 4);
        $dbo->transactionStart();
        $scheduleSuccess = $this->updateScheduleData($data, "'" . $data['id'] . "'");
        if ($scheduleSuccess)
        {
            $table = JTable::getInstance('rooms', 'thm_organizerTable');
            $roomSuccess = $table->save($data);
            if ($roomSuccess)
            {
                $dbo->transactionCommit();
                return true;
            }
        }
        $dbo->transactionRollback();
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
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('*')->from('#__thm_organizer_rooms')->order('longname, id ASC');
        $dbo->setQuery((string) $query);
        $roomEntries = $dbo->loadAssocList();

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
            $dbo = JFactory::getDbo();
            $query = $dbo->getQuery(true);
            $query->select('r.id, r.gpuntisID, r.name, r.longname, r.typeID');
            $query->from('#__thm_organizer_rooms AS r');

            $cids = "'" . implode("', '", JRequest::getVar('cid', array(), 'post', 'array')) . "'";
            $query->where("r.id IN ( $cids )");

            $query->order('r.id ASC');

            $dbo->setQuery((string) $query);
            $roomEntries = $dbo->loadAssocList();
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
            $data['id'] = JRequest::getInt('id');
            $data['name'] = JRequest::getString('name');
            $data['longname'] = JRequest::getString('longname');
            $data['gpuntisID'] = JRequest::getString('gpuntisID');
            $data['typeID'] = JRequest::getInt('typeID')? JRequest::getInt('typeID') : null;
            $data['otherIDs'] = "'" . implode("', '", explode(',', JRequest::getString('otherIDs'))) . "'";
        }

        $dbo = JFactory::getDbo();
        $dbo->transactionStart();

        $eventsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'event_rooms');
        if (!$eventsSuccess)
        {
            $dbo->transactionRollback();
            return false;
        }

        $monitorsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'monitors');
        if (!$monitorsSuccess)
        {
            $dbo->transactionRollback();
            return false;
        }

        if (!empty($data['gpuntisID']))
        {
            $allIDs = "'{$data['id']}', " . $data['otherIDs'];
            $schedulesSuccess = $this->updateScheduleData($data, $allIDs);
            if (!$schedulesSuccess)
            {
                $dbo->transactionRollback();
                return false;
            }
        }
 
        // Update entry with lowest ID
        $room = JTable::getInstance('rooms', 'thm_organizerTable');
        $success = $room->save($data);
        if (!$success)
        {
            $dbo->transactionRollback();
            return false;
        }

        $deleteQuery = $dbo->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_rooms');
        $deleteQuery->where("id IN ( {$data['otherIDs']} )");
        $dbo->setQuery((string) $deleteQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            $dbo->transactionRollback();
            return false;
        }

        $dbo->transactionCommit();
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
        $dbo = JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->update("#__thm_organizer_{$tableName}");
        $query->set("roomID = '$newID'");
        $query->where("roomID IN ( $oldIDs )");
        $dbo->setQuery((string) $query);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            $dbo->transactionRollback();
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
        $dbo = JFactory::getDbo();

        if (empty($data['gpuntisID']))
        {
            return true;
        }
        else
        {
            $data['gpuntisID'] = str_replace('RM_', '', $data['gpuntisID']);
        }

        $scheduleQuery = $dbo->getQuery(true);
        $scheduleQuery->select('id, schedule');
        $scheduleQuery->from('#__thm_organizer_schedules');
        $dbo->setQuery((string) $scheduleQuery);
        $schedules = $dbo->loadAssocList();
        if (empty($schedules))
        {
            return true;
        }

        if (!empty($data['typeID']))
        {
            $typeQuery = $dbo->getQuery(true);
            $typeQuery->select('gpuntisID');
            $typeQuery->from('__thm_organizer_room_types');
            $typeQuery->where("id = '{$data['typeID']}'");
            $dbo->setQuery((string) $typeQuery);
            $type = str_replace('DS_', '', $dbo->loadResult());
        }

        $oldNameQuery = $dbo->getQuery(true);
        $oldNameQuery->select('gpuntisID');
        $oldNameQuery->from('#__thm_organizer_rooms');
        $oldNameQuery->where("id IN ( $IDs )");
        $oldNameQuery->where("gpuntisID IS NOT NULL");
        $dbo->setQuery((string) $oldNameQuery);
        $oldNames = $dbo->loadResultArray();

        $scheduleTable = JTable::getInstance('schedules', 'thm_organizerTable');
        foreach ($schedules as $schedule)
        {
            $scheduleObject = json_decode($schedule['schedule']);

            foreach ($oldNames AS $oldName)
            {
                if (isset($scheduleObject->rooms->{$oldName}))
                {
                    unset($scheduleObject->rooms->{$oldName});
                }
                foreach ($scheduleObject->calendar as $date => $blocks)
                {
                    if (is_object($blocks))
                    {
                        foreach ($blocks as $block => $lessons)
                        {
                            $lessonIDs = array_keys((array) $lessons);
                            foreach ($lessonIDs as $lessonID)
                            {
                                if (isset($scheduleObject->calendar->$date->$block->$lessonID->$oldName))
                                {
                                    $delta = $scheduleObject->calendar->$date->$block->$lessonID->$oldName;
                                    unset($scheduleObject->calendar->$date->$block->$lessonID->$oldName);
                                    $scheduleObject->calendar->$date->$block->$lessonID->{$data['gpuntisID']} = $delta;
                                }
                            }
                        }
                    }
                }
            }

            if (!isset($scheduleObject->rooms->{$data['gpuntisID']}))
            {
                $scheduleObject->rooms->{$data['gpuntisID']} = new stdClass;
            }

            $scheduleObject->rooms->{$data['gpuntisID']}->gpuntisID = $data['gpuntisID'];
            $scheduleObject->rooms->{$data['gpuntisID']}->name = $data['name'];
            $scheduleObject->rooms->{$data['gpuntisID']}->longname = $data['longname'];
 
            if (!empty($data['typeID']))
            {
                $scheduleObject->rooms->{$data['gpuntisID']}->typeID = $data['typeID'];
                if (!empty($type))
                {
                    $scheduleObject->rooms->{$data['gpuntisID']}->description = $type;
                }
            }

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
            $warning = JText::sprintf("COM_THM_ORGANIZER_RM_FIELD_MISSING", $longname, $roomID, $warningString);
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
            if (!in_array(JText::_("COM_THM_ORGANIZER_RM_ID_MISSING"), $this->_scheduleModel->scheduleErrors))
            {
                $this->_scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_RM_ID_MISSING");
            }
            return false;
        }
        if (empty($externalID))
        {
            $warningString .= empty($warningString)? '' : ', ';
            $warningString .= JText::_('COM_THM_ORGANIZER_EXTERNALID');
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
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_RM_LN_MISSING', $roomID);
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
            $warningString .= JText::_('COM_THM_ORGANIZER_DESCRIPTION_PROPERTY');
        }
        $this->_scheduleModel->schedule->rooms->$roomID->description
            = empty($descriptionID)? '' : $descriptionID;
    }
}
