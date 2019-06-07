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

use Joomla\CMS\Factory;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Rooms;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which calculates department statistic data.
 */
class Department_Statistics extends BaseModel
{
    private $calendarData;

    public $endDate;

    public $terms;

    public $rooms;

    public $roomTypes;

    public $roomTypeMap;

    public $startDate;

    public $useData;

    /**
     * Department_Statistics constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $input  = OrganizerHelper::getInput();
        $format = $input->getString('format');

        switch ($format) {
            case 'xls':
                $this->setRoomTypes();
                $this->setRooms();

                $year            = $input->getString('year', date('Y'));
                $this->startDate = "$year-01-01";
                $this->endDate   = "$year-12-31";

                $this->setTerms($year);

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

        foreach ($this->terms as $term) {
            $termName                 = $term['name'];
            $this->useData[$termName] = [];

            $currentDate = $term['startDate'] < $this->startDate ? $this->startDate : $term['startDate'];
            $endDate     = $this->endDate < $term['endDate'] ? $this->endDate : $term['endDate'];

            while ($currentDate <= $endDate) {
                if (empty($this->calendarData[$currentDate])) {
                    continue;
                }

                foreach ($this->calendarData[$currentDate] as $times => $roomDepts) {
                    list($startTime, $endTime) = explode('-', $times);
                    $minutes = round((strtotime($endTime) - strtotime($startTime)) / 60);

                    foreach ($roomDepts as $roomID => $departments) {
                        $departmentName = $this->getDepartmentName($departments);
                        $this->setUseData('total', $departmentName, $roomID, $minutes);
                        $this->setUseData($termName, $departmentName, $roomID, $minutes);
                    }
                }

                $currentDate = date('Y-m-d', strtotime('+1 day', strtotime($currentDate)));
            }

            ksort($this->useData['total']);
            ksort($this->useData[$termName]);
        }
        unset($this->calendarData);
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
    private function getDepartmentName($departments)
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
     * Creates year selection options
     *
     * @return array
     */
    public function getYearOptions()
    {
        $options = [];

        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT YEAR(schedule_date) AS year')->from('#__thm_organizer_calendar')->order('year');

        $this->_db->setQuery($query);
        $years = OrganizerHelper::executeQuery('loadColumn', []);

        if (!empty($years)) {
            foreach ($years as $year) {
                $options[$year] = $year;
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
     */
    private function setData($roomID)
    {
        $tag     = Languages::getShortTag();
        $cSelect = "c.schedule_date AS date, TIME_FORMAT(c.startTime, '%H:%i') AS startTime, ";
        $cSelect .= "TIME_FORMAT(c.endTime, '%H:%i') AS endTime";

        $ringQuery = $this->_db->getQuery(true);
        $ringQuery->select('DISTINCT ccm.id AS ccmID')
            ->from('#__thm_organizer_calendar_configuration_map AS ccm')
            ->select($cSelect)
            ->innerJoin('#__thm_organizer_calendar AS c ON c.id = ccm.calendarID')
            ->select('conf.configuration')
            ->innerJoin('#__thm_organizer_lesson_configurations AS conf ON conf.id = ccm.configurationID')
            ->innerJoin('#__thm_organizer_lessons AS l ON l.id = c.lessonID')
            ->select("d.id AS departmentID, d.short_name_$tag AS department")
            ->innerJoin('#__thm_organizer_departments AS d ON l.departmentID = d.id')
            ->select('lcrs.id as lcrsID')
            ->innerJoin('#__thm_organizer_lesson_courses AS lcrs ON lcrs.lessonID = l.id');


        $ringQuery->where("lcrs.delta != 'removed'");
        $ringQuery->where("l.delta != 'removed'");
        $ringQuery->where("c.delta != 'removed'");
        $ringQuery->where("schedule_date BETWEEN '$this->startDate' AND '$this->endDate'");

        $regexp = '"rooms":\\{("[0-9]+":"[\w]*",)*"' . $roomID . '":("new"|"")';
        $ringQuery->where("conf.configuration REGEXP '$regexp'");
        $dbo->setQuery($ringQuery);

        $roomConfigurations = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($roomConfigurations)) {
            return false;
        }

        $this->aggregateInstances($roomConfigurations);

        return true;
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
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $tag   = Languages::getShortTag();

        $query->select("id, name_$tag AS name, description_$tag AS description");
        $query->from('#__thm_organizer_room_types');
        $query->order('name');
        $dbo->setQuery($query);

        $this->roomTypes = OrganizerHelper::executeQuery('loadAssocList', [], 'id');
    }

    /**
     * Retrieves the relevant term data from the database
     *
     * @param string $year the year used for the statistics generation
     *
     * @return bool true if the query was successfull, otherwise false
     */
    private function setTerms($year)
    {
        $query = $this->_db->getQuery(true);
        $query->select('*')->from('#__thm_organizer_terms')
            ->where("(YEAR(startDate) = $year OR YEAR(endDate) = $year)")
            ->order('startDate');
        $this->_db->setQuery($query);

        $this->terms = OrganizerHelper::executeQuery('loadAssocList', [], 'id');

        return empty($this->terms) ? false : true;
    }

    /**
     * Sets/sums individual usage values in it's container property
     *
     * @param string $termName the name of the term
     * @param string $deptName the name of the department
     * @param int    $roomID   the id of the room
     * @param int    $value    the number of minutes
     *
     * @return void
     */
    private function setUseData($termName, $deptName, $roomID, $value)
    {
        if (empty($this->useData[$termName][$deptName])) {
            $this->useData[$termName][$deptName] = [];
        }

        $existingValue = empty($this->useData[$termName][$deptName][$roomID]) ?
            0 : $this->useData[$termName][$deptName][$roomID];

        $this->useData[$termName][$deptName][$roomID] = $existingValue + $value;
    }
}
