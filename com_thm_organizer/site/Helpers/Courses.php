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

use DateInterval;
use DateTime;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;
use stdClass;

/**
 * Provides general functions for course access checks, data retrieval and display.
 */
class Courses
{
    const MANUAL_ACCEPTANCE = 1;
    const PERIOD_MODE = 2;
    const INSTANCE_MODE = 3;

    /**
     * Check if course with specific id is full
     *
     * @param int $courseID identifier of course
     *
     * @return bool true when course can accept more participants, false otherwise
     */
    public static function canAcceptParticipant($courseID)
    {
        $course = self::getCourse($courseID);

        if (empty($course)) {
            return false;
        }

        $open = self::isRegistrationOpen($courseID);
        if (empty($open)) {
            return false;
        }

        $regType          = $course['registration_type'];
        $manualAcceptance = (!empty($regType) and $regType === self::MANUAL_ACCEPTANCE);

        if ($manualAcceptance) {
            return false;
        }

        $acceptedParticipants = count(self::getParticipants($courseID, 1));
        $maxParticipants      = empty($course['lessonP']) ? $course['subjectP'] : $course['lessonP'];

        if (empty($maxParticipants)) {
            return true;
        }

        return ($acceptedParticipants < $maxParticipants);
    }

    /**
     * Creates a button for user interaction with the course. (De-/registration, Administration)
     *
     * @param string $view     the view to be redirected to after registration action
     * @param int    $courseID the id of the course
     *
     * @return string the HTML for the action button as appropriate for the user
     */
    public static function getActionButton($view, $courseID)
    {
        $expired    = !self::isRegistrationOpen($courseID);
        $authorized = self::authorized($courseID);

        $tag             = Languages::getTag();
        $menuID          = OrganizerHelper::getInput()->getInt('Itemid');
        $pathPrefix      = 'index.php?option=com_thm_organizer';
        $managerURL      = "{$pathPrefix}&view=courses&languageTag=$tag";
        $registrationURL = "$pathPrefix&task=$view.register&languageTag=$tag";
        $registrationURL .= $view == 'subject' ? '&id=' . OrganizerHelper::getInput()->getInt('id', 0) : '';

        if (!empty($menuID)) {
            $managerURL      .= "&Itemid=$menuID";
            $registrationURL .= "&Itemid=$menuID";
        }

        if (!empty(Factory::getUser()->id)) {
            $lessonURL = "&lessonID=$courseID";

            if ($authorized) {
                $manage       = '<span class="icon-cogs"></span>' . Languages::_('THM_ORGANIZER_MANAGE');
                $managerRoute = Route::_($managerURL . $lessonURL);
                $register     = "<a class='btn' href='$managerRoute'>$manage</a>";
            } else {
                $regState = self::getParticipantState($courseID);

                if ($expired) {
                    $register = '';
                } else {
                    $registerRoute = Route::_($registrationURL . $lessonURL);

                    if (!empty($regState)) {
                        $registerText = '<span class="icon-out-2"></span>';
                        $registerText .= Languages::_('THM_ORGANIZER_COURSE_DEREGISTER');
                    } else {
                        $registerText = '<span class="icon-apply"></span>';
                        $registerText .= Languages::_('THM_ORGANIZER_COURSE_REGISTER');
                    }

                    $register = "<a class='btn' href='$registerRoute' type='button'>$registerText</a>";
                }
            }
        } else {
            $register = '';
        }

        return $register;
    }

    /**
     * Sets campus information (id and name) for a given course
     *
     * @param mixed $course    the course information (array|int|object)
     * @param bool  $redundant whether redundant names should be set
     *
     * @return array an array with the actionable campus id and name
     */
    public static function getCampus($course, $redundant = false)
    {
        if (is_object($course)) {
            $course = (array)$course;
        } elseif (is_int($course)) {
            $course = self::getCourse($course);
        }

        if (empty($course['abstractCampusID']) and empty($course['campusID'])) {
            $campus = ['id' => '', 'name' => Campuses::getName()];
        } elseif (empty($course['campusID']) or $course['abstractCampusID'] == $course['campusID']) {
            $campus         = ['id' => $course['abstractCampusID']];
            $campus['name'] = $redundant ? Campuses::getName($course['abstractCampusID']) : null;
        } else {
            $campus = [
                'id'   => $course['campusID'],
                'name' => Campuses::getName($course['campusID'])
            ];
        }

        return $campus;
    }

    /**
     * Looks up the names of the categories associated with the course
     *
     * @param int $courseID the id of the course
     *
     * @return array the associated program names
     */
    public static function getCategories($courseID)
    {
        $names = [];
        $dbo   = Factory::getDbo();
        $tag   = Languages::getTag();

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$tag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
        $query->select('cat.name AS categoryName, ' . $query->concatenate($nameParts, "") . ' AS name')
            ->select('cat.id')
            ->from('#__thm_organizer_categories AS cat')
            ->innerJoin('#__thm_organizer_groups AS gr ON gr.categoryID = cat.id')
            ->innerJoin('#__thm_organizer_lesson_groups AS lg ON lg.groupID = gr.id')
            ->innerJoin('#__thm_organizer_lesson_courses AS lc ON lc.id = lg.lessonCourseID')
            ->leftJoin('#__thm_organizer_programs AS p ON cat.programID = p.id')
            ->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id')
            ->where("lc.courseID = '$courseID'");

        $dbo->setQuery($query);

        $results = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($results)) {
            return [];
        }

        foreach ($results as $result) {
            $names[$result['id']] = empty($result['name']) ? $result['categoryName'] : $result['name'];
        }

        return $names;
    }

    /**
     * Loads course information from the database
     *
     * @param int $courseID int id of requested lesson
     *
     * @return array  with course data on success, otherwise empty
     */
    public static function getCourse($courseID = 0)
    {
        $courseID = OrganizerHelper::getInput()->getInt('lessonID', $courseID);

        if (empty($courseID)) {
            return [];
        }

        $dbo   = Factory::getDbo();
        $tag   = Languages::getTag();
        $query = $dbo->getQuery(true);

        $query->select('term.name as termName, term.id as termID')
            ->select('l.id, l.max_participants as lessonP, l.campusID AS campusID')
            ->select('l.registration_type, l.deadline, l.fee')
            ->select("s.id as subjectID, s.name_$tag as name, s.instructionLanguage")
            ->select('s.campusID AS abstractCampusID, s.is_prep_course, s.max_participants as subjectP');

        $query->from('#__thm_organizer_lessons AS l');
        $query->leftJoin('#__thm_organizer_lesson_courses AS lc ON lc.lessonID = l.id');
        $query->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.courseID = lc.courseID');
        $query->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');
        $query->leftJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id');
        $query->leftJoin('#__thm_organizer_terms AS term ON l.termID = term.id');
        $query->where("l.id = '$courseID'");

        $dbo->setQuery($query);
        $courseData = OrganizerHelper::executeQuery('loadAssoc', []);

        // If empty it should stay empty
        if (empty($courseData)) {
            return $courseData;
        }

        $params = OrganizerHelper::getParams();
        if (empty($courseData['deadline'])) {
            $courseData['deadline'] = $params->get('deadline', 5);
        }

        if ($courseData['fee'] === null) {
            $courseData['fee'] = $params->get('fee', 50);
        }

        return $courseData;
    }

    /**
     * Creates a display of formatted dates for a course
     *
     * @param int $courseID the id of the course to be loaded
     *
     * @return string the dates to display
     */
    public static function getDateDisplay($courseID = 0)
    {
        $courseID = OrganizerHelper::getInput()->getInt('lessonID', $courseID);

        $dates = self::getDates($courseID);

        if (!empty($dates)) {
            $dateFormat = OrganizerHelper::getParams()->get('dateFormat', 'd.m.Y');
            $start      = HTML::_('date', $dates[0], $dateFormat);
            $end        = HTML::_('date', end($dates), $dateFormat);

            return "$start - $end";
        }

        return '';
    }

    /**
     * Loads all calendar information for specific course  from the database
     *
     * @param int $courseID id of course to be loaded
     *
     * @return array  array with calendar registration data on success, otherwise empty
     */
    public static function getDates($courseID = 0)
    {
        $courseID = OrganizerHelper::getInput()->getInt('lessonID', $courseID);

        if (empty($courseID)) {
            return [];
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('DISTINCT schedule_date');
        $query->from('#__thm_organizer_lessons AS l');
        $query->leftJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id');
        $query->where("l.id = '$courseID'");
        $query->where("c.delta != 'removed'");
        $query->order('c.schedule_date');

        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadColumn', []);
    }

    /**
     * Loads all all participants for specific course from database
     *
     * @param int     $courseID id of course to be loaded
     * @param boolean $includeWaitList
     *
     * @return array  with course registration data on success, otherwise empty
     */
    public static function getFullParticipantData($courseID = 0, $includeWaitList = false)
    {
        if (empty($courseID)) {
            return [];
        }


        $dbo   = Factory::getDbo();
        $tag   = Languages::getTag();
        $query = $dbo->getQuery(true);

        $nameParts    = ['pt.surname', "', '", 'pt.forename'];
        $programParts = ["pr.name_$tag", "' ('", 'dg.abbreviation', "' '", 'pr.version', "')'"];

        $query->select($query->concatenate($nameParts, '') . ' AS userName, pt.address, pt.zip_code, pt.city')
            ->select('u.id, u.email')
            ->select($query->concatenate($programParts, '') . ' AS programName, pr.id AS programID')
            ->select("dp.short_name_$tag AS departmentName, dp.id AS departmentID");

        $query->from('#__thm_organizer_user_lessons AS ul');
        $query->innerJoin('#__users AS u ON u.id = ul.userID');
        $query->innerJoin('#__thm_organizer_participants AS pt ON pt.id = ul.userID');
        $query->innerJoin('#__thm_organizer_programs AS pr ON pr.id = pt.programID');
        $query->innerJoin('#__thm_organizer_degrees AS dg ON dg.id = pr.degreeID');
        $query->innerJoin('#__thm_organizer_departments AS dp ON dp.id = pr.departmentID');
        $query->where("ul.lessonID = '$courseID'");

        if (!$includeWaitList) {
            $query->where("ul.status = '1'");
        }

        $query->order('userName');

        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

    /**
     * Retrieves the course instances
     *
     * @param int    $courseID     the id of the course
     * @param int    $mode         the retrieval mode (empty => all, 2 => same block, 3 => single instance
     * @param object $calReference a reference calendar entry modeled on an object
     *
     * @return array the instance ids on success, otherwise empty
     */
    public static function getInstances($courseID, $mode = null, $calReference = null)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('map.id')
            ->from('#__thm_organizer_calendar_configuration_map AS map')
            ->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
            ->where("cal.lessonID = '$courseID'")
            ->where("delta != 'removed'");

        // Restrictions
        if ($mode == self::PERIOD_MODE or $mode == self::INSTANCE_MODE) {
            $query->where("cal.startTime = '$calReference->startTime'");
            $query->where("cal.endTime = '$calReference->endTime'");

            if ($mode == self::INSTANCE_MODE) {
                $query->where("cal.schedule_date = '$calReference->schedule_date'");
            } else {
                $query->where("DAYOFWEEK(cal.schedule_date) = '$calReference->weekday'");
            }
        }

        $query->order('map.id');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadColumn', []);
    }

    /**
     * Loads course information from the database
     *
     * @param int $subjectID id of subject with which courses must be associated
     * @param int $campusID  id of the course campus
     *
     * @return array  with course data on success, otherwise empty
     */
    public static function getLatestCourses($subjectID, $campusID = null)
    {
        if (empty($subjectID)) {
            return [];
        }

        $dbo   = Factory::getDbo();
        $tag   = Languages::getTag();
        $query = $dbo->getQuery(true);

        $query->select('DISTINCT l.id, l.max_participants as lessonP, l.campusID AS campusID')
            ->from('#__thm_organizer_lessons AS l')
            ->innerJoin('#__thm_organizer_lesson_courses AS lc ON lc.lessonID = l.id')
            ->innerJoin('#__thm_organizer_subject_mappings AS sm ON sm.courseID = lc.courseID')
            ->select("s.id as subjectID, s.name_$tag as name, s.campusID AS abstractCampusID")
            ->select('s.instructionLanguage, s.max_participants as subjectP')
            ->innerJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id')
            ->innerJoin('#__thm_organizer_calendar AS ca ON ca.lessonID = l.id')
            ->select('term.name as termName')
            ->innerJoin('#__thm_organizer_terms AS term ON term.id = l.termID')
            ->leftJoin('#__thm_organizer_campuses as cp on s.campusID = cp.id')
            ->where("s.id = '$subjectID'")
            ->where("(s.is_prep_course = '1' OR s.registration_type IS NOT NULL OR l.registration_type IS NOT NULL)");

        $query->order('schedule_date DESC');

        if (!empty($campusID)) {
            $campusConditions = "(l.campusID = '{$campusID}' OR (l.campusID IS NULL AND ";
            $campusConditions .= "(c.id = '{$campusID}' OR c.parentID = '{$campusID}' OR s.campusID IS NULL)))";
            $query->where($campusConditions);
        }

        $dbo->setQuery($query);

        $courses = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($courses)) {
            return [];
        }

        $campuses = [];

        foreach ($courses as $index => &$course) {
            $campus   = self::getCampus($course);
            $campusID = empty($campus['id']) ? 0 : $campus['id'];

            if (isset($campuses[$campusID])) {
                unset($courses[$index]);
                continue;
            }

            $course['campus']    = $campus;
            $campuses[$campusID] = $campusID;
        }

        return $courses;
    }

    /**
     * Retrieves a list of lessons associated with a subject
     *
     * @return array the lessons associated with the subject
     */
    public static function getLessons()
    {
        $input = OrganizerHelper::getInput();

        $courseIDs = ArrayHelper::toInteger(explode(',', $input->getString('courseIDs', '')));
        if (empty($courseIDs[0])) {
            return [];
        }
        $courseIDs = implode(',', $courseIDs);

        $date = $input->getString('date');
        if (!Dates::isStandardized($date)) {
            $date = date('Y-m-d');
        }

        $interval = $input->getString('interval');
        if (!in_array($interval, ['day', 'week', 'month', 'semester'])) {
            $interval = 'semester';
        }

        $tag = Languages::getTag();

        $dbo = Factory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select("DISTINCT l.id, l.comment, lc.courseID, m.abbreviation_$tag AS method")
            ->from('#__thm_organizer_lessons AS l')
            ->innerJoin('#__thm_organizer_methods AS m on m.id = l.methodID')
            ->innerJoin('#__thm_organizer_lesson_courses AS lc on lc.lessonID = l.id')
            ->where("lc.courseID IN ($courseIDs)")
            ->where("l.delta != 'removed'")
            ->where("lc.delta != 'removed'");

        $dateTime = strtotime($date);
        switch ($interval) {
            case 'semester':
                $query->innerJoin('#__thm_organizer_terms AS term ON term.id = l.termID')
                    ->where("'$date' BETWEEN term.startDate AND term.endDate");
                break;
            case 'month':
                $monthStart = date('Y-m-d', strtotime('first day of this month', $dateTime));
                $startDate  = date('Y-m-d', strtotime('Monday this week', strtotime($monthStart)));
                $monthEnd   = date('Y-m-d', strtotime('last day of this month', $dateTime));
                $endDate    = date('Y-m-d', strtotime('Sunday this week', strtotime($monthEnd)));
                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
                    ->where("c.schedule_date BETWEEN '$startDate' AND '$endDate'");
                break;
            case 'week':
                $startDate = date('Y-m-d', strtotime('Monday this week', $dateTime));
                $endDate   = date('Y-m-d', strtotime('Sunday this week', $dateTime));
                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
                    ->where("c.schedule_date BETWEEN '$startDate' AND '$endDate'");
                break;
            case 'day':
                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
                    ->where("c.schedule_date = '$date'");
                break;
        }

        $dbo->setQuery($query);

        $results = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($results)) {
            return [];
        }

        $lessons = [];
        foreach ($results as $lesson) {
            $index = '';

            $lesson['subjectName'] = Courses::getName($lesson['subjectID'], true);

            $index .= $lesson['subjectName'];

            if (!empty($lesson['method'])) {
                $index .= " - {$lesson['method']}";
            }

            $index           .= " - {$lesson['id']}";
            $lessons[$index] = $lesson;
        }

        ksort($lessons);

        return $lessons;
    }

    /**
     * Get name of course/lesson
     *
     * @param int $courseID
     *
     * @return string
     */
    public static function getNameByLessonID($courseID = 0)
    {
        $courseID = OrganizerHelper::getInput()->getInt('lessonID', $courseID);

        if (empty($courseID)) {
            return '';
        }

        $tag   = Languages::getTag();
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("name_$tag")
            ->from('#__thm_organizer_lesson_courses AS lc')
            ->innerJoin('#__thm_organizer_subject_mappings AS map ON map.courseID = lc.courseID')
            ->innerJoin('#__thm_organizer_subjects AS s ON s.id = map.subjectID')
            ->where("lc.lessonID = '{$courseID}'");
        $dbo->setQuery($query);

        return (string)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Retrieves the course name
     *
     * @param int     $courseID the table id for the subject
     * @param boolean $withNumber
     *
     * @return string the course name
     */
    public static function getName($courseID, $withNumber = false)
    {
        $dbo = Factory::getDbo();
        $tag = Languages::getTag();

        $query = $dbo->getQuery(true);
        $query->select("co.name as courseName, s.name_$tag as name")
            ->select("s.short_name_$tag as shortName, s.abbreviation_$tag as abbreviation")
            ->select('co.subjectNo as courseSubjectNo, s.externalID as subjectNo')
            ->from('#__thm_organizer_courses AS co')
            ->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.courseID = co.id')
            ->leftJoin('#__thm_organizer_subjects AS s ON s.id = sm.subjectID')
            ->where("co.id = '$courseID'");

        $dbo->setQuery($query);

        $names = OrganizerHelper::executeQuery('loadAssoc', []);
        if (empty($names)) {
            return '';
        }

        $suffix = '';

        if ($withNumber) {
            if (!empty($names['subjectNo'])) {
                $suffix .= " ({$names['subjectNo']})";
            } elseif (!empty($names['courseSubjectNo'])) {
                $suffix .= " ({$names['courseSubjectNo']})";
            }
        }

        if (!empty($names['name'])) {
            return $names['name'] . $suffix;
        }

        if (!empty($names['shortName'])) {
            return $names['shortName'] . $suffix;
        }

        return empty($names['courseName']) ? $names['abbreviation'] . $suffix : $names['courseName'] . $suffix;
    }

    /**
     * Get list of registered students in specific course
     *
     * @param int $courseID identifier of course
     * @param int $status   status of participants (1 registered, 0 waiting)
     *
     * @return mixed list of students in course with $id, false on error
     */
    public static function getParticipants($courseID, $status = null)
    {
        if (empty($courseID)) {
            return [];
        }

        $dbo   = Factory::getDbo();
        $tag   = Languages::getTag();
        $query = $dbo->getQuery(true);

        $query->select("l.*, pt.*, u.email, u.username, u.id as cid, p.name_$tag as program")
            ->select('CONCAT(pt.surname, ", ", pt.forename) as name')
            ->from('#__thm_organizer_user_lessons as ul')
            ->innerJoin('#__users as u on u.id = ul.userID')
            ->leftJoin('#__thm_organizer_participants as pt on pt.id = ul.userID')
            ->leftJoin('#__thm_organizer_programs as p on p.id = pt.programID')
            ->where("ul.lessonID = '$courseID'")
            ->order('name ASC');

        if ($status === 1) {
            $query->where("ul.status = '1'");
        } elseif ($status === 0) {
            $query->where("ul.status = '0'");
        }

        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

    /**
     * Figure out if student is signed into course
     *
     * @param int $courseID of lesson
     *
     * @return array containing the user specific information or empty on error
     */
    public static function getParticipantState($courseID = 0)
    {
        $userID   = Factory::getUser()->id;
        $courseID = OrganizerHelper::getInput()->getInt('lessonID', $courseID);

        if (empty($courseID) || empty($userID)) {
            return [];
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_user_lessons');
        $query->where("userID = '$userID' AND lessonID = '$courseID'");
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssoc', []);
    }

    /**
     * Creates a status display for the user's relation to the respective course.
     *
     * @param int $courseID the id of the course
     *
     * @return string the HTML for the status display
     */
    public static function getStatusDisplay($courseID)
    {
        $expired    = !self::isRegistrationOpen($courseID);
        $authorized = self::authorized($courseID);

        // Personal Status
        $none        = $expired ?
            Languages::_('THM_ORGANIZER_EXPIRED') : Languages::_('THM_ORGANIZER_COURSE_NOT_REGISTERED');
        $notLoggedIn = '<span class="icon-warning"></span>' . Languages::_('THM_ORGANIZER_NOT_LOGGED_IN');
        $waitList    = '<span class="icon-checkbox-partial"></span>' . Languages::_('THM_ORGANIZER_WAIT_LIST');
        $registered  = '<span class="icon-checkbox-checked"></span>' . Languages::_('THM_ORGANIZER_COURSE_REGISTERED');

        if (!empty(Factory::getUser()->id)) {
            if ($authorized) {
                $userStatus = Languages::_('THM_ORGANIZER_COURSE_ADMINISTRATOR');
            } else {
                $regState = self::getParticipantState($courseID);

                if (empty($regState)) {
                    $text = $none;
                } else {
                    $text = empty($regState['status']) ? $waitList : $registered;
                }

                $disabled   = '<span class="disabled">%s</span>';
                $userStatus = $expired ? sprintf($disabled, $text) : $text;
            }
        } else {
            $userStatus = $expired ? $none : '<span class="disabled">' . $notLoggedIn . '</span>';
        }

        return $userStatus;
    }

    /**
     * Gets the subject id which corresponds to a given lesson id
     *
     * @param int $lessonID the id of the course
     *
     * @return int the id of the subject or 0 if the course could not be resolved to a subject
     */
    public static function getSubjectID($lessonID)
    {
        if (empty($lessonID)) {
            return 0;
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('sm.subjectID AS id')
            ->from('#__thm_organizer_subject_mappings AS sm')
            ->innerJoin('#__thm_organizer_lesson_courses AS lc ON lc.courseID = sm.courseID')
            ->where("lc.lessonID = '$lessonID'");

        $dbo->setQuery($query);

        return (int)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Check if the course is open for registration
     *
     * @param int $courseID id of lesson
     *
     * @return bool true if registration deadline not yet in the past, false otherwise
     */
    public static function isRegistrationOpen($courseID = 0)
    {
        $dates = self::getDates($courseID);
        if (empty($dates)) {
            return false;
        }

        try {
            $startDate = new DateTime($dates[0]);
            $deadline  = self::getCourse($courseID)['deadline'];
            $interval  = new DateInterval("P{$deadline}D");
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');

            return false;
        }

        $adjustedDate = new DateTime;
        $adjustedDate->add($interval);

        return $startDate > $adjustedDate;
    }

    /**
     * Get formatted array with all prep courses in format id => name
     *
     * @return array  assoc array with all prep courses with id => name
     */
    public static function prepCourseList()
    {
        $dbo   = Factory::getDbo();
        $tag   = Languages::getTag();
        $query = $dbo->getQuery(true);

        $query->select("id, name_$tag AS name")
            ->from('#__thm_organizer_subjects')
            ->where("is_prep_course = '1'")
            ->order('name ASC');

        $dbo->setQuery($query);

        $courses = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($courses)) {
            return [];
        }

        $return = [];
        foreach ($courses as $course) {
            $return[$course['id']] = $course['name'];
        }

        return $return;
    }

    /**
     * Might move users from state waiting to registered
     *
     * @param int $courseID lesson id of lesson where participants have to be moved up
     *
     * @return void
     */
    public static function refreshWaitList($courseID)
    {
        $canAccept = self::canAcceptParticipant($courseID);

        if ($canAccept) {
            $dbo   = Factory::getDbo();
            $query = $dbo->getQuery(true);

            $query->select('userID');
            $query->from('#__thm_organizer_user_lessons');
            $query->where("lessonID = '$courseID' and status = '0'");
            $query->order('status_date, user_date');

            $dbo->setQuery($query);

            $nextParticipantID = OrganizerHelper::executeQuery('loadResult');

            if (!empty($nextParticipantID)) {
                Participants::changeState($nextParticipantID, $courseID, 1);
            }
        }
    }

    /**
     * Check if user is registered as a teacher, optionally for a specific course
     *
     * @param int $lessonID id of the lesson resource
     *
     * @return boolean if user is authorized
     */
    public static function teaches($lessonID = 0)
    {
        $subjectID = self::getSubjectID($lessonID);

        // Documented coordinator
        if (Access::allowSubjectAccess($subjectID)) {
            return true;
        }

        $user  = Factory::getUser();
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('COUNT(*)')
            ->from('#__thm_organizer_lesson_courses AS lc')
            ->innerJoin('#__thm_organizer_lesson_teachers AS lt ON lt.lessonCourseID = lc.id')
            ->innerJoin('#__thm_organizer_teachers AS t ON t.id = lt.teacherID')
            ->where("t.username = '{$user->username}'");

        if (!empty($lessonID)) {
            $query->where("lc.lessonID = '$lessonID'");
        }

        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadResult');
    }
}
