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
use Organizer\Tables\Courses as CoursesTable;

/**
 * Provides general functions for course access checks, data retrieval and display.
 */
class Courses extends ResourceHelper
{
	// RoleIDs
	const TEACHER = 1, TUTOR = 2, SUPERVISOR = 3, SPEAKER = 4;

	/**
	 * Check if the user is a course coordinator.
	 *
	 * @param   int  $courseID  the optional id of the course
	 *
	 * @return boolean true if the user is a coordinator, otherwise false
	 */
	public static function coordinates($courseID = 0)
	{
		if (Can::administrate())
		{
			return true;
		}

		if (!$personID = Persons::getIDByUserID())
		{
			return false;
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__thm_organizer_event_coordinators AS ec')
			->where("ec.personID = $personID");

		if ($courseID)
		{
			$query->innerJoin('#__thm_organizer_events AS e ON e.id = ec.eventID')
				->innerJoin('#__thm_organizer_instances AS i ON i.eventID = e.id')
				->innerJoin('#__thm_organizer_units AS u ON u.id = i.unitID')
				->where("u.courseID = $courseID");
		}

		$dbo->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('loadResult');
	}

	/**
	 * Generates a capacity text for active course participants.
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return string the course capacity text
	 */
	public static function getCapacityText($courseID)
	{
		$course = new CoursesTable;

		if (!$course->load($courseID))
		{
			return '';
		}

		$max = $course->maxParticipants;

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('COUNT(DISTINCT participantID)')
			->from('#__thm_organizer_course_participants')
			->where("courseID = $courseID")
			->where("status = 1");
		$dbo->setQuery($query);

		$current = OrganizerHelper::executeQuery('loadResult', 0);

		return "<span class=\"icon-user-check\"></span>$current/$max";
	}

	/**
	 * Creates a display of formatted dates for a course
	 *
	 * @param   int  $courseID  the id of the course to be loaded
	 *
	 * @return string the dates to display
	 */
	public static function getDateDisplay($courseID)
	{
		if ($dates = self::getDates($courseID))
		{
			return Dates::getDisplay($dates['startDate'], $dates ['endDate']);
		}

		return '';
	}

	/**
	 * Gets the course start and end dates.
	 *
	 * @param   int  $courseID  id of course to be loaded
	 *
	 * @return array  the start and end date for the given course
	 */
	public static function getDates($courseID = 0)
	{
		if (empty($courseID))
		{
			return [];
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('DISTINCT MIN(date) AS startDate, MAX(date) AS endDate')
			->from('#__thm_organizer_blocks AS b')
			->innerJoin('#__thm_organizer_instances AS i ON i.blockID = b.id')
			->innerJoin('#__thm_organizer_units AS u ON u.id = i.unitID')
			->where("u.courseID = $courseID");

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadAssoc', []);
	}

	/**
	 * Retrieves events associated with the given course.
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return array the events associated with the course
	 */
	public static function getEvents($courseID)
	{
		$dbo = Factory::getDbo();
		$tag = Languages::getTag();

		$query = $dbo->getQuery('true');
		$query->select("DISTINCT e.id, e.name_$tag AS name, contact_$tag AS contact")
			->select("courseContact_$tag AS courseContact, content_$tag AS content, e.description_$tag AS description")
			->select("organization_$tag AS organization, pretests_$tag AS pretests, preparatory")
			->from('#__thm_organizer_events AS e')
			->innerJoin('#__thm_organizer_instances AS i on i.eventID = e.id')
			->innerJoin('#__thm_organizer_units AS u on u.id = i.unitID')
			->where("u.courseID = $courseID")
			->order('name ASC');

		$dbo->setQuery($query);
		if (!$events = OrganizerHelper::executeQuery('loadAssocList', []))
		{
			return [];
		}

		foreach ($events as &$event)
		{
			$event['speakers'] = self::getPersons($courseID, $event['id'], [self::SPEAKER]);
			$event['teachers'] = self::getPersons($courseID, $event['id'], [self::TEACHER]);
			$event['tutors']   = self::getPersons($courseID, $event['id'], [self::TUTOR]);
		}

		return $events;
	}

	/**
	 * Gets persons associated with the given course, optionally filtered by event and role.
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return array the rooms in which the course takes place
	 */
	public static function getGroups($courseID)
	{
		$dbo = Factory::getDbo();

		$query = $dbo->getQuery('true');
		$query->select("DISTINCT g.untisID")
			->from('#__thm_organizer_groups AS g')
			->innerJoin('#__thm_organizer_instance_groups AS ig ON ig.groupID = g.id')
			->innerJoin('#__thm_organizer_instance_persons AS ip ON ip.id = ig.assocID')
			->innerJoin('#__thm_organizer_instances AS i ON i.id = ip.instanceID')
			->innerJoin('#__thm_organizer_units AS u on u.id = i.unitID')
			->where("ig.delta != 'removed'")
			->where("ip.delta != 'removed'")
			->where("i.delta != 'removed'")
			->where("u.delta != 'removed'")
			->where("u.courseID = $courseID")
			->order('g.untisID');

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadColumn', []);
	}

	/**
	 * Gets instances associated with the given course.
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return array the instances which are a part of the course
	 */
	public static function getInstances($courseID)
	{
		$dbo = Factory::getDbo();

		$query = $dbo->getQuery('true');
		$query->select("DISTINCT i.id")
			->from('#__thm_organizer_instances AS i')
			->innerJoin('#__thm_organizer_units AS u on u.id = i.unitID')
			->where("u.courseID = $courseID")
			->order('i.id');

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadColumn', []);
	}

	/**
	 * Retrieves the name of the course.
	 *
	 * @param   int  $courseID  the id of the resource
	 *
	 * @return string
	 */
	public static function getName($courseID)
	{
		if ($explicitName = parent::getName($courseID))
		{
			return $explicitName;
		}

		if (!$events = self::getEvents($courseID))
		{
			return '';
		}

		$eventNames = [];
		foreach ($events as $event)
		{
			$eventNames[$event['name']] = $event['name'];
		}

		return implode(' / ', $eventNames);
	}

	/**
	 * Attempts to retrieve the names of the resource.
	 *
	 * @param   int  $courseID  the id of the resource
	 *
	 * @return string
	 */
	public static function getNames($courseID)
	{
		$course = new CoursesTable;
		if (!$course->load($courseID))
		{
			return '';
		}

		$groups = '';
		if (trim($course->groups))
		{
			$groups = trim($course->groups);
		}

		$nameProperty = 'name_' . Languages::getTag();
		$names        = [];

		if ($name = trim($course->$nameProperty))
		{
			$names[] = [$name];
		}
		elseif ($events = self::getEvents($courseID))
		{
			foreach ($events as $event)
			{
				$names[] = $event['name'];
			}
		}
		else
		{
			return '';
		}

		$names = implode('<br>', $names);
		$names .= $groups ? '<br>' . Languages::_('THM_ORGANIZER_COURSE_GROUPS') . ": $groups" : '';

		return $names;
	}

	/**
	 * Gets an array of participant IDs for a given course, optionally filtered by the participant's status
	 *
	 * @param   int  $courseID  the course id
	 * @param   int  $status    the participant status
	 *
	 * @return array list of participants in course
	 */
	public static function getParticipants($courseID, $status = null)
	{
		if (empty($courseID))
		{
			return [];
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select("participantID")
			->from('#__thm_organizer_course_participants')
			->where("courseID = $courseID")
			->order('participantID ASC');

		if ($status !== null and is_numeric($status))
		{
			$query->where("status = $status");
		}

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadColumn', []);
	}

	/**
	 * Gets persons associated with the given course, optionally filtered by event and role.
	 *
	 * @param   int    $courseID  the id of the course
	 * @param   int    $eventID   the id of the event
	 * @param   array  $roleIDs   the id of the roles the persons should have
	 *
	 * @return array the persons matching the search criteria
	 */
	public static function getPersons($courseID, $eventID = 0, $roleIDs = [])
	{
		$dbo = Factory::getDbo();

		$query = $dbo->getQuery('true');
		$query->select("DISTINCT ip.personID")
			->from('#__thm_organizer_instance_persons AS ip')
			->innerJoin('#__thm_organizer_instances AS i ON i.id = ip.instanceID')
			->innerJoin('#__thm_organizer_units AS u on u.id = i.unitID')
			->where("u.courseID = $courseID");

		if ($eventID)
		{
			$query->where("i.eventID = $eventID");
		}

		if ($roleIDs)
		{
			$query->where('ip.roleID IN (' . implode(',', $roleIDs) . ')');
		}

		$dbo->setQuery($query);
		if (!$personIDs = OrganizerHelper::executeQuery('loadColumn', []))
		{
			return [];
		}

		$persons = [];
		foreach ($personIDs as $personID)
		{
			$persons[$personID] = Persons::getLNFName($personID);
		}

		return $persons;
	}

	/**
	 * Gets persons associated with the given course, optionally filtered by event and role.
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return array the rooms in which the course takes place
	 */
	public static function getRooms($courseID)
	{
		$dbo = Factory::getDbo();

		$query = $dbo->getQuery('true');
		$query->select("DISTINCT r.untisID")
			->from('#__thm_organizer_rooms AS r')
			->innerJoin('#__thm_organizer_instance_rooms AS ir ON ir.roomID = r.id')
			->innerJoin('#__thm_organizer_instance_persons AS ip ON ip.id = ir.assocID')
			->innerJoin('#__thm_organizer_instances AS i ON i.id = ip.instanceID')
			->innerJoin('#__thm_organizer_units AS u on u.id = i.unitID')
			->where("ir.delta != 'removed'")
			->where("ip.delta != 'removed'")
			->where("i.delta != 'removed'")
			->where("u.delta != 'removed'")
			->where("u.courseID = $courseID")
			->order('r.name');

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadColumn', []);
	}

	/**
	 * Generates a status text for the course itself.
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return string the course status text
	 */
	public static function getStatusText($courseID)
	{
		if (self::isExpired($courseID))
		{
			$status = Languages::_('THM_ORGANIZER_EXPIRED');
		}
		elseif (self::isOngoing($courseID))
		{
			$status = Languages::_('THM_ORGANIZER_COURSE_ONGOING');
		}
		elseif (self::isFull($courseID))
		{
			$status = Languages::_('THM_ORGANIZER_COURSE_FULL');
		}
		else
		{
			$status = Languages::_('THM_ORGANIZER_COURSE_OPEN');
		}

		if (self::hasResponsibility($courseID))
		{
			$status .= '<br>' . self::getCapacityText($courseID);
		}

		return $status;
	}

	/**
	 * Check if user has a course responsibility.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 * @param   int  $roleID    the optional if of the person's role
	 *
	 * @return boolean true if the user has a course responsibility, otherwise false
	 */
	public static function hasResponsibility($courseID = 0, $personID = 0, $roleID = 0)
	{
		if (Can::administrate())
		{
			return true;
		}

		if (empty($personID))
		{
			$user = Factory::getUser();
			if (!$personID = Persons::getIDByUserID($user->id))
			{
				return false;
			}
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__thm_organizer_instance_persons AS ip')
			->innerJoin('#__thm_organizer_instances AS i ON i.id = ip.instanceID')
			->innerJoin('#__thm_organizer_units AS u ON u.id = i.unitID')
			->where("ip.personID = $personID");

		if ($courseID)
		{
			$query->where("u.courseID = $courseID");
		}

		if ($roleID)
		{
			$query->where("ip.roleID = $roleID");
		}

		$dbo->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('loadResult');
	}

	/**
	 * Checks if the course is expired
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return bool true if the course is expired, otherwise false
	 */
	public static function isExpired($courseID)
	{
		if ($dates = self::getDates($courseID))
		{
			return date('Y-m-d') > $dates['endDate'];
		}

		return true;
	}

	/**
	 * Checks if the number of active participants is less than the number of max participants
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return bool true if the course is full, otherwise false
	 */
	public static function isFull($courseID)
	{
		$table = new CoursesTable;
		if (!$maxParticipants = $table->getProperty('maxParticipants', $courseID))
		{
			return false;
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__thm_organizer_course_participants')
			->where("courseID = $courseID")
			->where('status = 1');
		$dbo->setQuery($query);
		$count = OrganizerHelper::executeQuery('loadResult', 0);

		return $count >= $maxParticipants;
	}

	/**
	 * Checks if the course is ongoing
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return bool true if the course is expired, otherwise false
	 */
	public static function isOngoing($courseID)
	{
		if ($dates = self::getDates($courseID))
		{
			$today = date('Y-m-d');

			return ($today >= $dates['startDate'] and $today <= $dates['endDate']);
		}

		return false;
	}

	/**
	 * Checks if the course is a preparatory course.
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return bool true if the course is expired, otherwise false
	 */
	public static function isPreparatory($courseID)
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__thm_organizer_units AS u')
			->innerJoin('#__thm_organizer_instances AS i ON i.unitID = u.id')
			->innerJoin('#__thm_organizer_events AS e ON e.id = i.eventID')
			->where("u.courseID = $courseID")
			->where('e.preparatory = 1');

		$dbo->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('loadResult', 0);
	}

	/**
	 * Check if user is a speaker.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 *
	 * @return boolean true if the user is a speaker, otherwise false
	 */
	public static function speaks($courseID = 0, $personID = 0)
	{
		return self::hasResponsibility($courseID, $personID, self::SPEAKER);
	}

	/**
	 * Check if user a course supervisor.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 *
	 * @return boolean true if the user is a supervisor, otherwise false
	 */
	public static function supervises($courseID = 0, $personID = 0)
	{
		return self::hasResponsibility($courseID, $personID, self::SUPERVISOR);
	}

	/**
	 * Check if user is a course teacher.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 *
	 * @return boolean true if the user is a course teacher, otherwise false
	 */
	public static function teaches($courseID = 0, $personID = 0)
	{
		return self::hasResponsibility($courseID, $personID, self::TEACHER);
	}

	/**
	 * Check if user is a course tutor.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 *
	 * @return boolean true if the user is a tutor, otherwise false
	 */
	public static function tutors($courseID = 0, $personID = 0)
	{
		return self::hasResponsibility($courseID, $personID, self::TUTOR);
	}

//    /**
//     * Might move users from state pending to accepted
//     *
//     * @param int $courseID lesson id of lesson where participants have to be moved up
//     *
//     * @return void
//     */
//    public static function refreshWaitList($courseID)
//    {
//        $canAccept = self::canAcceptParticipant($courseID);
//
//        if ($canAccept) {
//            $dbo   = Factory::getDbo();
//            $query = $dbo->getQuery(true);
//
//            $query->select('userID');
//            $query->from('#__thm_organizer_user_lessons');
//            $query->where("lessonID = '$courseID' and status = '0'");
//            $query->order('status_date, user_date');
//
//            $dbo->setQuery($query);
//
//            $nextParticipantID = OrganizerHelper::executeQuery('loadResult');
//
//            if (!empty($nextParticipantID)) {
//                Participants::changeState($nextParticipantID, $courseID, 1);
//            }
//        }
//    }
}
