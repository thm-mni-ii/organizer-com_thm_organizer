<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;

/**
 * Provides general functions for subject access checks, data retrieval and display.
 */
class Events extends ResourceHelper
{
	/**
	 * Check if user is registered as a subject's coordinator.
	 *
	 * @param   int  $eventID   the optional id of the subject
	 * @param   int  $personID  the optional id of the person entry
	 *
	 * @return boolean true if the user registered as a coordinator, otherwise false
	 */
	public static function coordinates($eventID = 0, $personID = 0)
	{
		if (!$personID)
		{
			$user     = Factory::getUser();
			$personID = Persons::getIDByUserID($user->id);
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__thm_organizer_event_coordinators')
			->where("personID = $personID");

		if ($eventID)
		{
			$query->where("eventID = '$eventID'");
		}

		$dbo->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('loadResult');
	}

	/**
	 * Check if user is registered as a subject's teacher.
	 *
	 * @param   int  $eventID   the optional id of the subject
	 * @param   int  $personID  the optional id of the person entry
	 *
	 * @return boolean true if the user registered as a coordinator, otherwise false
	 */
	public static function teaches($eventID = 0, $personID = 0)
	{
		if (!$personID)
		{
			$user     = Factory::getUser();
			$personID = Persons::getIDByUserID($user->id);
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__thm_organizer_instances AS i')
			->innerJoin('#__thm_organizer_instance_persons AS ip ON ip.instanceID = i.id')
			->where("ip.personID = $personID")
			->where("ip.roleID = 1");

		if ($eventID)
		{
			$query->where("i.eventID = '$eventID'");
		}

		$dbo->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('loadResult');
	}
}
