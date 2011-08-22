<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        reservation ajax response model
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined('_JEXEC') or die;
jimport( 'joomla.application.component.model' );
class thm_organizerModelbooking extends JModel
{
    /**
     *
     * @var array $data holds test data created in the html view 
     */
    private $data;

    /**
     * @var int the id of an existing event
     */
    private $eventID;
    
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
     * @var int the number of recurrences (currently not used)
     */
    private $rec_number;

    /**
     * @var int event recurrance type<br />
     *  '0': block event starts on startdate at starttime ends on enddate at
     *       endtime
     *  '1': daily event repeats every day between startdate and enddate between
     *       starttime and endtime
     *  othervalues are currently unused
     */
    private $rec_type;
    private $rooms;
    private $roomKeys;
    private $teachers;
    private $teacherKeys;
    private $groups;
    private $groupKeys;
    private $reservingCats;
    private $reservingCatsKeys;
    private $dayNumbers;
    
    public $conflicts;

    public function __construct()
    {
        parent::__construct();
        $this->conflicts = array();
        $this->conflictingEvents = array();
        $this->conflictingLessons = array();
    }

    /**
     * prepareData
     *
     * loads data into the object from an array of test data or from the user
     * request
     *
     * @param array $data test data for debugging
     */
    public function prepareData($data = null)
    {
        if(isset($data))
        {
            $this->data = $data;
            $this->eventID = $data['eventID'];
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
        $this->getReservingCats();
        $this->getDayNumbers();
        $this->checkForConflicts();
    }

    /**
     * getResourceData
     *
     * retrieves the names of resources requested in the appointment to be created
     *
     * @param string $requestName name of the request variable
     * @param string $columnName name of the db table column
     * @param string $tableName name of the db table
     * @return array array of resource ids and associated names (empty if no resources
     *               were requested
     */
    private function getResourceData($requestName, $columnName, $tableName)
    {
        $resourceData = array();
        if(isset($this->data))
            $$requestName = (isset($this->data[$requestName]))? $this->data[$requestName] : array();
        else $$requestName = (isset($_REQUEST[$requestName]))? explode(",", $_REQUEST[$requestName]) : array();
        $dummyIndex = array_search('-1', $$requestName);
        if($dummyIndex)unset($$requestName[$dummyIndex]);
        if(count($$requestName))
        {
            $dbo = JFactory::getDbo();
            $query = $dbo->getQuery(true);
            $query->select("id, $columnName AS name");
            $query->from("$tableName");
            $requestedIDs = "( '".implode("', '", $$requestName)."' )";
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
     * getReservingCats
     *
     * retrieves and sets the ids of event categories which reserve resources
     */
    private function getReservingCats()
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
            $this->reservingCatsKeys = "( '".implode("', '", $this->reservingCats)."' )";
        }
        else
        {
            $this->reservingCats = array();
            $this->reservingCatsKeys = "";
        }
    }

    /**
     * getDayNumbers
     *
     * retrieves the numbers which coincide with the weekdays between the start-
     * and enddates. these numbers are then formed into a string usable in a
     * sql query
     */
    private function getDayNumbers()
    {
        $startdt = strtotime($this->dbDateFormat($this->startdate));
        $enddt = strtotime($this->dbDateFormat($this->enddate));
        $diff = $enddt - $startdt;
        $numberOfWholeDays = round($diff / 86400) + 1;
        if($numberOfWholeDays >= 7) $this->dayNumbers = "";
        else
        {
            $startDate = getdate($startdt);
            $DoW = $startDate['wday'];
            $numericDays = array();
            for($i = 0; $i < $numberOfWholeDays; $i++)
            {
                $DoW = $DoW % 7;
                $numericDays[] = $DoW;
                $DoW++;
            }
            $numericDays = "( '".implode("', '", $numericDays)."' )";
            $this->dayNumbers = $numericDays;
        }
    }

    private function checkForConflicts()
    {
        $this->checkEvents();
        $this->checkLessons();
    }

    /**
     *  checkEvents
     *
     *
     */
    private function checkEvents()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $select = "DISTINCT(c.id), c.title, u.name AS author, 'event' AS type, ";
        $select .= "DATE_FORMAT(e.startdate, '%d.%m.%Y') AS startdate, ";
        $select .= "DATE_FORMAT(e.enddate, '%d.%m.%Y') AS enddate, ";
        $select .= "SUBSTR(e.starttime, 1, 5) AS starttime, ";
        $select .= "SUBSTR(e.endtime, 1, 5) AS endtime, ";
        $select .= "e.recurrence_type AS rec_type";
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
                
        //check daily events
        $this->prepEventWhere(&$query);
        $query->where($this->dailyDateRestriction());
        if($this->starttime OR $this->endtime)
            $query->where($this->dailyTimeRestriction());
        $query->where("e.recurrence_type = '1'");
        if($this->eventID)
            $query->where("c.id != '{$this->eventID}'");
        $dbo->setQuery((string)$query);
        $dailyEvents = $dbo->loadAssocList();

        //check block events
        $this->prepEventWhere(&$query);
        $query->where($this->blockRestriction());
        $query->where("e.recurrence_type = '0'");
        if($this->eventID)
            $query->where("c.id != '{$this->eventID}'");
        $dbo->setQuery((string)$query);
        $blockEvents = $dbo->loadAssocList();

        $conflictingEvents = array();
        if(isset($dailyEvents))
        {
            if(isset($blockEvents)) $conflictingEvents = array_merge($dailyEvents, $blockEvents);
            else $conflictingEvents = $dailyEvents;
        }
        else if(isset($blockEvents)) $conflictingEvents = $blockEvents;
        if(count($conflictingEvents))
        {
            $this->prepareEvents(&$conflictingEvents);
            $this->conflicts = array_merge($this->conflicts, $conflictingEvents);
        }
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
        $query->where("e.categoryID IN {$this->reservingCatsKeys}");
        $query->where($this->eventResourceRestriction());
    }

    /**
     * eventResourceRestriction
     *
     *
     *
     * @return string
     */
    private function eventResourceRestriction()
    {
        $restriction = "";
        if($this->roomKeys or $this->teacherKeys or $this->groupKeys)
        {
            $restriction .= "( ";
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
        }
        return $restriction;
    }

    private function dailyDateRestriction()
    {
        $startdate = $this->dbDateFormat($this->startdate);
        $enddate = $this->dbDateFormat($this->enddate);
        $restriction = "( ";
        $restriction .= "e.startdate <= $startdate AND ";
        $restriction .= "e.enddate >= $enddate ";
        $restriction .= " ) ";
        return $restriction;
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

    /**
     * dailyTimeRestriction
     * 
     * creates the sql for the time restriction while searching daily events
     * 
     * @return string suitable for inclusin in an sql query 
     */
    private function dailyTimeRestriction()
    {
        $restriction = "( ";
        $restriction .= "( e.starttime = '00:00:00' AND e.endtime = '00:00:00' ) OR ";
        if($this->starttime AND $this->endtime)
        {
            $restriction .= "( e.starttime <= '{$this->starttime}' AND e.endtime >= '{$this->starttime}' ) OR ";
            $restriction .= "( e.starttime <= '{$this->endtime}' AND e.endtime >= '{$this->endtime}' ) OR ";
            $restriction .= "( e.starttime >= '{$this->starttime}' AND e.endtime <= '{$this->endtime}' ) ";
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
            $restriction .= "( e.starttime <= '{$this->endtime}' AND e.endtime = '00:00:00' ) OR ";
            $restriction .= "( e.starttime = '00:00:00' AND e.endtime >= '{$this->endtime}' ) ";
        }
        $restriction .= ")";
        return $restriction;
    }

    private function blockRestriction()
    {
        $newEventStart = ($this->starttime)? "{$this->startdate} {$this->starttime}" : "{$this->startdate} 00:00";
        $newEventStart = strtotime($newEventStart);
        $newEventEnd = ($this->endtime)? "{$this->enddate} {$this->endtime}" : "{$this->enddate} 00:00";
        $newEventEnd = strtotime($newEventEnd);
        $restriction = "( ";
        $restriction .= "(e.start <= '$newEventStart' AND e.end >= '$newEventStart' ) OR ";
        $restriction .= "(e.start > '$newEventStart' AND e.start <= '$newEventEnd' ) ";
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
        $dateTimeText = JText::_("COM_THM_ORGANIZER_B_EVENT_START");
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
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_BETWEEN")." ".$event['starttime'];
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_ON")." ".$event['startdate'];
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_AND")." ".$event['endtime'];
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_ON")." ".$event['enddate'];
                }
                else if(isset($event['starttime']))
                {
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_FROM")." ".$event['starttime'];
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_ON")." ".$event['startdate'];
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_TO")." ".$event['enddate'];
                }
                else if(isset($event['endtime']))
                {
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_FROM")." ".$event['startdate'];
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_TO")." ".$event['endtime'];
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_ON")." ".$event['enddate'];
                }
                else
                {
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_FROM")." ".$event['startdate'];
                    $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_UNTIL")." ".$event['enddate'];
                    $dateTimeText .= $timeText;
                }
            }
            else
            {
                $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_FROM")." ".$event['startdate'];
                $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_UNTIL")." ".$event['enddate'];
                $dateTimeText .= $timeText;
            }
        }
        else
        {
            $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_ON")." ".$event['startdate'].$timeText;
        }
        $dateTimeText .= " ".JText::_("COM_THM_ORGANIZER_B_END");
        return $dateTimeText;
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

    private function checkLessons()
    {
        $dbo = JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select("DISTINCT(l.id), s.alias AS title");
        $query->from("#__thm_organizer_lessons AS l");
        $query->innerJoin("#__thm_organizer_subjects AS s ON l.subjectID = s.id");
        $query->innerJoin("#__thm_organizer_lesson_times AS ltimes ON l.id = ltimes.lessonID");
        $query->innerJoin("#__thm_organizer_periods AS p ON ltimes.periodID = p.id");
        $query->innerJoin("#__thm_organizer_lesson_teachers AS lteachers ON l.id = lteachers.lessonID");
        if($this->roomKeys or $this->teacherKeys)$query->where($this->lessonResourceRestriction());
        if($this->dayNumbers and $this->rec_type == 1)
            $query->where("p.day IN {$this->dayNumbers}");
        $this->lessonTimeRestriction(&$query);
        $dbo->setQuery((string)$query);
        $lessons = $dbo->loadAssocList();
        if(isset($lessons) and count($lessons))
        {
            $this->prepareLessons(&$lessons);
            $this->conflicts = array_merge($this->conflicts, $lessons);
        }
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
     * lessonTimeRestriction
     *
     * calls the appropriate function for the creation for the temporal inter-
     * section clause based upon the reccurance type of the event
     *
     * @return string sql clause
     */
    private function lessonTimeRestriction(&$query)
    {
        if($this->rec_type == 0) $this->blockLessonTimeRestriction(&$query);
        if($this->rec_type == 1) $this->dailyLessonTimeRestriction(&$query);
    }

    /**
     * blockLessonTimeRestriction
     *
     * creates a sql clause to determine intersection of block events with lessons
     *
     * @return string sql clause
     */
    private function blockLessonTimeRestriction(&$query)
    {
        $startdt = strtotime($this->dbDateFormat($this->startdate));
        $startDate = getdate($startdt);
        $startDoW = $startDate['wday'];
        $enddt = strtotime($this->dbDateFormat($this->enddate));
        $endDate = getdate($enddt);
        $endDoW = $endDate['wday'];
        $restriction = "( ";
        
        //single day events
        if($startDoW == $endDoW and $this->starttime and $this->endtime)
        {
            $restriction .= "( p.day = '$startDoW' AND ( ";
            $restriction .= "( p.starttime >= '{$this->starttime}' AND p.starttime <= '{$this->endtime}' ) OR ";
            $restriction .= "( p.endtime >= '{$this->starttime}' AND p.endtime <= '{$this->endtime}' ) OR ";
            $restriction .= "( p.starttime <= '{$this->starttime}' AND p.endtime >= '{$this->endtime}' ) ";
            $restriction .= ") ";
        }
        //first and last days of multiple day events
        else
        {
            if($this->starttime) $restriction .= "(p.day = '$startDoW' AND p.endtime >= '{$this->starttime}') ";
            if($this->endtime)
            {
                if($this->starttime) $restriction .= "OR ";
                $restriction .= "(p.day = '$endDoW' AND p.starttime <= '{$this->endtime}') ";
            }
        }
        //other days of multiple day events
        if($startDoW != $endDoW and $startDoW + 1 != $endDoW)
        {
            if($this->starttime or $this->endtime) $restriction .= "OR ";
            $restriction .= "( p.day > '$startDoW' AND p.day > '$endDoW' ) OR ";
            $restriction .= "( p.day < '$startDoW' AND p.day < '$endDoW' ) OR ";
            $restriction .= "( p.day > '$startDoW' AND p.day < '$endDoW' ) ";
        }
        $restriction .= ")";
        if($restriction != "( )") $query->where($restriction);
    }

    /**
     * dailyLessonTimeRestriction
     *
     * creates a sql clause to determine intersection of daily events with lessons
     *
     * @return string sql clause
     */
    private function dailyLessonTimeRestriction(&$query)
    {
        if($this->starttime or $this->endtime)
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
            $query->where($restriction);
        }
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
            $reformattedLesson = array();
            $reformattedLesson['details'] = JText::_('COM_THM_ORGANIZER_B_LESSON').": ".$lesson['title'];
            $reformattedLesson['details'] .= " ".JText::_('COM_THM_ORGANIZER_B_LESSON_TEACHERS');
            $reformattedLesson['details'] .= " ".$this->getLessonTeachers($lesson['id']);
            $reformattedLesson['details'] .= " ".$this->makeLessonTimeText($lesson['id']);
            $reformattedLesson['resourcesText'] = $this->getResourcesText($lesson['id'], 'lesson').".";
            $lessons[$key] = $reformattedLesson;
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
        $query->select("day, SUBSTR( starttime , 1 , 5 ) AS starttime, SUBSTR( endtime , 1 , 5 ) AS endtime");
        $query->from("#__thm_organizer_lesson_times AS ltimes");
        $query->innerJoin("#__thm_organizer_periods AS p ON ltimes.periodID = p.id");
        $query->innerJoin("#__thm_organizer_lesson_teachers AS lteachers ON ltimes.lessonID = lteachers.lessonID");
        $query->where($this->lessonResourceRestriction());
        if(!empty($this->dayNumbers))
            $query->where("p.day IN {$this->dayNumbers}");
        $this->lessonTimeRestriction(&$query);
        $query->where("ltimes.lessonID = '$id'");
        $query->order("day, starttime");
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
}