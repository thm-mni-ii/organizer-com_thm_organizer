<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;

/**
 * Provides general functions for course access checks, data retrieval and display.
 */
class CourseParticipants extends ResourceHelper
{
	const UNREGISTERED = null;

	/**
	 * Retrieves the participant state for the given course
	 *
	 * @param   int  $courseID       the course id
	 * @param   int  $eventID        the id of the specific course event
	 * @param   int  $participantID  the id of the participant
	 *
	 * @return  mixed int (0 - pending or 1- accepted) if the user has registered for the course, otherwise null
	 */
	public static function getState($courseID, $eventID, $participantID)
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
}
