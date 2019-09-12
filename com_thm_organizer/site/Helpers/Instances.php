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

use Exception;
use JDatabaseQuery;
use Joomla\CMS\Factory;

/**
 * Provides functions for XML lesson validation and modeling.
 */
class Instances extends ResourceHelper
{
    const SEMESTER_MODE = 1;

    const PERIOD_MODE = 2;

    const INSTANCE_MODE = 3;

    /**
     * Adds a delta clause for a joined table.
     *
     * @param JDatabaseQuery &$query the query to be modified
     * @param string          $alias the table alias
     * @param mixed           $delta string the date for the delta or bool false
     *
     * @return void modifies the query
     */
    private static function addDeltaClause(&$query, $alias, $delta)
    {
        if ($delta) {
            $query->where("($alias.delta != 'removed' OR $alias.modified > '$delta')");
        } else {
            $query->where("$alias.delta != 'removed'");
        }
    }

    /**
     * Builds the array of parameters used for lesson retrieval.
     *
     * @return array the paramters used to retrieve lessons.
     * @throws Exception unauthorized access to person schedules.
     */
    public static function getConditions()
    {
        $conditions               = [];
        $conditions['userID']     = Users::getID();
        $conditions['mySchedule'] = empty($conditions['userID']) ? false : Input::getBool('mySchedule', false);
        $conditions['date']       = Dates::standardizeDate(Input::getCMD('date'));

        $interval               = Input::getCMD('interval', 'week');
        $validRestrictions      = ['day', 'ics', 'month', 'semester', 'week'];
        $conditions['interval'] = in_array($interval, $validRestrictions) ? $interval : 'week';

        self::setDates($conditions);

        $delta               = Input::getInt('delta', 0);
        $conditions['delta'] = empty($delta) ? false : date('Y-m-d', strtotime('-' . $delta . ' days'));

        if (empty($conditions['mySchedule'])) {

            // Instance aggregates
            if ($courseIDs = Input::getFilterIDs('course')) {
                $conditions['courseIDs'] = $courseIDs;
            }

            if ($departmentIDs = Input::getFilterIDs('department')) {
                $conditions['departmentIDs'] = $departmentIDs;
            }

            // Department specific events
            if ($eventIDs = Input::getFilterIDs('event')) {
                $conditions['eventIDs'] = $eventIDs;
            }

            if ($groupIDs = Input::getFilterIDs('group')) {
                $conditions['groupIDs'] = $groupIDs;
            }

            if ($personIDs = Input::getFilterIDs('person')) {
                self::filterPersonIDs($personIDs, $conditions['userID']);
                if ($personIDs) {
                    $conditions['personIDs'] = $personIDs;
                } else {
                    throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
                }
            }


            if ($roomIDs = Input::getFilterIDs('room')) {
                $conditions['roomIDs'] = $roomIDs;
            }

            // Documented and associated subjects
            if ($subjectIDs = Input::getFilterIDs('subject')) {
                $conditions['subjectIDs'] = $subjectIDs;
            }

            if (count($conditions['departmentIDs'])) {
                $allowedIDs   = Access::getAccessibleDepartments('schedule');
                $overlap      = array_intersect($conditions['departmentIDs'], $allowedIDs);
                $overlapCount = count($overlap);

                // If the user has planning access to all requested departments show unpublished automatically.
                if ($overlapCount and $overlapCount == count($conditions['departmentIDs'])) {
                    $conditions['departmentIDs']   = $overlap;
                    $conditions['showUnpublished'] = true;
                } else {
                    $conditions['showUnpublished'] = false;
                }
            } else {
                $conditions['showUnpublished'] = Access::isAdmin();
            }
        } elseif ($personID = Persons::getIDByUserID($conditions['userID'])) {
            // Schedule items which have been planned for the person should appear in their schedule
            $conditions['personIDs']       = [$personID];
            $conditions['showUnpublished'] = true;
        }

        return $conditions;
    }

    public static function getItems($conditions)
    {
        $instanceIDs = self::getInstanceIDs($conditions);
        $instances   = [];
        foreach ($instanceIDs as $instanceID) {
            if (!$instance = self::getInstance($instanceID)) {
                continue;
            }

            self::setPersons($instance, $conditions);
            if (empty($instance['resources'])) {
                continue;
            }

            self::setCourse($instance, $conditions);
            self::setSubject($instance, $conditions);

            $instances[] = $instance;
        }
        echo "<pre>" . print_r($instances, true) . "</pre>";
        die;
    }

    /**
     * Retrieves the core information for one instance.
     *
     * @param int $instanceID the id of the instance
     *
     * @return array an array modelling the instance
     */
    private static function getInstance($instanceID)
    {
        $tag = Languages::getTag();

        $instancesTable = self::getTable();
        if (!$instancesTable->load($instanceID)) {
            return [];
        }

        $instance = [
            'blockID'        => $instancesTable->blockID,
            'eventID'        => $instancesTable->eventID,
            'instanceID'     => $instanceID,
            'instanceStatus' => $instancesTable->delta,
            'methodID'       => $instancesTable->methodID,
            'unitID'         => $instancesTable->unitID
        ];

        unset($instancesTable);

        $blocksTable = OrganizerHelper::getTable('Blocks');
        if (!$blocksTable->load($instance['blockID'])) {
            return [];
        }

        $block = [
            'date'      => $blocksTable->date,
            'endTime'   => $blocksTable->endTime,
            'startTime' => $blocksTable->startTime
        ];

        unset($blocksTable);

        $eventsTable = OrganizerHelper::getTable('Events');
        if (!$eventsTable->load($instance['eventID'])) {
            return [];
        }

        $event = [
            'campusID'         => $eventsTable->campusID,
            'deadline'         => $eventsTable->deadline,
            'description'      => $eventsTable->{"description_$tag"},
            'fee'              => $eventsTable->fee,
            'name'             => $eventsTable->{"name_$tag"},
            'registrationType' => $eventsTable->registrationType,
            'subjectNo'        => $eventsTable->subjectNo
        ];

        unset($eventsTable);

        $method       = ['methodCode' => '', 'methodName' => ''];
        $methodsTable = OrganizerHelper::getTable('Methods');
        if ($methodsTable->load($instance['methodID'])) {
            $method = [
                'methodCode' => $methodsTable->{"abbreviation_$tag"},
                'method'     => $methodsTable->{"name_$tag"}
            ];
        }

        unset($methodsTable);

        $unitsTable = OrganizerHelper::getTable('Units');
        if (!$unitsTable->load($instance['unitID'])) {
            return [];
        }

        $unit = [
            'comment'      => $unitsTable->comment,
            'courseID'     => $unitsTable->courseID,
            'departmentID' => $unitsTable->departmentID,
            'gridID'       => $unitsTable->gridID,
            'unitStatus'   => $unitsTable->delta
        ];

        unset($unitsTable);

        return array_merge($block, $event, $instance, $method, $unit);
    }

    /**
     * Retrieves a list of instance IDs for instances which fulfill the requirements.
     *
     * @param array $conditions the conditions filtering the instances
     *
     * @return array the ids matching the conditions
     */
    public static function getInstanceIDs($conditions)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        // TODO: resolve course information (registration type, available capacity) and consequences
        $query->select('DISTINCT i.id')
            ->from('#__thm_organizer_instances AS i')
            ->innerJoin('#__thm_organizer_blocks AS b ON b.id = i.blockID')
            ->innerJoin('#__thm_organizer_units AS u ON u.id = i.unitID')
            ->where("b.date BETWEEN '{$conditions['startDate']} 00:00:00' AND '{$conditions['endDate']} 23:59:59'")
            ->order('b.date, b.startTime, b.endTime');

        self::addDeltaClause($query, 'i', $conditions['delta']);
        self::addDeltaClause($query, 'u', $conditions['delta']);

        if ($conditions['mySchedule'] AND !empty($conditions['userID'])) {
            // Aggregate of selected items and the teacher schedule
            if (!empty($conditions['personIDs'])) {
                $personIDs = implode(',', $conditions['personIDs']);
                $query->leftJoin('#__thm_organizer_instance_participants AS ipa ON ipa.instanceID = i.id')
                    ->innerJoin('#__thm_organizer_instance_persons AS ipe ON ipe.instanceID = i.id')
                    ->where("(ipa.participantID = {$conditions['userID']} OR ipe.personID IN ($personIDs))");
            } else {
                $query->innerJoin('#__thm_organizer_instance_participants AS ipa ON ipa.instanceID = i.id')
                    ->where("ipa.participantID = {$conditions['userID']}");
            }
        }

        $eventsRequired = !empty($conditions['courseIDs']);
        $eventsRequired = ($eventsRequired or !empty($conditions['eventIDs']));
        $eventsRequired = ($eventsRequired or !empty($conditions['subjectIDs']));

        if ($eventsRequired) {

            $query->innerJoin('#__thm_organizer_events AS e ON i.eventID = e.id');

            if (!empty($conditions['courseIDs'])) {
                $courseIDs = implode(',', $conditions['courseIDs']);
                $query->where("e.courseID IN ($courseIDs)");
            }

            if (!empty($conditions['eventIDs'])) {
                $eventIDs = implode(',', $conditions['eventIDs']);
                $query->where("e.id IN ($eventIDs)");
            }

            if (!empty($conditions['subjectIDs'])) {
                $subjectIDs = implode(',', $conditions['subjectIDs']);
                $query->innerJoin('#__thm_organizer_subject_events AS se ON se.eventID = e.id')
                    ->where("se.subjectID IN ($subjectIDs)");
            }
        }

        if (!empty($conditions['groupIDs']) or !empty($conditions['personIDs']) or !empty($conditions['roomIDs'])) {

            $query->innerJoin('#__thm_organizer_instance_persons AS ipe ON ipe.instanceID = i.id');
            self::addDeltaClause($query, 'ipe', $conditions['delta']);

            if (!empty($conditions['personIDs'])) {
                $personIDs = implode(',', $conditions['personIDs']);
                $query->where("ipe.personID IN ($personIDs)");
            }

            if (!empty($conditions['groupIDs'])) {
                $groupIDs = implode(',', $conditions['groupIDs']);
                $query->innerJoin('#__thm_organizer_instance_groups AS ig ON ig.assocID = ipe.id')
                    ->where("ig.groupID IN ($groupIDs)");
                self::addDeltaClause($query, 'ig', $conditions['delta']);
            }

            if (!empty($conditions['roomIDs'])) {
                $roomIDs = implode(',', $conditions['roomIDs']);
                $query->innerJoin('#__thm_organizer_instance_rooms AS ir ON ir.assocnID = ipe.id')
                    ->where("ir.roomID IN ($roomIDs)");
                self::addDeltaClause($query, 'ir', $conditions['delta']);
            }
        }

        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadColumn', []);
    }

    /**
     * Filters the person ids to view access
     *
     * @param array &$personIDs the person ids.
     * @param int    $userID    the id of the user whose authorizations will be checked
     *
     * @return void removes unauthorized entries from the array
     */
    private static function filterPersonIDs(&$personIDs, $userID)
    {
        if (empty($userID)) {
            $personIDs = [];

            return;
        }

        if (Access::isAdmin($userID) or Access::allowHRAccess()) {
            return;
        }

        $thisPersonID      = Persons::getIDByUserID($userID);
        $accessibleDeptIDs = Access::getAccessibleDepartments('view', $userID);

        foreach ($personIDs as $key => $personID) {
            if (!empty($thisPersonID) and $thisPersonID == $personID) {
                continue;
            }
            $personDepartments = Persons::getDepartmentIDs($personID);
            $overlap           = array_intersect($accessibleDeptIDs, $personDepartments);

            if (empty($overlap)) {
                unset($personIDs[$key]);
            }
        }
    }

    /**
     * Searches for the next and most recent previous date where events matching the query can be found.
     *
     * @param array $parameters the schedule configuration parameters
     *
     * @return array next and latest available dates
     */
    public static function getJumpDates($parameters)
    {
        // Do not show dates from removed lessons
        $parameters['delta'] = false;

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('MIN(c.schedule_date) AS minDate');
        self::setFrom($query, $parameters);
        self::setWhere($query, $parameters);
        $query->where("c.schedule_date > '" . $parameters['date'] . "'");
        $dbo->setQuery($query);

        $futureDate = OrganizerHelper::executeQuery('loadResult');

        $query = $dbo->getQuery(true);
        $query->select('MAX(c.schedule_date) AS maxDate');
        self::setFrom($query, $parameters);
        self::setWhere($query, $parameters);
        $query->where("c.schedule_date < '" . $parameters['date'] . "'");
        $dbo->setQuery($query);

        $pastDate = OrganizerHelper::executeQuery('loadResult');

        if (empty($futureDate) and empty($pastDate)) {
            return [];
        }

        return ['pastDate' => $pastDate, 'futureDate' => $futureDate];
    }

    /**
     * Retrieves the event instances
     *
     * @param int    $eventID      the id of the event
     * @param int    $mode         the retrieval mode (empty => all, 2 => same block, 3 => single instance
     * @param object $calReference a reference calendar entry modeled on an object
     *
     * @return array the instance ids on success, otherwise empty
     */
    public static function getConfigurationMapIDs($eventID, $mode = null, $calReference = null)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('map.id')
            ->from('#__thm_organizer_calendar_configuration_map AS map')
            ->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
            ->where("cal.lessonID = '$eventID'")
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

    public static function getBla()
    {
        $query      = null;
        $parameters = null;
        self::setFrom($query, $parameters);
        self::setSelect($query, $parameters);
        self::setWhere($query, $parameters);

        $query->where("c.schedule_date >= '{$parameters['startDate']}'")
            ->where("c.schedule_date <= '{$parameters['endDate']}'")
            ->order('c.startTime');

        //$dbo->setQuery($query);

        $rawEvents = OrganizerHelper::executeQuery('loadAssocList', []);
        if (empty($rawEvents)) {
            return self::getNextAvailableDates($parameters);
        }

        $aggregatedEvents = self::aggregateInstances($rawEvents, $parameters['delta']);

        $startDT = strtotime($parameters['startDate']);
        $endDT   = strtotime($parameters['endDate']);

        for ($currentDT = $startDT; $currentDT <= $endDT; $currentDT = strtotime('+1 days', $currentDT)) {
            $index = date('Y-m-d', $currentDT);
            if (!isset($aggregatedEvents[$index])) {
                $aggregatedEvents[$index] = [];
            }
        }

        ksort($aggregatedEvents);

        if (!empty($parameters['mySchedule'])) {
            return self::getUserFilteredLessons($aggregatedEvents, $parameters['userID']);
        }

        return $aggregatedEvents;
    }

    /**
     * Get an array with matching ccmIDs, sorted by lessonIDs
     *
     * @param int $mode  global param like self::SEMESTER_MODE
     * @param int $ccmID primary key of ccm
     *
     * @return array (lessonID => [ccmIDs])
     */
    public static function getMatchingEvents($mode, $ccmID)
    {
        $calReference = self::getCalendarData($ccmID);

        if (!$calReference) {
            return [];
        }

        $lessonID = $calReference->lessonID;

        if ($mode == self::INSTANCE_MODE) {
            return [$lessonID => [$ccmID]];
        }

        $ccmIDs = self::getConfigurationMapIDs($lessonID, $mode, $calReference);

        return empty($ccmIDs) ? [] : [$lessonID => $ccmIDs];
    }

    /**
     * Sets the start and end date parameters and adjusts the date parameter as appropriate.
     *
     * @param array &$parameters the parameters used for event retrieval
     *
     * @return void modifies $parameters
     */
    private static function setDates(&$parameters)
    {
        $date     = $parameters['date'];
        $dateTime = strtotime($date);
        $reqDoW   = date('w', $dateTime);

        $startDayNo   = empty($parameters['startDay']) ? 1 : $parameters['startDay'];
        $endDayNo     = empty($parameters['endDay']) ? 6 : $parameters['endDay'];
        $displayedDay = ($reqDoW >= $startDayNo and $reqDoW <= $endDayNo);
        if (!$displayedDay) {
            if ($reqDoW === 6) {
                $dateTime = strtotime('-1 day', $dateTime);
            } else {
                $dateTime = strtotime('+1 day', $dateTime);
            }
            $date = date('Y-m-d', strtotime($dateTime));
        }

        $parameters['date'] = $date;

        switch ($parameters['interval']) {
            case 'day':
                $dates = ['startDate' => $date, 'endDate' => $date];
                break;

            case 'month':
                $dates = Dates::getMonth($date, $startDayNo, $endDayNo);
                break;

            case 'semester':
                $dates = Dates::getSemester($date);
                break;

            case 'ics':
                // ICS calendars get the next 6 months of data
                $dates = Dates::getICSDates($date, $startDayNo, $endDayNo);
                break;

            case 'week':
            default:
                $dates = Dates::getWeek($date, $startDayNo, $endDayNo);
        }

        $parameters = array_merge($parameters, $dates);
    }

    /**
     * Sets/overwrites attributes based on subject associations.
     *
     * @param array &$instance the array of instance attributes
     *
     * @return void modifies the instance
     */
    private static function setCourse(&$instance)
    {
        $coursesTable = OrganizerHelper::getTable('Courses');
        if (empty($instance['courseID']) or !$coursesTable->load($instance['courseID'])) {
            return;
        }

        $tag                      = Languages::getTag();
        $instance['campusID']     = $coursesTable->campusID ? $coursesTable->campusID : $instance['campusID'];
        $instance['courseGroups'] = $coursesTable->groups ? $coursesTable->groups : '';
        $instance['courseName']   = $coursesTable->{"name_$tag"} ? $coursesTable->{"name_$tag"} : '';
        $instance['deadline']     = $coursesTable->deadline ? $coursesTable->deadline : $instance['deadline'];
        $instance['fee']          = $coursesTable->fee ? $coursesTable->fee : $instance['fee'];
        $instance['full']         = Courses::isFull($instance['courseID']);

        $instance['description']      = $coursesTable->{"description_$tag"} ?
            $coursesTable->{"description_$tag"} : $instance['description'];
        $instance['registrationType'] = $coursesTable->registrationType ?
            $coursesTable->registrationType : $instance['registrationType'];
    }

    /**
     * Gets the groups associated with the instance => person association.
     *
     * @param array &$person     the array of person attributes
     * @param array  $conditions the conditions which instances must fulfill
     *
     * @return void modifies $person
     */
    private static function setGroups(&$person, $conditions)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('ig.groupID, ig.delta, g.untisID AS code, g.name, g.fullName, g.gridID')
            ->from('#__thm_organizer_instance_groups AS ig')
            ->innerJoin('#__thm_organizer_groups AS g ON g.id = ig.groupID')
            ->where("ig.assocID = {$person['assocID']}");
        self::addDeltaClause($query, 'ig', $conditions['delta']);

        $dbo->setQuery($query);
        if (!$groupAssocs = OrganizerHelper::executeQuery('loadAssocList', [])) {
            return;
        }

        $groups = [];
        foreach ($groupAssocs as $groupAssoc) {
            $groupID = $groupAssoc['groupID'];
            $group   = [
                'code'     => $groupAssoc['code'],
                'fullName' => $groupAssoc['fullName'],
                'group'    => $groupAssoc['name'],
                'status'   => $groupAssoc['delta']
            ];

            $groups[$groupID] = $group;
        }

        $person['groups'] = $groups;
    }

    /**
     * Gets the persons and person associated resources associated with the instance.
     *
     * @param array &$instance   the array of instance attributes
     * @param array  $conditions the conditions which instances must fulfill
     *
     * @return void modifies the instance array
     */
    private static function setPersons(&$instance, $conditions)
    {
        $tag   = Languages::getTag();
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('ip.id AS assocID, ip.personID, ip.roleID, ip.delta AS status')
            ->select("r.abbreviation_$tag AS roleCode, r.name_$tag AS role")
            ->from('#__thm_organizer_instance_persons AS ip')
            ->innerJoin('#__thm_organizer_roles AS r ON r.id = ip.roleID')
            ->where("ip.instanceID = {$instance['instanceID']}");
        self::addDeltaClause($query, 'ip', $conditions['delta']);

        $dbo->setQuery($query);
        if (!$personAssocs = OrganizerHelper::executeQuery('loadAssocList', [])) {
            return;
        }

        $persons = [];
        foreach ($personAssocs as $personAssoc) {
            $assocID  = $personAssoc['assocID'];
            $personID = $personAssoc['personID'];
            $person   = [
                'assocID' => $assocID,
                'code'    => $personAssoc['roleCode'],
                'person'  => Persons::getLNFName($personID, true),
                'role'    => $personAssoc['role'],
                'status'  => $personAssoc['status']
            ];

            self::setGroups($person, $conditions);
            self::setRooms($person, $conditions);

            $persons[$personID] = $person;
        }

        $instance['resources'] = $persons;
    }

    /**
     * Gets the rooms associated with the instance => person association.
     *
     * @param array &$person     the array of person attributes
     * @param array  $conditions the conditions which instances must fulfill
     *
     * @return void modifies $person
     */
    private static function setRooms(&$person, $conditions)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('ir.roomID, ir.delta, r.name')
            ->from('#__thm_organizer_instance_rooms AS ir')
            ->innerJoin('#__thm_organizer_rooms AS r ON r.id = ir.roomID')
            ->where("ir.assocID = {$person['assocID']}");
        self::addDeltaClause($query, 'ir', $conditions['delta']);

        $dbo->setQuery($query);
        if (!$roomAssocs = OrganizerHelper::executeQuery('loadAssocList', [])) {
            return;
        }

        $rooms = [];
        foreach ($roomAssocs as $room) {
            $roomID = $room['roomID'];
            $room   = [
                'room'   => $room['name'],
                'status' => $room['delta']
            ];

            $rooms[$roomID] = $room;
        }

        $person['rooms'] = $rooms;
    }

    /**
     * Sets/overwrites attributes based on subject associations.
     *
     * @param array &$instance   the instance
     * @param array  $conditions the conditions used to specify the instances
     *
     * @return void modifies the instance
     */
    private static function setSubject(&$instance, $conditions)
    {
        $dbo           = Factory::getDbo();
        $tag           = Languages::getTag();
        $subjectsQuery = $dbo->getQuery(true);
        $subjectsQuery->select("s.id, s.abbreviation_$tag AS code, s.name_$tag AS fullName, s.shortName_$tag AS name")
            ->select("s.description_$tag AS description, s.departmentID")
            ->from('#__thm_organizer_subjects AS s')
            ->innerJoin('#__thm_organizer_subject_events AS se ON se.subjectID = s.id')
            ->where("se.eventID = {$instance['eventID']}");

        $dbo->setQuery($subjectsQuery);

        if ($subjects = OrganizerHelper::executeQuery('loadAssocList', [])) {
            $subject = [];

            // In the event of multiple results take the first one to fulfill the department condition
            if (!empty($conditions['departmentIDs']) and count($subjects) > 1) {
                foreach ($subjects as $subjectItem) {
                    if (in_array($subjectItem['departmentID'], $conditions['departmentIDs'])) {
                        $subject = $subjectItem;
                        break;
                    }
                }
            }

            // Default
            if (empty($subject)) {
                $subject = $subjects[0];
            }

            $instance['subjectID'] = $subject['id'];
            $instance['code']      = empty($subject['code']) ? '' : $subject['code'];
            $instance['fullName']  = empty($subject['fullName']) ? '' : $subject['fullName'];
            $instance['name']      = empty($subject['name']) ? $instance['name'] : $subject['name'];

            if (empty($instance['description']) and !empty($subject['description'])) {
                $instance['description'] = $subject['description'];
            }
        }
    }
}
