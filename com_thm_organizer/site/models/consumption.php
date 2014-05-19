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
    /**
     * Gets all schedules in the database
     *
     * @return array An array with the schedules
     */
    public function getSchedulesFromDB()
    {
        $dbo = $this->_db;
        $query = $dbo->getQuery(true);

        $select = "id, departmentname, semestername, active, description, ";
        $select .= "creationdate, ";
        $select .= "creationtime, ";
        $select .= "startdate, ";
        $select .= "enddate ";
        $query->select($select);
        $query->from("#__thm_organizer_schedules");
        $query->order('departmentname, semestername');



        $dbo->setQuery((string) $query);
        try 
        {
            $result = $dbo->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_SCHEDULES"), 500);
        }

        if(!empty($result))
        {
            foreach($result as $schedule)
            {
                $schedule->creationdate = date('d.m.Y' ,strtotime($schedule->creationdate));
                $schedule->startdate = date('d.m.Y' ,strtotime($schedule->startdate));
                $schedule->enddate = date('d.m.Y' ,strtotime($schedule->enddate));
                $schedule->creationtime = date('H:i' ,strtotime($schedule->creationtime));
            }
        }

        return $result;
    }
    
    /**
     * Method to get a schedule by its id from the database
     *
     * @param   integer  $scheduleID  A schedule database id
     *
     * @return  object   An schedule object
     */
    public function getScheduleJSONFromDB($scheduleID)
    {
        $result = null;
        if (! is_int($scheduleID))
        {
            return new stdClass;
        }
        
        $dbo = $this->_db;
        $query = $dbo->getQuery(true);
        
        $select = "schedule";
        $query->select($select);
        $query->from("#__thm_organizer_schedules");
        $query->where("id=" . $scheduleID);
        
        try
        {
            $dbo->setQuery((string) $query);
            $result = $dbo->loadObject();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_SCHEDULE"), 500);
        }
        
        return $result;
    }
    
    /**
     * Method to get a room consumtion from a schedule
     *
     * @param   string  $schedule  A schedule object
     *
     * @return array An consumption array
     */
    public function getConsumptionFromSchedule($schedule)
    {
        $consumption = new stdClass;
        $consumption->rooms = new stdClass;
        $consumption->teachers = new stdClass;
        
        $scheduleCalendar = $schedule->calendar;
        $scheduleLessons = $schedule->lessons;
        $schedulePools = $schedule->pools;
        
        // To save memory
        unset($schedule->calendar); 
        
        // To save memory
        unset($schedule->subjects); 
        
        // To save memory
        unset($schedule->lessons); 
        
        foreach ($scheduleCalendar as $day)
        {
            if (is_object($day))
            {
                foreach ($day as $block)
                {
                    foreach ($block as $lessonKey => $lessonValue)
                    {
                        foreach ($lessonValue as $roomKey => $roomValue)
                        {
                            if ($roomKey !== "delta" && $roomValue !== "removed")
                            {
                                $lessonPools = $scheduleLessons->{$lessonKey}->pools;
                                $lessonTeachers = $scheduleLessons->{$lessonKey}->teachers;
                                
                                foreach ($lessonPools as $lessonPoolKey => $lessonPoolValue)
                                {
                                    if ($lessonPoolValue !== "removed")
                                    {
                                        $pool = $schedulePools->{$lessonPoolKey};
                                        $degree = $pool->degree;
                                        
                                        if (! isset($consumption->rooms->{$degree}))
                                        {
                                            $consumption->rooms->{$degree} = new stdClass;
                                        }
                                        
                                        if (! isset($consumption->rooms->{$degree}->{$roomKey}))
                                        {
                                            $consumption->rooms->{$degree}->{$roomKey} = 1;
                                        }
                                        else
                                        {
                                            $consumption->rooms->{$degree}->{$roomKey} ++;
                                        }
                                        
                                        foreach ($lessonTeachers as $lessonTeacherKey => $lessonTeacherValue)
                                        {
                                            if ($lessonTeacherValue !== "removed")
                                            {
                                                if (! isset($consumption->teachers->{$degree}))
                                                {
                                                    $consumption->teachers->{$degree} = new stdClass;
                                                }
                                                
                                                if (! isset($consumption->teachers->{$degree}->{$lessonTeacherKey}))
                                                {
                                                    $consumption->teachers->{$degree}->{$lessonTeacherKey} = 1;
                                                }
                                                else
                                                {
                                                    $consumption->teachers->{$degree}->{$lessonTeacherKey} ++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else
            {
            	
            }
        }
        
        return $consumption;
    }
    
    
    /**
     * Method to get a schedule table for consumptions
     * 
     * @param   array   $consumptionColumns  The column headlines
     * @param   array   $consumptionRows     The row headlines
     * @param   objet   $consumptions        The cell data
     * @param   string  $type                Either teachers or rooms
     * @param   string  $schedule            The actual schedule
     * 
     * @return HTML_Table  A html table with the consumptions
     */
    public function getConsumptionTable($consumptionColumns, $consumptionRows, $consumptions, $type, $schedule = null)
    {
        $consumptionTable = '<table id="thm_organizer_' . $type . '_consumption_table" ' .
                            'class="thm_organizer_consumption_table_class"><thead><tr><td />';

        $modifier = 1.5;
        
        $consumption = $consumptions->{$type};
        
        $degreeLongnames = $this->getDegreesLongname($consumptionColumns, $schedule);
        
        foreach ($consumptionColumns as $column)
        {
            $consumptionTable .= '<th>' . $degreeLongnames[$column] . '</th>';
        }
        
        $consumptionTable .= '</tr></thead><tbody>';
        
        foreach ($consumptionRows as $key => $value)
        {
            $consumptionTable .= '<tr>';
            
            if ($type === "rooms")
            {
                $roomLongnames = $this->getRoomsLongname(array_keys($consumptionRows), $schedule);
                $roonLongname = $roomLongnames[$key];
                $consumptionTable .= '<td>' . $roonLongname . '</td>';
            }
            elseif ($type === "teachers")
            {
                $teacherLongnames = $this->getTeachersLongname(array_keys($consumptionRows), $schedule);
                $teacherLongname = $teacherLongnames[$key];
                $consumptionTable .= '<td>' . $teacherLongname . '</td>';
            }
            else
            {
                $consumptionTable .= '<td>' . $key . '</td>';
            }
            foreach ($consumptionColumns as $column)
            {
                if (isset($consumption->{$column}->{$key}))
                {
                    $consumptionTime = $consumption->{$column}->{$key};
                    $consumptionTime = $consumptionTime * $modifier;
                    $consumptionTable .= '<td>' . str_replace(".", ",", $consumptionTime) . '</td>';
                }
                else
                {
                    $consumptionTable .= '<td/>';
                }
            }
            $consumptionTable .= '</tr>';
        }
        $consumptionTable .= '</tbody></table>';
        return $consumptionTable;
    }
    
    /**
     * Method to get the longname of degrees
     * 
     * @param   array   $degrees   All degrees to get the longname for.
     * @param   object  $schedule  A schedule object
     * 
     * @return array Degrees with longnames
     */
    public function getDegreesLongname($degrees, $schedule)
    {
        $return = array();
        $scheduleDegrees = $schedule->degrees;
        foreach ($degrees as $degree)
        {
            $return[$degree] = $scheduleDegrees->{$degree}->name;
        }
        
        return $return;
    }

    /**
     * Method to get the longname of rooms
     *
     * @param   array   $rooms     All rooms to get the longname for.
     * @param   object  $schedule  A schedule object
     *
     * @return array Rooms with longnames
     */
    public function getRoomsLongname($rooms, $schedule)
    {
        $return = array();
        $scheduleRooms = $schedule->rooms;
        foreach ($rooms as $room)
        {
            $return[$room] = $scheduleRooms->{$room}->longname;
        }
        
        return $return;
    }

    /**
     * Method to get the longname of teachers
     *
     * @param   array   $teachers  All teachers to get the longname for.
     * @param   object  $schedule  A schedule object
     *
     * @return array Teachers with longnames
     */
    public function getTeachersLongname($teachers, $schedule)
    {
        $return = array();
        $scheduleTeachers = $schedule->teachers;
        
        foreach ($teachers as $teacher)
        {
            $return[$teacher] = $scheduleTeachers->{$teacher}->surname . ", " . $scheduleTeachers->{$teacher}->forename;
        }
        
        return $return;
    }
}
