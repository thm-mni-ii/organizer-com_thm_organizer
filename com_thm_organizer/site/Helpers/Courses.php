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
class Courses extends ResourceHelper
{
	const MANUAL_ACCEPTANCE = 1;
	const BLOCK_MODE = 2;
	const INSTANCE_MODE = 3;
	const TEACHER = 1;
	const TUTOR = 2;
	const SPEAKER = 4;

	/**
	 * Check if user is registered as a course's coordinator.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 *
	 * @return boolean true if the user registered as a coordinator, otherwise false
	 */
	public static function coordinates($courseID = 0, $personID = 0)
	{
		if (!$personID)
		{
			$user     = Factory::getUser();
			$personID = Persons::getIDByUserID($user->id);
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__thm_organizer_event_coordinators AS ec')
			->innerJoin('#__thm_organizer_events AS e ON e.id = ed.eventID')
			->innerJoin('#__thm_organizer_instances AS i ON i.eventID = e.id')
			->innerJoin('#__thm_organizer_units AS u ON u.id = i.unitID')
			->where("ec.personID = $personID");

		if ($courseID)
		{
			$query->where("u.courseID = '$courseID'");
		}

		$dbo->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('loadResult');
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
			->where("u.courseID = $courseID");

		$dbo->setQuery($query);
		if (!$events = OrganizerHelper::executeQuery('loadAssocList', []))
		{
			return [];
		}

		foreach ($events as &$event)
		{
			$event['speakers'] = self::getPersons($courseID, $event['id'], self::SPEAKER);
			$event['teachers'] = self::getPersons($courseID, $event['id'], self::TEACHER);
			$event['tutors']   = self::getPersons($courseID, $event['id'], self::TUTOR);
		}

		return $events;
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
	 * @param   int  $courseID  the id of the course
	 * @param   int  $eventID   the id of the event
	 * @param   int  $roleID    the id of the role
	 *
	 * @return array the persons matching the search criteria
	 */
	public static function getPersons($courseID, $eventID = 0, $roleID = 0)
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

		if ($roleID)
		{
			$query->where("ip.roleID = $roleID");
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
	 * Retrieves the participant state for the given course
	 *
	 * @param   int  $courseID  the course id
	 * @param   int  $eventID   the id of an event associated with the given course
	 *
	 * @return  mixed int (0 - pending or 1- accepted) if the user has registered for the course, otherwise null
	 */
	public static function getParticipantState($courseID, $eventID = 0)
	{
		$participantID = Factory::getUser()->id;

		if (empty($courseID) or empty($participantID))
		{
			return null;
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('status')
			->from('#__thm_organizer_course_participants AS cp')
			->where("courseID = $courseID")
			->where("participantID = $participantID");

		if ($eventID)
		{
			$query->innerJoin('#__thm_organizer_units AS u ON u.courseID = cp.courseID')
				->innerJoin('#__thm_organizer_instances AS i ON i.unitID = u.id')
				->where("i.eventID = $eventID");
		}

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadResult', null);
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
		$table = self::getTable();
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
	 * Check if user is registered as a course's tutor.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 *
	 * @return boolean true if the user registered as a coordinator, otherwise false
	 */
	public static function teaches($courseID = 0, $personID = 0)
	{
		if (!$personID)
		{
			$user     = Factory::getUser();
			$personID = Persons::getIDByUserID($user->id);
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__thm_organizer_instance_persons AS ip')
			->innerJoin('#__thm_organizer_instances AS i ON i.id = ip.instanceID')
			->innerJoin('#__thm_organizer_units AS u ON u.id = i.unitID')
			->where("ip.personID = $personID")
			->where('ip.roleID = ' . self::TEACHER);

		if ($courseID)
		{
			$query->where("u.courseID = '$courseID'");
		}

		$dbo->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('loadResult');
	}

	/**
	 * Check if user is registered as a course's tutor.
	 *
	 * @param   int  $courseID  the optional id of the course
	 * @param   int  $personID  the optional id of the person
	 *
	 * @return boolean true if the user registered as a coordinator, otherwise false
	 */
	public static function tutors($courseID = 0, $personID = 0)
	{
		if (!$personID)
		{
			$user     = Factory::getUser();
			$personID = Persons::getIDByUserID($user->id);
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__thm_organizer_instance_persons AS ip')
			->innerJoin('#__thm_organizer_instances AS i ON i.id = ip.instanceID')
			->innerJoin('#__thm_organizer_units AS u ON u.id = i.unitID')
			->where("ip.personID = $personID")
			->where('ip.roleID = ' . self::TUTOR);

		if ($courseID)
		{
			$query->where("u.courseID = '$courseID'");
		}

		$dbo->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('loadResult');
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
//                        $registerText .= Languages::_('THM_ORGANIZER_COURSE_DEREGISTER');
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
//     * Looks up the names of the categories associated with the course
//     *
//     * @param int $courseID the id of the course
//     *
//     * @return array the associated program names
//     */
//    public static function getCategories($courseID)
//    {
//        $names = [];
//        $dbo   = Factory::getDbo();
//        $tag   = Languages::getTag();
//
//        $query     = $dbo->getQuery(true);
//        $nameParts = ["p.name_$tag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
//        $query->select('cat.name AS categoryName, ' . $query->concatenate($nameParts, "") . ' AS name')
//            ->select('cat.id')
//            ->from('#__thm_organizer_categories AS cat')
//            ->innerJoin('#__thm_organizer_groups AS gr ON gr.categoryID = cat.id')
//            ->innerJoin('#__thm_organizer_lesson_groups AS lg ON lg.groupID = gr.id')
//            ->innerJoin('#__thm_organizer_lesson_courses AS lc ON lc.id = lg.lessonCourseID')
//            ->leftJoin('#__thm_organizer_programs AS p ON p.categoryID = cat.id')
//            ->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id')
//            ->where("lc.courseID = '$courseID'");
//
//        $dbo->setQuery($query);
//
//        $results = OrganizerHelper::executeQuery('loadAssocList');
//        if (empty($results)) {
//            return [];
//        }
//
//        foreach ($results as $result) {
//            $names[$result['id']] = empty($result['name']) ? $result['categoryName'] : $result['name'];
//        }
//
//        return $names;
//    }
//
//    /**
//     * Creates a display of formatted dates for a course
//     *
//     * @param int $courseID the id of the course to be loaded
//     *
//     * @return string the dates to display
//     */
//    public static function getDateDisplay($courseID = 0)
//    {
//        $courseID = Input::getInt('lessonID', $courseID);
//
//        $dates = self::getDates($courseID);
//
//        if (!empty($dates)) {
//            $dateFormat = Input::getParams()->get('dateFormat', 'd.m.Y');
//            $start      = HTML::_('date', $dates[0], $dateFormat);
//            $end        = HTML::_('date', end($dates), $dateFormat);
//
//            return "$start - $end";
//        }
//
//        return '';
//    }
//
//    /**
//     * Loads all all participants for specific course from database
//     *
//     * @param int     $courseID id of course to be loaded
//     * @param boolean $includeWaitList
//     *
//     * @return array  with course registration data on success, otherwise empty
//     */
//    public static function getFullParticipantData($courseID = 0, $includeWaitList = false)
//    {
//        if (empty($courseID)) {
//            return [];
//        }
//
//
//        $dbo   = Factory::getDbo();
//        $tag   = Languages::getTag();
//        $query = $dbo->getQuery(true);
//
//        $nameParts    = ['pt.surname', "', '", 'pt.forename'];
//        $programParts = ["pr.name_$tag", "' ('", 'dg.abbreviation', "' '", 'pr.version', "')'"];
//
//        $query->select($query->concatenate($nameParts, '') . ' AS userName, pt.address, pt.zipCode, pt.city')
//            ->select('u.id, u.email')
//            ->select($query->concatenate($programParts, '') . ' AS programName, pr.id AS programID')
//            ->select("dp.shortName_$tag AS departmentName, dp.id AS departmentID");
//
//        $query->from('#__thm_organizer_user_lessons AS ul');
//        $query->innerJoin('#__users AS u ON u.id = ul.userID');
//        $query->innerJoin('#__thm_organizer_participants AS pt ON pt.id = ul.userID');
//        $query->innerJoin('#__thm_organizer_programs AS pr ON pr.id = pt.programID');
//        $query->innerJoin('#__thm_organizer_degrees AS dg ON dg.id = pr.degreeID');
//        $query->innerJoin('#__thm_organizer_departments AS dp ON dp.id = pr.departmentID');
//        $query->where("ul.lessonID = '$courseID'");
//
//        if (!$includeWaitList) {
//            $query->where("ul.status = '1'");
//        }
//
//        $query->order('userName');
//
//        $dbo->setQuery($query);
//
//        return OrganizerHelper::executeQuery('loadAssocList', []);
//    }
//
//    /**
//     * Retrieves the course instances
//     *
//     * @param int    $courseID     the id of the course
//     * @param int    $mode         the retrieval mode (empty => all, 2 => same block, 3 => single instance
//     * @param object $calReference a reference calendar entry modeled on an object
//     *
//     * @return array the instance ids on success, otherwise empty
//     */
//    public static function getInstances($courseID, $mode = null, $calReference = null)
//    {
//        $dbo   = Factory::getDbo();
//        $query = $dbo->getQuery(true);
//
//        $query->select('map.id')
//            ->from('#__thm_organizer_calendar_configuration_map AS map')
//            ->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
//            ->where("cal.lessonID = '$courseID'")
//            ->where("delta != 'removed'");
//
//        // Restrictions
//        if ($mode == self::BLOCK_MODE or $mode == self::INSTANCE_MODE) {
//            $query->where("cal.startTime = '$calReference->startTime'");
//            $query->where("cal.endTime = '$calReference->endTime'");
//
//            if ($mode == self::INSTANCE_MODE) {
//                $query->where("cal.schedule_date = '$calReference->schedule_date'");
//            } else {
//                $query->where("DAYOFWEEK(cal.schedule_date) = '$calReference->weekday'");
//            }
//        }
//
//        $query->order('map.id');
//        $dbo->setQuery($query);
//
//        return OrganizerHelper::executeQuery('loadColumn', []);
//    }
//
//    /**
//     * Loads course information from the database
//     *
//     * @param int $subjectID id of subject with which courses must be associated
//     * @param int $campusID  id of the course campus
//     *
//     * @return array  with course data on success, otherwise empty
//     */
//    public static function getLatestCourses($subjectID, $campusID = null)
//    {
//        if (empty($subjectID)) {
//            return [];
//        }
//
//        $dbo   = Factory::getDbo();
//        $tag   = Languages::getTag();
//        $query = $dbo->getQuery(true);
//
//        $query->select('DISTINCT l.id, l.max_participants as lessonP, l.campusID AS campusID')
//            ->from('#__thm_organizer_lessons AS l')
//            ->innerJoin('#__thm_organizer_lesson_courses AS lc ON lc.lessonID = l.id')
//            ->innerJoin('#__thm_organizer_subject_mappings AS sm ON sm.courseID = lc.courseID')
//            ->select("s.id as subjectID, s.name_$tag as name, s.campusID AS abstractCampusID")
//            ->select('s.instructionLanguage, s.max_participants as subjectP')
//            ->innerJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id')
//            ->innerJoin('#__thm_organizer_calendar AS ca ON ca.lessonID = l.id')
//            ->select('term.name as termName')
//            ->innerJoin('#__thm_organizer_terms AS term ON term.id = l.termID')
//            ->leftJoin('#__thm_organizer_campuses as cp on s.campusID = cp.id')
//            ->where("s.id = '$subjectID'")
//            ->where("(s.is_prep_course = '1' OR s.registration_type IS NOT NULL OR l.registration_type IS NOT NULL)");
//
//        $query->order('schedule_date DESC');
//
//        if (!empty($campusID)) {
//            $campusConditions = "(l.campusID = '{$campusID}' OR (l.campusID IS NULL AND ";
//            $campusConditions .= "(c.id = '{$campusID}' OR c.parentID = '{$campusID}' OR s.campusID IS NULL)))";
//            $query->where($campusConditions);
//        }
//
//        $dbo->setQuery($query);
//
//        $courses = OrganizerHelper::executeQuery('loadAssocList');
//        if (empty($courses)) {
//            return [];
//        }
//
//        $campuses = [];
//
//        foreach ($courses as $index => &$course) {
//            $campus   = self::getCampus($course);
//            $campusID = empty($campus['id']) ? 0 : $campus['id'];
//
//            if (isset($campuses[$campusID])) {
//                unset($courses[$index]);
//                continue;
//            }
//
//            $course['campus']    = $campus;
//            $campuses[$campusID] = $campusID;
//        }
//
//        return $courses;
//    }
//
//    /**
//     * Retrieves a list of lessons associated with a subject
//     *
//     * @return array the lessons associated with the subject
//     */
//    public static function getLessons()
//    {
//        $courseIDs = Input::getFilterIDs('course');
//        if (empty($courseIDs[0])) {
//            return [];
//        }
//        $courseIDs = implode(',', $courseIDs);
//
//        $date = Input::getCMD('date');
//        if (!Dates::isStandardized($date)) {
//            $date = date('Y-m-d');
//        }
//
//        $interval = Input::getCMD('interval');
//        if (!in_array($interval, ['day', 'week', 'month', 'semester'])) {
//            $interval = 'semester';
//        }
//
//        $tag = Languages::getTag();
//
//        $dbo = Factory::getDbo();
//
//        $query = $dbo->getQuery(true);
//        $query->select("DISTINCT l.id, l.comment, lc.courseID, m.abbreviation_$tag AS method")
//            ->from('#__thm_organizer_lessons AS l')
//            ->innerJoin('#__thm_organizer_methods AS m on m.id = l.methodID')
//            ->innerJoin('#__thm_organizer_lesson_courses AS lc on lc.lessonID = l.id')
//            ->where("lc.courseID IN ($courseIDs)")
//            ->where("l.delta != 'removed'")
//            ->where("lc.delta != 'removed'");
//
//        $dateTime = strtotime($date);
//        switch ($interval) {
//            case 'semester':
//                $query->innerJoin('#__thm_organizer_terms AS term ON term.id = l.termID')
//                    ->where("'$date' BETWEEN term.startDate AND term.endDate");
//                break;
//            case 'month':
//                $monthStart = date('Y-m-d', strtotime('first day of this month', $dateTime));
//                $startDate  = date('Y-m-d', strtotime('Monday this week', strtotime($monthStart)));
//                $monthEnd   = date('Y-m-d', strtotime('last day of this month', $dateTime));
//                $endDate    = date('Y-m-d', strtotime('Sunday this week', strtotime($monthEnd)));
//                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
//                    ->where("c.schedule_date BETWEEN '$startDate' AND '$endDate'");
//                break;
//            case 'week':
//                $startDate = date('Y-m-d', strtotime('Monday this week', $dateTime));
//                $endDate   = date('Y-m-d', strtotime('Sunday this week', $dateTime));
//                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
//                    ->where("c.schedule_date BETWEEN '$startDate' AND '$endDate'");
//                break;
//            case 'day':
//                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
//                    ->where("c.schedule_date = '$date'");
//                break;
//        }
//
//        $dbo->setQuery($query);
//
//        $results = OrganizerHelper::executeQuery('loadAssocList');
//        if (empty($results)) {
//            return [];
//        }
//
//        $lessons = [];
//        foreach ($results as $lesson) {
//            $index = '';
//
//            $lesson['subjectName'] = Courses::getName($lesson['subjectID'], true);
//
//            $index .= $lesson['subjectName'];
//
//            if (!empty($lesson['method'])) {
//                $index .= " - {$lesson['method']}";
//            }
//
//            $index           .= " - {$lesson['id']}";
//            $lessons[$index] = $lesson;
//        }
//
//        ksort($lessons);
//
//        return $lessons;
//    }
//
//    /**
//     * Get name of course/lesson
//     *
//     * @param int $courseID
//     *
//     * @return string
//     */
//    public static function getNameByLessonID($courseID = 0)
//    {
//        $courseID = Input::getInt('lessonID', $courseID);
//
//        if (empty($courseID)) {
//            return '';
//        }
//
//        $tag   = Languages::getTag();
//        $dbo   = Factory::getDbo();
//        $query = $dbo->getQuery(true);
//        $query->select("name_$tag")
//            ->from('#__thm_organizer_lesson_courses AS lc')
//            ->innerJoin('#__thm_organizer_subject_mappings AS map ON map.courseID = lc.courseID')
//            ->innerJoin('#__thm_organizer_subjects AS s ON s.id = map.subjectID')
//            ->where("lc.lessonID = '{$courseID}'");
//        $dbo->setQuery($query);
//
//        return (string)OrganizerHelper::executeQuery('loadResult');
//    }
//
//    /**
//     * Retrieves the course name
//     *
//     * @param int     $courseID the table id for the subject
//     * @param boolean $withNumber
//     *
//     * @return string the course name
//     */
//    public static function getName($courseID, $withNumber = false)
//    {
//        $dbo = Factory::getDbo();
//        $tag = Languages::getTag();
//
//        $query = $dbo->getQuery(true);
//        $query->select("co.name as courseName, s.name_$tag as name")
//            ->select("s.shortName_$tag as shortName, s.abbreviation_$tag as abbreviation")
//            ->select('co.subjectNo as courseSubjectNo, s.code as subjectNo')
//            ->from('#__thm_organizer_courses AS co')
//            ->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.courseID = co.id')
//            ->leftJoin('#__thm_organizer_subjects AS s ON s.id = sm.subjectID')
//            ->where("co.id = '$courseID'");
//
//        $dbo->setQuery($query);
//
//        $names = OrganizerHelper::executeQuery('loadAssoc', []);
//        if (empty($names)) {
//            return '';
//        }
//
//        $suffix = '';
//
//        if ($withNumber) {
//            if (!empty($names['subjectNo'])) {
//                $suffix .= " ({$names['subjectNo']})";
//            } elseif (!empty($names['courseSubjectNo'])) {
//                $suffix .= " ({$names['courseSubjectNo']})";
//            }
//        }
//
//        if (!empty($names['name'])) {
//            return $names['name'] . $suffix;
//        }
//
//        if (!empty($names['shortName'])) {
//            return $names['shortName'] . $suffix;
//        }
//
//        return empty($names['courseName']) ? $names['abbreviation'] . $suffix : $names['courseName'] . $suffix;
//    }
//
//    /**
//     * Creates a status display for the user's relation to the respective course.
//     *
//     * @param int $courseID the id of the course
//     *
//     * @return string the HTML for the status display
//     */
//    public static function getStatusDisplay($courseID)
//    {
//        $expired    = !self::isRegistrationOpen($courseID);
//        $authorized = self::authorized($courseID);
//
//        // Personal Status
//        $none        = $expired ?
//            Languages::_('THM_ORGANIZER_EXPIRED') : Languages::_('THM_ORGANIZER_COURSE_NOT_REGISTERED');
//        $notLoggedIn = '<span class="icon-warning"></span>' . Languages::_('THM_ORGANIZER_NOT_LOGGED_IN');
//        $waitList    = '<span class="icon-checkbox-partial"></span>' . Languages::_('THM_ORGANIZER_WAIT_LIST');
//        $registered  = '<span class="icon-checkbox-checked"></span>' . Languages::_('THM_ORGANIZER_REGISTERED');
//
//        if (!empty(Factory::getUser()->id)) {
//            if ($authorized) {
//                $userStatus = Languages::_('THM_ORGANIZER_COURSE_ADMINISTRATOR');
//            } else {
//                $regState = self::getParticipantState($courseID);
//
//                if (empty($regState)) {
//                    $text = $none;
//                } else {
//                    $text = empty($regState['status']) ? $waitList : $registered;
//                }
//
//                $disabled   = '<span class="disabled">%s</span>';
//                $userStatus = $expired ? sprintf($disabled, $text) : $text;
//            }
//        } else {
//            $userStatus = $expired ? $none : '<span class="disabled">' . $notLoggedIn . '</span>';
//        }
//
//        return $userStatus;
//    }
//
//    /**
//     * Gets the subject id which corresponds to a given lesson id
//     *
//     * @param int $lessonID the id of the course
//     *
//     * @return int the id of the subject or 0 if the course could not be resolved to a subject
//     */
//    public static function getSubjectID($lessonID)
//    {
//        if (empty($lessonID)) {
//            return 0;
//        }
//
//        $dbo   = Factory::getDbo();
//        $query = $dbo->getQuery(true);
//
//        $query->select('sm.subjectID AS id')
//            ->from('#__thm_organizer_subject_mappings AS sm')
//            ->innerJoin('#__thm_organizer_lesson_courses AS lc ON lc.courseID = sm.courseID')
//            ->where("lc.lessonID = '$lessonID'");
//
//        $dbo->setQuery($query);
//
//        return (int)OrganizerHelper::executeQuery('loadResult');
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
//     * Get formatted array with all prep courses in format id => name
//     *
//     * @return array  assoc array with all prep courses with id => name
//     */
//    public static function prepCourseList()
//    {
//        $dbo   = Factory::getDbo();
//        $tag   = Languages::getTag();
//        $query = $dbo->getQuery(true);
//
//        $query->select("id, name_$tag AS name")
//            ->from('#__thm_organizer_subjects')
//            ->where("is_prep_course = '1'")
//            ->order('name ASC');
//
//        $dbo->setQuery($query);
//
//        $courses = OrganizerHelper::executeQuery('loadAssocList');
//        if (empty($courses)) {
//            return [];
//        }
//
//        $return = [];
//        foreach ($courses as $course) {
//            $return[$course['id']] = $course['name'];
//        }
//
//        return $return;
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
//
//    /**
//     * Check if user is registered as a person, optionally for a specific course
//     *
//     * @param int $lessonID id of the lesson resource
//     *
//     * @return boolean if user is authorized
//     */
//    public static function teaches($lessonID = 0)
//    {
//        $subjectID = self::getSubjectID($lessonID);
//
//        // Documented coordinator
//        if (Access::allowSubjectAccess($subjectID)) {
//            return true;
//        }
//
//        $user  = Factory::getUser();
//        $dbo   = Factory::getDbo();
//        $query = $dbo->getQuery(true);
//
//        $query->select('COUNT(*)')
//            ->from('#__thm_organizer_lesson_courses AS lc')
//            ->innerJoin('#__thm_organizer_lesson_persons AS lt ON lt.lessonCourseID = lc.id')
//            ->innerJoin('#__thm_organizer_persons AS t ON t.id = lt.personID')
//            ->where("t.username = '{$user->username}'");
//
//        if (!empty($lessonID)) {
//            $query->where("lc.lessonID = '$lessonID'");
//        }
//
//        $dbo->setQuery($query);
//
//        return (bool)OrganizerHelper::executeQuery('loadResult');
//    }
}
