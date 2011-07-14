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
    private $rec_counter;
    private $rooms;
    private $roomKeys;
    private $teachers;
    private $teacherKeys;
    private $groups;
    private $groupKeys;
    private $reservingCats;
    private $dayNumbers;
    
    public $conflicts;
    private $conflictingEvents;
    private $conflictingLessons;

    public function __construct()
    {
        parent::__construct();
        $this->prepareData();
        $this->conflicts = array();
        $this->conflictingEvents = array();
        $this->conflictingLessons = array();
        $this->checkForConflicts();
    }

    private function prepareData()
    {
        $this->startdate = JRequest::getString('startdate');
        $this->enddate = JRequest::getString('enddate', $this->startdate);
        $this->starttime = JRequest::getString('starttime', '00:00');
        $this->endtime = JRequest::getString('endtime', '00:00');
        $this->rec_number = JRequest::getInt('rec_number');//not used yet
        $this->rec_type = JRequest::getInt('rec_type');
        $this->rec_counter = JRequest::getString('rec_counter', '');//not used yet
        $this->rooms = $this->getResourceData('rooms', 'name', '#__thm_organizer_rooms');
        $this->roomKeys = (count($this->rooms))?
                "( '".implode("', '", array_keys($this->rooms))."' )" : "";
        $this->teachers = $this->getResourceData('teachers', 'name', '#__thm_organizer_teachers');
        $this->teacherKeys = (count($this->teachers))?
                "( '".implode("', '", array_keys($this->teachers))."' )" : "";
        $this->groups = $this->getResourceData('groups', 'title', '#__usergroups');
        $this->groupKeys = (count($this->groups))?
                "( '".implode("', '", array_keys($this->groups))."' )" : "";
        $this->getReservingCats();
        $this->getDayNumbers();
    }
    
    private function getResourceData($requestName, $columnName, $tableName)
    {
        $resourceData = array();
        $$requestName = (isset($_REQUEST[$requestName]))? JRequest::getVar($requestName) : array();
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
            $dbo->setQuery((string)$query );
            $results = $dbo->loadResultArray();
            if(count($results))
                foreach($results as $result) $resourceData[$result['id']] = $result['name'];
        }
        return $resourceData;
    }

    private function getReservingCats()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__thm_organizer_categories");
        $query->where("reservesobjects = '1'");
        $dbo->setQuery((string)$query);
        $categoryIDs = $dbo->loadResultArray();
        $categoryIDs = "( '".implode("', '", $categoryIDs)."' )";
        $this->reservingCats = $categoryIDs;
    }

    private function getDayNumbers()
    {
        $startdt = strtotime($this->dbDateFormat($this->startdate));
        $enddt = strtotime($this->dbDateFormat($this->enddate));
        $diff = $enddt - $startdt;
        $numberOfWholeDays = round($diff / 86400) + 1;
        if($numberOfWholeDays >= 7) return;//everyday is affected
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

    private function checkForConflicts()
    {
        $this->checkEvents();
        $this->checkLessons();
    }

    private function checkEvents()
    {
        $dbo = JFactory::getDbo();

        $query = $dbo->getQuery(true);

        $select = "DISTINCT(c.id), c.title, u.name AS author, 'event' AS type";
        $select .= "e.startdate, e.starttime, e.enddate, e.endtime, e.recurrence_type AS rec_type";
        $query->select($select);
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->innerJoin("#__users AS u ON u.id = c.created_by");
        $query->leftJoin("#__thm_organizer_event_rooms AS er ON e.id = er.eventID");
        $query->leftJoin("#__thm_organizer_event_teachers AS et ON e.id = et.eventID");
        $query->leftJoin("#__thm_organizer_event_groups AS eg ON e.id = eg.eventID");
                
        //check daily events
        $this->prepEventWhere(&$query);
        $query->where($this->dailyDateRestriction());
        if($this->starttime != '00:00' OR $this->endtime != '00:00')
            $query->where($this->dailyTimeRestriction());
        $query->where("e.recurrence_type = '1'");
        $dbo->setQuery((string)$query);
        $dailyEvents = $dbo->loadAssocList();

        //check block events
        $this->prepEventWhere(&$query);
        $query->where($this->blockRestriction());
        $query->where("e.recurrence_type = '0'");
        $dbo->setQuery((string)$query);
        $blockEvents = $dbo->loadAssocList();

        $conflictingEvents = array_merge($dailyEvents, $blockEvents);
        if(count($conflictingEvents))
        {
            $this->prepareEvents(&$conflictingEvents);
            $this->conflicts = array_merge($this->conflicts, $conflictingEvents);
        }
    }

    private function prepEventWhere(&$query)
    {
        $query->clear('where');
        $query->where("e.categoryID IN {$this->reservingCats}");
        $query->where($this->eventResourceRestriction());
    }

    private function eventResourceRestriction()
    {
        $restriction = "( ";
        $restriction .= "er.roomID IN {$this->roomKeys} OR ";
        $restriction .= "et.teacherID IN {$this->teacherKeys} OR ";
        $restriction .= "eg.groupID IN {$this->groupKeys} ";
        $restriction .= ") ";
        return $restriction;
    }

    private function dailyDateRestriction()
    {
        $startdate = $this->dbDateFormat($this->startdate);
        $enddate = $this->dbDateFormat($this->enddate);
        $enddate = substr($enddate, 6)."-".substr($enddate, 3, 2)."-".substr($enddate, 0, 2);
        $restriction = "( ";
        $restriction .= "e.startdate <= $startdate AND ";
        $restriction .= "e.enddate >= $enddate ";
        $restriction .= " ) ";
        return $restriction;
    }

    private function dbDateFormat($date)
    {
        return substr($date, 6)."-".substr($date, 3, 2)."-".substr($date, 0, 2);
    }

    private function dailyTimeRestriction()
    {
        $restriction = "( ";
        $restriction .= "( e.starttime = '00:00:00' AND e.endtime = '00:00:00' ) OR ";
        if($this->starttime != '00:00' AND $this->endtime != '00:00')
        {
            $restriction .= "( e.starttime <= '{$this->starttime}' AND e.endtime >= '{$this->starttime}' ) OR ";
            $restriction .= "( e.starttime <= '{$this->endtime}' AND e.endtime >= '{$this->endtime}' ) OR ";
            $restriction .= "( e.starttime >= '{$this->starttime}' AND e.endtime <= '{$this->endtime}' ) ";
        }
        else if($this->starttime != '00:00')
        {
            $restriction .= "( e.starttime <= '{$this->starttime}' AND e.endtime >= '{$this->starttime}' ) OR ";
            $restriction .= "( e.starttime = '00:00:00' AND e.endtime >= '{$this->starttime}' ) OR ";
            $restriction .= "( e.starttime >= '{$this->starttime}' ) ";
        }
        else if($this->endtime != '00:00')
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
        $startdate = $this->dbDateFormat($this->startdate);
        $enddate = $this->dbDateFormat($this->enddate);
        $restriction = "( ";
        $restriction .= "( e.startdate < '$startdate' AND e.enddate > '$enddate' ) OR ";
        $restriction .= "( e.startdate > '$startdate' AND e.enddate < '$enddate') OR ";
        $restriction .= "( e.startdate > '$startdate' AND e.startdate < '$enddate') OR ";
        $restriction .= "( e.enddate > '$startdate' AND e.enddate < '$enddate') OR ";
        if($this->starttime != '00:00')
            $restriction .= "( e.enddate = '{$this->startdate}' AND e.endtime > '{$this->starttime}' ) OR ";
        else $restriction .= "( e.enddate = '$startdate' ) OR";
        if($this->endtime != '00:00')
            $restriction .= "( e.startdate <= '{$this->enddate}' AND e.starttime < '{$this->endtime}' ) ";
        else $restriction .= "( e.startdate = '$enddate' ) ";
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
            $reformattedEvent['type'] = $event['type'];
            $reformattedEvent['title'] = $event['title'];
            $reformattedEvent['author'] = $event['author'];
            $reformattedEvent['timeText'] = $this->makeEventTimeText($event);
            $reformattedEvent['resourcestText'] = $this->getResourcesText($event['id']);
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
        if($event['starttime'] == "00:00")unset($event['starttime']);
        if($event['endtime'] == "00:00")unset($event['endtime']);
        if($event['enddate'] == "00.00.0000" or $event['startdate'] == $event['enddate'])
            unset($event['enddate']);

        //creation of the sentence display of the dates & times
        $dateTimeText = JText::_("COM_THM_ORGANIZER_E_DATES_START");
        $timeText = "";
        if(isset($event['starttime']) && isset($event['endtime']))
        {
            $timeText = JText::_("COM_THM_ORGANIZER_E_BETWEEN");
            $timeText .= $event['starttime'].JText::_("COM_THM_ORGANIZER_E_AND").$event['endtime'];
        }
        else if(isset($event['starttime']))
            $timeText = JText::_("COM_THM_ORGANIZER_E_FROM").$event['starttime'];
        else if(isset($event['endtime']))
            $timeText = JText::_("COM_THM_ORGANIZER_E_TO").$event['endtime'];
        else
            $timeText = JText::_ ("COM_THM_ORGANIZER_E_ALLDAY");

        if(isset($event['startdate']) and isset($event['enddate']) and $event['startdate'] != $event['enddate'])
        {
            if($event['rec_type'] == 0)
            {
                if(isset($event['starttime']) && isset($event['endtime']))
                {
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_BETWEEN").$event['starttime'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON").$event['startdate'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_AND").$event['endtime'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON").$event['enddate'];
                }
                else if(isset($event['starttime']))
                {
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_FROM").$event['starttime'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON").$event['startdate'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_TO").$event['enddate'];
                }
                else if(isset($event['endtime']))
                {
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_FROM").$event['startdate'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_TO").$event['endtime'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON").$event['enddate'];
                }
                else
                {
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_FROM").$event['startdate'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_UNTIL").$event['enddate'];
                    $dateTimeText .= $timeText;
                }
            }
            else
            {
                $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_FROM").$event['startdate'];
                $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_UNTIL").$event['enddate'];
                $dateTimeText .= $timeText;
            }
        }
        else
        {
            $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON").$event['startdate'].$timeText;
        }
        $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_DATES_END");
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
    private function getResourcesText($eventID)
    {
        $resources = array();
        $teacherIDs =
            $this->getResourceIDs("#__thm_organizer_event_teachers", "teacherID", $eventID, $this->teacherKeys);
        if(count($teacherIDs))
        {
            $teachers = $this->resolveIDstoNames($this->teachers, $teacherIDs);
            $resources[] = implode(", ", $teachers);
        }
        $roomIDs =
            $this->getResourceIDs("#__thm_organizer_event_rooms", "roomID", $eventID, $this->roomKeys);
        if(count($roomIDs))
        {
            $rooms = $this->resolveIDstoNames($this->rooms, $roomIDs);
            $resources[] = implode(", ", $teachers);
        }
        $groupIDs =
            $this->getResourceIDs("#__thm_organizer_event_groups", "groupID", $eventID, $this->groupKeys);
        if(count($groupIDs))
        {
            $groups = $this->resolveIDstoNames($this->groups, $groupIDs);
            $resources[] = implode(", ", $teachers);
        }
        $resources = implode(", ", $resources);
        return JText::_('COM_THM_ORGANIZER_B_RESOURCE_START').$resources;
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
    private function getResourceIDs($tableName, $columnName, $eventID, $keys)
    {
        $dbo = JFactory::getDbo;
        $query = $dbo->getQuery(true);
        $query->select($columnName);
        $query->from($tableName);
        $query->where("eventID = '$eventID");
        $query->where("$columnName IN $keys'");
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
        $query->select("DISTINCT(l.id), s.alias");
        $query->from("#__thm_organizer_lessons AS l");
        $query->innerJoin("#__thm_organizer_subjects AS s ON l.subjectID = s.id");
        $query->innerJoin("#__thm_organizer_lesson_times AS ltimes ON l.id = ltimes.lessonID");
        $query->innerJoin("#__thm_organizer_rooms AS r ON ltimes.roomID = r.id");
        $query->innerJoin("#__thm_organizer_periods AS p ON ltimes.periodID = p.id");
        $query->innerJoin("#__thm_organizer_lesson_teachers AS lteachers ON l.id = lteachers.lessonID");
        $query->innerJoin("#__thm_organizer_teachers AS t ON lteachers.periodID = t.id");
        $query->where($this->lessonResourceRestriction());
        if(!empty($this->dayNumbers))
            $query->where("p.day IN {$this->dayNumbers}");
        $query->where($this->lessonTimeRestriction());
        $dbo->setQuery((string)$query);
        $lessons = $dbo->loadAssocList();

    }

    private function lessonResourceRestriction()
    {
        $restriction = "( ";
        $restriction .= "r.id IN {$this->roomKeys} OR ";
        $restriction .= "t.id IN {$this->teacherKeys} ";
        $restriction .= ") ";
        return $restriction;
    }

    private function lessonTimeRestriction()
    {
        if($this->rec_type == 0) return $this->blockLessonTimeRestriction();
        if($this->rec_type == 1) return $this->dailyLessonTimeRestriction();
    }

    private function blockLessonTimeRestriction()
    {
        $startdt = strtotime($this->dbDateFormat($this->startdate));
        $startDate = getdate($startdt);
        $startDoW = $startDate['wday'];
        $enddt = strtotime($this->dbDateFormat($this->enddate));
        $endDate = getdate($enddt);
        $endDoW = $endDate['wday'];
        $restriction = "( ";

        //between start and enddates modulo 7
        $restriction .= "( p.day > '$startDoW' AND p.day > '$endDoW' ) OR ";
        $restriction .= "( p.day < '$startDoW' AND p.day < '$endDoW' ) OR ";
        $restriction .= "( p.day > '$startDoW' AND p.day < '$endDoW' ) OR ";

        $restriction .= "( p.day = '$startDoW' AND p.endtime > '{$this->starttime}' ) OR ";
        $restriction .= "( p.day = '$endDoW' AND p.starttime > '{$this->endttime}' ) ";

        $restriction .= ")";
        return $restriction;
    }

    private function dailyLessonTimeRestriction()
    {
        $restriction = "( ";
        $restriction .= "( p.starttime > '{$this->starttime}' AND p.starttime < '{$this->endtime}' ) OR ";
        $restriction .= "( p.endtime > '{$this->starttime}' AND p.endtime < '{$this->endtime}' ) OR ";
        $restriction .= "( p.starttime < '{$this->starttime}' AND p.endtime > '{$this->endtime}' ) OR";
        $restriction .= ")";
        return $restriction;
    }
}