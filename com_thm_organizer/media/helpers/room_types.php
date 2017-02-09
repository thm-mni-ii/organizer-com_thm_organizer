<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerModelRoomTypes
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'departments.php';

/**
 * Provides methods for room type objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperRoomTypes
{
	/**
	 * Checks for the room type name for a given room type id
	 *
	 * @param string $typeID the room type's id
	 *
	 * @return  string the name if the room type could be resolved, otherwise empty
	 */
	public static function getName($typeID)
	{
		$roomTypesTable = JTable::getInstance('room_types', 'thm_organizerTable');

		try
		{
			$success = $roomTypesTable->load($typeID);
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return '';
		}

		$languageTag   = THM_OrganizerHelperLanguage::getShortTag();
		$attribute = "name_$languageTag";

		return $success ? $roomTypesTable->$attribute : '';
	}
}
