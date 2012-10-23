<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        room display model
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
/**
 * Retrieves lesson and event data for a single room and day
 * 
 * @package  Joomla.Site
 * 
 * @since    2.5.4 
 */
class thm_organizerModelroom_display extends JModel
{
    public $roomName;

    public $_roomID;

    public $layout = 'default';

    private $_schedules;

    public $blocks;

    public $date;

    private $_dbDate = "";

    public $lessonsExist = false;

    public $eventsExist = false;

    public $appointments = array();

    public $information = array();

    public $notices = array();

    public $upcoming = array();

    public $roomSelectLink = "";

    /**
     * Constructor 
     */
    public function __construct()
    {
        parent::__construct();
        $monitor = JTable::getInstance('monitors', thm_organizerTable);
        $where = array('ip' => $_SERVER['REMOTE_ADDR']);
        $registered = $monitor->load($where);
        if ($registered)
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
                case 4:
                    $this->layout = 'events';
                    $this->setRoomInformation($monitor->roomID);
                    $this->setScheduleInformation();
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
     * Retrieves the name and id of the room
     *
     * @param   int  $roomID  the id of the room referenced in the monitors table
     * 
     * @return  void
     */
    private function setRoomInformation($roomID = 0)
    {
        if (!$roomID)
        {
            $form = JRequest::getVar('jform');
            $where = array('name' => $form['room']);
        }
        $room = JTable::getInstance('rooms', 'thm_organizerTable');
        $exists = $room->load(($roomID)? $roomID : $where);
        if ($exists)
        {
            $this->roomName = $room->name;
            $this->_gpuntisID = $room->_gpuntisID;
        }
        else
        {
            $this->redirect('COM_THM_ORGANIZER_RD_NO_ROOM');
        }
    }

    /**
     * setScheduleInformation
     */
    private function setScheduleInformation()
    {
        $request = JRequest::getVar('jform');
        if (isset($request['date']))
        {
            $this->date = getDate(strtotime($request['date']));
        }
        else
        {
            $this->date = getdate();
        }
        $this->_dbDate = date('Y-m-d', $this->date[0]);
        $this->getSchedules();
        if (count($this->_schedules))
        {
            $this->getBlocks();
        }
        $this->setInformation();
        $this->setAppointments();
        $this->setUpcoming();
        $this->setMenuLinks();
    }

    /**
     * Retireves schedules valid for the requested date
     *
     * @return  void
     */
     private function getSchedules()
     {
         $dbo = $this->getDbo();
         $query = $dbo->getQuery(true);
         $query->select("schedule");
         $query->from("#__thm_organizer_schedules");
         $query->where("startdate <= '$this->_dbDate'");
         $query->where("enddate >= '$this->_dbDate'");
         $query->where("active = 1");
         $dbo->setQuery((string) $query);
         $schedules = $dbo->loadResultArray();
         if (empty($schedules))
         {
             $this->redirect(JText::_('COM_THM_ORGANIZER_NO_SCHEDULES'));
         }
         else
         {
             foreach ($schedules as $key => $schedule)
             {
                 $schedules[$key] = json_decode($schedule);
             }
             $this->_schedules = $schedules;
         }
     }

    /**
     * Creates an array of blocks and fills them with data
     * 
     * @return void
     */
    private function getBlocks()
    {
        $schedulePeriods = $this->_schedules[0]->periods;
        $this->blocks = array();
        foreach ($schedulePeriods as $period)
        {
            if ($period->day == $this->date['wday'])
            {
                $this->blocks[$period->period] = array();
                $this->blocks[$period->period]['period'] = $period->period;
                $this->blocks[$period->period]['starttime'] = $period->starttime;
                $this->blocks[$period->period]['endtime'] = $period->endtime;
            }
        }
        foreach ($this->blocks as $key => $block)
        {
            $this->setLessonData($key);
            $this->setAppointments($key);
            $this->setNotices($key);
        }
    }

    /**
     * Adds basic lesson information to a block (if available)
     *
     * @todo add teacher associations to user/group views
     * @todo add module associations to former curriculum views
     * 
     * @return void
     */
    private function setLessonData($blockID)
    {
        $lessonFound = false;
        foreach ($this->_schedules as $scheduleID => $schedule)
        {
            if($lessonFound)
            {
                break;
            }
            foreach ($schedule->calendar->$this->_dbDate->$blockID as $lessonID => $rooms)
            {
                if($lessonFound)
                {
                    break;
                }
                foreach ($rooms as $gpuntisID => $delta)
                {
                    if ($gpuntisID == 'delta')
                    {
                        if ($delta == 'removed')
                        {
                            break;
                        }
                        else
                        {
                            continue;
                        }
                    }
                    if ($gpuntisID == $this->_gpuntisID)
                    {
                        $lessonFound = true;
                        $subjects = (array) $schedule->lessons->$lessonID->subjects;
                        if (count($subjects) > 1)
                        {
                            $lessonName = $schedule->lessons->$lessonID->name;
                        }
                        else
                        {
                            $longname = $schedule->subjects->$subjects[0]->longname;
                            $shortname = $schedule->subjects->$subjects[0]->name;
                            $lessonName = (strlen($longname) <= 30)? $longname : $shortname;
                            $lessonName .= " - " . $schedule->lessons->$lessonID->description;
                        }
                        $teachers = (array) $schedule->lessons->$lessonID->teachers;
                        foreach ($teachers as $key => $name)
                        {
                            $teachers[$key] = $schedule->teachers->$key->surname;
                        }
                        $teacherText = implode(', ', $teachers);
                        if (strlen($teacherText) > 30)
                        {
                            $teacherText = implode(', ', array_keys($teachers));
                        }
                    }
                }
            }
        }
        if (isset($lessonFound))
        {
            $this->blocks[$blockID]['title'] = $lessonName;
            $this->blocks[$blockID]['extraInformation'] = $teacherText;
            $this->blocks[$blockID]['type'] = 1;
            $this->lessonsExist = true;
        }
        else
        {
            $block['title'] = JText::_('COM_THM_ORGANIZER_NO_LESSON');
            $block['extraInformation'] = '';
            $block['type'] = 0;
        }
    }

    /**
     * Retrieves reserving events for the given time frame
     *
     * @param   int  &$block  the optional schedule block to be processed
     * 
     * @return  void
     */
    private function setAppointments(&$block = null)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($this->select());
        $this->from($query, $block);
        $query->where($this->whereDates());
        if ($block != null)
        {
            $query->where($this->whereTimes($block));
        }
        $query->where($this->getAccessClause());
        $query->where("ec.reservesobjects = '1'");
        if (isset($block['teacherIDs']))
        {
            $teacherIDs = "'".implode("', '", $block['teacherIDs'])."'";
            $query->where("(r.name = '$this->roomName' OR t.id IN ( $teacherIDs ))");
        }
        else
        {
            $query->where("r.name = '$this->roomName'");
        }
        $query->order($this->orderBy());
        $dbo->setQuery((string) $query);
        $appointments = $dbo->loadAssocList();
        if (isset($appointments) and count($appointments) > 0)
        {
            if (count($appointments) == 1)
            {
                $block['title'] = $appointments[0]['title'];
                $block['extraInformation'] = $this->makeEventTime($appointments[0]);
                $block['eventID'] = $appointments[0]['id'];
                $block['link'] = $this->getEventLink($appointments[0]['id'], $appointments[0]['title']);
                $block['type'] = 'COM_THM_ORGANIZER_RD_TYPE_APPOINTMENT';
            }
            if (count($appointments) > 1)
            {
                $block['title'] = "verschiedene Termine";
                $block['extraInformation'] = "";
                $block['type'] = 'COM_THM_ORGANIZER_RD_TYPE_APPOINTMENTS';
            }
            foreach ($appointments as $k => $appointment)
            {
                if (isset($this->eventIDs) AND in_array($appointment['id'], $this->eventIDs))
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
     * Retrieves nonreserving/nonglobal events for the given time frame
     *
     * @param   int  $blockIndex  the index of the block array to be processed
     * 
     * @return  void
     */
    private function setNotices($blockIndex)
    {
        $user = JFactory::getUser();
        $user->getAuthorisedViewLevels();
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($this->select());
        $this->from($query, $block);
        $query->where($this->whereDates());
        $query->where($this->getAccessClause());
        $query->where("ec.reservesobjects = '0'");
        $query->where("ec.globaldisplay = '0'");
        if ($block != null AND isset($block['teacherIDs']))
        {
            $teacherIDs = "'" . implode("', '", $block['teacherIDs']) . "'";
            $query->where("( r.name = '$this->name' OR t.id IN ( $teacherIDs ) )");
        }
        else
        {
            $query->where("r.name = '$this->name'");
        }
        $dbo->setQuery((string) $query);
        $notices = $dbo->loadAssocList();
        if (isset($notices) and count($notices) > 0)
        {
            foreach ($notices as $k => $notice)
            {
                if (isset($this->eventIDs) AND in_array($notice['id'], $this->eventIDs))
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
     * Retrieves global events for the given time frame
     * 
     * @return void
     */
    private function setInformation()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($this->select());
        $this->from($query);
        $query->where($this->whereDates());
        $query->where($this->getAccessClause());
        $query->where("ec.globaldisplay = '1'");
        $dbo->setQuery((string) $query);
        $information = $dbo->loadAssocList();
        if (isset($information) and count($information) > 0)
        {
            foreach ($information as $k => $info)
            {
                if (isset($this->eventIDs) AND in_array($info['id'], $this->eventIDs))
                {
                    unset($information[$k]);
                    continue;
                }
                $this->eventIDs[] = $info['id'];
                $information[$k]['displayDates'] = $this->makeEventDates($info);
                $information[$k]['link'] = $this->getEventLink($info['id'], $info['title']);
            }
            if ($this->layout != 'default')
            {
                $this->eventsExist = true;
            }
            $this->information = array_merge($this->information, $information);
        }
    }

    /**
     * Retrieves reserving events for the future time frame
     * 
     * @return void
     */
    private function setUpcoming()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($this->select());
        $this->from($query);
        $whereFutureDates = "( ";
        $whereFutureDates .= "(e.startdate > '{$this->_dbDate}' AND e.enddate > '{$this->_dbDate}') ";
        $whereFutureDates .= "OR (startdate > '{$this->_dbDate}' AND enddate = '0000-00-00') ";
        $whereFutureDates .= ") ";
        $query->where($whereFutureDates);
        $query->where($this->getAccessClause());
        $query->where("ec.reservesobjects = '1'");
        $query->where("r.name = '$this->roomName'");
        $query->order($this->orderBy());
        $dbo->setQuery((string) $query);
        $upcoming = $dbo->loadAssocList();
        if (isset($upcoming) and count($upcoming) > 0)
        {
            foreach ($upcoming as $k => $coming)
            {
                if (isset($this->eventIDs) AND in_array($coming['id'], $this->eventIDs))
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
     * Creates the select clause for events
     *
     * @return  string  an sql select clause
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
     * Creates the order by clause for event queries
     *
     * @return  string  the order by clause
     */
    private function orderBy()
    {
    	return  "DATE(startdate) ASC, starttime ASC";
    }

    /**
     * Creates the from clause for events
     * 
     * @param   object  &$query  the query to be modified
     * @param   type    &$block  an optional block for association with teacher
     *                           resources
     *
     * @return  void
     */
    private function from(&$query, &$block = null)
    {
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->innerJoin("#__thm_organizer_categories AS ec ON e.categoryID = ec.id");
        $query->leftJoin("#__thm_organizer_event_rooms AS er ON e.id = er.eventID");
        $query->leftJoin("#__thm_organizer_rooms AS r ON er.roomID = r.id");
        if (isset($block) and isset($block['teacherIDs']))
        {
            $query->leftJoin("#__thm_organizer_event_teachers AS et ON e.id = et.eventID");
            $query->leftJoin("#__thm_organizer_teachers AS t ON et.teacherID = t.id");
        }
    }

    /**
     * Creates an sql text for date restrictions based on the actual day
     *
     * @return  string  sql date restriction
     */
    private function whereDates()
    {
        $whereDates = "( ";
        $whereDates .= "(e.startdate <= '{$this->_dbDate}' AND e.enddate >= '{$this->_dbDate}') ";
        $whereDates .= "OR (startdate = '{$this->_dbDate}' AND enddate = '0000-00-00') ";
        $whereDates .= ") ";
        return $whereDates;
    }

    /**
     * Creates an sql clause for event time restrictions based on the actual block
     * 
     * @param   type  &$block  the block used as a basis for the time restriction
     *
     * @return  string  sql time restriction
     */
    private function whereTimes(&$block)
    {
        $whereTimes = "( ";
        $whereTimes .= "(e.starttime <= '{$block['starttime']}' AND e.endtime >= '{$block['endtime']}') ";
        $whereTimes .= "OR (starttime <= '{$block['starttime']}' AND endtime = '00:00:00') ";
        $whereTimes .= "OR (starttime = '00:00:00' AND endtime >= '$endtime') ";
        $whereTimes .= "OR (starttime = '00:00:00' AND endtime = '00:00:00') ";
        $whereTimes .= ") ";
        return $whereTimes;
    }

    /**
     * Makes a time string from the start and end times of an event if existent
     *
     * @param   array  $event  sql result array
     * 
     * @return  string  times of event / all day event
     */
    private function makeEventTime($event)
    {
        $timestring = "";
        if (isset($event['starttime']) AND $event['starttime'] != "00:00")
        {
            $timestring .= "von " . $event['starttime'];
            if (isset($event['endtime']) AND $event['endtime'] != "00:00")
            {
                $timestring .= " bis " . $event['endtime'];
            }
        }
        elseif (isset($event['endtime']) AND $event['endtime'] != "00:00")
        {
            $timestring .= " bis " . $event['endtime'];
        }
        else
        {
            $timestring .= JText::_("COM_THM_ORGANIZER_EL_ALLDAY");
        }
        return $timestring;
    }

    /**
     * Makes a date string from the start and end dates of an event if existent
     *
     * @param   array  $event  sql result array
     * 
     * @return  string  the date(s) of event
     */
    private function makeEventDates($event)
    {
        $edSet = $stSet = $etSet = false;
        $displayDates = $timestring = "";
        $edSet = $event['enddate'] != "00.00.0000";
        $stSet = $event['starttime'] != "00:00";
        $etSet = $event['endtime'] != "00:00";
        if ($stSet and $etSet)
        {
            $timestring = " ({$event['starttime']} - {$event['endtime']})";
        }
        elseif ($stSet)
        {
            $timestring = " (ab {$event['starttime']})";
        }
        elseif ($etSet)
        {
            $timestring = " (bis {$event['endtime']})";
        }
        else
        {
            $timestring = " " . JText::_("COM_THM_ORGANIZER_EL_ALLDAY");
        }
        if ($edSet and $event['rec_type'] == 0 AND $event['startdate'] != $event['enddate'])
        {
            $displayDates = "{$event['startdate']}";
            if ($stSet)
            {
                $displayDates .= " ({$event['starttime']})";
            }
            $displayDates .= JText::_('COM_THM_ORGANIZER_RD_UNTIL') . $event['enddate'];
            if ($etSet)
            {
                $displayDates .= " ({$event['endtime']})";
            }
        }
        elseif ($edSet and $event['rec_type'] == 1 AND $event['startdate'] != $event['enddate'])
        {
            $displayDates = $event['startdate'] . JText::_('COM_THM_ORGANIZER_RD_UNTIL') . $event['enddate'] . " " . $timestring;
        }
        else
        {
            $displayDates = JText::_(strtoupper(date_format(new DateTime($date), 'D'))) . " " . $event['startdate'] . " " . $timestring;
        }
        return $displayDates;
    }

    /**
     * Generates a link to an event based on id and title
     *
     * @param   int     $id     the event id
     * @param   string  $title  the event title
     * 
     * @return  string  a span containing a link to the event
     */
    private function getEventLink($id, $title)
    {
        $url = "index.php?option=com_thm_organizer&view=event&eventID=$id";
        $title = "$title::" . JText::_('COM_THM_ORGANIZER_RD_EVENT_LINK_TEXT');
        return JHtml::_('link', $url, $title);
    }

    /**
     * Sets a link back to the room selection interface
     * 
     * @return  void
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
        $dbo->setQuery((string) $query);
        $link = $dbo->loadResult();
        if (isset($link) and $link != "")
        {
            $this->roomSelectLink = JRoute::_($link);
        }
    }

    /**
     * Redirects from the view if requested room is invalid or no data is available
     * 
     * @return  void
     */
    private function redirect($message = '')
    {
        $application = JFactory::getApplication();
        $menuID = JRequest::getInt('Itemid');
        $rd_string = 'index.php';
        if (isset($menuID))
        {
            $rd_string .= "&Itemid=$menuID";
        }
        $application->redirect($rd_string, $message, 'error');
    }

    /**
     * Determines which display behaviour is desired based on the interval
     * setting and session variables
     *
     * @param   object  &$monitor  a monitor entry in the db table
     * 
     * @return  void
     */
    private function determineDisplayBehaviour(&$monitor)
    {
        $session = JFactory::getSession();
        $displayTime = $session->get('displayTime', 0);
        $displayContent = $session->get('displayContent', 'schedule');
        if ($displayTime % $monitor->interval == 0)
        {
            $displayContent = ($displayContent == 'schedule')? 'content' : 'schedule';
        }
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
     * Creates an sql restriction to check user access
     * 
     * @return  string  an sql where clause
     */
    private function getAccessClause()
    {
        $user = JFactory::getUser();
        $authorizedAccessLevels = $user->getAuthorisedViewLevels();
        return "c.access IN ( '" . implode("', '", $authorizedAccessLevels) . "' )";
    }
}

