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
use Organizer\Tables\Courses as CoursesTable;

/**
 * Provides general functions for course access checks, data retrieval and display.
 */
class Courses extends ResourceHelper
{
	// Registration status codes
	const UNREGISTERED = null;

	// RoleIDs
	const TEACHER = 1, TUTOR = 2, SUPERVISOR = 3, SPEAKER = 4;

	/**
	 * Check if user is registered as a course's coordinator.
	 *
	 * @param   int  $courseID  the optional id of the course
	 *
	 * @return boolean true if the user registered as a coordinator, otherwise false
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
	 * Attempts to retrieve the name of the resource.
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
	 * Get list of registered participants in specific course
	 *
	 * @param   int  $courseID  identifier of course
	 * @param   int  $status    status of participants (1 registered, 0 waiting)
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
		$tag   = Languages::getTag();
		$query = $dbo->getQuery(true);

		$query->select("pt.*, us.name AS usersName, us.email, us.username, pr.name_$tag as program")
			->select('CONCAT(pt.surname, ", ", pt.forename) AS name')
			->from('#__thm_organizer_course_participants AS cp')
			->innerJoin('#__thm_organizer_participants as pt on pt.id = cp.participantID')
			->innerJoin('#__users as us on us.id = pt.id')
			->leftJoin('#__thm_organizer_programs as pr on pr.id = pt.programID')
			->where("cp.courseID = '$courseID'")
			->order('name ASC');

		if ($status !== null and is_numeric($status))
		{
			$query->where("cp.status = '$status'");
		}

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadAssocList', []);
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
	 * Check if user is registered as a person with a course responsibility.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 * @param   int  $roleID    the optional if of the person's role
	 *
	 * @return boolean true if the user registered as a coordinator, otherwise false
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
	 * Check if user is registered as a course's speaker.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 *
	 * @return boolean true if the user registered as a speaker, otherwise false
	 */
	public static function speaks($courseID = 0, $personID = 0)
	{
		return self::hasResponsibility($courseID, $personID, self::SPEAKER);
	}

	/**
	 * Check if user is registered as a course's supervisor.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 *
	 * @return boolean true if the user registered as a supervisor, otherwise false
	 */
	public static function supervises($courseID = 0, $personID = 0)
	{
		return self::hasResponsibility($courseID, $personID, self::SUPERVISOR);
	}

	/**
	 * Check if user is registered as a course's teacher.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 *
	 * @return boolean true if the user registered as a teacher, otherwise false
	 */
	public static function teaches($courseID = 0, $personID = 0)
	{
		return self::hasResponsibility($courseID, $personID, self::TEACHER);
	}

	/**
	 * Check if user is registered as a course's tutor.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 *
	 * @return boolean true if the user registered as a tutor, otherwise false
	 */
	public static function tutors($courseID = 0, $personID = 0)
	{
		return self::hasResponsibility($courseID, $personID, self::TUTOR);
	}

//    /**
//     * Check if course with specific id is full
//     *
//     * @param int $courseID identifier of course
//     *
//     * @return bool true when course can accept more participants, false otherwise
//     */
//    public static function canAcceptParticipant($courseID)
//    {
//        $course = self::getCourse($courseID);
//
//        if (empty($course)) {
//            return false;
//        }
//
//        $open = self::isRegistrationOpen($courseID);
//        if (empty($open)) {
//            return false;
//        }
//
//        $regType          = $course['registration_type'];
//        $manualAcceptance = (!empty($regType) and $regType === self::MANUAL_ACCEPTANCE);
//
//        if ($manualAcceptance) {
//            return false;
//        }
//
//        $acceptedParticipants = count(self::getParticipants($courseID, 1));
//        $maxParticipants      = empty($course['lessonP']) ? $course['subjectP'] : $course['lessonP'];
//
//        if (empty($maxParticipants)) {
//            return true;
//        }
//
//        return ($acceptedParticipants < $maxParticipants);
//    }
//
//    /**
//     * Creates a button for user interaction with the course. (De-/registration, Administration)
//     *
//     * @param string $view     the view to be redirected to after registration action
//     * @param int    $courseID the id of the course
//     *
//     * @return string the HTML for the action button as appropriate for the user
//     */
//    public static function getActionButton($view, $courseID)
//    {
//        $expired    = !self::isRegistrationOpen($courseID);
//        $authorized = self::authorized($courseID);
//
//        $tag             = Languages::getTag();
//        $menuID          = Input::getItemid();
//        $pathPrefix      = 'index.php?option=com_thm_organizer';
//        $managerURL      = "{$pathPrefix}&view=courses&languageTag=$tag";
//        $registrationURL = "$pathPrefix&task=$view.register&languageTag=$tag";
//        $registrationURL .= $view == 'subject' ? '&id=' . Input::getID() : '';
//
//        if (!empty($menuID)) {
//            $managerURL      .= "&Itemid=$menuID";
//            $registrationURL .= "&Itemid=$menuID";
//        }
//
//        if (!empty(Factory::getUser()->id)) {
//            $lessonURL = "&lessonID=$courseID";
//
//            if ($authorized) {
//                $manage       = '<span class="icon-cogs"></span>' . Languages::_('THM_ORGANIZER_MANAGE');
//                $managerRoute = Route::_($managerURL . $lessonURL);
//                $register     = "<a class='btn' href='$managerRoute'>$manage</a>";
//            } else {
//                $regState = self::getParticipantState($courseID);
//
//                if ($expired) {
//                    $register = '';
//                } else {
//                    $registerRoute = Route::_($registrationURL . $lessonURL);
//
//                    if (!empty($regState)) {
//                        $registerText = '<span class="icon-out-2"></span>';
//                        $registerText .= Languages::_('THM_ORGANIZER_DEREGISTER');
//                    } else {
//                        $registerText = '<span class="icon-apply"></span>';
//                        $registerText .= Languages::_('THM_ORGANIZER_REGISTER');
//                    }
//
//                    $register = "<a class='btn' href='$registerRoute' type='button'>$registerText</a>";
//                }
//            }
//        } else {
//            $register = '';
//        }
//
//        return $register;
//    }
//
//    /**
//     * Check if the course is open for registration
//     *
//     * @param int $courseID id of lesson
//     *
//     * @return bool true if registration deadline not yet in the past, false otherwise
//     */
//    public static function isRegistrationOpen($courseID = 0)
//    {
//        $dates = self::getDates($courseID);
//        if (empty($dates)) {
//            return false;
//        }
//
//        try {
//            $startDate = new DateTime($dates[0]);
//            $deadline  = self::getCourse($courseID)['deadline'];
//            $interval  = new DateInterval("P{$deadline}D");
//        } catch (Exception $exc) {
//            OrganizerHelper::message($exc->getMessage(), 'error');
//
//            return false;
//        }
//
//        $adjustedDate = new DateTime;
//        $adjustedDate->add($interval);
//
//        return $startDate > $adjustedDate;
//    }
//
//    /**
//     * Might move users from state waiting to registered
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
