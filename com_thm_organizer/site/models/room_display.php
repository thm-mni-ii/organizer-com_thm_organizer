<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelRoom_display
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT . '/helper/teacher.php';
require_once JPATH_COMPONENT . '/helper/event.php';

/**
 * Retrieves lesson and event data for a single room and day
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelRoom_Display extends JModelLegacy
{
    public $roomName;

    private $_gpuntisID;

    public $layout = 'default';

    public $schedule_refresh;

    public $content_refresh;

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
        $input = JFactory::getApplication()->input;
        $monitor = JTable::getInstance('monitors', 'thm_organizerTable');
        $remoteIPData = array('ip' => $input->server->getString('REMOTE_ADDR', ''));
        $registered = $monitor->load($remoteIPData);

        if ($registered)
        {
            $templateSet = $input->getString('tmpl', '') == 'component';
            if (!$templateSet)
            {
                $this->redirectToComponentTemplate();
            }

            if ($monitor->useDefaults)
            {
                $display = JComponentHelper::getParams('com_thm_organizer')->get('display');
                $this->schedule_refresh = JComponentHelper::getParams('com_thm_organizer')->get('schedule_refresh');
                $this->content_refresh = JComponentHelper::getParams('com_thm_organizer')->get('content_refresh');
                $this->content = JComponentHelper::getParams('com_thm_organizer')->get('content');
            }
            else
            {
                $display = $monitor->display;
                $this->schedule_refresh = $monitor->schedule_refresh;
                $this->content_refresh = $monitor->content_refresh;
                $this->content = $monitor->content;
            }
            switch ($monitor->display)
            {
                case 1:
                    $this->layout = 'registered';
                    break;
                case 2:
                    $this->setAlternatingLayout();
                    break;
                case 3:
                    $this->layout = 'content';
                    break;
                case 4:
                    $this->layout = 'events';
                    break;
                default:
                    $this->layout = 'registered';
                    break;
            }
        }
        else
        {
            $this->layout = 'default';
            $this->setRoomInformation();
        }
        $roomID = empty($monitor->roomID)? null : $monitor->roomID;
        $this->setRoomInformation($roomID);
        $this->setScheduleInformation();
    }

    /**
     * Redirects to the component template
     *
     * @return  void
     */
    private function redirectToComponentTemplate()
    {
        $application = JFactory::getApplication()->input;
        $requestURL = $application->input->server->get('SERVER_NAME', '');
        $requestURL .= $application->input->server->get('REQUEST_URI', '');
        $redirectURL = $requestURL . '&tmpl=component';
        $application->redirect($redirectURL);
    }

    /**
     * Retrieves the name and id of the room
     *
     * @param   int  $roomID  the id of the room referenced in the monitors table
     *
     * @return  void
     */
    private function setRoomInformation($roomID = null)
    {
        if (empty($roomID))
        {
            $form = JFactory::getApplication()->input->get('jform', null, 'array');
            $roomID = $form['room'];
        }
        $room = JTable::getInstance('rooms', 'thm_organizerTable');
        $exists = $room->load($roomID);
        if ($exists)
        {
            $this->roomName = $room->longname;
            $this->_gpuntisID = strpos($room->gpuntisID, 'RM_') === 0?
                substr($room->gpuntisID, 3) : $room->gpuntisID;
        }
        else
        {
            $this->redirect('COM_THM_ORGANIZER_RD_NO_ROOM');
        }
    }

    /**
     * Sets information about the daily schedule
     *
     * @return  void
     */
    private function setScheduleInformation()
    {
        $request = JRequest::getVar('jform');
        if (!empty($request['date']))
        {
            $this->date = getDate(strtotime($request['date']));
        }
        else
        {
            $this->date = getdate(time());
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
         
        try 
        {
            $schedules = $dbo->loadColumn();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
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
                $this->blocks[$period->period]['starttime'] = substr($period->starttime, 0, 2) . ":" . substr($period->starttime, 2);
                $this->blocks[$period->period]['endtime'] = substr($period->endtime, 0, 2) . ":" . substr($period->endtime, 2);
                $this->blocks[$period->period]['displayTime'] = $this->blocks[$period->period]['starttime'] . " - ";
                $this->blocks[$period->period]['displayTime'] .= $this->blocks[$period->period]['endtime'];
            }
        }
        foreach (array_keys($this->blocks) as $key)
        {
            $this->setLessonData($key);
            $this->setAppointments($key);
            $this->setNotices($key);
        }
    }

    /**
     * Adds basic lesson information to a block (if available)
     *
     * @param   int  $blockID  the id of the block being iterated
     *
     * @return void
     */
    private function setLessonData($blockID)
    {
        $menuID = JFactory::getApplication()->input->getInt('Itemid', 0);
        $lessonFound = false;
        $lessonTitle = $teacherText = '';
        foreach ($this->_schedules as $schedule)
        {
            if ($lessonFound)
            {
                break;
            }
            foreach ($schedule->calendar->{$this->_dbDate}->$blockID as $lessonID => $rooms)
            {
                if ($lessonFound)
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
                        foreach ($subjects as $subjectID => $delta)
                        {
                            if ($delta == 'removed')
                            {
                                unset($subjects[$subjectID]);
                            }
                        }
                        if (count($subjects) > 1)
                        {
                            $lessonName = $schedule->lessons->$lessonID->name;
                        }
                        else
                        {
                            $subjects = array_keys($subjects);
                            $subjectID = array_shift($subjects);
                            $longname = $schedule->subjects->$subjectID->longname;
                            $shortname = $schedule->subjects->$subjectID->name;
                            $lessonName = (strlen($longname) <= 30)? $longname : $shortname;
                            $lessonName .= " - " . $schedule->lessons->$lessonID->description;
                            if (!empty($schedule->subjects->$subjectID->subjectNo)
                             AND $this->layout != 'registered')
                            {
                                $subjectLink = "index.php?option=com_thm_organizer&view=subject_details.css";
                                $subjectLink .= "&languageTag=de&Itemid=$menuID&nrmni=";
                                $subjectLink .= $schedule->subjects->$subjectID->subjectNo;
                                $lessonTitle .= JHtml::_('link', $subjectLink, $lessonName);
                            }
                            else
                            {
                                $lessonTitle .= $lessonName;
                            }
                        }
                        $teachersIDs = (array) $schedule->lessons->$lessonID->teachers;
                        $teachers = array();
                        foreach ($teachersIDs as $key => $delta)
                        {
                            if ($delta == 'removed')
                            {
                                unset($teachers[$key]);
                                continue;
                            }
                            $teacherName = $schedule->teachers->$key->surname;
                            $userID = THM_OrganizerHelperTeacher::getUserIDfromUntisID($key);
                            if (!empty($userID) AND $this->layout != 'registered')
                            {
                                $groupsLink = THM_OrganizerHelperTeacher::getLink($userID, $teacherName);
                                $teacherLink = JHtml::_('link', $groupsLink, $teacherName);
                            }
 
                            $teachers[$schedule->teachers->$key->surname] = empty($teacherLink)? $teacherName : $teacherLink;
                        }
                        $teacherText .= implode(', ', $teachers);
                    }
                }
            }
        }
        if ($lessonFound)
        {
            $this->blocks[$blockID]['title'] = $lessonTitle;
            $this->blocks[$blockID]['extraInformation'] = $teacherText;
            $this->blocks[$blockID]['type'] = 'COM_THM_ORGANIZER_RD_TYPE_LESSON';
            $this->blocks[$blockID]['teacherText'] = $teacherText;
            $this->lessonsExist = true;
        }
        else
        {
            $this->blocks[$blockID]['title'] = JText::_('COM_THM_ORGANIZER_NO_LESSON');
            $this->blocks[$blockID]['extraInformation'] = '';
            $this->blocks[$blockID]['type'] = 'empty';
        }
    }

    /**
     * Retrieves reserving events for the given time frame
     *
     * @param   int  $key  the optional block key to be processed
     *
     * @return  void
     */
    private function setAppointments($key = null)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($this->select());
        $this->from($query, $key);
        $query->where($this->whereDates());
        if (isset($key))
        {
            $query->where($this->whereTimes($key));
        }
        $query->where($this->whereAccess());
        $query->where("ec.reserves = '1'");
        if (isset($this->blocks[$key]['teacherIDs']))
        {
            $query->where("(r.longname = '$this->roomName' OR t.id IN ( '" . implode("', '", $this->blocks[$key]['teacherIDs']) . "' ))");
        }
        else
        {
            $query->where("r.longname = '$this->roomName'");
        }
        $query->order("DATE(startdate) ASC, starttime ASC");
        $dbo->setQuery((string) $query);
        try 
        {
            $appointments = $dbo->loadAssocList();
            foreach ($appointments as &$event)
            {
                THM_OrganizerHelperEvent::localizeEvent($event);
            }
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        if (isset($appointments) and count($appointments) > 0)
        {
            if (isset($key))
            {
                if (count($appointments) == 1)
                {
                    if ($this->layout == 'registered' OR $this->layout == 'events')
                    {
                        $this->blocks[$key]['title'] = substr($appointments[0]['title'], 0, 20);
                    }
                    else
                    {
                        $this->blocks[$key]['title'] = $appointments[0]['title'];
                    }
                    $this->blocks[$key]['extraInformation'] = $this->makeEventTime($appointments[0]);
                    $this->blocks[$key]['eventID'] = $appointments[0]['id'];
                    $this->blocks[$key]['link'] = $this->getEventLink($appointments[0]['id'], $appointments[0]['title']);
                    $this->blocks[$key]['type'] = 'COM_THM_ORGANIZER_RD_TYPE_APPOINTMENT';
                }
                elseif (count($appointments) > 1)
                {
                    $this->blocks[$key]['title'] = "verschiedene Termine";
                    $this->blocks[$key]['extraInformation'] = "";
                    $this->blocks[$key]['type'] = 'COM_THM_ORGANIZER_RD_TYPE_APPOINTMENTS';
                }
            }
            foreach ($appointments as $k => $appointment)
            {
                if (isset($this->eventIDs) AND in_array($appointment['id'], $this->eventIDs))
                {
                    unset($appointments[$k]);
                    continue;
                }
                $this->eventIDs[] = $appointment['id'];
                $appointments[$k]['displayDates'] = THM_OrganizerHelperEvent::getDateText($appointment, false);
                $appointments[$k]['link'] = $this->getEventLink($appointment['id'], $appointment['title']);
            }
            $this->eventsExist = true;
            $this->appointments = array_merge($this->appointments, $appointments);
        }
    }

    /**
     * Retrieves non-reserving/non-global events for the given time frame
     *
     * @param   int  $key  the index of the block array to be processed
     *
     * @return  void
     */
    private function setNotices($key)
    {
        $user = JFactory::getUser();
        $user->getAuthorisedViewLevels();
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($this->select());
        $this->from($query, $key);
        $query->where($this->whereDates());
        $query->where($this->whereAccess());
        $query->where("ec.reserves = '0'");
        $query->where("ec.global = '0'");
        if ($this->blocks[$key] != null AND isset($this->blocks[$key]['teacherIDs']))
        {
            $query->where("( r.longname = '$this->name' OR t.id IN ( '" . implode("', '", $this->blocks[$key]['teacherIDs']) . "' ) )");
        }
        else
        {
            $query->where("r.longname = '$this->name'");
        }
        $dbo->setQuery((string) $query);
        
        try 
        {
            $notices = $dbo->loadAssocList();
            foreach ($notices as &$event)
            {
                THM_OrganizerHelperEvent::localizeEvent($event);
            }
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
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
                $notices[$k]['displayDates'] = THM_OrganizerHelperEvent::getDateText($notice, false);
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
        $query->where($this->whereAccess());
        $query->where("ec.global = '1'");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $information = $dbo->loadAssocList();
            foreach ($information as &$event)
            {
                THM_OrganizerHelperEvent::localizeEvent($event);
            }
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
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
                $information[$k]['displayDates'] = THM_OrganizerHelperEvent::getDateText($info, false);
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
        $query->where($this->whereAccess());
        $query->where("ec.reserves = '1'");
        $query->where("r.longname = '$this->roomName'");
        $query->order("DATE(startdate) ASC, starttime ASC");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $upcoming = $dbo->loadAssocList();
            foreach ($upcoming as &$event)
            {
                THM_OrganizerHelperEvent::localizeEvent($event);
            }
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
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
                $upcoming[$k]['displayDates'] = THM_OrganizerHelperEvent::getDateText($coming, false);
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
        $select .= "e.startdate AS startdate, ";
        $select .= "e.enddate AS enddate, ";
        $select .= "c.fulltext AS description, ";
        $select .= "e.starttime AS starttime, ";
        $select .= "e.endtime AS endtime, ";
        $select .= "e.recurrence_type AS rec_type";
        return $select;
    }

    /**
     * Creates the from clause for events
     *
     * @param   object  &$query  the query to be modified
     * @param   int     $key     an optional block for association with teacher
     *                           resources
     *
     * @return  void
     */
    private function from(&$query, $key = null)
    {
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->innerJoin("#__thm_organizer_categories AS ec ON e.categoryID = ec.id");
        $query->leftJoin("#__thm_organizer_event_rooms AS er ON e.id = er.eventID");
        $query->leftJoin("#__thm_organizer_rooms AS r ON er.roomID = r.id");
        if (isset($key) and isset($this->blocks[$key]['teacherIDs']))
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
        $whereDates .= "OR (startdate = '{$this->_dbDate}') ";
        $whereDates .= ") ";
        return $whereDates;
    }

    /**
     * Creates an sql clause for event time restrictions based on the actual block
     *
     * @param   int  $key  the key of the block used as a basis for the time restriction
     *
     * @return  string  sql time restriction
     */
    private function whereTimes($key)
    {
        $block = $this->blocks[$key];
        $whereTimes = "( ";
        $whereTimes .= "('{$block['starttime']}' <= starttime AND '{$block['endtime']}' >= starttime ) ";
        $whereTimes .= "OR ('{$block['starttime']}' >= starttime AND '{$block['starttime']}' <= endtime ) ";
        $whereTimes .= "OR ('{$block['endtime']}' >= starttime AND endtime = '00:00') ";
        $whereTimes .= "OR ('{$block['starttime']}' <= endtime AND starttime  = '00:00') ";
        $whereTimes .= "OR (starttime = '00:00' AND endtime = '00:00') ";
        $whereTimes .= ") ";
        return $whereTimes;
    }

    /**
     * Creates an sql restriction to check user access
     *
     * @return  string  an sql where clause
     */
    private function whereAccess()
    {
        return "c.access IN ( '" . implode("', '", JFactory::getUser()->getAuthorisedViewLevels()) . "' )";
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
     * Generates a link to an event based on id and title
     *
     * @param   int     $eventID  the event id
     * @param   string  $title    the event title
     *
     * @return  string  a span containing a link to the event
     */
    private function getEventLink($eventID, $title)
    {
        $url = "index.php?option=com_thm_organizer&view=event_details.css&eventID=$eventID";
        $attributes = array('title' => "$title::" . JText::_('COM_THM_ORGANIZER_RD_EVENT_LINK_TEXT'));
        return JHtml::_('link', $url, $title, $attributes);
    }

    /**
     * Sets a link back to the room selection interface
     *
     * @return  void
     */
    private function setMenuLinks()
    {
        $menuID = JFactory::getApplication()->input->getInt('Itemid', 0);
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("link");
        $query->from("#__menu AS eg");
        $query->where("id = $menuID");
        $query->where("link LIKE '%room_select%'");
        $dbo->setQuery((string) $query);
        try 
        {
            $link = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        if (isset($link) and $link != "")
        {
            $this->roomSelectLink = JRoute::_($link);
        }
    }

    /**
     * Redirects from the view if requested room is invalid or no data is available
     *
     * @param   string  $message  the error message to be displayed after redirect
     *
     * @return  void
     */
    private function redirect($message = '')
    {
        $application = JFactory::getApplication();
        $application->redirect('index.php', $message, 'error');
    }

    /**
     * Determines which display behaviour is desired based on the interval
     * setting and session variables
     *
     * @return  void
     */
    private function setAlternatingLayout()
    {
        $session = JFactory::getSession();
        $displayContent = $session->get('displayContent', 'schedule');
        $session->set('displayContent', ($displayContent == 'schedule')? 'content' : 'schedule');

        if ($displayContent == 'schedule')
        {
            $this->layout = 'registered';
            return;
        }
        if ($displayContent == 'content')
        {
            $this->layout = 'content';
            return;
        }
    }
}
