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
     * Attempts an iterative merge of all teacher entries. Due to the attempted
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
        for($index = 0; $index < count($roomEntries); $index++)
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
		foreach ($roomEntries as $key => $entry)
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

		$eventsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'event');
		if (!$eventsSuccess)
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
		$query->update("#__thm_organizer_{$tableName}_rooms");
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
                            foreach ($lessons as $lesson => $rooms)
                            {
                                if (isset($scheduleObject->calendar->$date->$block->$lesson->$oldName))
                                {
                                    $delta = $scheduleObject->calendar->$date->$block->$lesson->$oldName;
                                    unset($scheduleObject->calendar->$date->$block->$lesson->$oldName);
                                    $scheduleObject->calendar->$date->$block->$lesson->{$data['gpuntisID']} = $delta;
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
	 * @todo check saved schedules for reference and block delete
	 * 
	 * @return boolean
	 */
	public function delete()
	{
		$query = $this->_db->getQuery(true);
		$query->delete('#__thm_organizer_rooms');
		$cids = "'" . implode("', '", JRequest::getVar('cid', array(), 'post', 'array')) . "'";
		$query->where("id IN ( $cids )");
		$this->_db->setQuery((string) $query);
		try
		{
			$this->_db->query();
			return true;
		}
		catch ( Exception $exception)
		{
			return false;
		}
	}
}
