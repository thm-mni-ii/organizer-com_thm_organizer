<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelTeacher
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
class THM_OrganizerModelTeacher extends JModel
{
	public function save()
	{
		$dbo = JFactory::getDbo();
        $data = JRequest::getVar('jform', null, null, null, 4);
		$dbo->transactionStart();
        $table = JTable::getInstance('teachers', 'thm_organizerTable');
		$success = $table->save($data);
		if ($success)
		{
			$dbo->transactionCommit();
			return true;
		}
		else
		{
			$dbo->transactionRollback();
			return false;
		}
	}

	public function autoMerge()
	{
		$dbo = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('t.id, t.gpuntisID, surname, forename, username, fieldID, field, title');
		$query->from('#__thm_organizer_teachers AS t');
		$query->leftJoin('#__thm_organizer_fields AS f ON t.fieldID = f.id');

		$cids = "'" . implode("', '", JRequest::getVar('cid', array(), 'post', 'array')) . "'";
		$query->where("t.id IN ( $cids )");

		$query->order('t.id ASC');

		$dbo->setQuery((string) $query);
		$teacherEntries = $dbo->loadAssocList();

		$data = array();
		$otherIDs = array();
		foreach ($teacherEntries as $key => $entry)
		{
			
			$entry['gpuntisID'] = str_replace('TR_', '', $entry['gpuntisID']);
			foreach ($entry as $property => $value)
			{
				// Property value is not set for DB Entry
				if (empty($value))
				{
					continue;
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
	 * @return  boolean  true on success, otherwise false
	 */
	public function merge($data = null)
	{
		// Clean POST variables
		if (empty($data))
		{
			$data['id'] = JRequest::getInt('id');
			$data['surname'] = JRequest::getString('surname');
			$data['forename'] = JRequest::getString('forename');
			$data['title'] = JRequest::getString('title');
			$data['username'] = JRequest::getString('username');
			$data['gpuntisID'] = JRequest::getString('gpuntisID');
			$data['fieldID'] = JRequest::getInt('fieldID')? JRequest::getInt('fieldID') : null;
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
		
		$subjectsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'subject');
		if (!$subjectsSuccess)
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
        $teacher = JTable::getInstance('teachers', 'thm_organizerTable');
		$success = $teacher->save($data);
		if (!$success)
		{
			$dbo->transactionRollback();
			return false;
		}

		$query = $dbo->getQuery(true);
		$query->delete('#__thm_organizer_teachers');
		$query->where("id IN ( {$data['otherIDs']} )");
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

		$dbo->transactionCommit();
		return true;
	}

	/**
	 * Replaces old teacher associations
	 * 
	 * @param   int     $newID      the id onto which the teacher entries merge
	 * @param   string  $oldIDs     a string containing the ids to be replaced
	 * @param   string  $tableName  the unique part of the table name
	 * 
	 * @return  boolean  true on success, otherwise false
	 */
	private function updateAssociation($newID, $oldIDs, $tableName)
	{
		$dbo = JFactory::getDbo();

		$query = $dbo->getQuery(true);
		$query->update("#__thm_organizer_{$tableName}_teachers");
		$query->set("teacherID = '$newID'");
		$query->where("teacherID IN ( $oldIDs )");
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

	public function updateScheduleData($data, $IDs)
	{
		$dbo = JFactory::getDbo();

		$scheduleQuery = $dbo->getQuery(true);
		$scheduleQuery->select('id, schedule');
		$scheduleQuery->from('#__thm_organizer_schedules');
		$dbo->setQuery((string) $scheduleQuery);
		$schedules = $dbo->loadAssocList();
		if (empty($schedules))
		{
			return true;
		}

		if (!empty($data['fieldID']))
		{
			$fieldQuery = $dbo->getQuery(true);
			$fieldQuery->select('gpuntisID');
			$fieldQuery->from('__thm_organizer_fields');
			$fieldQuery->where("id = '{$data['fieldID']}'");
			$dbo->setQuery((string) $fieldQuery);
			$field = str_replace('DS_', '', $dbo->loadResult());
		}

		$oldNameQuery = $dbo->getQuery(true);
		$oldNameQuery->select('gpuntisID');
		$oldNameQuery->from('#__thm_organizer_teachers');
		$oldNameQuery->where("id IN ( $IDs )");
		$oldNameQuery->where("gpuntisID IS NOT NULL");
		$oldNameQuery->where("gpuntisID NOT IN ( '', '{$data['gpuntisID']}')");
		$dbo->setQuery((string) $oldNameQuery);
		$oldNames = $dbo->loadResultArray();

		// Remove deprecated redundant resource type identification if existant
		foreach ($oldNames AS $key => $value)
		{
			$oldNames[$key] = str_replace('TR_', '', $value);
		}

		$scheduleTable = JTable::getInstance('schedules', 'thm_organizerTable');
		foreach ($schedules as $schedule)
		{
			$scheduleObject = json_decode($schedule['schedule']);

			foreach ($oldNames AS $oldName)
			{
				if (isset($scheduleObject->teachers->{$oldName}))
				{
					unset($scheduleObject->teachers->{$oldName});
				}
				foreach ($scheduleObject->lessons as $lessonID => $lesson)
				{
					if (isset($lesson->teachers->$oldName))
					{
						$delta = $lesson->teachers->$oldName;
						unset($scheduleObject->lessons->{$lessonID}->teachers->$oldName);
						$scheduleObject->lessons->{$lessonID}->teachers->{$data['gpuntisID']} = $delta;
					}
				}
			}

			if (!isset($scheduleObject->teachers->{$data['gpuntisID']}))
			{
				$scheduleObject->teachers->{$data['gpuntisID']} = new stdClass;
			}

			$scheduleObject->teachers->{$data['gpuntisID']}->gpuntisID = $data['gpuntisID'];
			$scheduleObject->teachers->{$data['gpuntisID']}->surname = $data['surname'];
			$scheduleObject->teachers->{$data['gpuntisID']}->forename = $data['forename'];
			$scheduleObject->teachers->{$data['gpuntisID']}->username = $data['username'];
			
			if (!empty($data['fieldID']))
			{
				$scheduleObject->teachers->{$data['gpuntisID']}->fieldID = $data['fieldID'];
				if (!empty($field))
				{
					$scheduleObject->teachers->{$data['gpuntisID']}->description = $field;
				}
			}
			if (isset($scheduleObject->teachers->{$data['gpuntisID']}->firstname))
			{
				unset($scheduleObject->teachers->{$data['gpuntisID']}->firstname);
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
	 * Deletes teacher resource entries
	 * 
	 * @todo add update of saved schedules
	 * 
	 * @return boolean
	 */
	public function delete()
	{
		$query = $this->_db->getQuery(true);
		$query->delete('#__thm_organizer_teachers');
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
