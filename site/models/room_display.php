<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        room display model
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
class thm_organizerModelroom_display extends JModel
{
    public $roomName = '';
    public $roomID;
    public $layout = 'default';
    public $semesterIDs = null;
    public $blocks = array();
    public $date = null;
    private $dbDate = "";
    public $lessonsExist = false;
    public $eventsExist = false;
    public $appointments = array();
    public $information = array();
    public $notices = array();
    public $upcoming = array();
    public $roomSelectLink = "";


    public function __construct()
    {
        parent::__construct();
        $monitor = JTable::getInstance('monitors', thm_organizerTable);
        $where = array('ip' => $_SERVER['REMOTE_ADDR']);
        $registered = $monitor->load($where);
        if($registered)
        {
            switch ($monitor->display)
            {
                case 1:
                    $this->layout = 'registered';
                    $this->setRoomInformation($monitor->roomID);
                    $this->setScheduleInformation();
                    break;
                case 2:
                    $this->determineDisplayBehaviour($monitor);
                    break;
                case 3:
                    $this->layout = 'content';
                    $this->content = $monitor->content;
                    break;
                default:
                    $this->layout = 'registered';
                    $this->setRoomInformation($monitor->roomID);
                    $this->setScheduleInformation();
                    break;
            }
        }
        else
        {
            $this->layout = 'default';
            $this->setRoomInformation();
            $this->setScheduleInformation();
        }
    }

    /**
     * setRoomInformation
     *
     * retrieves the name and id of the room
     *
     * @param int $roomID the id of the room referenced in the monitors table
     */
    function setRoomInformation($roomID = 0)
    {
        if(!$roomID)
        {
            $request = JRequest::getVar('jform');
            $where = array('name' => $request['room']);
        }
        $room = JTable::getInstance('rooms', 'thm_organizerTable');
        $exists = $room->load(($roomID)? $roomID : $where);
        if($exists)
        {
            $this->roomName = $room->name;
            $this->roomID = $room->id;
        }
        else $this->redirect('COM_THM_ORGANIZER_RD_NO_ROOM');
    }

    /**
     * setScheduleInformation
     */
    private function setScheduleInformation()
    {
        $request = JRequest::getVar('jform');
        if(isset($request['date'])) $this->date = getDate(strtotime($request['date']));
        else $this->date = getdate();
        $this->dbDate = date('Y-m-d', $this->date[0]);
        $this->semesterIDs = $this->getSemesterIDs();
        $this->blocks = $this->getBlocks();
        $this->setInformation();
        $this->setUpcoming();
        $this->setMenuLinks();
        $this->getAccessClause();
    }

    /**
     * getSemester
     *
     * retireves a list of semester IDs valid for the requested date and formats
     * them into a string suitable for an sql where clause
     *
     * @return string $semesterIDs
     */
     private function getSemesterIDs()
     {
         $dbo = $this->getDbo();
         $query = $dbo->getQuery(true);
         $query->select("semesters.id");
         $query->from("#__thm_organizer_semesters AS semesters");
         $query->innerJoin("#__thm_organizer_schedules AS schedules ON schedules.sid = semesters.id");
         $query->where("schedules.startdate <= '$this->dbDate'");
         $query->where("schedules.enddate >= '$this->dbDate'");
         $query->where("schedules.active IS NOT NULL");
         $dbo->setQuery((string)$query);
         $semesterIDs = $dbo->loadResultArray();
         if(empty($semesterIDs))$this->redirect(JText::_('COM_THM_ORGANIZER_RD_NO_SEMESTERS'));
         return "( '".implode ("', '", $semesterIDs)."' )";
     }
	
    /**
     * getBlocks
     *
     * creates an array of blocks and fills them with data
     *
     * @return mixed $blocks
     */
    private function getBlocks()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("id, period, SUBSTRING(starttime, 1, 5) AS starttime, SUBSTRING(endtime, 1, 5) AS endtime");
        $query->from("#__thm_organizer_periods");
        $query->where("day = '{$this->date['wday']}'");
        $query->order('period ASC');
        $dbo->setQuery((string)$query);
        $periods = $dbo->loadAssocList();
        $blocks = array();
        foreach($periods as $period)
        {
            $blocks[$period['period']] = $period;
            $this->setLessonData($blocks[$period['period']]);
            $this->setAppointments($blocks[$period['period']]);
            $this->setNotices($blocks[$period['period']]);
        }
        return $blocks;
    }

    /**
     * setLessonData
     *
     * adds basic lesson information to a block (if available)
     *
     * @todo add teacher associations to user/thm_groups views
     * @todo add module associations to thm_lsf views
     */
    private function setLessonData(&$block)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $select = "l.id AS lessonID, s.alias AS lessonName, ";
        $select .= "l.type AS lessonType, s.moduleID AS moduleID";
        $query->select($select);
        $query->from("#__thm_organizer_lessons AS l");
        $query->innerJoin("#__thm_organizer_subjects AS s ON l.subjectID = s.id");
        $lessonTimes = "#__thm_organizer_lesson_times AS lt ";
        $lessonTimes .= "ON lt.lessonID = l.id ";
        $lessonTimes .= "AND lt.roomID = '{$this->roomID}' ";
        $lessonTimes .= "AND lt.periodID = '{$block['id']}'";
        $query->innerJoin($lessonTimes);
        $query->where("l.semesterID IN {$this->semesterIDs}");
        $dbo->setQuery((string)$query);
        $lessonInfo = $dbo->loadAssoc();
        if(isset($lessonInfo))
        {
            if($lessonInfo['lessonType'] != 'V' and $lessonInfo['lessonType'] != '')
                $lessonName = $lessonInfo['lessonName']." - ".$lessonInfo['lessonType'];
            else $lessonName = $lessonInfo['lessonName'];
            $block['title'] = $lessonName;
            $block['moduleID'] = $lessonInfo['moduleID'];

            $query = $dbo->getQuery(true);
            $query->select("t.name AS name, t.id AS id");
            $query->from("#__thm_organizer_lesson_teachers AS lt");
            $query->innerJoin("#__thm_organizer_teachers AS t ON lt.teacherID = t.id");
            $query->where("lt.lessonID = {$lessonInfo['lessonID']}");
            $dbo->setQuery((string)$query);
            $lessonTeacherNames = $dbo->loadResultArray(0);
            $lessonTeacherIDs = $dbo->loadResultArray(1);
            if(isset($lessonTeacherNames))
            {
                $block['teacherIDs'] = $lessonTeacherIDs;
                $extraInformation = implode (", ", $lessonTeacherNames);
                $block['extraInformation'] = $extraInformation;
            }
            $block['type'] = 'COM_THM_ORGANIZER_RD_TYPE_LESSON';
            $this->lessonsExist = true;
        }
        else
        {
            $block['title'] = JText::_('COM_THM_ORGANIZER_NO_LESSON');
            $block['extraInformation'] = '';
            $block['type'] = 'empty';
        }
    }

    /**
     * setAppointments
     *
     * retrieves reserving events for the given time frame
     *
     * @param integer $blockIndex the index of the block array to be processed
     */
    private function setAppointments(&$block)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($this->select());
        $this->eventFrom($query, $block);
        $query->where($this->whereDates());
        $query->where($this->whereTimes($block));
        $query->where($this->getAccessClause());
        $query->where("ec.reservesobjects = '1'");
        if(isset($block['teacherIDs']))
        {
            $teacherIDs = "'".implode("', '", $block['teacherIDs'])."'";
            $query->where("(r.name = '$this->roomName' OR t.id IN ( $teacherIDs ))");
        }
        else $query->where("r.name = '$this->roomName'");
        $dbo->setQuery((string)$query);
        $appointments = $dbo->loadAssocList();
        if(isset($appointments) and count($appointments) > 0)
        {
            if(count($appointments) == 1)
            {
                $block['title'] = $appointments[0]['title'];
                $block['extraInformation'] = $this->makeEventTime($appointments[0]);
                $block['eventID'] = $appointments[0]['id'];
                $block['link'] = $this->getEventLink($appointments[0]['id'], $appointments[0]['title']);
                $block['type'] = 'COM_THM_ORGANIZER_RD_TYPE_APPOINTMENT';
            }
            if(count($appointments) > 1)
            {
                $block['title'] = "verschiedene Termine";
                $block['extraInformation'] = "";
                $block['type'] = 'COM_THM_ORGANIZER_RD_TYPE_APPOINTMENTS';
            }
            foreach($appointments as $k => $appointment)
            {
                if(isset($this->eventIDs) AND in_array($appointment['id'], $this->eventIDs))
                {
                    unset($appointments[$k]);
                    continue;
                }
                $this->eventIDs[] = $appointment['id'];
                $appointments[$k]['displayDates'] = $this->makeEventDates($appointment);
                $appointments[$k]['link'] = $this->getEventLink($appointment['id'], $appointment['title']);
            }
            $this->eventsExist = true;
            $this->appointments = array_merge($this->appointments, $appointments);
        }
    }

    /**
     * setNotices
     *
     * retrieves nonreserving/nonglobal events for the given time frame
     *
     * @param integer $blockIndex the index of the block array to be processed
     */
    private function setNotices($blockIndex)
    {
        $user = JFactory::getUser();
        $user->getAuthorisedViewLevels();
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($this->select());
        $this->eventFrom($query, $block);
        $query->where($this->whereDates());
        $query->where($this->getAccessClause());
        $query->where("ec.reservesobjects = '0'");
        $query->where("ec.globaldisplay = '0'");
        if(isset($block['teacherIDs']))
        {
            $teacherIDs = "'".implode("', '", $block['teacherIDs'])."'";
            $query->where("( r.name = '$this->name' OR t.id IN ( $teacherIDs ) )");
        }
        else $query->where("r.name = '$this->name'");
        $dbo->setQuery((string)$query);
        $notices = $dbo->loadAssocList();
        if(isset($notices) and count($notices) > 0)
        {
            foreach($notices as $k => $notice)
            {
                if(isset($this->eventIDs) AND in_array($notice['id'], $this->eventIDs))
                {
                    unset($notices[$k]);
                    continue;
                }
                $this->eventIDs[] = $notice['id'];
                $notices[$k]['displayDates'] = $this->makeEventDates($notice);
                $notices[$k]['link'] = $this->getEventLink($notice['id'], $notice['title']);
            }
            $this->eventsExist = true;
            $this->notices = array_merge($this->notices, $notices);
        }
    }

    /**
     * setInformation
     *
     * retrieves global events for the given time frame
     *
     * @param integer $blockIndex the index of the block array to be processed
     */
    private function setInformation()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($this->select());
        $this->eventFrom($query);
        $query->where($this->whereDates());
        $query->where($this->getAccessClause());
        $query->where("ec.globaldisplay = '1'");
        $dbo->setQuery((string)$query);
        $information = $dbo->loadAssocList();
        if(isset($information) and count($information) > 0)
        {
            foreach($information as $k => $info)
            {
                if(isset($this->eventIDs) AND in_array($info['id'], $this->eventIDs))
                {
                    unset($information[$k]);
                    continue;
                }
                $this->eventIDs[] = $info['id'];
                $information[$k]['displayDates'] = $this->makeEventDates($info);
                $information[$k]['link'] = $this->getEventLink($info['id'], $info['title']);
            }
            if($this->layout != 'default') $this->eventsExist = true;
            $this->information = array_merge($this->information, $information);
        }
    }

    /**
     * setUpcoming
     *
     * retrieves reserving events for the future time frame
     *
     * @param integer $blockIndex the index of the block array to be processed
     */
    private function setUpcoming()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($this->select());
        $this->eventFrom($query);
        $whereFutureDates = "( ";
        $whereFutureDates .= "(e.startdate > '{$this->dbDate}' AND e.enddate > '{$this->dbDate}') ";
        $whereFutureDates .= "OR (startdate > '{$this->dbDate}' AND enddate = '0000-00-00') ";
        $whereFutureDates .= ") ";
        $query->where($whereFutureDates);
        $query->where($this->getAccessClause());
        $query->where("ec.reservesobjects = '1'");
        $query->where("r.name = '$this->name'");
        $dbo->setQuery((string)$query);
        $upcoming = $dbo->loadAssocList();
        if(isset($upcoming) and count($upcoming) > 0)
        {
            foreach($upcoming as $k => $coming)
            {
                if(isset($this->eventIDs) AND in_array($coming['id'], $this->eventIDs))
                {
                    unset($upcoming[$k]);
                    continue;
                }
                $this->eventIDs[] = $coming['id'];
                $upcoming[$k]['displayDates'] = $this->makeEventDates($coming);
                $upcoming[$k]['link'] = $this->getEventLink($coming['id'], $coming['title']);
            }
            $this->eventsExist = true;
            $this->upcoming = array_merge($this->upcoming, $upcoming);
        }
    }

    /**
     * select
     *
     * creates the select clause for events
     */
    private function select()
    {
        $select = "DISTINCT (e.id) AS id, c.title AS title, ";
        $select .= "DATE_FORMAT(e.startdate, '%d.%m.%Y') AS startdate, ";
        $select .= "DATE_FORMAT(e.enddate, '%d.%m.%Y') AS enddate, ";
        $select .= "c.fulltext AS description, ";
        $select .= "SUBSTRING(e.starttime, 1, 5) AS starttime, ";
        $select .= "SUBSTRING(e.endtime, 1, 5) AS endtime, ";
        $select .= "e.recurrence_type AS rec_type";
        return $select;
    }

    /**
     * eventFrom
     *
     * creates the from clause for events
     */
    private function eventFrom(&$query, &$block = null)
    {
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->innerJoin("#__thm_organizer_categories AS ec ON e.categoryID = ec.id");
        $query->leftJoin("#__thm_organizer_event_rooms AS er ON e.id = er.eventID");
        $query->leftJoin("#__thm_organizer_rooms AS r ON er.roomID = r.id");
        if(isset($block) and isset($block['teacherIDs']))
        {
            $query->leftJoin("#__thm_organizer_event_teachers AS et ON e.id = et.eventID");
            $query->leftJoin("#__thm_organizer_teachers AS t ON et.teacherID = t.id");
        }
    }

    /**
     * whereDates
     *
     * creates an sql text for date restrictions based on the actual day
     *
     * @return string sql date restriction
     * @access private
     */
    private function whereDates()
    {
        $whereDates = "( ";
        $whereDates .= "(e.startdate <= '{$this->dbDate}' AND e.enddate >= '{$this->dbDate}') ";
        $whereDates .= "OR (startdate = '{$this->dbDate}' AND enddate = '0000-00-00') ";
        $whereDates .= ") ";
        return $whereDates;
    }

    /**
     * whereTimes
     *
     * creates an sql text for time restrictions based on the actual block
     *
     * @return string sql time restriction
     * @access private
     */
    private function whereTimes(&$block)
    {
        $starttime = $block['starttime'];
        $endtime = $block['endtime'];
        $whereTimes = "( ";
        $whereTimes .= "(e.starttime <= '$starttime' AND e.endtime >= '$endtime') ";
        $whereTimes .= "OR (starttime <= '$starttime' AND endtime = '00:00:00') ";
        $whereTimes .= "OR (starttime = '00:00:00' AND endtime >= '$endtime') ";
        $whereTimes .= "OR (starttime = '00:00:00' AND endtime = '00:00:00') ";
        $whereTimes .= ") ";
        return $whereTimes;
    }

    /**
     * Makes a time string from the start and end times of an event
     * if existent
     *
     * @param array $event sql result array
     * @return string times of event / all day event
     */
    function makeEventTime($event)
    {
        $timestring = "";
        if(isset($event['starttime']) && $event['starttime'] != "00:00")
        {
            $timestring .= "von ".$event['starttime'];
            if(isset($event['endtime']) && $event['endtime'] != "00:00")
                $timestring .= " bis ".$event['endtime'];
        }
        else if(isset($event['endtime']) && $event['endtime'] != "00:00")
            $timestring .= " bis ".$event['endtime'];
        else $timestring .= JText::_("COM_THM_ORGANIZER_EL_ALLDAY");
        return $timestring;
    }

    /**
     * Makes a date string from the start and end dates of an event
     * if existent
     *
     * @param array $e sql result array
     * @return string date(s) of event
     */
    function makeEventDates($event)
    {
        $edSet = $stSet = $etSet = false;
        $displayDates = $timestring = "";
        $edSet = $event['enddate'] != "00.00.0000";
        $stSet = $event['starttime'] != "00:00";
        $etSet = $event['endtime'] != "00:00";
        if($stSet and $etSet) $timestring = " ({$event['starttime']} - {$event['endtime']})";
        else if($stSet) $timestring = " (ab {$event['starttime']})";
        else if($etSet) $timestring = " (bis {$event['endtime']})";
        else $timestring = " ".JText::_("COM_THM_ORGANIZER_EL_ALLDAY");
        if($edSet and $event['rec_type'] == 0)
        {
            $displayDates = "{$event['startdate']}";
            if($stSet) $displayDates .= " ({$event['starttime']})";
            $displayDates .= JText::_('COM_THM_ORGANIZER_RD_UNTIL').$event['enddate'];
            if($etSet) $displayDates .= " ({$event['endtime']})";
        }
        else if($edSet and $event['rec_type'] == 1)
            $displayDates = $event['startdate'].JText::_('COM_THM_ORGANIZER_RD_UNTIL').$event['enddate']." ".$timestring;
        else
            $displayDates = $event['startdate']." ".$timestring;
        return $displayDates;
    }

    /**
     * getEventLink
     *
     * generates a link to an event based on id and title
     *
     * @param int $id the event id
     * @param string $title the event title
     * @return string a span containing a link to the event
     */
    private function getEventLink($id, $title)
    {
        $url = "index.php?option=com_thm_organizer&view=event&eventID=$id";
        $attribs['title'] = "$title::".JText::_('COM_THM_ORGANIZER_RD_EVENT_LINK_TEXT');
        return JHtml::_('link', $url, $title);
    }


    /**
     * funtion setMenuLink
     */
    private function setMenuLinks()
    {
        $menuID = JRequest::getInt('Itemid');
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("link");
        $query->from("#__menu AS eg");
        $query->where("id = $menuID");
        $query->where("link LIKE '%room_select%'");
        $dbo->setQuery((string)$query);
        $link = $dbo->loadResult();
        if(isset($link) and $link != "") $this->roomSelectLink = JRoute::_($link);
    }

    /**
     * redirects from the view if requested room is invalid or no data is available
     */
    private function redirect($message = '')
    {
        $application = JFactory::getApplication();
        $menuID = JRequest::getInt('Itemid');
        $rd_string = 'index.php';
        if(isset($menuID))$rd_string .= "&Itemid=$menuID";
        $application->redirect($rd_string, $message, 'error');
    }

    /**
     * determineDisplayBehaviour
     *
     * determines which display behaviour is desired based on the interval
     * setting and session variables
     *
     * @param JTable $monitor
     */
    private function determineDisplayBehaviour(&$monitor)
    {
        $session = JFactory::getSession();
        $displayTime = $session->get('displayTime', 0);
        $displayContent = $session->get('displayContent', 'schedule');
        if($displayTime % $monitor->interval == 0)
            $displayContent = ($displayContent == 'schedule')? 'content' : 'schedule';
        $displayTime++;
        $session->set('displayTime', $displayTime);
        $session->set('displayContent', $displayContent);

        switch ($displayContent)
        {
            case 'schedule':
                $this->layout = 'registered';
                $this->setRoomInformation($monitor->roomID);
                $this->setScheduleInformation();
                break;
            case 'content':
                $this->layout = 'content';
                $this->content = $monitor->content;
                break;
            default:
                $this->layout = 'registered';
                $this->setRoomInformation($monitor->roomID);
                $this->setScheduleInformation();
                break;
        }
    }

    /**
     *
     * @return string
     */
    private function getAccessClause()
    {
        $user = JFactory::getUser();
        $authorizedAccessLevels = $user->getAuthorisedViewLevels();
        return "c.access IN ( '".implode("', '", $authorizedAccessLevels)."' )";
    }
}

