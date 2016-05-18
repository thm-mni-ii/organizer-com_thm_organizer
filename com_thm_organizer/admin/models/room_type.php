<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelRoom_Type
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class THM_OrganizerModelRoom_Type for component com_thm_organizer
 * Class provides methods to deal with room type
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelRoom_Type extends JModelLegacy
{
    /**
     * Removes room type entries from the database
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        return THM_OrganizerHelper::delete('room_types');
    }

    /**
     * Merges resource entries and cleans association tables.
     *
     * @param   array  $data  array used by the automerge function to
     *                        automatically set room values
     *
     * @return  boolean  true on success, otherwise false
     */
    public function merge()
    {
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');

        $this->_db->transactionStart();

        $roomsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'rooms');
        if (!$roomsSuccess)
        {
            $this->_db->transactionRollback();
            return false;
        }

        $planRoomsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'plan_rooms');
        if (!$planRoomsSuccess)
        {
            $this->_db->transactionRollback();
            return false;
        }

        $oldIDs = "'" . implode("', '", explode(',', $data['otherIDs'])) . "'";
        $schedulesSuccess = $this->updateScheduleData($data, $oldIDs);
        if (!$schedulesSuccess)
        {
            $this->_db->transactionRollback();
            return false;
        }

        $deleteQuery = $this->_db->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_room_types');
        $deleteQuery->where("id IN ( $oldIDs )");
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

        // Update entry with lowest ID
        $room_type = JTable::getInstance('room_types', 'thm_organizerTable');
        $success = $room_type->save($data);
        if (!$success)
        {
            $this->_db->transactionRollback();
            return false;
        }

        $this->_db->transactionCommit();
        return true;
    }

    /**
     * Processes the data for an individual schedule
     *
     * @param   object  &$schedule    the schedule being processed
     * @param   array   &$data        the data for the schedule db entry
     * @param   array   $oldUntisIDs  the existing Untis IDs
     *
     * @return  void
     */
    private function processSchedule(&$schedule, &$data, $oldUntisIDs)
    {
        // Remove deprecated objects
        foreach ($oldUntisIDs AS $untisID)
        {
            if (isset($schedule->roomtypes->$untisID))
            {
                unset($schedule->roomtypes->$untisID);
            }
        }

        $newUntisID = $data['gpuntisID'];
        if (!isset($schedule->roomtypes->$newUntisID))
        {
            $schedule->roomtypes->$newUntisID = new stdClass;
            $schedule->roomtypes->$newUntisID->gpuntisID = $newUntisID;
        }

        $schedule->roomtypes->$newUntisID->name = $data['name_de'];

        // Update room associations
        foreach ($schedule->rooms AS $roomNo => $room)
        {
            $update = (in_array($room->description, $oldUntisIDs) OR $room->description == $newUntisID);
            if ($update)
            {
                $schedule->rooms->$roomNo->description = $newUntisID;
                $schedule->rooms->$roomNo->typeID = $data['id'];
            }
        }
    }

    /**
     * Saves room type data
     *
     * @return  mixed  int id value on success, otherwise false
     */
    public function save()
    {
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');

        if (!empty($data['min_capacity']) AND $data['min_capacity'] == '-1')
        {
            unset($data['min_capacity']);
        }

        if (!empty($data['max_capacity']) AND $data['max_capacity'] == '-1')
        {
            unset($data['max_capacity']);
        }

        $schedulesUpdated = $this->updateScheduleData($data);
        if (!$schedulesUpdated)
        {
            return false;
        }

        $table = JTable::getInstance('room_types', 'thm_organizerTable');
        $success = $table->save($data);
        return $success? $table->id : false;
    }

    /**
     * Replaces old room type associations
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
        $query->set("typeID = '$newID'");
        $query->where("typeID IN ( $oldIDs )");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return false;
        }
        return true;
    }

    /**
     * Updates room data and lesson associations in active schedules
     *
     * @param   array  &$data   room type data corresponding to a table row
     * @param   mixed  $oldIDs  the Untis IDs to be replaced as a string or null for updates
     *
     * @return bool  true on success, otherwise false
     */
    public function updateScheduleData(&$data, $oldIDs = null)
    {
        $scheduleQuery = $this->_db->getQuery(true);
        $scheduleQuery->select('id');
        $scheduleQuery->from('#__thm_organizer_schedules');
        $this->_db->setQuery((string) $scheduleQuery);

        try
        {
            $scheduleIDs = $this->_db->loadColumn();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return false;
        }

        if (empty($scheduleIDs))
        {
            return true;
        }

        if (!empty($oldIDs))
        {
            $oldUntisIDsQuery = $this->_db->getQuery(true);
            $oldUntisIDsQuery->select('gpuntisID');
            $oldUntisIDsQuery->from('#__thm_organizer_room_types');
            $oldUntisIDsQuery->where("id IN ( $oldIDs )");
            $oldUntisIDsQuery->where("gpuntisID IS NOT NULL");
            $this->_db->setQuery((string) $oldUntisIDsQuery);

            try
            {
                $oldUntisIDs = $this->_db->loadColumn();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
                return false;
            }
        }

        if (empty($oldUntisIDs))
        {
            $oldUntisIDs = array();
        }

        $scheduleTable = JTable::getInstance('schedules', 'thm_organizerTable');
        foreach ($scheduleIDs as $scheduleID)
        {
            $scheduleQuery = $this->_db->getQuery(true);
            $scheduleQuery->select('schedule');
            $scheduleQuery->from('#__thm_organizer_schedules');
            $scheduleQuery->where("id = '$scheduleID'");
            $this->_db->setQuery((string) $scheduleQuery);

            try
            {
                $schedule = $this->_db->loadResult();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return false;
            }

            $scheduleObject = json_decode($schedule);
            $this->processSchedule($scheduleObject, $data, $oldUntisIDs);
            $tableData['id'] = $scheduleID;
            $tableData['schedule'] = json_encode($scheduleObject);
            $success = $scheduleTable->save($tableData);
            if (!$success)
            {
                return false;
            }
        }

        return true;
    }
}
