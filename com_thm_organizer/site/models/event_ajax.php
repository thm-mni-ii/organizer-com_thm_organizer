<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        event_ajax model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_ADMINISTRATOR . '/components/com_thm_organizer/models/schedule.php';
require_once JPATH_COMPONENT . '/helper/event.php';

/**
 * Retrieves data about conflicting events and lessons against an event to be saved
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelEvent_Ajax extends JModelLegacy
{
    /**
     * @var int the id of an existing event
     */
    private $_eventID;

    /**
     * @var string the startdate given by the request formatted d%.m%.Y%
     *      the corresponding value in the db is formatted Y%-m%-d%
     */
    private $_startdate;

    /**
     * @var string the enddate given by the request formatted d%.m%.Y%
     *      the corresponding value in the db is formatted Y%-m%-d%
     */
    private $_enddate;

    /**
     * @var string the starttime given by the request formatted hh:mm
     *      the corresponding value in the db is formatted hh:mm:ss
     */
    private $_starttime;

    /**
     * @var string the endtime given by the request formatted hh:mm
     *      the corresponding value in the db is formatted hh:mm:ss
     */
    private $_endtime;

    /**
     * @var int event recurrance type<br />
     *  '0': block event starts on startdate at starttime ends on enddate at
     *       endtime
     *  '1': daily event repeats every day between startdate and enddate between
     *       starttime and endtime
     *  othervalues are currently unused
     */
    private $_rec_type;

    /**
     * @var array resolving room ids to their respective names
     */
    private $_rooms;

    /**
     * @var string the room ids formatted for use in sql queries
     */
    private $_roomKeys;

    /**
     * Array containing the untis room keys for the resources requested
     *
     * @var array
     */
    private $_roomUntisKeys;

    /**
     * @var array resolving teacher ids to their respective unique names,
     *      typically the last name
     */
    private $_teachers;

    /**
     * @var string the teacher ids formatted for use in sql queries
     */
    private $_teacherKeys;

    /**
     * Array containing the untis teacher keys for the resources requested
     *
     * @var array
     */
    private $_teacherUntisKeys;

    /**
     * @var array resolving group ids to their names
     */
    private $_groups;

    /**
     * @var string the group ids formatted for use in sql queries
     */
    private $_groupKeys;

    /**
     * @var string the event category ids which reserve resources formatted for
     *      use in sql queries
     */
    private $_reservingCatIDs;

    /**
     * @var array the schedules which are currently active
     */
    private $_activeSchedules;

    /**
     * Holds conflicting lessons data
     *
     * @var array
     */
    private $_lessons;

    /**
     * Holds conflicting events data
     *
     * @var array
     */
    private $_events;

    /**
     * loads data into the object from an array or user request
     *
     * @return array array of conflicting events and lessons
     */
    public function getConflicts()
    {
        $input = JFactory::getApplication()->input;
        $conflicts = array();

        // If the event isn't associated with a reserving category it cannot reserve
        $categoryID = $input->getInt('category', 0);
        $this->_reservingCatIDs = $this->getReservingCatIDs();
        if (strpos($this->_reservingCatIDs, "'$categoryID'") === false)
        {
            return $conflicts;
        }

        // If the event isn't associated with a resource it cannot reserve
        $resourcesSet = $this->setResourceVariables();
        if (!$resourcesSet)
        {
            return $conflicts;
        }

        $this->_eventID = $input->getInt('eventID', 0);
        $this->_startdate = date('Y-m-d', strtotime($input->getString('startdate', '')));
        $this->_enddate = date('Y-m-d', strtotime($input->getString('enddate', $this->_startdate)));
        $this->_starttime = $input->getString('starttime', '00:00');
        $this->_endtime = $input->getString('endtime', '00:00');
        $this->_rec_type = $input->getInt('rec_type', 0);

        $this->getEvents();
        if (!empty($this->_events))
        {
            $conflicts = array_merge($conflicts, $this->_events);
        }

        $this->_activeSchedules = $this->getActiveSchedules();
        $potentialLessons = ((!empty($this->_roomKeys) OR !empty($this->_teacherKeys)) AND count($this->_activeSchedules));
        if ($potentialLessons)
        {
            $this->getLessons();
        }
        if (!empty($this->_lessons))
        {
            $conflicts = array_merge($conflicts, $this->_lessons);
        }

        return $conflicts;
    }

    /**
     * Sets the resource collection variables with data from the database
     *
     * @return  bool  true if resources have been set, otherwise false
     */
    private function setResourceVariables()
    {
        $this->_rooms = $this->getResourceData('rooms', 'longname', 'thm_organizer_rooms');
        $this->_roomKeys = empty($this->_rooms)? '' : "( '" . implode("', '", array_keys($this->_rooms)) . "' )";
        $this->_roomUntisKeys = $this->getUntisKeys('rooms', $this->_roomKeys);

        $this->_teachers = $this->getResourceData('teachers', 'surname', 'thm_organizer_teachers');
        $this->_teacherKeys = empty($this->_teachers)? '' : "( '" . implode("', '", array_keys($this->_teachers)) . "' )";
        $this->_teacherUntisKeys = $this->getUntisKeys('teachers', $this->_teacherKeys);

        $this->_groups = $this->getResourceData('groups', 'title', 'usergroups');
        $this->_groupKeys = empty($this->_groups)? '' : "( '" . implode("', '", array_keys($this->_groups)) . "' )";

        return (!empty($this->_rooms) OR !empty($this->_teachers) OR !empty($this->_groups));
    }

    /**
     * retrieves the names of resources requested in the appointment to be created
     *
     * @param   string  $resourceName  name of the resource variable
     * @param   string  $columnName    name of the db table column
     * @param   string  $tableName     name of the db table
     *
     * @return  array  array of resource ids and associated names (empty if no resources
     *                 were requested
     *
     * @throws  exception
     */
    private function getResourceData($resourceName, $columnName, $tableName)
    {
        $resourceData = array();
        if (empty($resources))
        {
            $resources = JFactory::getApplication()->input->get($resourceName, array(), 'array');
        }

        // Remove the dummy index if selected
        $dummyIndex = array_search('-1', $resources);
        if ($dummyIndex)
        {
            unset($resources[$dummyIndex]);
        }

        if (empty($resources))
        {
            return $resourceData;
        }

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("id, $columnName AS name");
        $query->from("#__$tableName");
        $requestedIDs = "( " . implode(", ", $resources) . " )";
        $query->where("id IN $requestedIDs");
        $query->order("id");
        $dbo->setQuery((string) $query);

        try
        {
            $results = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        if (!empty($results))
        {
            foreach ($results as $result)
            {
                $resourceData[$result['id']] = $result['name'];
            }
        }
        return $resourceData;
    }

    /**
     * Retrieves an array of gpuntisIDs to the requested resources
     *
     * @param   string  $table  the name of the table in which the resource is saved
     * @param   string  $idSet  a string containing the table ids under which the
     *                          requested resources are saved
     *
     * @return  array  contains the gpuntisIDs of the requested resources
     *
     * @throws  exception
     */
    private function getUntisKeys($table, $idSet)
    {
        $untisKeys = array();
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT id, gpuntisID");
        $query->from("#__thm_organizer_$table");
        $query->where("id IN $idSet");
        $dbo->setQuery((string) $query);
        
        try
        {
            $result = $dbo->loadAssocList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return $untisKeys;
        }
        
        if (count($result))
        {
            foreach ($result as $entry)
            {
                $untisKeys[$entry['id']] = substr($entry['gpuntisID'], 3);
            }
        }
        return $untisKeys;
    }

    /**
     * retrieves information about conflicting events
     *
     * @return array of event data
     */
    private function getEvents()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $select = "DISTINCT(c.id), c.title, u.name AS author, e.recurrence_type AS rec_type, ";
        $select .= "e.startdate AS startdate, ";
        $select .= "e.enddate AS enddate, ";
        $select .= "e.starttime AS starttime, ";
        $select .= "e.endtime AS endtime ";
        $query->select($select);
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->innerJoin("#__users AS u ON u.id = c.created_by");
        if ($this->_roomKeys)
        {
            $query->leftJoin("#__thm_organizer_event_rooms AS er ON e.id = er.eventID");
        }
        if ($this->_teacherKeys)
        {
            $query->leftJoin("#__thm_organizer_event_teachers AS et ON e.id = et.eventID");
        }
        if ($this->_groupKeys)
        {
            $query->leftJoin("#__thm_organizer_event_groups AS eg ON e.id = eg.eventID");
        }

        $dailyEvents = $this->getDailyEvents($query);             
        foreach ($dailyEvents as &$event)
        {
            THM_OrganizerHelperEvent::localizeEvent($event);
        }

        $blockEvents = $this->getBlockEvents($query);
        foreach ($blockEvents as &$event)
        {
            THM_OrganizerHelperEvent::localizeEvent($event);
        }

        $events = array_merge($dailyEvents, $blockEvents);

        if (!empty($events))
        {
            $this->prepareEvents($events);
        }
        $this->_events = $events;
    }

    /**
     * Adds resource restrictions to the where clause if applicable
     *
     * @return  string  a string containing the resource restriction clauses
     */
    private function getEventResourceRestriction()
    {
        $restriction = array();
        if (!empty($this->_roomKeys))
        {
            $restriction[] = "er.roomID IN {$this->_roomKeys}";
        }
        if (!empty($this->_teacherKeys))
        {
            $restriction[] = "et.teacherID IN {$this->_teacherKeys}";
        }
        if (!empty($this->_groupKeys))
        {
            $restriction[] = "eg.groupID IN {$this->_groupKeys}";
        }
        return "( " . implode(' OR ', $restriction) . " ) ";
    }

    /**
     * retrieves details to conflicting daily events
     *
     * @param   object  &$query  the query to be modified
     *
     * @return  array of event data
     */
    private function getDailyEvents(&$query)
    {
        $dbo = JFactory::getDbo();
        $query->clear('where');
        $query->where("e.categoryID IN {$this->_reservingCatIDs}");
        $query->where("e.recurrence_type = 1");
        $query->where($this->getEventResourceRestriction());
        $query->where($this->getDailyEventDateRestriction());
        if ($this->_starttime OR $this->_endtime)
        {
            $query->where($this->getDailyEventTimeRestriction());
        }
        if ($this->_eventID)
        {
            $query->where("e.id != '{$this->_eventID}'");
        }
        $dbo->setQuery((string) $query);

        try
        {
            $dailyEvents = $dbo->loadAssocList();
            return empty($dailyEvents)? array() : $dailyEvents;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * creates an sql restriction for the dates of daily events
     *
     * @return string suitable for inclusion in an sql query
     */
    private function getDailyEventDateRestriction()
    {
        $restriction = "( ";
        $restriction .= "( e.startdate <= '$this->_startdate' AND e.enddate >= '$this->_startdate' ) ";
        $restriction .= "OR ";
        $restriction .= "( e.startdate > '$this->_startdate' AND e.startdate <= '$this->_enddate' ) ";
        $restriction .= " ) ";
        return $restriction;
    }

    /**
     * creates an sql restriction for the times of daily events
     *
     * @return string suitable for inclusion in an sql query
     */
    private function getDailyEventTimeRestriction()
    {
        $restriction = "( ";
        $restriction .= "( e.starttime = '00:00:00' AND e.endtime = '00:00:00' ) OR ";
        if ($this->_starttime AND $this->_endtime)
        {
            $restriction .= "( e.starttime <= '{$this->_starttime}' AND e.endtime >= '{$this->_starttime}' ) OR ";
            $restriction .= "( e.starttime > '{$this->_starttime}' AND e.starttime <= '{$this->_endtime}' ) OR ";
            $restriction .= "( e.starttime = '00:00:00' AND e.endtime >= '{$this->_starttime}' ) OR ";
            $restriction .= "( e.starttime <= '{$this->_endtime}' AND e.endtime = '00:00:00' ) ";
        }
        elseif ($this->_starttime)
        {
            $restriction .= "( e.starttime <= '{$this->_starttime}' AND e.endtime >= '{$this->_starttime}' ) OR ";
            $restriction .= "( e.starttime = '00:00:00' AND e.endtime >= '{$this->_starttime}' ) OR ";
            $restriction .= "( e.starttime >= '{$this->_starttime}' ) ";
        }
        elseif ($this->_endtime)
        {
            $restriction .= "( e.starttime <= '{$this->_endtime}' AND e.endtime >= '{$this->_endtime}' ) OR ";
            $restriction .= "( e.starttime = '00:00:00' AND e.endtime >= '{$this->_endtime}' ) OR ";
            $restriction .= "( e.endtime <= '{$this->_endtime}' )  ";
        }
        $restriction .= ")";
        return $restriction;
    }

    /**
     * Retrieves details to conflicting block events
     *
     * @param   object  &$query  the query to be modified
     *
     * @return  array of event data
     */
    private function getBlockEvents(&$query)
    {
        $dbo = JFactory::getDbo();
        $query->clear('where');
        $query->where("e.categoryID IN {$this->_reservingCatIDs}");
        $query->where("e.recurrence_type = '0'");
        $query->where($this->getEventResourceRestriction());
        $query->where($this->getEventBlockRestriction());
        if ($this->_eventID)
        {
            $query->where("e.id != '{$this->_eventID}'");
        }
        $dbo->setQuery((string) $query);
        
        try
        {
            $blockEvents = $dbo->loadAssocList();
            return empty($blockEvents)? array() : $blockEvents;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Generates the where clause for the restriction to conflicting event blocks
     *
     * @return string
     */
    private function getEventBlockRestriction()
    {
        $flooredEventStart = strtotime("{$this->_startdate} 00:00");
        $ceilingEventStart = strtotime("{$this->_startdate} 23:59");
        $flooredEventEnd = strtotime("{$this->_enddate} 00:00");
        $newEventStart = strtotime(($this->_starttime)? "{$this->_startdate} {$this->_starttime}" : "{$this->_startdate} 00:00");
        $newEventEnd = strtotime(($this->_endtime)? "{$this->_enddate} {$this->_endtime}" : "{$this->_enddate} 23:59");
        $restriction = "( ";
        $restriction .= "(e.start <= '$newEventStart' AND e.end >= '$newEventStart' ) OR ";
        $restriction .= "(e.start > '$newEventStart' AND e.start <= '$newEventEnd' ) OR ";
        $restriction .= "(e.starttime = '00:00:00' AND e.start <= '$flooredEventStart' AND e.end >= '$flooredEventStart' ) OR ";
        $restriction .= "(e.endtime = '00:00:00' AND e.start <= '$flooredEventEnd' AND e.end >= '$flooredEventEnd' ) ";
        if (!$this->_starttime and !$this->_endtime)
        {
            $restriction .= "OR (e.start >= '$flooredEventStart' AND e.start <= '$ceilingEventStart' ) ";
            $restriction .= "OR (e.start >= '$flooredEventStart' AND e.start <= '$ceilingEventStart' ) ";
        }
        $restriction .= ")";
        return $restriction;
    }

    /**
     * reformats an array of conflicting events
     *
     * @param   array  &$events  array containing information about conflicting events
     *
     * @return  void
     */
    private function prepareEvents(&$events)
    {
        foreach ($events as $key => $event)
        {
            $times = $this->getEventTimeText($event);
            $resources = $this->getEventResources($event['id']);
            $text = JText::sprintf('COM_THM_ORGANIZER_B_EVENT', $event['title'], $event['author'], $times, $resources);
            $events[$key]['text'] = $text;
        }
    }

    /**
     * creates a formatted text explaining the run of an event
     *
     * @param   array  $event  array containing event information
     *
     * @return  string formatted text explaining the run of an event
     */
    private function getEventTimeText($event)
    {
        $useStartTime = $event['starttime'] == "00:00"? false : true;
        $useEndTime = $event['endtime'] == "00:00"? false : true;
        $useTimes = ($useStartTime OR $useEndTime);
        $singleDay = ($event['enddate'] == "00.00.0000" OR $event['startdate'] == $event['enddate']);

        if ($singleDay)
        {
            return $this->getSingleDayText($event);
        }

        if ($event['rec_type'] == 0 AND $useTimes)
        {
            return $this->getBlockText($event);
        }

        if ($event['rec_type'] == 1 AND $useTimes)
        {
            return $this->getDailyText($event);
        }

        return JText::sprintf('COM_THM_ORGANIZER_B_MULTIPLENOTIME', $event['startdate'], $event['enddate']);
    }

    /**
     * Gets a formatted text for events that take place on a single day
     *
     * @param   array  &$event  the event array
     *
     * @return  string  formatted text for date/time output
     */
    private function getSingleDayText(&$event)
    {
        $useStartTime = $event['starttime'] == "00:00"? false : true;
        $useEndTime = $event['endtime'] == "00:00"? false : true;
        if ($useStartTime AND $useEndTime)
        {
            return JText::sprintf('COM_THM_ORGANIZER_B_SINGLESTARTEND', $event['startdate'], $event['starttime'], $event['endtime']);
        }

        if ($useStartTime)
        {
            return JText::sprintf('COM_THM_ORGANIZER_B_SINGLESTART', $event['startdate'], $event['starttime']);
        }

        if ($useEndTime)
        {
            return JText::sprintf('COM_THM_ORGANIZER_B_SINGLEEND', $event['startdate'], $event['endtime']);
        }

        return JText::sprintf('COM_THM_ORGANIZER_B_SINGLE', $event['startdate']);
    }

    /**
     * Gets a formatted text for block events that take place on a multiple days and use times
     *
     * @param   array  &$event  the event array
     *
     * @return  string  formatted text for date/time output
     */
    private function getBlockText(&$event)
    {
        $useStartTime = $event['starttime'] == "00:00"? false : true;
        $useEndTime = $event['endtime'] == "00:00"? false : true;
        if ($useStartTime AND $useEndTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_B_BLOCKSTARTEND',
                $event['startdate'],
                $event['starttime'],
                $event['endtime'],
                $event['enddate']
            );
        }

        if ($useStartTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_B_BLOCKSTART',
                $event['startdate'],
                $event['starttime'],
                $event['enddate']
            );
        }

        if ($useEndTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_B_BLOCKEND',
                $event['startdate'],
                $event['endtime'],
                $event['enddate']
            );
        }

        return '';
    }

    /**
     * Gets a formatted text for daily events that take place on a multiple days and use times
     *
     * @param   array  &$event  the event array
     *
     * @return  string  formatted text for date/time output
     */
    private function getDailyText(&$event)
    {
        $useStartTime = $event['starttime'] == "00:00"? false : true;
        $useEndTime = $event['endtime'] == "00:00"? false : true;
        if ($useStartTime AND $useEndTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_B_DAILYSTARTEND',
                $event['startdate'],
                $event['enddate'],
                $event['starttime'],
                $event['endtime']
            );
        }

        if ($useStartTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_B_DAILYSTART',
                $event['startdate'],
                $event['enddate'],
                $event['starttime']
            );
        }

        if ($useEndTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_B_DAILYEND',
                $event['startdate'],
                $event['enddate'],
                $event['endtime']
            );
        }

        return '';
    }

    /**
     * creates a preformatted text containing the names of resources which are
     * in conflict with those of the event to be created
     *
     * @param   int  $eventID  the ID of the event which is in conflict with the
     *                         one to be created
     *
     * @return  string  formatted text containing resource names
     */
    private function getEventResources($eventID)
    {
        $resources = array();
        $teachers = $this->getResourceNames("#__thm_organizer_event_teachers", "eventID", "teacherID", $eventID, $this->_teacherKeys,
         $this->_teachers
        );
        $rooms = $this->getResourceNames("#__thm_organizer_event_rooms", "eventID", "roomID", $eventID, $this->_roomKeys, $this->_rooms);
        $groups = $this->getResourceNames("#__thm_organizer_event_groups", "eventID",  "groupID", $eventID, $this->_groupKeys, $this->_groups);
        if (count($teachers))
        {
            $resources[] = implode(", ", $teachers);
        }
        if (count($rooms))
        {
            $resources[] = implode(", ", $rooms);
        }
        if (count($groups))
        {
            $resources[] = implode(", ", $groups);
        }
        return implode(", ", $resources);
    }

    /**
     * Retrieves the subset of resource ids for a particular resource which are
     * are the same as those of the event to be created
     *
     * @param   string  $tableName       the name of the table which stores the
     *                                   association of event to resource
     * @param   string  $keyColumn       the name of the column holding the key value
     * @param   string  $resourceColumn  the name of the column which aliases the
     *                                   resource
     * @param   int     $eventID         the id of the event
     * @param   string  $keys            a prepared list of resource keys to be
     *                                   inserted into the sql statement
     * @param   array   $namesArray      the array associating ids to names
     *
     * @return  array  list of associated resource ids
     */
    private function getResourceNames($tableName, $keyColumn, $resourceColumn, $eventID, $keys, $namesArray)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT($resourceColumn)");
        $query->from($tableName);
        $query->where("$keyColumn = '$eventID'");
        $query->where("$resourceColumn IN $keys");
        $dbo->setQuery((string) $query);
        
        try
        {
            $IDsArray = $dbo->loadColumn();
            if (count($IDsArray))
            {
                $resources = array();
                foreach ($IDsArray as $resourceID)
                {
                    $resources[] = $namesArray[$resourceID];
                }
                return $resources;
            }
            else
            {
                return array();
            }
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }

    }

    /**
     * Retrieves a sql formatted list of active schedule ids whos dates overlap
     * those of the event
     *
     * @return array  the actual schedules
     */
    private function getActiveSchedules()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("schedule");
        $query->from("#__thm_organizer_schedules");
        $query->where("active = 1");
        $query->where("startdate <= '{$this->_startdate}'");
        $query->where("enddate >= '{$this->_enddate}'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $results = $dbo->loadAssocList();
            if (count($results))
            {
                $scheduleModel = JModel::getInstance('Schedule', 'THM_OrganizerModel');
                foreach ($results as $key => $value)
                {
                    $schedule = json_decode($value['schedule']);
                    $scheduleModel->sanitizeSchedule($schedule);
                    $results[$key] = $schedule;
                }
                return $results;
            }
            else
            {
                return array();
            }
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * retrieves an array of lessons which conflict with the event to be saved
     *
     * @return array $lessons empty if no conflicts were found
     */
    private function getLessons()
    {
        $this->_lessons = array();
        foreach ($this->_activeSchedules as $schedule)
        {
            if (strtotime($this->_startdate) == strtotime($this->_enddate))
            {
                $this->getDailyLessons($schedule, $this->_startdate, date('w', strtotime($this->_startdate)), $this->_starttime, $this->_endtime);
            }
            else
            {
                $indexDT = strtotime($this->_startdate);
                for ($indexDT; $indexDT <= strtotime($this->_enddate); $indexDT = strtotime('+1 day', $indexDT))
                {
                    switch ($this->_rec_type)
                    {
                        case '0';
                            if ($indexDT == strtotime($this->_startdate))
                            {
                                $this->getDailyLessons($schedule, date('Y-m-d', $indexDT), date('w', $indexDT), $this->_starttime);
                            }
                            elseif ($indexDT < strtotime($this->_enddate))
                            {
                                $this->getDailyLessons($schedule, date('Y-m-d', $indexDT), date('w', $indexDT));
                            }
                            else
                            {
                                $this->getDailyLessons($schedule, date('Y-m-d', $indexDT), date('w', $indexDT), $this->_endtime);
                            }
                            break;
                        case '1';
                            $this->getDailyLessons($schedule, date('Y-m-d', $indexDT), date('w', $indexDT), $this->_starttime, $this->_endtime);
                            break;
                    }
                }
            }
        }
    }

    /**
     * Retrieves an array of lessons which conflict with the event to be saved
     * for one day
     *
     * @param   object  &$schedule  the current schedule being iterated
     * @param   string  $date       the date being iterated
     * @param   int     $dow        the numerical date of the week for the date
     *                              being iterated
     * @param   string  $starttime  the time at which the event starts on the
     *                              iterated date
     * @param   string  $endtime    the time at which the event ends on the
     *                              iterated date
     *
     * @return   void  the lessons which collide with the event to be saved are place in the lessons object variable
     */
    private function getDailyLessons(&$schedule, $date, $dow, $starttime = '', $endtime = '')
    {
        $affectedPeriods = $this->getPeriodsFromSchedule($schedule->periods, $dow, $starttime, $endtime);
        if (empty($affectedPeriods))
        {
            return;
        }
        else
        {
            foreach ($schedule->calendar->$date AS $periodNumber => $lessons)
            {
                if (in_array($periodNumber, $affectedPeriods))
                {
                    foreach ($lessons AS $lessonID => $rooms)
                    {
                        foreach ($rooms AS $roomID => $delta)
                        {
                            if (in_array($roomID, $this->_roomUntisKeys))
                            {
                                if (!isset($this->_lessons[$lessonID]))
                                {
                                    $this->_lessons[$lessonID] = array();
                                    $this->_lessons[$lessonID]['name'] = $schedule->lessons->$lessonID->name;
                                    $this->_lessons[$lessonID]['date'] = strftime('%d.%m.%Y', strtotime($date));
                                    $this->_lessons[$lessonID]['period'] = $periodNumber;
                                }
                                if (!isset($this->_lessons[$lessonID]['resources']))
                                {
                                    $this->_lessons[$lessonID]['resources'] = array();
                                }
                                if (!in_array($roomID, $this->_lessons[$lessonID]['resources']))
                                {
                                    $this->_lessons[$lessonID]['resources'][] = $roomID;
                                }
                            }
                        }
                        foreach ($schedule->lessons->$lessonID->teachers AS $teacherID => $delta)
                        {
                            if (in_array($teacherID, $this->_teacherUntisKeys))
                            {
                                if (!isset($this->_lessons[$lessonID]))
                                {
                                    $this->_lessons[$lessonID] = array();
                                    $this->_lessons[$lessonID]['name'] = $schedule->lessons->$lessonID->name;
                                    $this->_lessons[$lessonID]['date'] = strftime('%d.%m.%Y', strtotime($date));
                                    $this->_lessons[$lessonID]['period'] = $periodNumber;
                                }
                                if (!isset($this->_lessons[$lessonID]['resources']))
                                {
                                    $this->_lessons[$lessonID]['resources'] = array();
                                }
                                if (!in_array($schedule->teachers->$teacherID->surname, $this->_lessons[$lessonID]['resources']))
                                {
                                    $this->_lessons[$lessonID]['resources'][] = $schedule->teachers->$teacherID->surname;
                                }
                            }
                        }
                        if (isset($this->_lessons[$lessonID]))
                        {
                            $name = $this->_lessons[$lessonID]['name'];
                            if (!isset($this->_lessons[$lessonID]['teachers']))
                            {
                                $this->_lessons[$lessonID]['teachers'] = array();
                            }
                            foreach ($schedule->lessons->$lessonID->teachers AS $teacherID => $delta)
                            {
                                if (!in_array($schedule->teachers->$teacherID->surname, $this->_lessons[$lessonID]['teachers']))
                                {
                                    $this->_lessons[$lessonID]['teachers'][] = $schedule->teachers->$teacherID->surname;
                                }
                            }
                            $authors = implode(", ", $this->_lessons[$lessonID]['teachers']);
                            $day = $this->_lessons[$lessonID]['date'];
                            $block = $this->_lessons[$lessonID]['period'];
                            $resources = implode(", ", $this->_lessons[$lessonID]['resources']);
                            $text = JText::sprintf('COM_THM_ORGANIZER_B_LESSON', $name, $authors, $day, $block, $resources);
                            $this->_lessons[$lessonID]['text'] = $text;
                        }
                    }
                }
            }
        }
    }

    /**
     * Retrieves the affected period numbers for a single date
     *
     * @param   object  &$schedulePeriods  the periods object from the schedule
     * @param   int     $dow               the numerical day of the week
     * @param   string  $starttime         the start time on the day in question
     * @param   string  $endtime           the end time on the day in question
     *
     * @return  array  contains the numerical values of the affected periods
     */
    private function getPeriodsFromSchedule(&$schedulePeriods, $dow, $starttime = '', $endtime = '')
    {
        $periods = array();
        foreach ($schedulePeriods AS $period)
        {
            $periodStart = substr($period->starttime, 0, 2) . ':' . substr($period->starttime, 2);
            $periodEnd = substr($period->endtime, 0, 2) . ':' . substr($period->endtime, 2);
            if ($period->day != $dow)
            {
                continue;
            }
            if (!empty($starttime) AND !empty($endtime))
            {
                if (($starttime <= $periodStart AND $endtime >= $periodStart)
                 OR ($starttime >= $periodStart AND $starttime <= $periodEnd))
                {
                    $periods[$period->period] = $period->period;
                }
            }
            elseif (!empty($starttime))
            {
                if ($starttime <= $periodEnd)
                {
                    $periods[$period->period] = $period->period;
                }
            }
            elseif (!empty($endtime))
            {
                if ($endtime >= $periodStart)
                {
                    $periods[$period->period] = $period->period;
                }
            }
            else
            {
                $periods[$period->period] = $period->period;
            }
        }
        return $periods;
    }
}
