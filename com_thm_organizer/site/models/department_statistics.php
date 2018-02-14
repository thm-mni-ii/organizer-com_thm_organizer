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
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/component.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/departments.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/programs.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/planning_periods.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/schedule.php';

/**
 * Class which calculates department statistic data.
 */
class THM_OrganizerModelDepartment_Statistics extends JModelLegacy
{
    private $calendarData;

    public $endDate;

    public $planningPeriods;

    public $rooms;

    public $roomTypes;

    public $roomTypeMap;

    public $startDate;

    public $useData;

    public function __construct(array $config)
    {
        parent::__construct($config);

        $input  = JFactory::getApplication()->input;
        $format = $input->getString('format');

        switch ($format) {
            case 'xls':

                $this->setRoomTypes();
                $this->setRooms();

                $year            = $input->getString('year', date('Y'));
                $this->startDate = "$year-01-01";
                $this->endDate   = "$year-12-31";

                $this->setPlanningPeriods($year);

                $this->calendarData = [];

                // the rooms property is restructured here for quicker access superfluous rooms are removed altogether
                foreach ($this->rooms as $roomName => $roomData) {
                    $booked = $this->setData($roomData['id']);
                    unset($this->rooms[$roomName]);

                    if ($booked) {
                        $this->rooms[$roomData['id']] = $roomName;
                    } else {
                        unset($this->roomTypeMap[$roomData['id']]);
                    }
                }

                foreach (array_keys($this->roomTypes) as $rtID) {
                    if (!in_array($rtID, $this->roomTypeMap)) {
                        unset($this->roomTypes[$rtID]);
                    }
                }

                $this->createUseData();

                break;

            case 'html':
            default:

                $this->setRooms();
                $this->setRoomTypes();

                break;
        }
    }

    /**
     * Restructures the data for the department usage statistics
     *
     * @return void
     */
    private function createUseData()
    {
        $this->useData          = [];
        $this->useData['total'] = [];

        foreach ($this->planningPeriods as $pp) {
            $ppName                 = $pp['name'];
            $this->useData[$ppName] = [];

            $currentDate = $pp['startDate'] < $this->startDate ? $this->startDate : $pp['startDate'];
            $endDate     = $this->endDate < $pp['endDate'] ? $this->endDate : $pp['endDate'];

            for ($currentDate; $currentDate <= $endDate; $currentDate = date('Y-m-d',
                strtotime('+1 day', strtotime($currentDate)))) {
                if (empty($this->calendarData[$currentDate])) {
                    continue;
                }

                foreach ($this->calendarData[$currentDate] as $times => $roomDepts) {
                    list($startTime, $endTime) = explode('-', $times);
                    $minutes = round((strtotime($endTime) - strtotime($startTime)) / 60);

                    foreach ($roomDepts as $roomID => $departments) {
                        $departmentName = $this->getDeparmentName($departments);
                        $this->setUseData('total', $departmentName, $roomID, $minutes);
                        $this->setUseData($ppName, $departmentName, $roomID, $minutes);
                    }
                }
            }

            ksort($this->useData['total']);
            ksort($this->useData[$ppName]);
        }
        unset ($this->calendarData);
    }

    /**
     * Aggregates the raw instance data into calendar entries
     *
     * @param array $rawInstances the raw lesson instances for a specific room
     *
     * @return void
     */
    private function aggregateInstances($rawInstances)
    {
        foreach ($rawInstances as $rawInstance) {
            $rawConfig = json_decode($rawInstance['configuration'], true);

            // Should not be able to occur because of the query conditions.
            if (empty($rawConfig['rooms'])) {
                continue;
            }

            $date  = $rawInstance['date'];
            $times = "{$rawInstance['startTime']}-{$rawInstance['endTime']}";

            foreach ($rawConfig['rooms'] as $roomID => $delta) {
                if (!in_array($roomID, array_keys($this->roomTypeMap)) or $delta == 'removed') {
                    continue;
                }

                if (empty($this->calendarData[$date])) {
                    $this->calendarData[$date] = [];
                }

                if (empty($this->calendarData[$date][$times])) {
                    $this->calendarData[$date][$times] = [];
                }

                if (empty($this->calendarData[$date][$times][$roomID])) {
                    $this->calendarData[$date][$times][$roomID] = [];
                }

                $this->calendarData[$date][$times][$roomID][$rawInstance['departmentID']] = $rawInstance['department'];
            }
        }
    }

    /**
     * Makes the department name or department name aggregate
     *
     * @param $departments
     *
     * @return string the department name
     */
    private function getDeparmentName($departments)
    {
        $deptCount = count($departments);

        if ($deptCount === 1) {
            return array_pop($departments);
        }

        $count          = 1;
        $departmentName = '';

        asort($departments);

        foreach ($departments as $department) {
            if ($count == 1) {
                $departmentName .= $department;
            } elseif ($count == $deptCount) {
                $departmentName .= " & $department";
            } else {
                $departmentName .= ", $department";
            }

            $count++;
        }

        return $departmentName;
    }

    /**
     * Retrieves room options
     *
     * @return array an array of room options
     */
    public function getRoomOptions()
    {
        $rooms = $this->rooms;

        $options = [];

        foreach ($rooms as $roomName => $roomData) {
            $option['value'] = $roomData['id'];
            $option['text']  = $roomName;
            $options[]       = $option;
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
            $option['value'] = $typeID;
            $option['text']  = $typeData['name'];
            $options[]       = $option;
        }

        return $options;
    }

    /**
     * Creates year selection options
     *
     * @return array
     * @throws Exception
     */
    public function getYearOptions()
    {
        $options = [];

        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT YEAR(schedule_date) AS year')->from('#__thm_organizer_calendar')->order('year');

        $this->_db->setQuery($query);

        try {
            $years = $this->_db->loadColumn();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'));

            return $options;
        }

        if (!empty($years)) {
            foreach ($years as $year) {

                $option['value'] = $year;
                $option['text']  = $year;
                $options[]       = $option;
            }
        }

        return $options;
    }

    /**
     * Retrieves raw lesson instance information from the database
     *
     * @param int $roomID the id of the room being iterated
     *
     * @return bool true if room information was found, otherwise false
     * @throws Exception
     */
    private function setData($roomID)
    {
        $tag       = THM_OrganizerHelperLanguage::getShortTag();
        $dbo       = JFactory::getDbo();
        $ringQuery = $dbo->getQuery(true);

        $rqSelect = "DISTINCT ccm.id AS ccmID, d.id AS departmentID, d.short_name_$tag AS department, lc.configuration, ";
        $rqSelect .= "c.schedule_date AS date, TIME_FORMAT(c.startTime, '%H:%i') AS startTime, TIME_FORMAT(c.endTime, '%H:%i') AS endTime";

        $ringQuery->select($rqSelect);
        $ringQuery->from('#__thm_organizer_lessons AS l');
        $ringQuery->innerJoin('#__thm_organizer_departments AS d ON l.departmentID = d.id');
        $ringQuery->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id');
        $ringQuery->innerJoin('#__thm_organizer_calendar AS c ON l.id = c.lessonID');
        $ringQuery->innerJoin('#__thm_organizer_lesson_configurations AS lc ON lc.lessonID = ls.id');
        $ringQuery->innerJoin('#__thm_organizer_calendar_configuration_map AS ccm ON ccm.calendarID = c.id AND ccm.configurationID = lc.id');

        $ringQuery->where("ls.delta != 'removed'");
        $ringQuery->where("l.delta != 'removed'");
        $ringQuery->where("c.delta != 'removed'");
        $ringQuery->where("schedule_date BETWEEN '$this->startDate' AND '$this->endDate'");

        $regexp = '[[.quotation-mark.]]rooms[[.quotation-mark.]][[.colon.]][[.{.]]' .
            '([[.quotation-mark.]][[:alnum:]]*[[.quotation-mark.]][[.colon.]]?[[.comma.]]?)*' .
            '[[.quotation-mark.]]' . $roomID . '[[.quotation-mark.]][[.colon.]]' .
            '[[.quotation-mark.]][^removed]';
        $ringQuery->where("lc.configuration REGEXP '$regexp'");
        $dbo->setQuery($ringQuery);

        try {
            $roomInstanceConfigurations = $dbo->loadAssocList();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

            return false;
        }

        if (empty($roomInstanceConfigurations)) {
            return false;
        }

        $this->aggregateInstances($roomInstanceConfigurations);

        return true;
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
     * @throws Exception
     */
    private function setRoomTypes()
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $tag   = THM_OrganizerHelperLanguage::getShortTag();

        $query->select("id, name_$tag AS name, description_$tag AS description");
        $query->from('#__thm_organizer_room_types');
        $query->order("name");

        $dbo->setQuery($query);

        $default = [];

        try {
            $results = $dbo->loadAssocList('id');
        } catch (RuntimeException $exc) {
            JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

            $this->roomTypes = $default;

            return;
        }

        $this->roomTypes = (empty($results)) ? $default : $results;
    }

    /**
     * Retrieves the relevant planning period data from the database
     *
     * @param string $year the year used for the statistics generation
     *
     * @return bool true if the query was successfull, otherwise false
     * @throws Exception
     */
    private function setPlanningPeriods($year)
    {
        $query = $this->_db->getQuery(true);
        $query->select('*')->from('#__thm_organizer_planning_periods')
            ->where("(YEAR(startDate) = $year OR YEAR(endDate) = $year)")
            ->order('startDate');
        $this->_db->setQuery($query);

        try {
            $this->planningPeriods = $this->_db->loadAssocList('id');
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
            $this->planningPeriods = [];

            return false;
        }

        return empty($this->planningPeriods) ? false : true;
    }

    /**
     * Sets/sums individual usage values in it's container property
     *
     * @param string $ppName   the name of the planning period
     * @param string $deptName the name of the department
     * @param int    $roomID   the id of the room
     * @param int    $value    the number of minutes
     *
     * @return void
     */
    private function setUseData($ppName, $deptName, $roomID, $value)
    {
        if (empty($this->useData[$ppName][$deptName])) {
            $this->useData[$ppName][$deptName] = [];
        }

        $existingValue                              = empty($this->useData[$ppName][$deptName][$roomID]) ? 0 : $this->useData[$ppName][$deptName][$roomID];
        $this->useData[$ppName][$deptName][$roomID] = $existingValue + $value;
    }
}
