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
        $starttime = strtotime($this->dbDateFormat($this->startdate));
        $endtime = strtotime($this->dbDateFormat($this->enddate));
        $diff = $endtime - $starttime;
        $numberOfWholeDays = round($diff / 86400) + 1;
        if($numberOfWholeDays >= 7) return;//everyday is affected
        $startDate = getdate($starttime);
        $startDoW = $startDate['wday'];
        $numericDays = array();
        for($i = 0; $i < $numberOfWholeDays; $i++)
        {
            $startDoW = $startDoW % 7;
            $numericDays[] = $startDoW;
            $startDoW++;
        }
        $numericDays = "( '".implode("', '", $numericDays)."' )";
        $this->dayNumbers = $numericDays;
    }

    private function checkForConflicts()
    {
        $this->checkEvents();
        $this->checkLessons();
        $this->formatResults();
    }

    private function checkEvents()
    {
        $dbo = JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select($this->eventSelect());
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->leftJoin("#__thm_organizer_event_rooms AS er ON e.id = er.eventID");
        $query->leftJoin("#__thm_organizer_event_teachers AS et ON e.id = et.eventID");
        $query->leftJoin("#__thm_organizer_event_groups AS eg ON e.id = eg.eventID");
                
        //check daily events
        $this->prepWhere(&$query);
        $query->where($this->dailyDateRestriction());
        if($this->starttime != '00:00' OR $this->endtime != '00:00')
            $query->where($this->dailyTimeRestriction());
        $query->where("e.recurrence_type = '1'");
        $dbo->setQuery((string)$query);
        $dailyEvents = $dbo->loadAssocList();

        //check block events
        $this->prepWhere(&$query);
        $query->where($this->blockRestriction());
        $query->where("e.recurrence_type = '0'");
        $dbo->setQuery((string)$query);
        $blockEvents = $dbo->loadAssocList();

        $this->conflictingEvents = array_merge($dailyEvents, $blockEvents);
    }

    private function eventSelect()
    {
        $select = "DISTINCT(c.id), c.title, c.created_by, ";
        $select .= "e.startdate, e.starttime, e.enddate, e.endtime, e.recurrence_type AS rec_type";
        return $select;
    }

    private function prepWhere(&$query)
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

        $this->conflictingEvents = array_merge($dailyEvents, $blockEvents);
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
        
    }

}