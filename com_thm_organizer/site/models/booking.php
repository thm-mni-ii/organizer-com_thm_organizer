<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        reservation ajax response model
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     0.0.1
 */
defined('_JEXEC') or die;
jimport( 'joomla.application.component.model' );
class thm_organizerModelbooking extends JModel
{
    /**
     *
     * @var array $data an array simulating http request data for testing or for
     *      use in the scheduler view
     */
    private $data;

    /**
     * @var int the id of an existing event
     */
    private $eventID;

    /**
     * @var int the id of the event category
     */
    private $categoryID;

    /**
     * @var string the startdate given by the request formatted d%.m%.Y%
     *      the corresponding value in the db is formatted Y%-m%-d%
     */
    private $startdate;

    /**
     * @var string the enddate given by the request formatted d%.m%.Y%
     *      the corresponding value in the db is formatted Y%-m%-d%
     */
    private $enddate;

    /**
     * @var string the starttime given by the request formatted hh:mm
     *      the corresponding value in the db is formatted hh:mm:ss
     */
    private $starttime;

    /**
     * @var string the endtime given by the request formatted hh:mm
     *      the corresponding value in the db is formatted hh:mm:ss
     */
    private $endtime;

    /**
     * @var int event recurrance type<br />
     *  '0': block event starts on startdate at starttime ends on enddate at
     *       endtime
     *  '1': daily event repeats every day between startdate and enddate between
     *       starttime and endtime
     *  othervalues are currently unused
     */
    private $rec_type;

    /**
     * @var array resolving room ids to their respective names
     */
    private $rooms;

    /**
     * @var string the room ids formatted for use in sql queries
     */
    private $roomKeys;

    /**
     * @var array resolving teacher ids to their respective unique names,
     *      typically the last name
     */
    private $teachers;

    /**
     * @var string the teacher ids formatted for use in sql queries
     */
    private $teacherKeys;

    /**
     * @var array resolving group ids to their names
     */
    private $groups;

    /**
     * @var string the group ids formatted for use in sql queries
     */
    private $groupKeys;

    /**
     * @var array ids of reserving categories
     */
    private $reservingCats;

    /**
     * @var string the event category ids which reserve resources formatted for
     *      use in sql queries
     */
    private $reservingCatIDs;

    /**
     * @var int number of whole days affected by the event
     */
    private $numberOfDays;

    /**
     * @var string the day of the week numbers upon which the event occurs,
     *      blank if the event spans more than 6 days
     */
    private $dayNumbers;

    /**
     * @var array containing details about the conflicting events and/or lessons
     */
    public $conflicts;

    /**
     * @var string formatted list of active schedule ids for use in sql queries
     */
    private $activeSchedules;

    /**
     * prepareData
     *
     * loads data into the object from an array or user request
     *
     * @param array $data optional mimics data from user request for testing and
     *        use in the scheduler view
     */
    public function prepareData($data = null)
    {
        $this->conflicts = array();
        $this->conflictingEvents = array();
        $this->conflictingLessons = array();
        if(isset($data))
        {
            $this->data = $data;
            $this->eventID = $data['id'];
            $this->categoryID = $data['category'];
            $this->startdate = $data['startdate'];
            $this->enddate = ($data['enddate'] != "")? $data['enddate'] : $data['startdate'];
            $this->starttime = ($data['starttime'] != "")? $data['starttime'] : "";
            $this->endtime = ($data['endtime'] != "")? $data['endtime'] : "";
            $this->rec_type = $data['rec_type'];
            $this->rooms = $this->getResourceData('rooms', 'name', '#__thm_organizer_rooms');
            $this->roomKeys = (count($this->rooms))?
                    "( '".implode("', '", array_keys($this->rooms))."' )" : "";
            $this->teachers = $this->getResourceData('teachers', 'name', '#__thm_organizer_teachers');
            $this->teacherKeys = (count($this->teachers))?
                    "( '".implode("', '", array_keys($this->teachers))."' )" : "";
            $this->groups = $this->getResourceData('groups', 'title', '#__usergroups');
            $this->groupKeys = (count($this->groups))?
                    "( '".implode("', '", array_keys($this->groups))."' )" : "";
        }
        else
        {
            $this->eventID = JRequest::getInt('eventID');
            $this->categoryID = JRequest::getInt('category');
            $this->startdate = JRequest::getString('startdate');
            $this->enddate = (JRequest::getString('enddate') != '')?
                JRequest::getString('enddate') : $this->startdate;
            $this->starttime = JRequest::getString('starttime');
            $this->endtime = JRequest::getString('endtime');
            $this->rec_type = JRequest::getInt('rec_type');
            $this->rooms = $this->getResourceData('rooms', 'name', '#__thm_organizer_rooms');
            $this->roomKeys = (count($this->rooms))?
                    "( '".implode("', '", array_keys($this->rooms))."' )" : "";
            $this->teachers = $this->getResourceData('teachers', 'name', '#__thm_organizer_teachers');
            $this->teacherKeys = (count($this->teachers))?
                    "( '".implode("', '", array_keys($this->teachers))."' )" : "";
            $this->groups = $this->getResourceData('groups', 'title', '#__usergroups');
            $this->groupKeys = (count($this->groups))?
                    "( '".implode("', '", array_keys($this->groups))."' )" : "";
        }
        $this->setReservingCatInfo();
        $this->setDayInfo();
        $this->setActiveSchedules();
        if(($this->rooms or $this->teachers or $this->groups) and in_array($this->categoryID, $this->reservingCats))
        {
            $events = $this->getEvents();
            $lessons = $this->getLessons();
            if(isset($events) and isset($lessons)) $this->conflicts = array_merge($events, $lessons);
            else if(isset($events)) $this->conflicts = $events;
            else if(isset($lessons)) $this->conflicts = $lessons;
        }
    }

    /**
     * getResourceData
     *
     * retrieves the names of resources requested in the appointment to be created
     *
     * @param string $resourceName name of the resource variable
     * @param string $columnName name of the db table column
     * @param string $tableName name of the db table
     * @return array array of resource ids and associated names (empty if no resources
     *               were requested
     */
    private function getResourceData($resourceName, $columnName, $tableName)
    {
        $resourceData = array();
        if(isset($this->data))
            $$resourceName = (isset($this->data[$resourceName]))? $this->data[$resourceName] : array();
        else $$resourceName = (isset($_REQUEST[$resourceName]))? explode(",", $_REQUEST[$resourceName]) : array();
        $dummyIndex = array_search('-1', $$resourceName);
        if($dummyIndex)unset($$resourceName[$dummyIndex]);
        if(count($$resourceName))
        {
            $dbo = JFactory::getDbo();
            $query = $dbo->getQuery(true);
            $query->select("id, $columnName AS name");
            $query->from("$tableName");
            $requestedIDs = "( '".implode("', '", $$resourceName)."' )";
            $query->where("id IN $requestedIDs");
            $query->order("id");
            $dbo->setQuery((string)$query);
            $results = $dbo->loadAssocList();
            if(count($results))
                foreach($results as $result)
                    $resourceData[$result['id']] = $result['name'];
        }
        return $resourceData;
    }

    /**
     * getreservingCatIDs
     *
     * retrieves the ids of event categories which reserve resources and formats
     * them in a string suitable for sql
     *
     * @return string reserving event category ids
     */
    private function setReservingCatInfo()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__thm_organizer_categories");
        $query->where("reservesobjects = '1'");
        $dbo->setQuery((string)$query);
        $result = $dbo->loadResultArray();
        if(count($result))
        {
            $this->reservingCats = $result;
            $this->reservingCatIDs = "( '".implode("', '", $this->reservingCats)."' )";
        }
        else
        {
            $this->reservingCats = array();
            $this->reservingCatIDs = "";
        }
    }

    /**
     * getDayNumbers
     *
     * retrieves the numbers which coincide with the weekdays between the start-
     * and enddates. these numbers are then formed into a string usable in a
     * sql query. empty if the event is larger than 6 days.
     *
     * @return string
     */
    private function setDayInfo()
    {
        $startdt = strtotime($this->dbDateFormat($this->startdate));
        $enddt = strtotime($this->dbDateFormat($this->enddate));
        $diff = $enddt - $startdt;
        $numberOfWholeDays = round($diff / 86400) + 1;
        $this->numberOfDays = $numberOfWholeDays;
        if($numberOfWholeDays >= 7) $dayNumbers = "";
        else
        {
            $startDate = getdate($startdt);
            $DoW = $startDate['wday'];
            $dayNumbers = array();
            for($i = 0; $i < $numberOfWholeDays; $i++)
            {
                $DoW = $DoW % 7;
                $dayNumbers[] = $DoW;
                $DoW++;
            }
            $dayNumbers = "( '".implode("', '", $dayNumbers)."' )";
        }
        $this->dayNumbers = $dayNumbers;
    }

    /**
     * getEvents
     *
     * retrieves information about conflicting events
     *
     * @return array of event data
     */
    private function getEvents()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $this->prepEventQuery($query);
        $dailyEvents = $this->getDailyEvents($query);
        $blockEvents = $this->getBlockEvents($query);

        $events = array();
        if(isset($dailyEvents) and isset($blockEvents))
            $events = array_merge($dailyEvents, $blockEvents);
        else if(isset($dailyEvents)) $events = $dailyEvents;
        else if(isset($blockEvents)) $events = $blockEvents;
        if(count($events)) $this->prepareEvents($events);
        return $events;
    }

    /**
     * prepEventQuery
     *
     * sets the select and from clauses of the query
     *
     * @param JDatabaseQuery $query
     */
    private function prepEventQuery(&$query)
    {
        $select = "DISTINCT(c.id), c.title, u.name AS author, e.recurrence_type AS rec_type, ";
        $select .= "DATE_FORMAT(e.startdate, '%d.%m.%Y') AS startdate, ";
        $select .= "DATE_FORMAT(e.enddate, '%d.%m.%Y') AS enddate, ";
        $select .= "SUBSTR(e.starttime, 1, 5) AS starttime, ";
        $select .= "SUBSTR(e.endtime, 1, 5) AS endtime ";
        $query->select($select);
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->innerJoin("#__users AS u ON u.id = c.created_by");
        if($this->roomKeys)
                $query->leftJoin("#__thm_organizer_event_rooms AS er ON e.id = er.eventID");
        if($this->teacherKeys)
                $query->leftJoin("#__thm_organizer_event_teachers AS et ON e.id = et.eventID");
        if($this->groupKeys)
                $query->leftJoin("#__thm_organizer_event_groups AS eg ON e.id = eg.eventID");
    }

    /**
     * prepEventWhere
     *
     * clears the previous where conditions and adds conditions which are used
     * in the where clauses of both event types
     *
     * @param JDatabaseQuery $query
     */
    private function prepEventWhere(&$query)
    {
        $query->clear('where');
        $query->where("e.categoryID IN {$this->reservingCatIDs}");
        $this->addEventResourceRestriction($query);
    }

    /**
     * addEventResourceRestriction
     *
     * adds resource restrictions to the where clause if applicable
     *
     * @param JDatabaseQuery $query
     */
    private function addEventResourceRestriction(&$query)
    {
        if($this->roomKeys or $this->teacherKeys or $this->groupKeys)
        {
            $restriction = "( ";
            if($this->roomKeys)$restriction .= "er.roomID IN {$this->roomKeys} ";
            if($this->teacherKeys)
            {
                if($this->roomKeys) $restriction .= "OR ";
                $restriction .= "et.teacherID IN {$this->teacherKeys} ";
            }
            if($this->groupKeys)
            {
                if($this->roomKeys or $this->teacherKeys) $restriction .= "OR ";
                $restriction .= "eg.groupID IN {$this->groupKeys} ";
            }
            $restriction .= ") ";
            $query->where($restriction);
        }
    }

    /**
     * getDailyEvents
     *
     * retrieves details to conflicting daily events
     *
     * @param JDatabaseQuery $query
     * @return array of event data
     */
    private function getDailyEvents(&$query)
    {
        $dbo = JFactory::getDbo();
        $this->prepEventWhere($query);
        $query->where($this->getDailyEventDateRestriction());
        if($this->starttime OR $this->endtime)
            $query->where($this->getDailyEventTimeRestriction());
        $query->where("e.recurrence_type = '1'");
        if($this->eventID) $query->where("c.id != '{$this->eventID}'");
        $dbo->setQuery((string)$query);
        return $dbo->loadAssocList();
    }

    /**
     * getDailyDateRestriction
     *
     * creates an sql restriction for the dates of daily events
     *
     * @return string suitable for inclusion in an sql query
     */
    private function getDailyEventDateRestriction()
    {
        $startdate = $this->dbDateFormat($this->startdate);
        $enddate = $this->dbDateFormat($this->enddate);
        $restriction = "( ";
        $restriction .= "( e.startdate <= '$startdate' AND e.enddate >= '$startdate' ) ";
        $restriction .= "OR ";
        $restriction .= "( e.startdate > '$startdate' AND e.startdate <= '$enddate' ) ";
        $restriction .= " ) ";
        return $restriction;
    }

    /**
     * dailyTimeRestriction
     *
     * creates an sql restriction for the times of daily events
     * 
     * @return string suitable for inclusion in an sql query
     */
    private function getDailyEventTimeRestriction()
    {
        $restriction = "( ";
        $restriction .= "( e.starttime = '00:00:00' AND e.endtime = '00:00:00' ) OR ";
        if($this->starttime AND $this->endtime)
        {
            $restriction .= "( e.starttime <= '{$this->starttime}' AND e.endtime >= '{$this->starttime}' ) OR ";
            $restriction .= "( e.starttime > '{$this->starttime}' AND e.starttime <= '{$this->endtime}' ) OR ";
            $restriction .= "( e.starttime = '00:00:00' AND e.endtime >= '{$this->starttime}' ) OR ";
            $restriction .= "( e.starttime <= '{$this->endtime}' AND e.endtime = '00:00:00' ) ";
        }
        else if($this->starttime)
        {
            $restriction .= "( e.starttime <= '{$this->starttime}' AND e.endtime >= '{$this->starttime}' ) OR ";
            $restriction .= "( e.starttime = '00:00:00' AND e.endtime >= '{$this->starttime}' ) OR ";
            $restriction .= "( e.starttime >= '{$this->starttime}' ) ";
        }
        else if($this->endtime)
        {
            $restriction .= "( e.starttime <= '{$this->endtime}' AND e.endtime >= '{$this->endtime}' ) OR ";
            $restriction .= "( e.starttime = '00:00:00' AND e.endtime >= '{$this->endtime}' ) OR ";
            $restriction .= "( e.endtime <= '{$this->endtime}' )  ";
        }
        $restriction .= ")";
        return $restriction;
    }

    /**
     * getBlockEvents
     *
     * retrieves details to conflicting block events
     *
     * @param JDatabaseQuery $query
     * @return array of event data
     */
    private function getBlockEvents(&$query)
    {
        $dbo = JFactory::getDbo();
        $this->prepEventWhere($query);
        $query->where($this->getEventBlockRestriction());
        $query->where("e.recurrence_type = '0'");
        if($this->eventID) $query->where("c.id != '{$this->eventID}'");
        $dbo->setQuery((string)$query);
        return $dbo->loadAssocList();
    }

    /**
     * getEventBlockRestriction
     *
     *
     *
     * @return string
     */
    private function getEventBlockRestriction()
    {
        $newEventStart = ($this->starttime)? "{$this->startdate} {$this->starttime}" : "{$this->startdate} 00:00";
        $flooredEventStart = strtotime("{$this->startdate} 00:00");
        $ceilingEventStart = strtotime("{$this->startdate} 23:59");
        $newEventStart = strtotime($newEventStart);
        $newEventEnd = ($this->endtime)? "{$this->enddate} {$this->endtime}" : "{$this->enddate} 23:59";
        $flooredEventEnd = strtotime("{$this->enddate} 00:00");
        $ceilingEventEnd = strtotime("{$this->enddate} 23:59");
        $newEventEnd = strtotime($newEventEnd);
        $restriction = "( ";
        $restriction .= "(e.start <= '$newEventStart' AND e.end >= '$newEventStart' ) OR ";
        $restriction .= "(e.start > '$newEventStart' AND e.start <= '$newEventEnd' ) OR ";
        $restriction .= "(e.starttime = '00:00:00' AND e.start <= '$flooredEventStart' AND e.end >= '$flooredEventStart' ) OR ";
        $restriction .= "(e.endtime = '00:00:00' AND e.start <= '$flooredEventEnd' AND e.end >= '$flooredEventEnd' ) ";
        if(!$this->starttime and !$this->endtime)
        {
            $restriction .= "OR (e.start >= '$flooredEventStart' AND e.start <= '$ceilingEventStart' ) ";
            $restriction .= "OR (e.start >= '$flooredEventStart' AND e.start <= '$ceilingEventStart' ) ";
        }
        $restriction .= ")";
        return $restriction;
    }

    /**
     * prepareEvents
     * 
     * reformats an array of conflicting events to the following structure:<br />
     * <b>type</b> the type of object(here: event)<br/>
     * <b>title</b> the title of event<br/>
     * <b>author</b> the name of the user who created the event<br/>
     * <b>timeText</b> a preformatted text explainng the run of the event<br/>
     * <b>resourcesText</b> a formatted text with the names of conflicting resources
     * @param array $events array containing information about conflicting events
     */
    private function prepareEvents(&$events)
    {
        foreach($events as $key => $event)
        {
            $reformattedEvent = array();
            $reformattedEvent['details'] = JText::_('COM_THM_ORGANIZER_B_EVENT').": ".$event['title'];
            $reformattedEvent['details'] .= " ".JText::_('COM_THM_ORGANIZER_B_EVENT_AUTHOR')." ".$event['author'];
            $reformattedEvent['details'] .= " ".$this->makeEventTimeText($event);
            $reformattedEvent['resourcesText'] = $this->getResourcesText($event['id'], 'event');
            $events[$key] = $reformattedEvent;
        }
    }

    /**
     * makeEventTimeText
     *
     * creates a formatted text explaining the run of an event
     *
     * @param array $event array containing event information
     * @return string formatted text explaining the run of an event
     */
    private function makeEventTimeText($event)
    {
        if($event['starttime'] == "00:00") unset($event['starttime']);
        if($event['endtime'] == "00:00")unset($event['endtime']);
        if($event['enddate'] == "00.00.0000" or $event['startdate'] == $event['enddate'])
            unset($event['enddate']);

        //creation of the sentence display of the dates & times
        $eventTimeText = JText::_("COM_THM_ORGANIZER_B_EVENT_START");
        $timeText = "";
        if(isset($event['starttime']) && isset($event['endtime']))
        {
            $timeText = " ".JText::_("COM_THM_ORGANIZER_B_BETWEEN");
            $timeText .= " ".$event['starttime'];
            $timeText .= " ".JText::_("COM_THM_ORGANIZER_B_AND");
            $timeText .= " ".$event['endtime'];
        }
        else if(isset($event['starttime']))
            $timeText = " ".JText::_("COM_THM_ORGANIZER_B_FROM")." ".$event['starttime'];
        else if(isset($event['endtime']))
            $timeText = " ".JText::_("COM_THM_ORGANIZER_B_TO")." ".$event['endtime'];
        else
            $timeText = " ".JText::_ ("COM_THM_ORGANIZER_B_ALLDAY");

        if(isset($event['startdate']) and isset($event['enddate']) and $event['startdate'] != $event['enddate'])
        {
            if($event['rec_type'] == 0)
            {
                if(isset($event['starttime']) && isset($event['endtime']))
                {
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_BETWEEN")." ".$event['starttime'];
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_ON")." ".$event['startdate'];
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_AND")." ".$event['endtime'];
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_ON")." ".$event['enddate'];
                }
                else if(isset($event['starttime']))
                {
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_FROM")." ".$event['starttime'];
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_ON")." ".$event['startdate'];
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_TO")." ".$event['enddate'];
                }
                else if(isset($event['endtime']))
                {
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_FROM")." ".$event['startdate'];
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_TO")." ".$event['endtime'];
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_ON")." ".$event['enddate'];
                }
                else
                {
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_FROM")." ".$event['startdate'];
                    $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_UNTIL")." ".$event['enddate'];
                    $eventTimeText .= $timeText;
                }
            }
            else
            {
                $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_FROM")." ".$event['startdate'];
                $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_UNTIL")." ".$event['enddate'];
                $eventTimeText .= $timeText;
            }
        }
        else
        {
            $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_ON")." ".$event['startdate'].$timeText;
        }
        $eventTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_END");
        return $eventTimeText;
    }

    /**
     * getResourcesText
     *
     * creates a preformatted text containing the names of resources which are
     * in conflict with those of the event to be created
     *
     * @param int $eventID the ID of the event which is in conflict with the one
     * to be created
     * @return string preformatted text containing resource names
     */
    private function getResourcesText($id, $type)
    {
        $resources = array();
        if($type == 'event')
        {
            $teacherIDs =
                $this->getResourceIDs("#__thm_organizer_event_teachers", "eventID", "teacherID", $id, $this->teacherKeys);
            $roomIDs =
                $this->getResourceIDs("#__thm_organizer_event_rooms", "eventID", "roomID", $id, $this->roomKeys);
            $groupIDs =
                $this->getResourceIDs("#__thm_organizer_event_groups", "eventID",  "groupID", $id, $this->groupKeys);
        }
        else if($type == 'lesson')
        {
            $teacherIDs =
                $this->getResourceIDs("#__thm_organizer_lesson_teachers", "lessonID", "teacherID", $id, $this->teacherKeys);
            $roomIDs =
                $this->getResourceIDs("#__thm_organizer_lesson_times", "lessonID", "roomID", $id, $this->roomKeys);
            $groupIDs = array();
        }
        if(count($teacherIDs))
        {
            $teachers = $this->resolveIDstoNames($this->teachers, $teacherIDs);
            $resources[] = implode(", ", $teachers);
        }
        if(count($roomIDs))
        {
            $rooms = $this->resolveIDstoNames($this->rooms, $roomIDs);
            $resources[] = implode(", ", $rooms);
        }
        if(count($groupIDs))
        {
            $groups = $this->resolveIDstoNames($this->groups, $groupIDs);
            $resources[] = implode(", ", $groups);
        }
        $resources = implode(", ", $resources);
        $resourceText = JText::_('COM_THM_ORGANIZER_B_RESOURCE_START').": ".$resources;
        return $resourceText;
    }

    /**
     * getResourceIDs
     *
     * retrieves the subset of resource ids for a particular resource which are
     * are the same as those of the event to be created
     *
     * @param string $tableName the name of the table which stores the association
     * of event to resource
     * @param string $columnName the name of the column which aliases the resource
     * @param int $eventID the id of the event
     * @param string $keys a prepared list of resource keys to be inserted into
     * the sql statement
     * @return array list of associated resource ids
     */
    private function getResourceIDs($tableName, $keyColumn, $resourceColumn, $id, $keys)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT($resourceColumn)");
        $query->from($tableName);
        $query->where("$keyColumn = '$id'");
        $query->where("$resourceColumn IN $keys");
        $dbo->setQuery((string)$query);
        return $dbo->loadResultArray();
    }

    /**
     * resolveIDstoNames
     *
     * filters the array of containing id/name pairs according to a subset of ids
     * returning an array containing the associated names
     *
     * @param array $namesArray list of id/name pairs
     * @param array $IDsArray list of IDs
     * @return array list of names
     */
    private function resolveIDstoNames($namesArray,$IDsArray)
    {
        $array = array();
        foreach($IDsArray as $ID) $array[]= $namesArray[$ID];
        return $array;
    }

    /**
     * getLessons
     *
     * retrieves an array of lessons which conflict with the event to be saved
     *
     * @return array $lessons empty if no conflicts were found
     */
    private function getLessons()
    {
        //if there is nothing to be reserved or no schedule is valid for the
        //time period of the event no conflicts are possible
        if((!$this->roomKeys and !$this->teacherKeys) or !$this->activeSchedules)
            return array();

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT(l.id), s.alias AS title, l.type");
        $this->prepareLessonQuery($query);
        $dbo->setQuery((string)$query);
        $lessons = $dbo->loadAssocList();
        if(isset($lessons) and count($lessons)) $this->prepareLessons($lessons);
        return $lessons;
    }

    /**
     * getActiveSchedules
     *
     * retrieves a sql formatted list of active schedule ids whos dates overlap
     * those of the event
     *
     * @return string list of schedule ids
     */
    private function setActiveSchedules()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT (id), startdate, enddate");
        $query->from("#__thm_organizer_schedules");
        $query->where("active IS NOT null");
        $dbo->setQuery((string)$query);
        $activeSchedules = $dbo->loadAssocList();
        if(!isset($activeSchedules) or !count($activeSchedules)) return;

        $newEventStart = ($this->starttime)? "{$this->startdate} {$this->starttime}" : "{$this->startdate} 00:00";
        $newEventStart = strtotime($newEventStart);
        $newEventEnd = ($this->endtime)? "{$this->enddate} {$this->endtime}" : "{$this->enddate} 23:59";
        $newEventEnd = strtotime($newEventEnd);

        $list = "";
        foreach($activeSchedules as $k => $schedule)
        {
            $start = strtotime("{$schedule['startdate']} 00:00");
            $end = strtotime("{$schedule['enddate']} 23:59");
            if($start <= $newEventStart and $end >= $newEventStart)
            {
                if($list) $list .= ", ";
                $list .= "'{$schedule['id']}'";
                break;
            }
            if($start >= $newEventStart and $start <= $newEventEnd)
            {
                if($list) $list .= ", ";
                $list .= "'{$schedule['id']}'";
                break;
            }
        }
        if($list) $list = "( $list ) ";
        return $this->activeSchedules = $list;
    }

    private function prepareLessonQuery(&$query)
    {
        $query->from("#__thm_organizer_lessons AS l");
        $query->innerJoin("#__thm_organizer_subjects AS s ON l.subjectID = s.id");
        $query->innerJoin("#__thm_organizer_lesson_times AS ltimes ON l.id = ltimes.lessonID");
        $query->innerJoin("#__thm_organizer_periods AS p ON ltimes.periodID = p.id");
        $query->innerJoin("#__thm_organizer_lesson_teachers AS lteachers ON l.id = lteachers.lessonID");
        $query->where($this->lessonResourceRestriction());
        $query->where("l.semesterID IN {$this->activeSchedules}");

        //if the event goes on longer than a week every lesson with the chosen
        //resource should cause a collision
        $restriction = "";
        if($this->numberOfDays < 8 and $this->rec_type == 0)
                $restriction = $this->getLessonBlockRestriction();
        else if($this->rec_type == 1)
        {
            if($this->dayNumbers)
                $query->where("p.day in {$this->dayNumbers}");
            if($this->starttime or $this->endtime)
                $restriction = $this->getLessonDailyRestriction();
        }
        if($restriction) $query->where($restriction);

        $query->order("day, starttime");
    }

    /**
     * lessonResourceRestriction
     *
     * creates an sql clause to determine if the lesson uses the resources requested by the user
     *
     * @return string sql clause
     */
    private function lessonResourceRestriction()
    {
        $restriction = "( ";
        if($this->roomKeys)$restriction .= "ltimes.roomID IN {$this->roomKeys} ";
        if($this->teacherKeys)
        {
            if($this->roomKeys)$restriction .= "OR ";
            $restriction .= "lteachers.teacherID IN {$this->teacherKeys} ";
        }
        $restriction .= ") ";
        return $restriction;
    }

    /**
     * getLessonBlockRestriction
     *
     * creates a sql clause to determine intersection of block events with lessonsy
     *
     * @return string sql clause
     */
    private function getLessonBlockRestriction()
    {
        $startdt = strtotime($this->dbDateFormat($this->startdate));
        $startDate = getdate($startdt);
        $startDoW = $startDate['wday'];
        $enddt = strtotime($this->dbDateFormat($this->enddate));
        $endDate = getdate($enddt);
        $endDoW = $endDate['wday'];

        $restriction = "";
        //single day events
        if($startdt == $enddt)
        {
            $restriction .= "( p.day = '$startDoW' ";
            if($this->starttime and $this->endtime)
            {
                $restriction .= "AND ( ";
                $restriction .= "( p.starttime <= '{$this->starttime}' AND p.endtime >= '{$this->starttime}' ) ";
                $restriction .= "OR ";
                $restriction .= "( p.starttime >= '{$this->starttime}' AND p.starttime <= '{$this->endtime}' ) ";
                $restriction .= ") ";
            }
            else if($this->starttime)
            {
                $restriction .= "AND p.endtime >= '{$this->starttime}' ";

            }
            else if($this->endtime)
            {
                $restriction .= "AND ( p.starttime <= '{$this->endtime}' ";
            }
            $restriction .= ") ";
        }
        //multiple day events
        else
        {
            if($this->starttime)
            {
                $restriction .= "(p.day = '$startDoW' AND p.endtime >= '{$this->starttime}') ";
            }
            if($this->endtime)
            {
                if($this->starttime) $restriction .= "OR ";
                $restriction .= "(p.day = '$endDoW' AND p.starttime <= '{$this->endtime}') ";
            }
            if(!$this->starttime and !$this->endtime)
                $restriction .= "( p.day = '$startDoW' OR p.day = '$endDoW' ) ";
            //other days of multiple day events
            if($startDoW + 1 != $endDoW)
            {
                $restriction .= "OR ";
                if($startDoW > $endDoW)
                {
                    $restriction .= "( p.day > '$startDoW' AND p.day > '$endDoW' ) OR ";
                    $restriction .= "( p.day < '$startDoW' AND p.day < '$endDoW' ) ";
                }
                else
                    $restriction .= "( p.day > '$startDoW' AND p.day < '$endDoW' ) ";
            }
            if($restriction) $restriction = "( $restriction ) ";
        }
        return $restriction;
    }

    /**
     * dailyLessonTimeRestriction
     *
     * creates a sql clause to determine intersection of daily events with lessons
     *
     * @return string sql clause
     */
    private function getLessonDailyRestriction()
    {
        $restriction = "( ";
        if($this->starttime and $this->endtime)
        {
            $restriction .= "( p.starttime >= '{$this->starttime}' AND p.starttime <= '{$this->endtime}' ) OR ";
            $restriction .= "( p.endtime >= '{$this->starttime}' AND p.endtime <= '{$this->endtime}' ) OR ";
            $restriction .= "( p.starttime <= '{$this->starttime}' AND p.endtime >= '{$this->endtime}' ) ";
        }
        else if($this->starttime)
            $restriction .= "p.endtime >= '{$this->starttime}' ";
        else if($this->endtime)
            $restriction .= "p.starttime <= '{$this->endtime}' ";
        $restriction .= ")";
        return $restriction;
    }

    /**
     * prepareLessons
     *
     * reformats an array of conflicting lessons to the following structure:<br />
     * <b>detailst</b> a preformatted text explaining the details of the lesson<br/>
     * <b>resourcesText</b> a formatted text with the names of conflicting resources
     * @param array $events array containing information about conflicting events
     */
    private function prepareLessons(&$lessons)
    {
        foreach($lessons as $key => $lesson)
        {
            $lessons[$key]['details'] = JText::_('COM_THM_ORGANIZER_B_LESSON').": ".$lesson['title'];
            if($lesson['type'] != 'V') $lessons[$key]['details'] .= "-".$lesson['type'];
            $lessons[$key]['details'] .= " (".$this->getLessonTeachers($lesson['id']);
            $lessons[$key]['details'] .= ") ".$this->makeLessonTimeText($lesson['id']);
            $lessons[$key]['resourcesText'] = $this->getResourcesText($lesson['id'], 'lesson').".";
            unset($reformattedLesson);
        }
    }

    /**
     * getLessonTeachers
     *
     * retrieves an array of teachers holding the lesson and formats them into
     * a text string for later output
     *
     * @param int $id the id under which the lesson is saved in the db
     * @return string a comma seperated list of teachers
     */
    private function getLessonTeachers($id)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT (name)");
        $query->from("#__thm_organizer_lesson_teachers AS lt");
        $query->innerJoin("#__thm_organizer_teachers AS t ON lt.teacherID = t.id");
        $query->where("lessonID = '$id'");
        $dbo->setQuery((string)$query);
        $teacherNames = $dbo->loadResultArray();
        return implode(", ", $teacherNames);
    }

    /**
     * makeLessonTimeText
     *
     * creates a string expressing when a conflicting lesson is being held
     *
     * @param int $id the id of the lesson
     * @return string
     */
    private function makeLessonTimeText($id)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $this->prepareLessonQuery($query);
        $query->select("p.day, SUBSTR( p.starttime , 1 , 5 ) AS starttime, SUBSTR( p.endtime , 1 , 5 ) AS endtime");
        $query->where("ltimes.lessonID = '$id'");
        $dbo->setQuery((string)$query);
        $periods = $dbo->loadAssocList();
        if(isset($periods))
        {
            $timeText = JText::_('COM_THM_ORGANIZER_B_LESSON_START');
            foreach($periods as $key => $period)
            {
                switch($period['day'])
                {
                    case 0:
                        $period['day'] = JText::_('SUNDAY');
                        break;
                    case 1:
                        $period['day'] = JText::_('MONDAY');
                        break;
                    case 2:
                        $period['day'] = JText::_('TUESDAY');
                        break;
                    case 3:
                        $period['day'] = JText::_('WEDNESDAY');
                        break;
                    case 4:
                        $period['day'] = JText::_('THURSDAY');
                        break;
                    case 5:
                        $period['day'] = JText::_('FRIDAY');
                        break;
                    case 6:
                        $period['day'] = JText::_('SATURDAY');
                        break;
                }
                $blockText = " ".$period['day']."s";
                $blockText .= " ".JText::_('COM_THM_ORGANIZER_B_FROM');
                $blockText .= " ".$period['starttime'];
                $blockText .= " ".JText::_('COM_THM_ORGANIZER_B_TO');
                $blockText .= " ".$period['endtime'];
                $periods[$key] = $blockText;
            }
            for($i = 0; $i < COUNT($periods); $i++)
            {
                $timeText .= $periods[$i];
                if($i == COUNT($periods) - 1)break;
                else if($i == COUNT($periods) - 2)$timeText .= " ".JText::_('COM_THM_ORGANIZER_B_AND');
                else $timeText .= ",";
            }
            $timeText .= " ".JText::_('COM_THM_ORGANIZER_B_END');
            return $timeText;
        }
        else return "";
    }

    /**
     * dbDateFormat
     *
     * reformats a german formatted date (dd.mm.yyyy) to the format used by the
     * database (yyyy-mm-dd)
     *
     * @param string $date a date string in german format
     * @return string a sql formatted date string
     */
    private function dbDateFormat($date)
    {
        return substr($date, 6)."-".substr($date, 3, 2)."-".substr($date, 0, 2);
    }
}