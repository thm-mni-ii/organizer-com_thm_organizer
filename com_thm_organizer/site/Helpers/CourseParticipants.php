<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Provides general functions for course access checks, data retrieval and display.
 */
class CourseParticipants extends ResourceHelper
{
	const UNREGISTERED = null, WAIT_LIST = 0;

	/**
	 * Retrieves the participant's state for the given course
	 *
	 * @param   int  $courseID       the course id
	 * @param   int  $eventID        the id of the specific course event
	 * @param   int  $participantID  the id of the participant
	 *
	 * @return  mixed int if the user has a course participant state, otherwise null
	 */
	public static function getState($courseID, $participantID, $eventID = 0)
	{
		if (empty($courseID) or empty($participantID))
		{
			return self::UNREGISTERED;
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('status')
			->from('#__thm_organizer_course_participants AS cp')
			->where("cp.courseID = $courseID")
			->where("cp.participantID = $participantID");

		if ($eventID)
		{
			$query->innerJoin('#__thm_organizer_units AS u ON u.courseID = cp.courseID')
				->innerJoin('#__thm_organizer_instances AS i ON i.unitID = u.id')
				->innerJoin('#__thm_organizer_instance_participants AS ip ON ip.instanceID = i.id')
				->where("i.eventID = $eventID")
				->where("ip.participantID = $participantID");
		}

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadResult', self::UNREGISTERED);
	}

	/**
	 * Checks whether all the necessary participant information has been entered.
	 *
	 * @param   int  $courseID       the id of the course to check against
	 * @param   int  $participantID  the id of the participant to validate
	 *
	 * @return bool true if the participant entry is incomplete, otherwise false
	 */
	public static function incomplete($courseID, $participantID)
	{
		if (empty($participantID))
		{
			return true;
		}

		$table = new Tables\Participants;
		if (!$table->load($participantID))
		{
			return true;
		}

		if (Courses::isPreparatory($courseID))
		{
			$requiredProperties = ['address', 'city', 'forename', 'programID', 'surname', 'zipCode'];
		}
		// Resolve any other contexts here later.
		else
		{
			$requiredProperties = [];
		}

		foreach ($requiredProperties as $property)
		{
			if (empty($table->get($property)))
			{
				return true;
			}
		}

		return false;
	}
}
