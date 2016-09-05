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

require_once 'department_resources.php';

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
	 * @param   string $gpuntisID the room's gpuntis ID
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
}
