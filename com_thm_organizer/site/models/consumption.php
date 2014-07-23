<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        consumption model
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_COMPONENT_SITE . "/helper/event.php";
define('ROOM', 1);
define('TEACHER', 2);

/**
 * Class THM_OrganizerModelConsumption for component com_thm_organizer
 * Class provides methods to get the neccessary data to display a schedule consumption
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelConsumption extends JModelLegacy
{
    public $schedule = null;

    public $reset = false;

    public $type = ROOM;

    public $consumption = null;

    public $process = array();

    public $selected = array();

    public $names = array();

    public $startDate = '';

    public $endDate = '';


    /**
     * Sets construction model properties
     *
     * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->setObjectProperties();

        if (!empty($this->schedule))
        {
            // Behaviour has to be set before consumption is calculated
            $this->process['rooms'] = ($this->type === ROOM);
            $this->process['teachers'] = ($this->type === TEACHER);

            $this->setConsumption();
            if ($this->process['rooms'])
            {
                $rooms = $this->getItems('rooms');
                $this->names['rooms'] = $this->getNameArray('rooms', $rooms, array('longname'));
                $this->setSelected('rooms');
                $this->names['roomtypes'] = $this->getNameArray('roomtypes', $this->consumption['roomtypes']);
                $this->setSelected('roomtypes');
                $this->filterType('rooms', 'roomtypes');
                $this->filterResource('rooms');
            }

            if ($this->process['teachers'])
            {
                $teachers = $this->getItems('teachers');
                $properties = array('surname', 'forename');
                $this->names['teachers'] = $this->getNameArray('teachers', $teachers, $properties, ', ');
                $this->setSelected('teachers');
                $this->names['fields'] = $this->getNameArray('fields', $this->consumption['fields']);
                $this->setSelected('fields');
                $this->filterType('teachers', 'fields');
                $this->filterResource('teachers');
            }
        }
    }

    /**
     * Sets object properties
     *
     * @return  void
     */
    private function setObjectProperties()
    {
        $input = JFactory::getApplication()->input;
        $this->reset = $input->getBool('reset', false);
        $this->type = $input->getInt('type', ROOM);
        $this->process['rooms'] = false;
        $this->process['teachers'] = false;
        $resources = array('rooms', 'teachers', 'roomtypes', 'fields');
        foreach ($resources as $resource)
        {
            $this->selected[$resource] = array();
            $this->names[$resource] = array();
        }
        $this->setSchedule();
        $this->setDates();
    }

    /**
     * Retrieves a list of room items
     *
     * @param   string  $type  the type of item to be retrieved
     *
     * @return array
     */
    private function getItems($type)
    {
        $rowKeys = array();
        foreach ($this->consumption[$type] as $resourceConsumption)
        {
            $rowKeys = array_merge($rowKeys, array_keys($resourceConsumption));
        }
        asort($rowKeys);
        return array_unique($rowKeys);
    }

    /**
     * Gets all schedules in the database
     *
     * @return array An array with the schedules
     */
    public function getActiveSchedules()
    {
        $query = $this->_db->getQuery(true);
        $columns = array('departmentname', 'semestername');
        $select = 'id, ' . $query->concatenate($columns, ' - ') . ' AS name';
        $query->select($select);
        $query->from("#__thm_organizer_schedules");
        $query->where("active = '1'");
        $query->order('name');

        $this->_db->setQuery((string) $query);
        try 
        {
            $result = $this->_db->loadAssocList();
            return $result;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }
    
    /**
     * Method to set a schedule by its id from the database
     *
     * @return  object  an schedule object on success, otherwise an empty object
     */
    public function setSchedule()
    {        
        $scheduleID = JFactory::getApplication()->input->getInt('activated', 0);
        $query = $this->_db->getQuery(true);
        $query->select('schedule');
        $query->from("#__thm_organizer_schedules");
        $query->where("id = '$scheduleID'");
        $this->_db->setQuery((string) $query);
        try
        {
            $result = $this->_db->loadResult();
            $this->schedule = json_decode($result);
        }
        catch (Exception $exception)
        {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');
            $this->schedule = null;
        }
    }
    
    /**
     * Calculates resource consumption from a schedule
     *
     * @return  object  an object modeling resource consumption
     */
    public function setConsumption()
    {
        $this->consumption = array();
        $this->consumption['rooms'] = array();
        $this->consumption['roomtypes'] = array();
        $this->consumption['teachers'] = array();
        $this->consumption['fields'] = array();

        $startDate = THM_OrganizerHelperEvent::standardizeDate($this->startDate);
        $endDate = THM_OrganizerHelperEvent::standardizeDate($this->endDate);
        if (isset($this->schedule->calendar))
        {
            foreach ($this->schedule->calendar as $day => $blocks)
            {
                // Sylength is not relevant for consumption and does not have object as a value
                if ($day == 'sylength')
                {
                    continue;
                }
                $invalidDay = ($startDate > $day OR $endDate < $day);
                if ($invalidDay)
                {
                    continue;
                }
                $this->setConsumptionByInstance($blocks);
            }
        }
    }

    /**
     * Sets consumption by instance (block + lesson)
     *
     * @param   object  &$blocks  the blocks of the date being iterated
     * 
     * @return  void
     */
    private function setConsumptionByInstance(&$blocks)
    {
        foreach ($blocks as $blockNumber => $blockLessons)
        {
            $starttime = $this->schedule->periods->$blockNumber->starttime;
            $startDT = strtotime(substr($starttime, 0, 2) . ':' . substr($starttime, 2, 2) . ':00');
            $endtime = $this->schedule->periods->$blockNumber->endtime;
            $endDT = strtotime(substr($endtime, 0, 2) . ':' . substr($endtime, 2, 2) . ':00');
            $hours = ($endDT - $startDT) / 3600;
            foreach ($blockLessons as $lessonID => $lessonValues)
            {
                if (isset($lessonValues->delta) AND $lessonValues->delta == 'removed')
                {
                    continue;
                }
                if ($this->process['teachers'])
                {
                    $this->setTeachersByInstance($lessonID, $hours);
                }
                if ($this->process['rooms'])
                {
                    foreach ($lessonValues as $roomID => $roomDelta)
                    {
                        $this->setRoomsByInstance($lessonID, $roomID, $roomDelta, $hours);
                    }
                }
            }
        }
    }

    /**
     * Iterates the lesson associated pools for the purpose of teacher consumption
     *
     * @param   string  $lessonID  the lesson ID
     * @param   int     $hours     the duration of the current block in hours
     *
     * @return  void
     */
    private function setTeachersByInstance($lessonID, $hours)
    {
        $pools = $this->schedule->lessons->$lessonID->pools;
        $teachers = $this->schedule->lessons->$lessonID->teachers;

        foreach ($teachers as $teacherID => $teacherDelta)
        {
            $this->setTeacherConsumption('raw', $teacherID, $teacherDelta, $hours);
        }

        foreach ($pools as $poolID => $poolDelta)
        {
            if ($poolDelta == 'removed')
            {
                continue;
            }
            $degree = $this->schedule->pools->$poolID->degree;
            foreach ($teachers as $teacherID => $teacherDelta)
            {
                $this->setTeacherConsumption($degree, $teacherID, $teacherDelta, $hours);
            }
        }
    }

    /**
     * Sets teacher consumption values, creating the storage objects if not set
     *
     * @param   string  $degree     the degree name
     * @param   string  $teacherID  the teacher id
     * @param   string  $delta      the teacher's delta information for the
     *                              lesson being iterated
     * @param   int     $hours      the duration of the current block in hours
     *
     * @return  void
     */
    private function setTeacherConsumption($degree, $teacherID, $delta, $hours)
    {
        if ($delta !== "removed")
        {
            if (!isset($this->consumption['teachers'][$degree]))
            {
                $this->consumption['teachers'][$degree] = array();
            }

            $fieldKey = empty($this->schedule->teachers->$teacherID->description)? '' : $this->schedule->teachers->$teacherID->description;
            if (!isset($this->consumption['teachers'][$degree][$teacherID]))
            {
                $this->consumption['teachers'][$degree][$teacherID] = array();
                $this->consumption['teachers'][$degree][$teacherID]['hours'] = $hours;
                $this->consumption['teachers'][$degree][$teacherID]['type'] = $fieldKey;
            }
            else
            {
                $this->consumption['teachers'][$degree][$teacherID]['hours'] += $hours;
            }

            if (!empty($fieldKey))
            {
                $fieldValue = $this->schedule->fields->$fieldKey->name;
                if (!isset($this->consumption['fields'][$fieldKey]))
                {
                    $this->consumption['fields'][$fieldKey] = $fieldValue;
                }
            }
        }
    }

    /**
     * Sets room consumption values by lesson
     * 
     * @param   string  $lessonID   the id of the lesson being iterated
     * @param   string  $roomID     the id of the room being iterated
     * @param   string  $roomDelta  the room's delta value
     * @param   int     $hours      the duration of the current block in hours
     *
     * @return  void
     */
    private function setRoomsByInstance($lessonID, $roomID, $roomDelta, $hours)
    {
        if ($roomID !== "delta" && $roomDelta !== "removed")
        {
            $this->setRoomConsumption('raw', $roomID, $hours);
            $pools = $this->schedule->lessons->$lessonID->pools;
            foreach ($pools as $poolID => $delta)
            {
                if ($delta == 'removed')
                {
                    continue;
                }
                $degree = $this->schedule->pools->$poolID->degree;
                $this->setRoomConsumption($degree, $roomID, $hours);
            }
        }
    }

    /**
     * Sets consumption values for a lesson instance
     * 
     * @param   string  $degree  the degree name
     * @param   string  $roomID  the room id
     * @param   int     $hours   the duration of the current block in hours
     *
     * @return  void
     */
    private function setRoomConsumption($degree, $roomID, $hours)
    {
        if (!isset($this->consumption['rooms'][$degree]))
        {
            $this->consumption['rooms'][$degree] = array();
        }

        $typeKey = $this->schedule->rooms->$roomID->description;
        if (!isset($this->consumption['rooms'][$degree][$roomID]))
        {
            $this->consumption['rooms'][$degree][$roomID] = array();
            $this->consumption['rooms'][$degree][$roomID]['hours'] = $hours;
            $this->consumption['rooms'][$degree][$roomID]['type'] = $typeKey;
        }
        else
        {
            $this->consumption['rooms'][$degree][$roomID]['hours'] += $hours;
        }

        $typeValue = $this->schedule->roomtypes->$typeKey->name;
        if (!isset($this->consumption['roomtypes'][$typeKey]))
        {
            $this->consumption['roomtypes'][$typeKey] = $typeValue;
        }
    }

    /**
     * Function to get a table displaying resource consumption for a schedule
     * 
     * @param   string  $type  either teachers or rooms
     * 
     * @return  string  a HTML string for a consumption table
     */
    public function getConsumptionTable($type)
    {
        if ($type != 'rooms' AND $type != 'teachers')
        {
            return;
        }
        $table = "<table id='thm_organizer-{$type}-consumption-table' ";
        $table .= "class='consumption-table'>";

        $columns = array_keys($this->consumption[$type]);
        asort($columns);

        $rows = $this->getItems($type);

        $table .= $this->getTableHead($columns, $rows, $type);
        $table .= $this->getTableBody($columns, $rows, $type);
        
        $table .= '</table>';
        return $table;
    }

    /**
     * Builds the consumption table head
     * 
     * @param   array   $columns  the columns of the table
     * @param   array   $rows     the rows used in the table
     * @param   string  $type     the type of resource
     * 
     * @return  string  the table head as a string
     */
    private function getTableHead($columns, $rows, $type)
    {
        $total = array('raw' => JText::_('COM_THM_ORGANIZER_TOTAL'));
        $degrees = $this->getNameArray('degrees', $columns, array('name'));
        $names = array_merge($total, $degrees);
        $tableName = 'COM_THM_ORGANIZER_' . strtoupper($type);
        $tableHead = '<tr><th>' . JText::_($tableName) . '</th>';
        $tableHead .= '<th>' . JText::_('COM_THM_ORGANIZER_TOTAL') . '</th>';
        foreach ($columns as $column)
        {
            if ($column == 'raw')
            {
                continue;
            }
            $tableHead .= '<th>' . $names[$column] . '</th>';
        }
        $tableHead .= '</tr>';
        $tableHead .= '<tr class="summary-row"><th>' . JText::_('COM_THM_ORGANIZER_SUM') . '</th>';
        $tableHead .= '<th class="degree-use-total resource-use-total">' . $this->getColumnSum($type, 'raw', $rows) . '</th>';
        foreach ($columns as $column)
        {
            if ($column == 'raw')
            {
                continue;
            }
            $tableHead .= '<th class="degree-use-total">' . $this->getColumnSum($type, $column, $rows) . '</th>';
        }
        $tableHead .= '</tr>';
        return $tableHead;
    }

    /**
     * Gets the sum for the column
     *
     * @param   string  $type         the type of resource
     * @param   string  $columnIndex  the index at which the sum is to be calculated
     * @param   array   &$resources   the resources whose values are to be summed
     *
     * @return  int  the sum for the column
     */
    private function getColumnSum($type, $columnIndex, &$resources)
    {
        $sum = 0;
        foreach ($resources as $resource)
        {
            if (isset($this->consumption[$type][$columnIndex][$resource]['hours']))
            {
                $sum += $this->consumption[$type][$columnIndex][$resource]['hours'];
            }
        }
        return $sum;
    }

    /**
     * Creates the consumption table body
     * 
     * @param   array   $columns  the columns used in the table
     * @param   array   $rows     the rows used in the table
     * @param   string  $type     the type of resource being observed
     * 
     * @return  string  a html sting containing the table body
     */
    private function getTableBody($columns, $rows, $type)
    {
        $tableBody = '';

        foreach ($rows as $row)
        {
            $tableBody .= '<tr>';
            
            if ($type === "rooms")
            {
                $tableBody .= '<th class="index-column">' . $this->names['rooms'][$row] . '</th>';
            }
            if ($type === "teachers")
            {
                $tableBody .= '<th class="index-column">' . $this->names['teachers'][$row] . '</th>';
            }

            $tableBody .= '<th class="resource-use-total">' . $this->consumption[$type]['raw'][$row]['hours'] . '</th>';
            $columnKeys = array_keys($columns);
            foreach ($columns as $column)
            {
                if ($column == 'raw')
                {
                    continue;
                }
                $tableBody .= '<td>';
                if (isset($this->consumption[$type][$column][$row]))
                {
                    $tableBody .= $this->consumption[$type][$column][$row]['hours'];
                }
                $tableBody .= '</td>';
            }
            $tableBody .= '</tr>';
        }
        return $tableBody;
    }

    /**
     * Gets a list of resource names
     *
     * @param   string  $category    the resource category (rooms|teachers)
     * @param   array   $resources   the resources
     * @param   array   $properties  the properties used to build the name
     * @param   string  $separator   an optional separator to place between property values
     *
     * @return  array  a list of resource names
     */
    public function getNameArray($category, $resources, $properties = null, $separator = ' ')
    {
        $names = array();
        foreach ($resources as $resourceKey => $resource)
        {
            if (empty($properties))
            {
                $names[$resourceKey] = $resource;
                continue;
            }

            $initial = true;
            $names[$resource] = '';
            foreach ($properties as $property)
            {
                if (empty($this->schedule->$category->$resource->$property))
                {
                    continue;
                }
                if (!$initial)
                {
                    $names[$resource] .= $separator;
                }
                $names[$resource] .= $this->schedule->$category->$resource->$property;
                $initial = false;
            }
            if (empty($names[$resource]))
            {
                unset($names[$resource]);
            }
        }
        asort($names);
        return $names;
    }

    /**
     * Gets the list of selected resources
     *
     * @param   string  $type  the resource type (rooms|roomtypes|teachers|fields)
     *
     * @return  void
     */
    private function setSelected($type)
    {
        $default = array();
        $default[] = '*';
        $selected = JFactory::getApplication()->input->get($type, $default, 'array');
        $useDefault = ($this->reset OR (count($selected) > 1 AND in_array('*', $selected)));
        if ($useDefault)
        {
            $this->selected[$type] = $default;
            return;
        }
        $this->selected[$type] = $selected;
    }

    /**
     * Sets the date attributes used for deciding which dates apply toward consumption calculation
     *
     * @return  void  sets class variables $startDate and $endDate
     */
    private function setDates()
    {
        $input = JFactory::getApplication()->input;
        $selectedSD = $input->getString('startdate', '');
        $selectedED = $input->getString('enddate', '');
        if (empty($this->schedule))
        {
            $this->startDate = $selectedSD;
            $this->endDate = $selectedED;
            return;
        }

        $useDefault = ($this->reset OR empty($selectedSD));
        if (!$useDefault)
        {
            $this->startDate = $selectedSD;
        }
        elseif (!empty($this->schedule->termStartDate))
        {
            $this->startDate = THM_OrganizerHelperEvent::localizeDate($this->schedule->termStartDate);
        }
        else
        {
            $this->startDate = THM_OrganizerHelperEvent::localizeDate($this->schedule->startdate);
        }

        if (!$this->reset AND !empty($selectedSD))
        {
            $this->endDate = $selectedED;
        }
        elseif (!empty($this->schedule->termEndDate))
        {
            $this->endDate = THM_OrganizerHelperEvent::localizeDate($this->schedule->termEndDate);
        }
        else
        {
            $this->endDate = THM_OrganizerHelperEvent::localizeDate($this->schedule->enddate);
        }
    }

    /**
     * Removed unselected resource entries from the consumption values
     * 
     * @param   string  $resource  the resource to be filtered
     *
     * @return  void  removes consumption entries
     */
    private function filterResource($resource)
    {
        $selected = $this->selected[$resource];

        // Display all
        if (count($selected) === 1 AND $selected[0] === '*')
        {
            return;
        }

        foreach ($this->consumption[$resource] as &$degree)
        {
            $resourceKeys = array_keys($degree);
            foreach ($resourceKeys as $resourceKey)
            {
                if (!in_array($resourceKey, $selected))
                {
                    unset($degree[$resourceKey]);
                }
            }
        }
    }

    /**
     * Removed unselected entries from the consumption values
     *
     * @param   string  $resource  the resource to be filtered
     * @param   string  $type      the type of resource to be filtered
     *
     * @return  void  removes consumption entries
     */
    private function filterType($resource, $type)
    {
        $selected = $this->selected[$type];
        if (count($selected) === 1 AND $selected[0] === '*')
        {
            return;
        }

        foreach ($this->consumption[$resource] as &$degree)
        {
            foreach ($degree as $resourceKey => $resourceValue)
            {
                if (!in_array($resourceValue['type'], $selected))
                {
                    unset($degree[$resourceKey]);
                    if (isset($this->names[$resource][$resourceKey]))
                    {
                        unset($this->names[$resource][$resourceKey]);
                    }
                }
            }
        }
    }
}
