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

require_once 'schedule_resource.php';

/**
 * Provides validation methods for xml room objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperXMLRooms
{
	/**
	 * Checks for the room entry in the database, creating it as necessary. Adds the id to the room entry in the
	 * schedule.
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   string $roomID         the room's gpuntis ID
	 *
	 * @return  void  sets the id if the room could be resolved/added
	 */
	private static function setID(&$scheduleModel, $roomID)
	{
		$roomTable    = JTable::getInstance('rooms', 'thm_organizerTable');
		$roomData     = $scheduleModel->schedule->rooms->$roomID;
		$loadCriteria = array('gpuntisID' => $roomData->gpuntisID);

		try
		{
			$success = $roomTable->load($loadCriteria);
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return;
		}

		if ($success)
		{
			$scheduleModel->schedule->rooms->$roomID->id = $roomTable->id;

			return;
		}

		// Entry not found
		$success = $roomTable->save($roomData);
		if ($success)
		{
			$scheduleModel->schedule->rooms->$roomID->id = $roomTable->id;
		}
	}

	/**
	 * Validates the rooms node
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$xmlObject     the xml object being validated
	 *
	 * @return  void
	 */
	public static function validate(&$scheduleModel, &$xmlObject)
	{
		if (empty($xmlObject->rooms))
		{
			$scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_ROOMS_MISSING");

			return;
		}

		$scheduleModel->schedule->rooms = new stdClass;

		foreach ($xmlObject->rooms->children() as $resourceNode)
		{
			self::validateIndividual($scheduleModel, $resourceNode);
		}

		if (!empty($scheduleModel->scheduleWarnings['ROOM-EXTERNALID']))
		{
			$warningCount = $scheduleModel->scheduleWarnings['ROOM-EXTERNALID'];
			unset($scheduleModel->scheduleWarnings['ROOM-EXTERNALID']);
			$scheduleModel->scheduleWarnings[] = JText::sprintf('COM_THM_ORGANIZER_WARNING_ROOM_EXTID_MISSING', $warningCount);
		}

		if (!empty($scheduleModel->scheduleWarnings['ROOM-TYPE']))
		{
			$warningCount = $scheduleModel->scheduleWarnings['ROOM-TYPE'];
			unset($scheduleModel->scheduleWarnings['ROOM-TYPE']);
			$scheduleModel->scheduleWarnings[] = JText::sprintf('COM_THM_ORGANIZER_WARNING_TYPE_MISSING', $warningCount);
		}

		unset($scheduleModel->schedule->room_types);

		return;
	}

	/**
	 * Validates the room's display name
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$roomNode      the room node object
	 * @param   string $roomID         the room's id
	 *
	 * @return  mixed  string display name if valid, otherwise false
	 */
	private static function validateDisplayName(&$scheduleModel, &$roomNode, $roomID)
	{
		$displayName = trim((string) $roomNode->longname);
		if (empty($displayName))
		{
			$scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_ERROR_ROOM_DISPLAY_NAME_MISSING', $roomID);

			return false;
		}

		$scheduleModel->schedule->rooms->$roomID->longname = $displayName;

		return $displayName;
	}

	/**
	 * Checks whether room nodes have the expected structure and required
	 * information
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$roomNode      the room node to be validated
	 *
	 * @return void
	 */
	public static function validateIndividual(&$scheduleModel, &$roomNode)
	{
		$gpuntisID = self::validateUntisID($scheduleModel, $roomNode);
		if (!$gpuntisID)
		{
			return;
		}

		$roomID                                             = str_replace('RM_', '', $gpuntisID);
		$scheduleModel->schedule->rooms->$roomID            = new stdClass;
		$scheduleModel->schedule->rooms->$roomID->name      = $roomID;
		$scheduleModel->schedule->rooms->$roomID->gpuntisID = $roomID;
		$scheduleModel->schedule->rooms->$roomID->localUntisID
		                                                    = str_replace('RM_', '', trim((string) $roomNode[0]['id']));

		$displayName = self::validateDisplayName($scheduleModel, $roomNode, $roomID);
		if (!$displayName)
		{
			return;
		}

		$capacity                                          = trim((int) $roomNode->capacity);
		$scheduleModel->schedule->rooms->$roomID->capacity = (empty($capacity)) ? '' : $capacity;

		self::validateType($scheduleModel, $roomNode, $roomID);
		self::setID($scheduleModel, $roomID);

		if (!empty($scheduleModel->schedule->rooms->$roomID->id))
		{
			THM_OrganizerHelperXMLSchedule_Resource::setDepartmentResource($scheduleModel->schedule->rooms->$roomID->id, 'roomID');
		}
	}

	/**
	 * Validates the room's description attribute
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$roomNode      the room node object
	 * @param   string $roomID         the room's id
	 *
	 * @return  void
	 */
	private static function validateType(&$scheduleModel, &$roomNode, $roomID)
	{
		$descriptionID      = str_replace('DS_', '', trim((string) $roomNode->room_description[0]['id']));
		$invalidDescription = (empty($descriptionID) OR empty($scheduleModel->schedule->room_types->$descriptionID));
		if ($invalidDescription)
		{
			$scheduleModel->scheduleWarnings['ROOM-TYPE']         = empty($scheduleModel->scheduleWarnings['ROOM-TYPE']) ?
				1 : $scheduleModel->scheduleWarnings['ROOM-TYPE'] + 1;
			$scheduleModel->schedule->rooms->$roomID->description = '';

			return;
		}

		$scheduleModel->schedule->rooms->$roomID->description = $descriptionID;
		$scheduleModel->schedule->rooms->$roomID->typeID      = $scheduleModel->schedule->room_types->$descriptionID->id;
	}

	/**
	 * Validates the room's untis id
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$roomNode      the room node object
	 *
	 * @return  mixed  string untis id if valid, otherwise false
	 */
	private static function validateUntisID(&$scheduleModel, &$roomNode)
	{
		$externalID = trim((string) $roomNode->external_name);
		$internalID = trim((string) $roomNode[0]['id']);
		if (empty($internalID))
		{
			if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_ROOM_ID_MISSING"), $scheduleModel->scheduleErrors))
			{
				$scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_ROOM_ID_MISSING");
			}

			return false;
		}

		if (empty($externalID))
		{
			$scheduleModel->scheduleWarnings['ROOM-EXTERNALID'] = empty($scheduleModel->scheduleWarnings['ROOM-EXTERNALID']) ?
				1 : $scheduleModel->scheduleWarnings['ROOM-EXTERNALID'] + 1;
			$gpuntisID                                          = $internalID;
		}
		else
		{

			$gpuntisID = $externalID;
		}

		return $gpuntisID;
	}
}
