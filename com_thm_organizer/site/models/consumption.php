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
defined('_JEXEC') or die();

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

    public $consumption = null;

    public $roomNames = null;

    public $teacherNames = null;


    /**
     * Sets construction model properties
     * 
     * @param type $config
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->setSchedule();
        $this->setConsumption();
    }

    /**
     * Gets all schedules in the database
     *
     * @return array An array with the schedules
     */
    public function getActiveSchedules()
    {
        $query = $this->_db->getQuery(true);

        $Columns = array('departmentname', 'semestername');
        $select = 'id, ' . $query->concatenate($Columns, ' - ') . ' AS name';
        $query->select($select);
        $query->from("#__thm_organizer_schedules");
        $query->order('name');

        $this->_db->setQuery((string) $query);
        try 
        {
            $result = $this->_db->loadAssocList();
        }
        catch (Exception $exception)
        {
            if (defined('DEBUG'))
            {
                JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');
            }
            else
            {
                JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_DB_ERROR', 'error');
            }
        }
        return $result;
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
            if (defined('DEBUG'))
            {
                JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');
            }
            else
            {
                JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_DB_ERROR', 'error');
            }
            $this->schedule = new stdClass();
        }
        
    }
    
    /**
     * Calculates resource consumtion from a schedule
     *
     * @param   object  &$schedule  a schedule object
     *
     * @return  object  an object modeling resource consumption
     */
    public function setConsumption()
    {
        $this->consumption = array();
        $this->consumption['rooms'] = array();
        $this->consumption['teachers'] = array();

        if(isset($this->schedule->calendar))
        {
            foreach ($this->schedule->calendar as $day => $blocks)
            {
                // Sylength is not relevant for consumption and does not have object as a value
                if ($day != 'sylength')
                {
                    $this->setConsumptionByInstance($day, $blocks);
                }
            }
        }
    }

    /**
     * Sets consumtion by instance (block + lesson)
     * 
     * @param   string  $day  the date being currently iterated
     * @param   object  $blocks  the blocks of the date being iterated
     * 
     * @return  void
     */
    private function setConsumptionByInstance($day, &$blocks)
    {
        foreach ($blocks as $block)
        {
            foreach ($block as $lessonID => $lessonValues)
            {
                if (isset($lessonValues->delta) AND $lessonValues->delta == 'removed')
                {
                    continue;
                }
                foreach ($lessonValues as $roomID => $roomDelta)
                {
                    $this->setConsumptionByRoom($lessonID, $roomID, $roomDelta);
                }
            }
        }
    }

    /**
     * Sets resource consumption values by room
     * 
     * @param   string  $lessonID   the id of the lesson being iterated
     * @param   string  $roomID     the id of the room being iterated
     * @param   string  $roomDelta  the room's delta value
     */
    private function setConsumptionByRoom($lessonID, $roomID, $roomDelta)
    {
        if ($roomID !== "delta" && $roomDelta !== "removed")
        {
            $lessonPools = $this->schedule->lessons->$lessonID->pools;
            $lessonTeachers = $this->schedule->lessons->$lessonID->teachers;

            foreach ($lessonPools as $lessonPoolID => $lessonPoolDelta)
            {
                $degree = $this->schedule->pools->$lessonPoolID->degree;
                $this->setConsumptionByPool($degree, $lessonPoolDelta, $roomID, $lessonTeachers);
            }
        }
    }

    /**
     * Sets consumption values for a lesson instance
     * 
     * @param   string  $degree           the degree name
     * @param   string  $lessonPoolDelta  the lesson's delta value
     * @param   string  $roomID           the room id
     * @param   object  &$teachers        the lesson's teachers
     */
    private function setConsumptionByPool($degree, $lessonPoolDelta, $roomID, &$teachers)
    {
        if ($lessonPoolDelta !== "removed")
        {
            $this->setRoomConsumption($degree, $roomID);

            foreach ($teachers as $teacherID => $teacherDelta)
            {
                $this->setTeacherConsumption($degree, $teacherID, $teacherDelta);
            }
        }
    }

    /**
     * Sets room consumption values, creating the storage objects if not set
     * 
     * @param   string  $degree  the degree name
     * @param   string  $roomID  the room id
     * 
     * @return  void
     */
    private function setRoomConsumption($degree, $roomID)
    {
        if (!isset($this->consumption['rooms'][$degree]))
        {
            $this->consumption['rooms'][$degree] = array();
        }

        if (!isset($this->consumption['rooms'][$degree][$roomID]))
        {
            $this->consumption['rooms'][$degree][$roomID] = 1;
        }
        else
        {
            $this->consumption['rooms'][$degree][$roomID] ++;
        }
    }

    /**
     * Sets teacher consumption values, creating the storage objects if not set
     * 
     * @param   string  $degree     the degree name
     * @param   string  $teacherID  the teacher id
     * @param   string  $delta      the teacher's delta information for the
     *                              lesson being iterated
     * 
     * @return  void
     */
    private function setTeacherConsumption($degree, $teacherID, $delta)
    {
        if ($delta !== "removed")
        {
            if (!isset($this->consumption['teachers'][$degree]))
            {
                $this->consumption['teachers'][$degree] = array();
            }

            if (!isset($this->consumption['teachers'][$degree][$teacherID]))
            {
                $this->consumption['teachers'][$degree][$teacherID] = 1;
            }
            else
            {
            $this->consumption['teachers'][$degree][$teacherID] ++;
            }
        }
    }

    /**
     * Method to get a schedule table for consumptions
     * 
     * @param   string  $type                Either teachers or rooms
     * @param   string  $schedule            The actual schedule
     * 
     * @return HTML_Table  A html table with the consumptions
     */
    public function getConsumptionTable($type)
    {
        if ($type != 'rooms' AND $type != 'teachers')
        {
            return;
        }
        $table = "<table id='thm_organizer_{$type}_consumption_table' ";
        $table .= "class='thm_organizer_consumption_table_class'>";

        $columns = array_keys($this->consumption[$type]);
        asort($columns);

        $rowKeys = array();
        foreach ($this->consumption[$type] as $resourceConsumption)
        {
            $rowKeys = array_merge($rowKeys, array_keys($resourceConsumption));
        }
        asort($rowKeys);
        $rows = array_unique($rowKeys);

        $this->roomNames = $this->getNameArray('rooms', $rows, array('longname'));
        $properties = array('surname', 'forename');
        $this->teacherNames = $this->getNameArray('teachers', $rows, $properties, ', ');

        $table .= $this->getTableHead($columns);
        $table .= $this->getTableBody($columns, $rows, $type);
        
        $table .= '</table>';
        return $table;
    }

    /**
     * Builds the consumption table head
     * 
     * @param   array  $columns  the columns of the table
     * 
     * @return  string  the table head as a string
     */
    private function getTableHead($columns)
    {
        $names = $this->getNameArray('degrees', $columns, array('name'));
        $tableHead = '<thead><tr><td />';
        foreach ($columns as $column)
        {
            $tableHead .= '<th>' . $names[$column] . '</th>';
        }
        $tableHead .= '</tr></thead>';
        return $tableHead;
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
        $modifier = 1.5;
        $tableBody = '<tbody>';

        foreach ($rows as $row)
        {
            $tableBody .= '<tr>';
            
            if ($type === "rooms")
            {
                $tableBody .= "<td>{$this->roomNames[$row]}</td>";
            }
            if ($type === "teachers")
            {
                $tableBody .= "<td>{$this->teacherNames[$row]}</td>";
            }

            foreach ($columns as $column)
            {
                if (isset($this->consumption[$type][$column][$row]))
                {
                    $consumptionTime = $this->consumption[$type][$column][$row]  * $modifier;
                    $tableBody .= '<td>' . str_replace(".", ",", $consumptionTime) . '</td>';
                }
                else
                {
                    $tableBody .= '<td/>';
                }
            }
            $tableBody .= '</tr>';
        }
        $tableBody .= '</tbody>';
        return $tableBody;
    }

    /**
     * Method to get the longname of teachers
     *
     * @param   array   $teachers  All teachers to get the longname for.
     * @param   object  $schedule  A schedule object
     *
     * @return array Teachers with longnames
     */
    public function getNameArray($resourceName, $resources, $properties, $seperator = ' ')
    {
        $names = array();
        foreach ($resources as $resource)
        {
            $initial = true;
            $names[$resource] = '';
            foreach ($properties as $property)
            {
                if (empty($this->schedule->{$resourceName}->$resource->{$property}))
                {
                    continue;
                }
                if (!$initial)
                {
                    $names[$resource] .= $seperator;
                }
                $names[$resource] .= $this->schedule->{$resourceName}->$resource->{$property};
                $initial = false;
            }
            if (empty($names[$resource]))
            {
                unset($names[$resource]);
            }
        }
        return $names;
    }
}
