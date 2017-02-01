<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerModelRoom
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'departments.php';

/**
 * Provides validation methods for xml room objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperRooms
{
	/**
	 * Checks for the room entry in the database, creating it as necessary. Adds the id to the room entry in the
	 * schedule.
	 *
	 * @param string $gpuntisID the room's gpuntis ID
	 *
	 * @return  mixed  int the id if the room could be resolved/added, otherwise null
	 */
	public static function getID($gpuntisID, $data)
	{
		$roomTable    = JTable::getInstance('rooms', 'thm_organizerTable');
		$loadCriteria = array('gpuntisID' => $gpuntisID);

		try
		{
			$success = $roomTable->load($loadCriteria);
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return null;
		}

		if ($success)
		{
			return $roomTable->id;
		}
		elseif (empty($data))
		{
			return null;
		}

		// Entry not found
		$success = $roomTable->save($data);

		return $success ? $roomTable->id : null;
	}

	/**
	 * Checks for the room name for a given room id
	 *
	 * @param string $roomID the room's id
	 *
	 * @return  mixed  string the name if the room could be resolved, otherwise null
	 */
	public static function getName($roomID)
	{
		$roomTable = JTable::getInstance('rooms', 'thm_organizerTable');

		try
		{
			$success = $roomTable->load($roomID);
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return null;
		}

		return $success ? $roomTable->longname : null;
	}

	/**
	 * Getter method for rooms in database. Only retrieving the IDs here allows for formatting the names according to
	 * the needs of the calling views.
	 *
	 * @return string  all pools in JSON format
	 *
	 * @throws RuntimeException
	 */
	public static function getPlanRooms()
	{
		$dbo           = JFactory::getDbo();
		$default       = array();
		$input         = JFactory::getApplication()->input;
		$selectedRooms = $input->get('roomIDs', array(), 'array');
		$selectedTypes = $input->get('typeIDs', array(), 'array');

		$allRoomQuery = $dbo->getQuery(true);
		$allRoomQuery->select('DISTINCT r.id, r.name, r.typeID')->from('#__thm_organizer_rooms AS r');

		if (!empty($selectedRooms))
		{
			$roomIDs = "'" . implode("', '", $selectedRooms) . "'";
			$allRoomQuery->where("r.id IN ($roomIDs)");
		}

		if (!empty($selectedTypes))
		{
			$allRoomQuery->innerJoin("#__thm_organizer_room_types AS rt ON r.typeID = rt.id");
			$typeIDs = "'" . implode("', '", $selectedTypes) . "'";
			$allRoomQuery->where("rt.id IN ($typeIDs)");
		}

		$dbo->setQuery($allRoomQuery);

		try
		{
			$allRooms = $dbo->loadAssocList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return $default;
		}

		if (empty($allRooms))
		{
			return $default;
		}

		$selectedDepartments = $input->getString('departmentIDs');
		$selectedPrograms    = $input->getString('programIDs');
		$relevantRooms       = array();

		foreach ($allRooms as $room)
		{
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

			if (!empty($selectedDepartments))
			{
				$relevanceQuery->innerJoin("#__thm_organizer_department_resources AS dr ON dr.roomID = '{$room['id']}'");
				$departmentIDs = "'" . str_replace(',', "', '", $selectedDepartments) . "'";
				$relevanceQuery->where("dr.departmentID IN ($departmentIDs)");
			}

			if (!empty($selectedPrograms))
			{
				$programIDs = "'" . str_replace(',', "', '", $selectedPrograms) . "'";
				$relevanceQuery->innerJoin('#__thm_organizer_plan_pools AS ppo ON lp.poolID = ppo.id');
				$relevanceQuery->where("ppo.programID in ($programIDs)");
			}

			$dbo->setQuery($relevanceQuery);

			try
			{
				$count = $dbo->loadResult();
			}
			catch (RuntimeException $exc)
			{
				JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

				return $default;
			}

			if (!empty($count))
			{
				$relevantRooms[$room['name']] = array('id' => $room['id'], 'typeID' => $room['typeID']);
			}
		}

		ksort($relevantRooms);

		return $relevantRooms;
	}
}
