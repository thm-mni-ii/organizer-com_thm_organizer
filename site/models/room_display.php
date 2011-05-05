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
    public $name = '';
    public $id;
    public $layout = '';
    public $semesterIDs = null;
    public $blocks = array();
    private $postDate = '';
    private $dayNumber;
    public $displayDate = '';
    public $dayName = '';
    public $lessonsExist = false;
    public $eventsExist = false;
    private $eventIDs = array();
    public $appointments = array();
    public $information = array();
    public $notices = array();
    public $upcoming = array();
    public $roomSelectLink = "";


    public function __construct()
    {
        parent::__construct();
        $this->setRoomInformation();//sets roomname and layout
        $this->setSemesters();
        $this->setDateInformation();//sets date variables
        $this->setBlocks();
        $this->setScheduleData();
        $this->setMenuLinks();
    }

    /**
     * checks whether the room is valid and sets room and layout variables
     * redirects to the calling room selection if no room was found with the given information
     */
    function setRoomInformation()
    {
        //check if registered
        $ip = $_SERVER['REMOTE_ADDR'];
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("r.id AS id, r.name AS name");
        $query->from("#__thm_organizer_monitors AS m");
        $query->innerJoin("#__thm_organizer_rooms AS r ON m.roomID = r.id");
        $query->where("ip = '$ip'");
        $dbo->setQuery((string)$query);
        $room = $dbo->loadAssoc();
        if(!empty($room))
        {
            $this->name = $room['name'];
            $this->id = $room['id'];
            $this->layout = 'registered';
            return;
        }
        else//check if room exists
        {
            $request = JRequest::getVar('jform');
            $roomname = $request['room'];
            if(isset($roomname) && $roomname != '')
            {
                $query = $dbo->getQuery(true);
                $query->select("id, name");
                $query->from("#__thm_organizer_rooms");
                $query->where("name = '$roomname'");
                $dbo->setQuery((string)$query);
                $room = $dbo->loadAssoc();
                if(!empty($room))
                {
                    $this->name = $room['name'];
                    $this->id = $room['id'];
                    $this->layout = 'default';
                    return;
                }
            }
        }
        //room does not exist => redirect to selection
        //$this->redirect('COM_THM_ORGANIZER_RD_NO_ROOM');
    }

    /**
     * function setSemester
     *
     * checks which semesters currently has validity
     */
     private function setSemesters()
     {
         $date = date("Y-m-d");
         $dbo = JFactory::getDbo();
         $query = $dbo->getQuery(true);
         $query->select("semesters.id");
         $query->from("#__thm_organizer_semesters AS semesters");
         $query->innerJoin("#__thm_organizer_schedules AS schedules ON schedules.sid = semesters.id");
         $query->where("schedules.startdate <= '$date'");
         $query->where("schedules.enddate >= '$date'");
         $query->where("schedules.active IS NOT NULL");
         $dbo->setQuery((string)$query);
         $semesterIDs = $dbo->loadResultArray();
         if(empty($semesterIDs))$this->redirect(JText::_('COM_THM_ORGANIZER_RD_NO_SEMESTERS'));
         else $this->semesterIDs = $semesterIDs;
     }
	
    /**
     * Resolves a date from $_POST or system
     * to its german name and a date string with german formatting.
     *
     */
    private function setDateInformation()
    {
        $request = JRequest::getVar('jform');
        $postDate = $request['date'];
        if(isset($postDate))
        {
            $postDate = substr($postDate, 6, 4)."-".substr($postDate, 3, 2)."-".substr($postDate, 0, 2);
            $date = strtotime($postDate);
        }
        else $date = false;
        if($date)
        {
            $this->postDate = $postDate;
            $date = getdate($date); //date info as array
            $this->dayNumber = $date['wday'];
            $this->displayDate = $date['mday'].".".$date['mon'].".".substr($date['year'], 2);
        }
        else
        {
            $this->postDate = date('y-m-d');
            $this->dayNumber = date('w');
            $this->displayDate = date('d.m.y');
        }
        switch($this->dayNumber)
        {
            case 0:
                $this->dayName = JText::_('SUNDAY');
                break;
            case 1:
                $this->dayName = JText::_('MONDAY');
                break;
            case 2:
                $this->dayName = JText::_('TUESDAY');
                break;
            case 3:
                $this->dayName = JText::_('WEDNESDAY');
                break;
            case 4:
                $this->dayName = JText::_('THURSDAY');
                break;
            case 5:
                $this->dayName = JText::_('FRIDAY');
                break;
            case 6:
                $this->dayName = JText::_('SATURDAY');
                break;
        }
    }

    /**
     * Creates an array with the start/endtimes of the periods and an associated id
     */
    private function setBlocks()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT period, SUBSTRING(starttime, 1, 5) AS starttime, SUBSTRING(endtime, 1, 5) AS endtime");
        $query->from("#__thm_organizer_periods");
        $semesters = "'".implode("', '", $this->semesterIDs)."'";
        $query->where("semesterID IN ( $semesters )");
        $query->where("day = '{$this->dayNumber}'");
        $dbo->setQuery((string)$query);
        $periods = $dbo->loadAssocList();
        foreach($periods as $k => $period)
        {
            $query = $dbo->getQuery(true);
            $query->select("id");
            $query->from("#__thm_organizer_periods");
            $query->where("semesterID IN ( $semesters )");
            $query->where("period = '{$period['period']}'");
            $query->where("SUBSTRING(starttime, 1, 5) = '{$period['starttime']}'");
            $query->where("SUBSTRING(endtime, 1, 5) = '{$period['endtime']}'");
            $query->where("day = '{$this->dayNumber}'");
            $dbo->setQuery((string)$query);
            $periods[$k]['ids'] = $dbo->loadResultArray();
        }
        if ($dbo->getErrorNum()) return "error";
        $blocks = array();
        foreach($periods as $period)
        {
            $blocks[$period['period']]['starttime'] = $period['starttime'];
            $blocks[$period['period']]['endtime'] = $period['endtime'];
            $blocks[$period['period']]['displayTime'] = $period['starttime']." - ".$period['endtime'];
            $blocks[$period['period']]['ids'] = $period['ids'];
        }
        asort($blocks);
        $this->blocks = $blocks;
    }
	
    /**
     * Gets the lessons for the room and day
     *
     * @return either an array of lessons or void if none were found
     */
    function setScheduleData()
    {
        foreach($this->blocks as $blockIndex => $blockValue)
        {
            $this->setLessonData($blockIndex);
            $this->setAppointments($blockIndex);
            $this->setNotices($blockIndex);
        }
        $this->setInformation();
        $this->setUpcoming();
    }

    /**
     * setLessonData
     *
     * adds basic lesson information to a block (if available)
     *
     * @todo add teacher associations to user/thm_groups views
     * @todo add module associations to thm_lsf views
     */
    private function setLessonData($blockIndex)
    {
        $block =& $this->blocks[$blockIndex];
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $select = "l.id AS lessonID, s.alias AS lessonName, l.type AS lessonType, s.moduleID AS moduleID";
        $query->select($select);
        $query->from("#__thm_organizer_lessons AS l");
        $query->innerJoin("#__thm_organizer_subjects AS s ON l.subjectID = s.id");
        $lessonTimes = "#__thm_organizer_lesson_times AS lt ";
        $lessonTimes .= "ON lt.lessonID = l.id ";
        $lessonTimes .= "AND lt.roomID = $this->id ";
        $periodIDs = "'".implode("', '", $block['ids'])."'";
        $lessonTimes .= "AND lt.periodID IN ( $periodIDs )";
        $query->innerJoin($lessonTimes);
        $dbo->setQuery((string)$query);
        $lessonInfo = $dbo->loadAssoc();
        if(isset($lessonInfo))
        {
            if($lessonInfo['lessonType'] != 'V')
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
    private function setAppointments($blockIndex)
    {
        $block =& $this->blocks[$blockIndex];
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $this->eventSelect(&$query);
        $this->eventFrom(&$query, &$block);
        $query->where($this->whereDates());
        $query->where($this->whereTimes(&$block));
        $query->where("c.access = '0'");
        $query->where("ec.reservesobjects = '1'");
        if(isset($block['teacherIDs']))
        {
            $teacherIDs = "'".implode("', '", $block['teacherIDs'])."'";
            $query->where("(r.name = '$this->name' OR t.id IN ( $teacherIDs ))");
        }
        else $query->where("r.name = '$this->name'");
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
                if(in_array($appointment['id'], $this->eventIDs))
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
        $block =& $this->blocks[$blockIndex];
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $this->eventSelect(&$query);
        $this->eventFrom(&$query, &$block);
        $query->where($this->whereDates());
        $query->where("c.access = '0'");
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
                if(in_array($notice['id'], $this->eventIDs))
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
        $this->eventSelect(&$query);
        $this->eventFrom(&$query);
        $query->where($this->whereDates());
        $query->where("c.access = '0'");
        $query->where("ec.globaldisplay = '1'");
        $dbo->setQuery((string)$query);
        $information = $dbo->loadAssocList();
        if(isset($information) and count($information) > 0)
        {
            foreach($information as $k => $info)
            {
                if(in_array($info['id'], $this->eventIDs))
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
        $this->eventSelect(&$query);
        $this->eventFrom(&$query);
        $whereFutureDates = "( ";
        $whereFutureDates .= "(e.startdate > '{$this->postDate}' AND e.enddate > '{$this->postDate}') ";
        $whereFutureDates .= "OR (startdate > '{$this->postDate}' AND enddate = '0000-00-00') ";
        $whereFutureDates .= ") ";
        $query->where($whereFutureDates);
        $query->where("c.access = '0'");
        $query->where("ec.reservesobjects = '1'");
        $query->where("r.name = '$this->name'");
        $dbo->setQuery((string)$query);
        $upcoming = $dbo->loadAssocList();
        if(isset($upcoming) and count($upcoming) > 0)
        {
            foreach($upcoming as $k => $coming)
            {
                if(in_array($coming['id'], $this->eventIDs))
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
     * eventSelect
     *
     * creates the select clause for events
     */
    private function eventSelect(&$query)
    {
        $select = "DISTINCT (e.id) AS id, c.title AS title, ";
        $select .= "DATE_FORMAT(e.startdate, '%d.%m.%Y') AS startdate, ";
        $select .= "DATE_FORMAT(e.enddate, '%d.%m.%Y') AS enddate, ";
        $select .= "c.introtext AS description, ";
        $select .= "SUBSTRING(e.starttime, 1, 5) AS starttime, ";
        $select .= "SUBSTRING(e.endtime, 1, 5) AS endtime, ";
        $select .= "e.recurrence_type AS rec_type";
        $query->select($select);
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
        $whereDates .= "(e.startdate <= '{$this->postDate}' AND e.enddate >= '{$this->postDate}') ";
        $whereDates .= "OR (startdate = '{$this->postDate}' AND enddate = '0000-00-00') ";
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
        $rd_string = 'index.php?option=com_thm_organizer&view=room_select';
        if(isset($menuID))$rd_string .= "&Itemid=$menuID";
        $application->redirect($rd_string, $message, 'error');
    }
}

