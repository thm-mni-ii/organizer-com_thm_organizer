<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Organizer\Helpers\Departments;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Rooms;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class calculates room usage statistics.
 */
class Room_Statistics extends BaseModel
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

    /**
     * Room_Statistics constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $format = Input::getCMD('format', 'html');

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
            $lcrsIDs  = [$instance['lcrsID'] => $instance['lcrsID']];

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

                    $existingLCRSIDs = empty($this->calendarData[$date][$blockNo][$roomID][$lessonID]['lcrsIDs']) ?
                        [] : $this->calendarData[$date][$blockNo][$roomID][$lessonID]['lcrsIDs'];

                    $this->calendarData[$date][$blockNo][$roomID][$lessonID]['lcrsIDs'] = $existingLCRSIDs + $lcrsIDs;
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
        foreach (Departments::getOptions(false) as $departmentID => $departmentName) {
            $options[$departmentID] = $departmentName;
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
        $tag       = Languages::getTag();
        $ringQuery = $this->_db->getQuery(true);
        $ringQuery->select('DISTINCT ccm.id AS ccmID')
            ->from('#__thm_organizer_calendar_configuration_map AS ccm')
            ->select('c.schedule_date AS date, c.startTime, c.endTime')
            ->innerJoin('#__thm_organizer_calendar AS c ON c.id = ccm.calendarID')
            ->select('conf.configuration')
            ->innerJoin('#__thm_organizer_lesson_configurations AS conf ON conf.id = ccm.configurationID')
            ->select('l.id AS lessonID, l.comment')
            ->innerJoin('#__thm_organizer_lessons AS l ON l.id = c.lessonID')
            ->select('lcrs.id as lcrsID')
            ->innerJoin('#__thm_organizer_lesson_courses AS lcrs ON lcrs.lessonID = l.id')
            ->select("m.id AS methodID, m.abbreviation_$tag AS method, m.name_$tag as methodName")
            ->leftJoin('#__thm_organizer_methods AS m ON l.methodID = m.id');

        $ringQuery->where("lcrs.delta != 'removed'");
        $ringQuery->where("l.delta != 'removed'");
        $ringQuery->where("c.delta != 'removed'");
        $ringQuery->where("schedule_date BETWEEN '$this->startDate' AND '$this->endDate'");

        $regexp = '"rooms":\\{("[0-9]+":"[\w]*",)*"' . $roomID . '":("new"|"")';
        $ringQuery->where("conf.configuration REGEXP '$regexp'");
        $this->_db->setQuery($ringQuery);
        $ringData = OrganizerHelper::executeQuery('loadAssocList');
        $lcrsIDs  = OrganizerHelper::executeQuery('loadColumn', [], 1);

        if (empty($ringData) or empty($lcrsIDs)) {
            return false;
        }

        $this->aggregateInstances($ringData);
        $this->setLSData($lcrsIDs);

        return true;
    }

    /**
     * Resolves form date information into where clauses for the query being built
     *
     * @return void the corresponding start and end dates
     */
    private function setDates()
    {
        $input       = Input::getInput();
        $use         = $input->getString('use');
        $termIDs     = $input->get('termIDs', [], 'array');
        $validTermID = (!empty($termIDs) and !empty($termIDs[0])) ? true : false;

        if ($use == 'termIDs' and $validTermID) {
            $table   = OrganizerHelper::getTable('Terms');
            $success = $table->load($termIDs[0]);

            if ($success) {
                $this->startDate = $table->startDate;
                $this->endDate   = $table->endDate;

                return;
            }
        }

        $dateFormat   = Input::getParams()->get('dateFormat');
        $date         = $input->getString('date', date($dateFormat));
        $startDoWNo   = empty($this->startDoW) ? 1 : $this->startDoW;
        $startDayName = date('l', strtotime("Sunday + $startDoWNo days"));
        $endDoWNo     = empty($this->endDoW) ? 6 : $this->endDoW;
        $endDayName   = date('l', strtotime("Sunday + $endDoWNo days"));
        $interval     = $input->getString('interval', 'week');

        if ($interval == 'month') {
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

        $rawGrid = OrganizerHelper::executeQuery('loadResult');
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
     * @param array $lcrsIDs the lesson subject database ids
     *
     * @return void sets object variable indexes
     */
    private function setLSData($lcrsIDs)
    {
        $tag   = Languages::getTag();
        $query = $this->_db->getQuery(true);

        $select = 'DISTINCT lcrs.id AS lcrsID, ';
        $query->from('#__thm_organizer_lesson_courses AS lcrs');

        // Subject Data
        $select .= 'co.id AS courseID, co.name AS courseName, co.subjectNo, co.untisID AS courseUntisID, ';
        $select .= "s.id AS subjectID, s.name_$tag AS subjectName, s.short_name_$tag AS subjectShortName, ";
        $select .= "s.abbreviation_$tag AS subjectAbbr, ";
        $query->innerJoin('#__thm_organizer_courses AS co ON co.id = lcrs.courseID');
        $query->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.courseID = co.id');
        $query->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');

        // Group Data
        $select .= 'group.id AS groupID, group.untisID AS groupUntisID, ';
        $select .= 'group.name AS groupName, group.full_name AS groupFullName, ';
        $query->innerJoin('#__thm_organizer_lesson_groups AS lg ON lg.lessonCourseID = lcrs.id');
        $query->innerJoin('#__thm_organizer_groups AS group ON group.id = lg.groupID');

        // Category/Program Data
        $select .= 'cat.id AS categoryID, cat.name AS categoryName, ';
        $select .= "prog.name_$tag AS progName, prog.version, dg.abbreviation AS progAbbr, ";
        $query->innerJoin('#__thm_organizer_categories AS cat ON cat.id = group.categoryID');
        $query->leftJoin('#__thm_organizer_programs AS prog ON cat.programID = prog.id');
        $query->leftJoin('#__thm_organizer_degrees AS dg ON prog.degreeID = dg.id');

        // Department Data
        $select .= "d.id AS departmentID, d.short_name_$tag AS department, d.name_$tag AS departmentName";
        $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = cat.id');
        $query->innerJoin('#__thm_organizer_departments AS d ON dr.departmentID = d.id');

        $query->select($select);
        $query->where("lg.delta != 'removed'");
        $query->where("lcrs.id IN ('" . implode("', '", $lcrsIDs) . "')");
        $this->_db->setQuery($query);

        $results = OrganizerHelper::executeQuery('loadAssocList', [], 'lcrsID');
        if (empty($results)) {
            return;
        }

        foreach ($results as $lcrsID => $lsData) {
            $this->lsData[$lcrsID] = $lsData;
        }
    }

    /**
     * Sets the rooms
     *
     * @return void sets an object variable
     */
    private function setRooms()
    {
        $rooms       = Rooms::getPlannedRooms();
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
        $tag   = Languages::getTag();
        $query = $this->_db->getQuery(true);

        $query->select("id, name_$tag AS name, description_$tag AS description")
            ->from('#__thm_organizer_room_types')
            ->order('name');

        $this->_db->setQuery($query);

        $this->roomTypes = OrganizerHelper::executeQuery('loadAssocList', [], 'id');
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
                $roomUse = empty($this->roomData[$roomData['id']]['days'][$date]) ?
                    0 : $this->roomData[$roomData['id']]['days'][$date];

                $this->metaData['days'][$date]['total'] += $dailyBlocks;
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
                    $this->roomData[$roomData['id']]['weeks'][$weekNo]['use']   +=
                        $this->roomData[$roomData['id']]['days'][$currentDate];

                    if ($dailyAverage > $this->threshhold) {
                        $this->roomData[$roomData['id']]['weeks'][$weekNo]['adjustedTotal'] += $dailyBlocks;
                        $this->roomData[$roomData['id']]['weeks'][$weekNo]['adjustedUse']   +=
                            $this->roomData[$roomData['id']]['days'][$currentDate];
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
