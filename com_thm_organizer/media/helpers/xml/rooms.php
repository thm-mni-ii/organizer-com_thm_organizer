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

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/department_resources.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/rooms.php';

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
	 * Validates the rooms node
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$xmlObject     the xml object being validated
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

		$scheduleModel->newSchedule->rooms = $scheduleModel->schedule->rooms;
	}

	/**
	 * Validates the room's display name
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$roomNode      the room node object
	 * @param string $roomID         the room's id
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
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$roomNode      the room node to be validated
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

		$gpuntisID                                             = str_replace('RM_', '', $gpuntisID);
		$scheduleModel->schedule->rooms->$gpuntisID            = new stdClass;
		$scheduleModel->schedule->rooms->$gpuntisID->name      = $gpuntisID;
		$scheduleModel->schedule->rooms->$gpuntisID->gpuntisID = $gpuntisID;
		$scheduleModel->schedule->rooms->$gpuntisID->localUntisID
		                                                       = str_replace('RM_', '', trim((string) $roomNode[0]['id']));

		$displayName = self::validateDisplayName($scheduleModel, $roomNode, $gpuntisID);
		if (!$displayName)
		{
			unset($scheduleModel->schedule->rooms->$gpuntisID);

			return;
		}

		$capacity                                             = trim((int) $roomNode->capacity);
		$scheduleModel->schedule->rooms->$gpuntisID->capacity = (empty($capacity)) ? '' : $capacity;

		self::validateType($scheduleModel, $roomNode, $gpuntisID);
		$roomID = THM_OrganizerHelperRooms::getID($gpuntisID, $scheduleModel->schedule->rooms->$gpuntisID);

		if (!empty($roomID))
		{
			$scheduleModel->schedule->rooms->$gpuntisID->id = $roomID;
			THM_OrganizerHelperDepartment_Resources::setDepartmentResource($roomID, 'roomID');
		}
	}

	/**
	 * Validates the room's description attribute
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$roomNode      the room node object
	 * @param string $roomID         the room's id
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
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$roomNode      the room node object
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
