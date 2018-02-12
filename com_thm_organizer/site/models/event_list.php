<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

define('SCHEDULE', 1);
define('ALTERNATING', 2);
define('CONTENT', 3);

/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';

/**
 * Class retrieves information about upcoming events for display on monitors.
 */
class THM_OrganizerModelEvent_List extends JModelLegacy
{
    public $params = [];

    public $events = [];

    public $rooms = [];

    private $days = [];

    private $dates = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $app          = JFactory::getApplication();
        $this->params = $app->getParams();

        $registered             = $this->isRegistered();
        $this->params['layout'] = empty($registered) ? 'default' : 'registered';

        $this->setRooms();
        $this->setDates();
        $this->setEvents();
    }

    /**
     * Aggregates events as appropriate
     *
     * @return void modifies the class's events property
     */
    private function aggregateEvents()
    {
        foreach ($this->events as $date => $dailyEvents) {
            $hAggregatedEvents   = $this->aggregateConcurrent($dailyEvents);
            $vAggregatedEvents   = $this->aggregateSequential($hAggregatedEvents);
            $this->events[$date] = $vAggregatedEvents;
        }
    }

    /**
     * Aggregates events belonging to the same lesson occuring at the same time
     *
     * @param array $events the previous event results
     *
     * @return array the horizontally aggregated events
     */
    private function aggregateConcurrent($events)
    {
        $aggregatedEvents = [];

        foreach ($events as $event) {
            $lessonID  = $event['lessonID'];
            $title     = empty($event['sName']) ? $event['psName'] : $event['sName'];
            $startTime = substr(str_replace(':', '', $event['startTime']), 0, 4);
            $endTime   = substr(str_replace(':', '', $event['endTime']), 0, 4);
            $times     = "$startTime-$endTime";

            if (empty($aggregatedEvents[$times])) {
                $aggregatedEvents[$times] = [];
            }

            if (empty($aggregatedEvents[$times][$lessonID])) {
                $aggregatedEvents[$times][$lessonID]              = [];
                $aggregatedEvents[$times][$lessonID]['titles']    = [$title];
                $aggregatedEvents[$times][$lessonID]['method']    = empty($event['method']) ? '' : $event['method'];
                $aggregatedEvents[$times][$lessonID]['comment']   = empty($event['comment']) ? '' : $event['comment'];
                $aggregatedEvents[$times][$lessonID]['rooms']     = $event['rooms'];
                $aggregatedEvents[$times][$lessonID]['teachers']  = $event['teachers'];
                $aggregatedEvents[$times][$lessonID]['startTime'] = $event['startTime'];
                $aggregatedEvents[$times][$lessonID]['endTime']   = $event['endTime'];

            } else {
                if (!in_array($title, $aggregatedEvents[$times][$lessonID]['titles'])) {
                    $aggregatedEvents[$times][$lessonID]['titles'][] = $title;
                }
                $aggregatedEvents[$times][$lessonID]['rooms']
                    = array_unique(array_merge($aggregatedEvents[$times][$lessonID]['rooms'], $event['rooms']));
                $aggregatedEvents[$times][$lessonID]['teachers']
                    = array_unique(array_merge($aggregatedEvents[$times][$lessonID]['teachers'], $event['teachers']));

            }
            $aggregatedEvents[$times][$lessonID]['departments'][$event['departmentID']] = $event['department'];
        }

        ksort($aggregatedEvents);

        return $aggregatedEvents;
    }

    /**
     * Aggregates events belonging to the same lesson occurring at the same time
     *
     * @param array &$blockEvents the events aggregated by their times
     *
     * @return array the vertically aggregated events
     */
    private function aggregateSequential(&$blockEvents)
    {
        foreach ($blockEvents as $outerTimes => $outerEvents) {
            foreach ($outerEvents as $lessonID => $outerLesson) {
                $outerStart = $outerLesson['startTime'];
                $outerEnd   = $outerLesson['endTime'];

                foreach ($blockEvents as $innerTimes => $innerEvents) {
                    // Identity or no need for comparison
                    if ($innerTimes == $outerTimes or empty($innerEvents[$lessonID])) {
                        continue;
                    }

                    $innerLesson  = $innerEvents[$lessonID];
                    $sameRooms    = $innerLesson['rooms'] == $outerLesson['rooms'];
                    $sameTeachers = $innerLesson['teachers'] == $outerLesson['teachers'];
                    $divergent    = (!$sameRooms or !$sameTeachers);

                    if ($divergent) {
                        continue;
                    }

                    $innerStart    = $innerLesson['startTime'];
                    $innerEnd      = $innerLesson['endTime'];
                    $relevantTimes = $this->getSequentialRelevance($outerStart, $outerEnd, $innerStart, $innerEnd);

                    if (empty($relevantTimes)) {
                        continue;
                    }

                    $outerLesson['startTime'] = $relevantTimes['startTime'];
                    $outerLesson['endTime']   = $relevantTimes['endTime'];
                    $outerStart               = $relevantTimes['startTime'];
                    $outerEnd                 = $relevantTimes['endTime'];

                    unset($blockEvents[$innerTimes][$lessonID]);
                }

                $startTime = substr(str_replace(':', '', $outerStart), 0, 4);
                $endTime   = substr(str_replace(':', '', $outerEnd), 0, 4);
                $newTimes  = "$startTime-$endTime";

                unset($blockEvents[$outerTimes][$lessonID]);

                if (empty($blockEvents[$newTimes])) {
                    $blockEvents[$newTimes] = [];
                }

                $blockEvents[$newTimes][$lessonID] = $outerLesson;
            }
        }

        ksort($blockEvents);

        return $blockEvents;
    }

    /**
     * Removes indexes which are no longer used after sequential aggregation
     *
     * @return void modifies object variable
     */
    private function cleanEvents()
    {
        foreach ($this->events as $date => $times) {
            foreach ($times as $index => $lessons) {
                if (empty($lessons)) {
                    unset($this->events[$date][$index]);
                }
            }
            if (empty($times)) {
                unset($this->events[$date]);
            }

        }
    }

    /**
     * Retrieves all roomIDs
     *
     * @return mixed  array of roomIDS on success, otherwise false
     */
    private function getAllRoomIDs()
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')->from('#__thm_organizer_rooms');
        $dbo->setQuery($query);

        return $dbo->loadColumn();
    }

    /**
     * Adds the room names to the room instances index, if the room was requested.
     *
     * @param array $instanceRooms the rooms associated with the instance
     *
     * @return array $rooms
     */
    private function getEventRooms(&$instanceRooms)
    {
        $rooms = [];
        foreach ($instanceRooms as $roomID => $delta) {
            if ($delta == 'removed' or empty($this->rooms[$roomID])) {
                unset($instanceRooms[$roomID]);
                continue;
            }

            $rooms[$roomID] = $this->rooms[$roomID];
        }
        asort($rooms);

        return $rooms;
    }

    /**
     * Gets the raw events from the database
     *
     * @return void sets the object variable events
     * @throws Exception
     */
    private function getEvents()
    {
        foreach ($this->dates as $date) {
            $shortTag = THM_OrganizerHelperLanguage::getShortTag();

            $query = $this->_db->getQuery(true);

            $select = "DISTINCT conf.id, conf.configuration, cal.startTime, cal.endTime, ";
            $select .= "d.short_name_$shortTag AS department, d.id AS departmentID, ";
            $select .= "l.id as lessonID, l.comment, m.abbreviation_$shortTag AS method, ";
            $select .= "ps.name AS psName, s.name_$shortTag AS sName";
            $query->select($select)
                ->from('#__thm_organizer_calendar AS cal')
                ->innerJoin('#__thm_organizer_calendar_configuration_map AS ccm ON ccm.calendarID = cal.id')
                ->innerJoin('#__thm_organizer_lesson_configurations AS conf ON ccm.configurationID = conf.id')
                ->innerJoin('#__thm_organizer_lessons AS l ON cal.lessonID = l.id')
                ->innerJoin('#__thm_organizer_departments AS d ON l.departmentID = d.id')
                ->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id AND conf.lessonID = ls.id')
                ->innerJoin('#__thm_organizer_plan_subjects AS ps ON ls.subjectID = ps.id')
                ->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.subjectID = ls.id')
                ->innerJoin('#__thm_organizer_plan_pools AS pp ON lp.poolID = pp.id')
                ->leftJoin('#__thm_organizer_plan_pool_publishing AS ppp ON ppp.planPoolID = pp.id AND ppp.planningPeriodID = l.planningPeriodID')
                ->leftJoin('#__thm_organizer_methods AS m ON l.methodID = m.id')
                ->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id')
                ->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id')
                ->where("cal.schedule_date = '$date'")
                ->where("cal.delta != 'removed'")
                ->where("l.delta != 'removed'")
                ->where("ls.delta != 'removed'")
                ->where("(ppp.published IS NULL OR ppp.published = '1')");
            $this->_db->setQuery($query);

            try {
                $events = $this->_db->loadAssocList();
            } catch (Exception $exception) {
                JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

                return;
            }

            if (!empty($events)) {
                foreach ($events as $index => $event) {
                    $instanceConfiguration = json_decode($event['configuration'], true);
                    $rooms                 = $this->getEventRooms($instanceConfiguration['rooms']);

                    if (count($rooms)) {
                        $events[$index]['rooms']    = $rooms;
                        $events[$index]['teachers'] = $this->getEventTeachers($instanceConfiguration['teachers']);
                        unset($events[$index]['configuration']);
                    } else {
                        unset($events[$index]);
                    }
                }
                $this->events[$date] = $events;
            }
        }
    }

    /**
     * Adds the teacher names to the teacher instances index.
     *
     * @param array $instanceTeachers the teachers associated with the instance
     *
     * @return array an array of teachers in the form id => 'forename(s) surname(s)'
     */
    private function getEventTeachers(&$instanceTeachers)
    {
        $teachers = [];

        foreach ($instanceTeachers as $teacherID => $delta) {
            if ($delta == 'removed') {
                unset($instanceTeachers[$teacherID]);
                continue;
            }

            $teachers[$teacherID] = THM_OrganizerHelperTeachers::getDefaultName($teacherID);
        }

        asort($teachers);

        return $teachers;
    }

    /**
     * Checks whether the accessing agent is a registered monitor
     *
     * @return mixed  int roomID on success, otherwise boolean false
     * @throws Exception
     */
    private function isRegistered()
    {
        $ipData       = ['ip' => JFactory::getApplication()->input->server->getString('REMOTE_ADDR', '')];
        $monitorEntry = JTable::getInstance('monitors', 'thm_organizerTable');
        $registered   = $monitorEntry->load($ipData);
        if (!$registered) {
            return false;
        }

        $roomID = $monitorEntry->roomID;
        if (empty($roomID)) {
            return false;
        }

        $app         = JFactory::getApplication();
        $templateSet = $app->input->getString('tmpl', '') == 'component';
        if (!$templateSet) {
            $app   = JFactory::getApplication();
            $base  = JUri::root() . 'index.php?';
            $query = $app->input->server->get('QUERY_STRING', '', 'raw');
            $query .= (strpos($query, 'com_thm_organizer') !== false) ? '' : '&option=com_thm_organizer';
            $query .= (strpos($query, 'event_list') !== false) ? '' : '&view=event_list';
            $query .= '&tmpl=component';
            $app->redirect($base . $query);
        }

        $this->rooms = [$roomID];
        $this->days  = [1, 2, 3, 4, 5, 6];

        return true;
    }

    /**
     * Determines the sequential relevance of two lesson blocks.
     *
     * @param string $startOuter the start time for the lesson in the outer loop
     * @param string $endOuter   the end time for the lesson in the outer loop
     * @param string $startInner the start time for the lesson in the inner loop
     * @param string $endInner   the end time for the lesson in the inner loop
     *
     * @return array|bool the new start and end times if relevant, otherwise false
     */
    private function getSequentialRelevance($startOuter, $endOuter, $startInner, $endInner)
    {
        // The maximum tolerance (break time) allowed for sequential aggregation
        $tolerance = 61;

        // Inner lesson ended before outer began
        $before = $endInner < $startOuter;

        if ($before) {
            $firstTime  = strtotime($endInner);
            $secondTime = strtotime($startOuter);
            $difference = ($secondTime - $firstTime) / 60;
            $relevant   = $difference <= $tolerance;

            return $relevant ? ['startTime' => $startInner, 'endTime' => $endOuter] : false;
        }

        // Outer lesson ended before inner began
        $after = $endOuter < $startInner;

        if ($after) {

            $firstTime  = strtotime($endOuter);
            $secondTime = strtotime($startInner);
            $difference = ($secondTime - $firstTime) / 60;
            $relevant   = $difference <= $tolerance;

            return $relevant ? ['startTime' => $startOuter, 'endTime' => $endInner] : false;
        }

        // Overlapping lessons
        $startTime = $startOuter < $startInner ? $startOuter : $startInner;
        $endTime   = $endOuter > $endInner ? $endOuter : $endInner;

        return ['startTime' => $startTime, 'endTime' => $endTime];
    }

    /**
     * Sets the dates used
     *
     * @return void  sets object variables $_startDate and $_endDate
     */
    private function setDates()
    {
        $isRegistered       = ($this->params['layout'] == 'registered');
        $nothingSelected    = empty($this->params['days']);
        $everythingSelected = (count($this->params['days']) === 1 and empty($this->params['days'][0]));
        if ($isRegistered or $nothingSelected or $everythingSelected) {
            $days = [1, 2, 3, 4, 5, 6];
        } else {
            $days = $this->params['days'];
        }

        $date      = getdate(time());
        $today     = date('Y-m-d', $date[0]);
        $startDate = $this->params->get('startDate', '');
        $startDate = (empty($startDate) or $startDate < $today) ? $today : $startDate;
        $endDate   = $this->params->get('endDate', '');
        if (empty($endDate)) {
            $query = $this->_db->getQuery(true);
            $query->select('MAX(schedule_date)')->from('#__thm_organizer_calendar')->where("delta != 'removed'");
            $this->_db->setQuery($query);
            $endDate = $this->_db->loadResult();
        }

        $startDT = strtotime($startDate);
        $endDT   = strtotime($endDate);

        for ($currentDT = $startDT; $currentDT <= $endDT; $currentDT = strtotime('+1 day', $currentDT)) {
            $currentDOW = date('w', $currentDT);
            if (in_array($currentDOW, $days)) {
                $this->dates[] = date('Y-m-d', $currentDT);
            }
        }
    }

    /**
     * Sets the events for display
     *
     * @return void  sets object variables
     * @throws Exception
     */
    private function setEvents()
    {
        $this->getEvents();
        $this->aggregateEvents();
        $this->cleanEvents();
    }

    /**
     * Retrieves the name and id of the room
     *
     * @return void  sets object variables
     * @throws Exception
     */
    private function setRooms()
    {
        // Registered room(s) would have already been set
        if (empty($this->rooms)) {
            $nothingSelected    = empty($this->params['rooms']);
            $everythingSelected = (count($this->params['rooms']) === 1 and empty($this->params['rooms'][0]));

            // All rooms
            if ($nothingSelected or $everythingSelected) {
                $this->rooms = $this->getAllRoomIDs();
            } else {
                $this->rooms = $this->params['rooms'];
            }
        }

        $rooms      = [];
        $roomsTable = JTable::getInstance('rooms', 'thm_organizerTable');

        // The current values are meaningless and will be overwritten here
        foreach ($this->rooms as $roomID) {
            try {
                $roomsTable->load($roomID);
            } catch (Exception $exc) {
                JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"),
                    'error');
                unset($this->rooms[$roomID]);
            }

            $roomName       = $roomsTable->name;
            $rooms[$roomID] = $roomName;
        }

        if ($this->params['layout'] == 'registered') {
            $roomValues               = array_values($rooms);
            $this->params['roomName'] = array_shift($roomValues);
        }

        asort($rooms);
        $this->rooms = $rooms;
    }
}
