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

require_once JPATH_SITE . '/media/com_thm_organizer/helpers/departments.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/programs.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/planning_periods.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/schedule.php';

/**
 * Class calculates room usage statistics.
 */
class THM_OrganizerModelRoom_Statistics extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    public $calendarData;

    public $endDate;

    public $endDoW;

    /**
     * Subject dependant data which would otherwise redundantly be in the calendar data
     *
     * @var array
     */
    public $lsData;

    private $grid;

    public $metaData;

    public $rooms;

    public $roomTypes;

    public $roomTypeMap;

    public $roomData;

    public $startDate;

    public $startDoW;

    private $threshhold = .2;

    public function __construct(array $config)
    {
        parent::__construct($config);

        $format = THM_OrganizerHelperComponent::getInput()->getString('format');

        switch ($format) {
            case 'xls':
                $this->setRoomTypes();
                $this->setRooms();
                $this->setGrid();
                $this->setDates();
                $this->initializeCalendar();

                foreach ($this->rooms as $roomName => $roomData) {
                    $booked = $this->setData($roomData['id']);

                    if (!$booked) {
                        unset($this->rooms[$roomName]);
                        unset($this->roomTypeMap[$roomData['id']]);
                    }
                }

                foreach (array_keys($this->roomTypes) as $rtID) {
                    if (!in_array($rtID, $this->roomTypeMap)) {
                        unset($this->roomTypes[$rtID]);
                    }
                }

                $this->createUseData();
                $this->createMetaData();

                break;

            case 'html':
            default:
                $this->setRooms();
                $this->setRoomTypes();

                break;
        }
    }

    /**
     * Aggregates the raw instance data into calendar entries
     *
     * @param array $ringData the raw lesson instances for a specific room
     *
     * @return void
     */
    private function aggregateInstances($ringData)
    {

        foreach ($ringData as $instance) {
            $rawConfig = json_decode($instance['configuration'], true);

            // Should not be able to occur because of the query conditions.
            if (empty($rawConfig['rooms'])) {
                continue;
            }

            $date     = $instance['date'];
            $lessonID = $instance['lessonID'];
            $method   = $instance['method'];
            $lsIDs    = [$instance['lsID'] => $instance['lsID']];

            foreach ($rawConfig['rooms'] as $roomID => $delta) {
                if (!in_array($roomID, array_keys($this->roomTypeMap)) or $delta == 'removed') {
                    continue;
                }

                $blocks = $this->getRelevantBlocks($instance['startTime'], $instance['endTime']);

                if (empty($blocks)) {
                    continue;
                }

                foreach ($blocks as $blockNo) {
                    if (empty($this->calendarData[$date][$blockNo][$roomID])) {
                        $this->calendarData[$date][$blockNo][$roomID] = [];
                    }

                    if (empty($this->calendarData[$date][$blockNo][$roomID][$lessonID])) {
                        $this->calendarData[$date][$blockNo][$roomID][$lessonID]           = [];
                        $this->calendarData[$date][$blockNo][$roomID][$lessonID]['method'] = $method;
                    }

                    $existingLSIDs = empty($this->calendarData[$date][$blockNo][$roomID][$lessonID]['lsIDs']) ?
                        [] : $this->calendarData[$date][$blockNo][$roomID][$lessonID]['lsIDs'];

                    $this->calendarData[$date][$blockNo][$roomID][$lessonID]['lsIDs'] = $existingLSIDs + $lsIDs;
                }
            }
        }
    }

    /**
     * Retrieves department options
     *
     * @return array an array of department options
     */
    public function getDepartmentOptions()
    {
        $options = [];
        foreach (THM_OrganizerHelperDepartments::getOptions(false) as $departmentID => $departmentName) {
            $options[$departmentID] = $departmentName;
        }

        return $options;
    }

    /**
     * Creates planning period selection options
     *
     * @return array
     */
    public function getPlanningPeriodOptions()
    {
        $options = [];
        foreach (THM_OrganizerHelperPlanning_Periods::getPlanningPeriods() as $planningPeriod) {
            $shortSD = THM_OrganizerHelperDate::formatDate($planningPeriod['startDate']);
            $shortED = THM_OrganizerHelperDate::formatDate($planningPeriod['endDate']);

            $options[$planningPeriod['id']] = "{$planningPeriod['name']} ($shortSD - $shortED)";
        }

        return $options;
    }

    /**
     * Retrieves program options
     *
     * @return array an array of program options
     */
    public function getProgramOptions()
    {
        $options = [];
        foreach (THM_OrganizerHelperPrograms::getPlanPrograms() as $program) {
            $options[$program['id']] = empty($program['name']) ? $program['ppName'] : $program['name'];
        }

        return $options;
    }

    /**
     * Determines the relevant grid blocks based upon the instance start and end times
     *
     * @param string $startTime the time the instance starts
     * @param string $endTime   the time the instance ends
     *
     * @return array the relevant block numbers
     */
    private function getRelevantBlocks($startTime, $endTime)
    {
        $relevantBlocks = [];

        foreach ($this->grid as $blockNo => $times) {
            $tooEarly = $times['endTime'] <= $startTime;
            $tooLate  = $times['startTime'] >= $endTime;

            if ($tooEarly or $tooLate) {
                continue;
            }

            $relevantBlocks[$blockNo] = $blockNo;
        }

        return $relevantBlocks;
    }

    /**
     * Retrieves room options
     *
     * @return array an array of room options
     */
    public function getRoomOptions()
    {
        $options = [];
        foreach ($this->rooms as $roomName => $roomData) {
            $options[$roomData['id']] = $roomName;
        }

        return $options;
    }

    /**
     * Retrieves room type options
     *
     * @return array an array of teacher options
     */
    public function getRoomTypeOptions()
    {
        $options = [];
        foreach ($this->roomTypes as $typeID => $typeData) {
            $options[$typeID] = $typeData['name'];
        }

        return $options;
    }

    /**
     * Creates a calendar to associate the instances with.
     *
     * @return void sets the object variable use
     */
    private function initializeCalendar()
    {
        $calendar = [];
        $startDT  = strtotime($this->startDate);
        $endDT    = strtotime($this->endDate);

        for ($currentDT = $startDT; $currentDT <= $endDT; $currentDT = strtotime('+1 days', $currentDT)) {
            $currentDoW = date('w', $currentDT);
            $invalidDoW = ($currentDoW < $this->startDoW or $currentDoW > $this->endDoW);

            if ($invalidDoW) {
                continue;
            }

            $date = date('Y-m-d', $currentDT);
            if (!isset($calendar[$date])) {
                $calendar[$date] = $this->grid;
            }
        }

        $this->calendarData = $calendar;
    }

    /**
     * Retrieves raw lesson instance information from the database
     *
     * @param int $roomID the id of the room being iterated
     *
     * @return bool true if room information was found, otherwise false
     */
    private function setData($roomID)
    {
        $tag       = THM_OrganizerHelperLanguage::getShortTag();
        $dbo       = \JFactory::getDbo();
        $ringQuery = $dbo->getQuery(true);

        $rqSelect = 'DISTINCT ccm.id AS ccmID, ls.id as lsID, l.id AS lessonID, l.comment, ';
        $rqSelect .= "m.id AS methodID, m.abbreviation_$tag AS method, m.name_$tag as methodName, ";
        $rqSelect .= 'c.schedule_date AS date, c.startTime, c.endTime, ';
        $rqSelect .= 'lc.configuration ';

        $ringQuery->select($rqSelect);
        $ringQuery->from('#__thm_organizer_lessons AS l');
        $ringQuery->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id');
        $ringQuery->innerJoin('#__thm_organizer_calendar AS c ON l.id = c.lessonID');
        $ringQuery->innerJoin('#__thm_organizer_lesson_configurations AS lc ON lc.lessonID = ls.id');
        $ringQuery->innerJoin('#__thm_organizer_calendar_configuration_map AS ccm ON ccm.calendarID = c.id AND ccm.configurationID = lc.id');
        $ringQuery->leftJoin('#__thm_organizer_methods AS m ON l.methodID = m.id');

        $ringQuery->where("ls.delta != 'removed'");
        $ringQuery->where("l.delta != 'removed'");
        $ringQuery->where("c.delta != 'removed'");
        $ringQuery->where("schedule_date BETWEEN '$this->startDate' AND '$this->endDate'");

        $regexp = '"rooms":\\{("[0-9]+":"[\w]*",)*"' . $roomID . '":("new"|"")';
        $ringQuery->where("lc.configuration REGEXP '$regexp'");
        $dbo->setQuery($ringQuery);
        $ringData = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
        $lsIDs    = THM_OrganizerHelperComponent::executeQuery('loadColumn', [], 1);

        if (empty($ringData) or empty($lsIDs)) {
            return false;
        }

        $this->aggregateInstances($ringData);
        $this->setLSData($lsIDs);

        return true;
    }

    /**
     * Resolves form date information into where clauses for the query being built
     *
     * @return void the corresponding start and end dates
     */
    private function setDates()
    {
        $input     = THM_OrganizerHelperComponent::getInput();
        $use       = $input->getString('use');
        $ppIDs     = $input->get('planningPeriodIDs', [], 'array');
        $validPPID = (!empty($ppIDs) and !empty($ppIDs[0])) ? true : false;

        if ($use == 'planningPeriodIDs' and $validPPID) {
            $ppTable = \JTable::getInstance('planning_periods', 'thm_organizerTable');
            $success = $ppTable->load($ppIDs[0]);

            if ($success) {
                $this->startDate = $ppTable->startDate;
                $this->endDate   = $ppTable->endDate;

                return;
            }
        }

        $dateFormat      = THM_OrganizerHelperComponent::getParams()->get('dateFormat');
        $date            = $input->getString('date', date($dateFormat));
        $startDoWNo      = empty($this->startDoW) ? 1 : $this->startDoW;
        $startDayName    = date('l', strtotime("Sunday + $startDoWNo days"));
        $endDoWNo        = empty($this->endDoW) ? 6 : $this->endDoW;
        $endDayName      = date('l', strtotime("Sunday + $endDoWNo days"));
        $dateRestriction = $input->getString('dateRestriction', 'week');

        if ($dateRestriction == 'month') {
            $monthStart      = date('Y-m-d', strtotime('first day of this month', strtotime($date)));
            $this->startDate = date('Y-m-d', strtotime("$startDayName this week", strtotime($monthStart)));
            $monthEnd        = date('Y-m-d', strtotime('last day of this month', strtotime($date)));
            $this->endDate   = date('Y-m-d', strtotime("$endDayName this week", strtotime($monthEnd)));

            return;
        }

        // Should only be week, but not asking gives a default behavior.
        $this->startDate = date('Y-m-d', strtotime("$startDayName this week", strtotime($date)));
        $this->endDate   = date('Y-m-d', strtotime("$endDayName this week", strtotime($date)));

        return;
    }

    /**
     * Retrieves the selected grid from the database
     *
     * @return void sets object variables
     */
    private function setGrid()
    {
        $query = $this->_db->getQuery(true);
        $query->select('grid')->from('#__thm_organizer_grids');

        if (empty($this->parameters['gridID'])) {
            $query->where("defaultGrid = '1'");
        } else {
            $query->where("id = '{$this->parameters['gridID']}'");
        }

        $this->_db->setQuery($query);

        $rawGrid = THM_OrganizerHelperComponent::executeQuery('loadResult');
        if (empty($rawGrid)) {
            return;
        }

        $gridSettings = json_decode($rawGrid, true);

        $grid = [];

        foreach ($gridSettings['periods'] as $number => $times) {
            $grid[$number]              = [];
            $grid[$number]['startTime'] = date('H:i:s', strtotime($times['startTime']));
            $grid[$number]['endTime']   = date('H:i:s', strtotime($times['endTime']));
        }

        $this->grid     = $grid;
        $this->startDoW = $gridSettings['startDay'];
        $this->endDoW   = $gridSettings['endDay'];
    }

    /**
     * Sets mostly textual data which is dependent on the lesson subject ids
     *
     * @param array $lsIDs the lesson subject database ids
     *
     * @return void sets object variable indexes
     */
    private function setLSData($lsIDs)
    {
        $tag   = THM_OrganizerHelperLanguage::getShortTag();
        $dbo   = \JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $select = 'DISTINCT ls.id AS lsID, ';
        $query->from('#__thm_organizer_lesson_subjects AS ls');

        // Subject Data
        $select .= 'ps.id AS psID, ps.name AS psName, ps.subjectNo, ps.gpuntisID AS psUntisID, ';
        $select .= "s.id AS subjectID, s.name_$tag AS subjectName, s.short_name_$tag AS subjectShortName, s.abbreviation_$tag AS subjectAbbr, ";
        $query->innerJoin('#__thm_organizer_plan_subjects AS ps ON ls.subjectID = ps.id');
        $query->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id');
        $query->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');

        // Pool Data
        $select .= 'pool.id AS poolID, pool.gpuntisID AS poolGPUntisID, pool.name AS poolName, pool.full_name AS poolFullName, ';
        $query->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.subjectID = ls.id');
        $query->innerJoin('#__thm_organizer_plan_pools AS pool ON pool.id = lp.poolID');

        // Program Data
        $select .= "pp.id AS programID, pp.name AS ppName, prog.name_$tag AS progName, prog.version, dg.abbreviation AS progAbbr, ";
        $query->innerJoin('#__thm_organizer_plan_programs AS pp ON pool.programID = pp.id');
        $query->leftJoin('#__thm_organizer_programs AS prog ON pp.programID = prog.id');
        $query->leftJoin('#__thm_organizer_degrees AS dg ON prog.degreeID = dg.id');

        // Department Data
        $select .= "d.id AS departmentID, d.short_name_$tag AS department, d.name_$tag AS departmentName";
        $query->innerJoin('#__thm_organizer_department_resources AS dr ON pp.id = dr.programID');
        $query->innerJoin('#__thm_organizer_departments AS d ON dr.departmentID = d.id');

        $query->select($select);
        $query->where("lp.delta != 'removed'");
        $query->where("ls.id IN ('" . implode("', '", $lsIDs) . "')");
        $dbo->setQuery($query);

        $results = THM_OrganizerHelperComponent::executeQuery('loadAssocList', [], 'lsID');
        if (empty($results)) {
            return;
        }

        foreach ($results as $lsID => $lsData) {
            $this->lsData[$lsID] = $lsData;
        }
    }

    /**
     * Sets the rooms
     *
     * @return void sets an object variable
     */
    private function setRooms()
    {
        $rooms       = THM_OrganizerHelperRooms::getPlanRooms();
        $roomTypeMap = [];

        foreach ($rooms as $room) {
            $roomTypeMap[$room['id']] = $room['typeID'];
        }

        $this->rooms       = $rooms;
        $this->roomTypeMap = $roomTypeMap;
    }

    /**
     * Sets the available room types based on the rooms
     *
     * @return void sets the room types object variable
     */
    private function setRoomTypes()
    {
        $dbo   = \JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $tag   = THM_OrganizerHelperLanguage::getShortTag();

        $query->select("id, name_$tag AS name, description_$tag AS description");
        $query->from('#__thm_organizer_room_types');
        $query->order('name');

        $dbo->setQuery($query);

        $results = THM_OrganizerHelperComponent::executeQuery('loadAssocList', [], 'id');

        $this->roomTypes = empty($results) ? [] : $results;
    }

    /**
     * Creates meta data for the weeks, totals and adjusted totals. Also sets room week data.
     *
     * @return void
     */
    private function createMetaData()
    {
        $this->metaData         = [];
        $this->metaData['days'] = [];
        $dailyBlocks            = count($this->grid);

        foreach (array_keys($this->calendarData) as $date) {
            $this->metaData['days'][$date]          = [];
            $this->metaData['days'][$date]['total'] = 0;
            $this->metaData['days'][$date]['use']   = 0;

            foreach ($this->rooms as $roomName => $roomData) {
                $this->metaData['days'][$date]['total'] += $dailyBlocks;
                $roomUse                                = empty($this->roomData[$roomData['id']]['days'][$date]) ? 0 : $this->roomData[$roomData['id']]['days'][$date];
                $this->metaData['days'][$date]['use']   += $roomUse;
            }
        }

        $this->metaData['weeks'] = [];
        $weekNo                  = 1;

        for ($weekStartDate = $this->startDate; $weekStartDate <= $this->endDate;) {
            $week['startDate']     = $weekStartDate;
            $endDayName            = date('l', strtotime("Sunday + $this->endDoW days"));
            $weekEndDate           = date('Y-m-d', strtotime("$endDayName this week", strtotime($weekStartDate)));
            $week['endDate']       = $weekEndDate;
            $week['adjustedTotal'] = 0;
            $week['adjustedUse']   = 0;
            $week['total']         = 0;
            $week['use']           = 0;

            for ($currentDate = $weekStartDate; $currentDate <= $weekEndDate;) {
                $week['total'] += $this->metaData['days'][$currentDate]['total'];
                $week['use']   += $this->metaData['days'][$currentDate]['use'];
                $dailyAverage  = $this->metaData['days'][$currentDate]['use'] / $this->metaData['days'][$date]['total'];

                if ($dailyAverage > $this->threshhold) {
                    $week['adjustedTotal'] += $this->metaData['days'][$currentDate]['total'];
                    $week['adjustedUse']   += $this->metaData['days'][$currentDate]['use'];
                }

                foreach ($this->rooms as $roomName => $roomData) {
                    if (empty($this->roomData[$roomData['id']]['weeks'])) {
                        $this->roomData[$roomData['id']]['weeks'] = [];
                    }

                    if (empty($this->roomData[$roomData['id']]['weeks'][$weekNo])) {
                        $this->roomData[$roomData['id']]['weeks'][$weekNo]                  = [];
                        $this->roomData[$roomData['id']]['weeks'][$weekNo]['adjustedTotal'] = 0;
                        $this->roomData[$roomData['id']]['weeks'][$weekNo]['adjustedUse']   = 0;
                        $this->roomData[$roomData['id']]['weeks'][$weekNo]['total']         = 0;
                        $this->roomData[$roomData['id']]['weeks'][$weekNo]['use']           = 0;
                    }

                    $this->roomData[$roomData['id']]['weeks'][$weekNo]['total'] += $dailyBlocks;
                    $this->roomData[$roomData['id']]['weeks'][$weekNo]['use']
                                                                                += $this->roomData[$roomData['id']]['days'][$currentDate];

                    if ($dailyAverage > $this->threshhold) {
                        $this->roomData[$roomData['id']]['weeks'][$weekNo]['adjustedTotal'] += $dailyBlocks;
                        $this->roomData[$roomData['id']]['weeks'][$weekNo]['adjustedUse']
                                                                                            += $this->roomData[$roomData['id']]['days'][$currentDate];
                    }
                }

                $currentDate = date('Y-m-d', strtotime("$currentDate + 1 days"));
            }

            $this->metaData['weeks'][$weekNo] = $week;
            $weekNo++;
            $weekStartDate = date('Y-m-d', strtotime("$weekStartDate + 7 days"));
        }

        $this->metaData['adjustedTotal'] = 0;
        $this->metaData['adjustedUse']   = 0;
        $this->metaData['total']         = 0;
        $this->metaData['use']           = 0;

        foreach ($this->metaData['weeks'] as $weekNo => $weekData) {
            $this->metaData['total'] += $weekData['total'];
            $this->metaData['use']   += $weekData['use'];

            if (empty($weekData['adjustedTotal'])) {
                continue;
            }

            $weeklyAverage = $weekData['adjustedUse'] / $weekData['adjustedTotal'];

            // TODO: find a good value for this through experimentation
            if ($weeklyAverage > $this->threshhold) {
                $this->metaData['adjustedTotal'] += $weekData['adjustedTotal'];
                $this->metaData['adjustedUse']   += $weekData['adjustedUse'];
            }
        }
    }

    /**
     * Sums number of used blocks per room per day
     * @return void
     */
    private function createUseData()
    {
        $this->roomData = [];

        foreach ($this->calendarData as $date => $blocks) {
            foreach ($blocks as $blockRoomData) {
                $roomIDs = array_keys($blockRoomData);

                // This will ignore double bookings because the lessons themselves are not iterated
                foreach ($this->rooms as $room) {
                    if (empty($this->roomData[$room['id']])) {
                        $this->roomData[$room['id']]         = [];
                        $this->roomData[$room['id']]['days'] = [];
                    }

                    $newValue = empty($this->roomData[$room['id']]['days'][$date]) ?
                        0 : $this->roomData[$room['id']]['days'][$date];

                    if (in_array($room['id'], $roomIDs)) {
                        $newValue++;
                    }

                    $this->roomData[$room['id']]['days'][$date] = $newValue;
                }
            }
        }
    }
}
