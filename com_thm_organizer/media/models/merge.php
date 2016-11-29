<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerModelMerge
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class provides methods for database abstraction for mergeable resources
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
abstract class THM_OrganizerModelMerge extends JModelLegacy
{
	/**
	 * Performs an automated merge of field entries, in as far as this is possible according to plausibility constraints.
	 *
	 * @param string $resource the resource type being merged
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	public function autoMerge($resource)
	{
		$entries = $this->getEntries("{$resource}s");

		$keyProperties = array('gpuntisID');
		if ($resource == 'teacher')
		{
			$keyProperties[] = 'username';
		}

		$data     = array();
		$otherIDs = array();

		foreach ($entries as $entry)
		{
			if (empty($data['id']))
			{
				$data['id'] = $entry['id'];
			}
			else
			{
				$otherIDs[] = $entry['id'];
			}

			foreach ($entry as $property => $value)
			{
				if ($property == 'id')
				{
					continue;
				}

				$value = trim($value);

				if (empty($value))
				{
					continue;
				}

				if (empty($data[$property]))
				{
					$data[$property] = $value;
					continue;
				}

				if ($data[$property] == $value)
				{
					continue;
				}

				// Differing key property or numerical values => auto merge impossible
				$isKeyProperty = in_array($property, $keyProperties);
				if ($isKeyProperty OR is_int($value))
				{
					return false;
				}

				$leftInRight = (strpos($value, $data[$property]) !== false AND strlen($value) > strlen($data[$property]));
				if ($leftInRight)
				{
					$data[$property] = $value;
					continue;
				}

				$rightInLeft = (strpos($data[$property], $value) !== false AND strlen($data[$property]) > strlen($value));
				if ($rightInLeft)
				{
					$data[$property] = $value;
					continue;
				}

				// string values are incompatible => auto merge impossible
				return false;
			}
		}

		// The "otherIDs" are expected as a comma separated string.
		$data['otherIDs'] = implode(",", $otherIDs);

		return $this->merge($resource, $data);
	}

	/**
	 * Attempts to delete resource entries
	 *
	 * @param string $resource the name of the resource type being deleted
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	public function delete($resource)
	{
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Should not occur
		if (empty($cids))
		{
			return false;
		}

		$table = JTable::getInstance("{$resource}s", 'thm_organizerTable');

		foreach ($cids as $resourceID)
		{
			try
			{
				$table->load($resourceID);
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
				$this->_db->transactionRollback();

				return false;
			}

			$this->_db->transactionStart();

			$removed = $this->removeFromSchedules($resourceID, $table->gpuntisID);
			if (!$removed)
			{
				$this->_db->transactionRollback();
				continue;
			}

			// No need to update associations, handled by foreign keys.

			try
			{
				$table->delete($resourceID);
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
				$this->_db->transactionRollback();

				return false;
			}

			$this->_db->transactionCommit();
		}

		return true;
	}

	/**
	 * Method to get all gpuntis IDs for the resources to be merged
	 *
	 * @param string $resource the name of the resource
	 * @param array  $allDBIDs all of the resource db entry ids
	 *
	 * @return  mixed  array on success, otherwise null
	 */
	protected function getAllGPUntisIDs($resource, $allDBIDs)
	{
		$idString = "'" . implode("', '", $allDBIDs) . "'";
		$query    = $this->_db->getQuery(true);
		$query->select('gpuntisID')->from("#__thm_organizer_{$resource}")->where("id in ( $idString )");
		$this->_db->setQuery((string) $query);
		try
		{
			return $this->_db->loadColumn();
		}
		catch (Exception $exc)
		{
			return null;
		}
	}

	/**
	 * Method to get all gpuntis IDs for the resources to be merged
	 *
	 * @param string $tableName the unique portion of the database table with the appropriate 'description' entries
	 * @param array  $dbID      the id of the resource entry
	 *
	 * @return  mixed  array on success, otherwise null
	 */
	protected function getDescriptionGPUntisID($tableName, $dbID)
	{
		$query = $this->_db->getQuery(true);
		$query->select('gpuntisID')->from("#__thm_organizer_{$tableName}")->where("id = '$dbID'");
		$this->_db->setQuery((string) $query);

		try
		{
			return $this->_db->loadResult();
		}
		catch (Exception $exc)
		{
			return null;
		}
	}

	/**
	 * Retrieves resource entries from the database
	 *
	 * @param string $tableName    the unique portion of the resource table name
	 * @param bool   $onlySelected whether or not to retrieve all entries
	 *
	 * @return  mixed  array on success, otherwise null
	 */
	protected function getEntries($tableName, $onlySelected = true)
	{
		$query = $this->_db->getQuery(true);
		$query->select('*');
		$query->from("#__thm_organizer_$tableName");

		if ($onlySelected)
		{
			$requestIDs = JFactory::getApplication()->input->get('cid', array(), 'array');
			$normedIDs  = Joomla\Utilities\ArrayHelper::toInteger($requestIDs);
			$selected   = "'" . implode("', '", $normedIDs) . "'";
			$query->where("id IN ( $selected )");
		}

		$query->order('id ASC');

		$this->_db->setQuery((string) $query);

		try
		{
			return $this->_db->loadAssocList();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return null;
		}
	}

	/**
	 * Retrieves the ids of all saved schedules
	 *
	 * @return  mixed  array on success, otherwise null
	 */
	protected function getAllSchedulesIDs()
	{
		$query = $this->_db->getQuery(true);
		$query->select('id');
		$query->from('#__thm_organizer_schedules');
		$this->_db->setQuery((string) $query);

		try
		{
			return $this->_db->loadColumn();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return null;
		}
	}

	/**
	 * Retrieves the (old format) schedule for the given id.
	 *
	 * @param int $scheduleID the id of the schedule
	 *
	 * @return  mixed  object on success, otherwise null
	 */
	protected function getOldScheduleObject($scheduleID)
	{
		$query = $this->_db->getQuery(true);
		$query->select('schedule');
		$query->from('#__thm_organizer_schedules');
		$query->where("id = '$scheduleID'");
		$this->_db->setQuery((string) $query);

		try
		{
			$schedule = $this->_db->loadResult();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return null;
		}

		return empty($schedule) ? null : json_decode($schedule);
	}

	/**
	 * Retrieves the schedule for the given id.
	 *
	 * @param int $scheduleID the id of the schedule
	 *
	 * @return  mixed  object on success, otherwise null
	 */
	protected function getScheduleObject($scheduleID)
	{
		$query = $this->_db->getQuery(true);
		$query->select('newSchedule');
		$query->from('#__thm_organizer_schedules');
		$query->where("id = '$scheduleID'");
		$this->_db->setQuery((string) $query);

		try
		{
			$schedule = $this->_db->loadResult();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return null;
		}

		return empty($schedule) ? null : json_decode($schedule);
	}

	/**
	 * Merges resource entries and cleans association tables.
	 *
	 * @param string $resource the name of the resource type
	 * @param array  $data     the data when called from auto merge
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	public function merge($resource, $data = null)
	{
		$data = empty($data) ? JFactory::getApplication()->input->get('jform', array(), 'array') : $data;

		$invalidForm = (empty($data['id']) OR empty($data['gpuntisID']));
		if ($invalidForm)
		{
			return false;
		}

		$newDBID  = $data['id'];
		$oldDBIDs = explode(',', $data['otherIDs']);

		$this->_db->transactionStart();

		$associationsUpdated = $this->updateAssociations($newDBID, $oldDBIDs);
		if (!$associationsUpdated)
		{
			$this->_db->transactionRollback();

			return false;
		}

		$allDBIDs      = array_merge(array($newDBID), $oldDBIDs);
		$newGPUntisID  = $data['gpuntisID'];
		$tableName     = "{$resource}s";
		$allGPUntisIDs = $this->getAllGPUntisIDs($tableName, $allDBIDs);

		$schedulesSuccess = $this->updateSchedules($newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs, $data);
		if (!$schedulesSuccess)
		{
			$this->_db->transactionRollback();

			return false;
		}

		// Update entry with ID from form (lowest)
		$resourceTable = JTable::getInstance($tableName, 'thm_organizerTable');

		foreach ($oldDBIDs as $oldDBID)
		{
			$deleted = $resourceTable->delete($oldDBID);
			if (!$deleted)
			{
				$this->_db->transactionRollback();

				return false;
			}
		}

		$success = $resourceTable->save($data);
		if (!$success)
		{
			$this->_db->transactionRollback();

			return false;
		}

		$this->_db->transactionCommit();

		return true;
	}

	/**
	 * Removes the resource from the schedule
	 *
	 * @param object &$schedule  the schedule from which the resource will be removed
	 * @param int    $resourceID the id of the resource in the db
	 * @param string $gpuntisID  the gpuntis ID for the given resource
	 *
	 * @return  void  modifies the schedule
	 */
	protected abstract function removeFromSchedule(&$schedule, $resourceID, $gpuntisID);

	/**
	 * Removes the resource from the schedule
	 *
	 * @param int    $resourceID the id of the resource in the db
	 * @param string $gpuntisID  the gpuntis ID for the given resource
	 *
	 * @return  bool  true on success, otherwise false
	 */
	protected function removeFromSchedules($resourceID, $gpuntisID)
	{
		$scheduleIDs = $this->getAllSchedulesIDs();
		if (empty($scheduleIDs))
		{
			return true;
		}

		$scheduleTable = JTable::getInstance('schedules', 'thm_organizerTable');
		foreach ($scheduleIDs as $scheduleID)
		{
			$scheduleObject = $this->getOldScheduleObject($scheduleID);
			if (empty($scheduleObject))
			{
				continue;
			}

			$this->removeFromSchedule($scheduleObject, $resourceID, $gpuntisID);
			$tableData['id']       = $scheduleID;
			$tableData['schedule'] = json_encode($scheduleObject);
			$success               = $scheduleTable->save($tableData);
			if (!$success)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Attempts to save a resource entry, updating schedule data as necessary.
	 *
	 * @param string $resource the name of the resource type being merged
	 *
	 * @return  mixed  integer on success, otherwise false
	 */
	public function save($resource)
	{
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

		if (empty($data['gpuntisID']))
		{
			return false;
		}

		$this->_db->transactionStart();

		$table = JTable::getInstance("{$resource}s", 'thm_organizerTable');
		if (!empty($data['id']))
		{
			$table->load($data['id']);

			$gpuntisIDs                     = array();
			$gpuntisIDs[$data['gpuntisID']] = $data['gpuntisID'];
			$gpuntisIDs[$table->gpuntisID]  = $table->gpuntisID;

			$schedulesUpdated = $this->updateSchedules($data['id'], $data['gpuntisID'], $gpuntisIDs, array($data['id']), $data);
			if (!$schedulesUpdated)
			{
				$this->_db->transactionRollback();

				return false;
			}
		}

		// No need to update associations. New entries have no associations. Existing entries keep their ids.

		$success = $table->save($data);
		if ($success)
		{
			$this->_db->transactionCommit();

			return $table->id;
		}
		$this->_db->transactionRollback();

		return false;
	}

	/**
	 * Replaces old room associations
	 *
	 * @param string $resource  the name of the resource type being merged
	 * @param int    $newDBID   the id onto which the room entries merge
	 * @param string $oldDBIDs  a string containing the ids to be replaced
	 * @param string $tableName the unique part of the table name
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	protected function updateAssociation($resource, $newDBID, $oldDBIDs, $tableName)
	{
		$oldDBIDString = "'" . implode("', '", $oldDBIDs) . "'";

		$query = $this->_db->getQuery(true);
		$query->update("#__thm_organizer_{$tableName}");
		$query->set("{$resource}ID = '$newDBID'");
		$query->where("{$resource}ID IN ( $oldDBIDString )");
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
	 * Replaces old room associations
	 *
	 * @param int    $newDBID  the id onto which the room entries merge
	 * @param string $oldDBIDs a string containing the ids to be replaced
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	protected abstract function updateAssociations($newDBID, $oldDBIDs);

	/**
	 * Updates department resource associations
	 *
	 * @param string $resource the name of the resource type being merged
	 * @param int    $newDBID  the id onto which the room entries merge
	 * @param string $oldDBIDs a string containing the ids to be replaced
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	protected function updateDRAssociation($resource, $newDBID, $oldDBIDs)
	{
		$oldIDString = "'" . implode("', '", $oldDBIDs) . "'";

		$allIDString = "'$newDBID', $oldIDString";
		$departmentQuery = $this->_db->getQuery(true);
		$departmentQuery->select("DISTINCT departmentID");
		$departmentQuery->from("#__thm_organizer_department_resources");
		$departmentQuery->where("{$resource}ID IN ( $allIDString )");
		$this->_db->setQuery((string) $departmentQuery);

		try
		{
			$allDeptAssociations = $this->_db->loadColumn();
		}
		catch (Exception $exception)
		{
			$this->_db->transactionRollback();

			return false;
		}

		// This should not be able to occur
		if (empty($allDeptAssociations))
		{
			return true;
		}

		// Remove entries that have been merged out
		$deleteQuery = $this->_db->getQuery(true);
		$deleteQuery->delete("#__thm_organizer_department_resources");
		$deleteQuery->where("{$resource}ID IN ( $oldIDString )");
		$this->_db->setQuery($deleteQuery);

		try
		{
			$this->_db->execute();
		}
		catch (Exception $exception)
		{
			$this->_db->transactionRollback();

			return false;
		}

		// Rerun the dept query to find the departments that remain
		$this->_db->setQuery((string) $departmentQuery);

		try
		{
			$remainingDeptAssociations = $this->_db->loadColumn();
		}
		catch (Exception $exception)
		{
			$this->_db->transactionRollback();

			return false;
		}

		// Should not occur
		if (empty($remainingDeptAssociations))
		{
			$this->_db->transactionRollback();

			return false;
		}

		// Find and readd any department associations that were lost
		$missingDeptAssociations = array_diff($allDeptAssociations, $remainingDeptAssociations);

		if (!empty($missingDeptAssociations))
		{
			foreach ($missingDeptAssociations as $departmentID)
			{
				$insertQuery = $this->_db->getQuery(true);
				$insertQuery->insert("#__thm_organizer_department_resources");
				$insertQuery->columns("departmentID, {$resource}ID");
				$insertQuery->values("'$departmentID', '$newDBID'");
				$this->_db->setQuery($insertQuery);

				try
				{
					$this->_db->execute();
				}
				catch (Exception $exception)
				{
					$this->_db->transactionRollback();

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Processes the data for an individual schedule
	 *
	 * @param object &$schedule     the schedule being processed
	 * @param array  &$data         the data for the schedule db entry
	 * @param int    $newDBID       the new id to use for the merged resource in the database (and schedules)
	 * @param string $newGPUntisID  the new gpuntis ID to use for the merged resource in the schedule
	 * @param array  $allGPUntisIDs all gpuntis IDs for the resources to be merged
	 * @param array  $allDBIDs      all db IDs for the resources to be merged
	 *
	 * @return  void
	 */
	protected abstract function updateOldSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs);

	/**
	 * Processes the data for an individual schedule
	 *
	 * @param object &$schedule     the schedule being processed
	 * @param array  &$data         the data for the schedule db entry
	 * @param int    $newDBID       the new id to use for the merged resource in the database (and schedules)
	 * @param string $newGPUntisID  the new gpuntis ID to use for the merged resource in the schedule
	 * @param array  $allGPUntisIDs all gpuntis IDs for the resources to be merged
	 * @param array  $allDBIDs      all db IDs for the resources to be merged
	 *
	 * @return  void
	 */
	protected abstract function updateSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs);

	/**
	 * Updates room data and lesson associations in active schedules
	 *
	 * @param int    $newDBID       the new id to use for the merged resource in the database (and schedules)
	 * @param string $newGPUntisID  the new gpuntis ID to use for the merged resource in the schedule
	 * @param array  $allGPUntisIDs all gpuntis IDs for the resources to be merged
	 * @param array  $allDBIDs      all db IDs for the resources to be merged
	 * @param array  $data          the data for the schedule db entry
	 *
	 * @return bool  true on success, otherwise false
	 */
	public function updateSchedules($newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs, $data = array())
	{
		$scheduleIDs = $this->getAllSchedulesIDs();
		if (empty($scheduleIDs))
		{
			return true;
		}

		$scheduleTable = JTable::getInstance('schedules', 'thm_organizerTable');
		foreach ($scheduleIDs as $scheduleID)
		{
			$oldScheduleObject = $this->getOldScheduleObject($scheduleID);
			if (empty($oldScheduleObject))
			{
				continue;
			}

			$this->updateOldSchedule($oldScheduleObject, $data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs);

			$tableData             = array();
			$tableData['id']       = $scheduleID;
			$tableData['schedule'] = json_encode($oldScheduleObject);

			$scheduleObject = $this->getScheduleObject($scheduleID);
			if (!empty($scheduleObject))
			{
				$this->updateSchedule($scheduleObject, $data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs);
				$tableData['newSchedule'] = json_encode($scheduleObject);
			}

			$success = $scheduleTable->save($tableData);
			if (!$success)
			{
				return false;
			}
		}

		return true;
	}
}
