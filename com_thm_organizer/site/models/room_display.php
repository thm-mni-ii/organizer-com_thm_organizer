<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelRoom_display
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT . '/helper/teacher.php';
require_once JPATH_COMPONENT . '/helper/event.php';

if (!defined('SCHEDULE'))
{
    define('SCHEDULE', 1);
}
if (!defined('ALTERNATING'))
{
    define('ALTERNATING', 2);
}
if (!defined('CONTENT'))
{
    define('CONTENT', 3);
}
if (!defined('APPOINTMENTS'))
{
    define('APPOINTMENTS', 4);
}

/**
 * Retrieves lesson and event data for a single room and day
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelRoom_Display extends JModelLegacy
{
    public $params = array();

    private $_schedules;

    public $blocks;

    private $_dbDate = "";

    public $appointments = array();

    public $information = array();

    public $notices = array();

    public $upcoming = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $input = JFactory::getApplication()->input;

        $ipData = array('ip' => $input->server->getString('REMOTE_ADDR', ''));
        $monitorEntry = JTable::getInstance('monitors', 'thm_organizerTable');
        $registered = $monitorEntry->load($ipData);

        if (!$registered)
        {
            $this->params['layout'] = 'default';
            return;
        }

        $templateSet = $input->getString('tmpl', '') == 'component';
        if (!$templateSet)
        {
            $this->redirectToComponentTemplate();
        }

        $this->setParams($monitorEntry);

        $this->setRoomInformation();

        $this->setScheduleInformation();
    }

    /**
     * Redirects to the component template
     *
     * @return  void
     */
    private function redirectToComponentTemplate()
    {
        $app = JFactory::getApplication();
        $base = JURI::root() . 'index.php?';
        $query = $app->input->server->get('QUERY_STRING', '', 'raw') . '&tmpl=component';
        $app->redirect($base . $query);
    }

    /**
     * Sets display parameters
     *
     * @param $monitorEntry
     */
    private function setParams(&$monitorEntry)
    {
        if ($monitorEntry->useDefaults)
        {
            $this->params['display'] = JComponentHelper::getParams('com_thm_organizer')->get('display', 1);
            $this->params['schedule_refresh'] = JComponentHelper::getParams('com_thm_organizer')->get('schedule_refresh');
            $this->params['content_refresh'] = JComponentHelper::getParams('com_thm_organizer')->get('content_refresh');
            $this->params['content'] = JComponentHelper::getParams('com_thm_organizer')->get('content');
            return;
        }
        else
        {
            $this->params['display'] = $monitorEntry->display;
            $this->params['schedule_refresh'] = $monitorEntry->schedule_refresh;
            $this->params['content_refresh'] = $monitorEntry->content_refresh;
            $this->params['content'] = $monitorEntry->content;
        }

        if ($this->params['display'] == SCHEDULE)
        {
            $this->params['layout'] = 'schedule';
        }
        if ($this->params['display'] == ALTERNATING)
        {
            $this->setAlternatingLayout();
        }
        if ($this->params['display'] == CONTENT)
        {
            $this->params['layout'] = 'content';
        }
        if ($this->params['display'] == APPOINTMENTS)
        {
            $this->params['layout'] = 'appointments';
        }

        $this->params['roomID'] = $monitorEntry->roomID;
    }


    /**
     * Determines which display behaviour is desired based on which layout was previously used
     *
     * @return  void
     */
    private function setAlternatingLayout()
    {
        $session = JFactory::getSession();
        $displayed = $session->get('displayed', 'schedule');

        if ($displayed == 'schedule')
        {
            $this->params['layout'] = 'content';
            return;
        }
        if ($displayed == 'schedule')
        {
            $this->params['layout'] = 'content';
            return;
        }

        $session->set('displayed', $this->params['layout']);
    }

    /**
     * Retrieves the name and id of the room
     *
     * @param   int  $roomID  the id of the room referenced in the monitors table
     *
     * @return  void
     */
    private function setRoomInformation()
    {
        $roomEntry = JTable::getInstance('rooms', 'thm_organizerTable');
        try
        {
            $roomEntry->load($this->params['roomID']);
            $this->params['roomName'] = $roomEntry->longname;
            $this->params['gpuntisID'] = strpos($roomEntry->gpuntisID, 'RM_') === 0?
                substr($roomEntry->gpuntisID, 3) : $roomEntry->gpuntisID;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->params['roomName'] = '';
            $this->params['gpuntisID'] = '';
        }
    }

    /**
     * Sets information about the daily schedule
     *
     * @return  void
     */
    private function setScheduleInformation()
    {
<<<<<<< HEAD
<<<<<<< HEAD
        // For testing
        //$this->params['date'] = getDate(strtotime('06.11.2014'));
        $this->params['date'] = getdate(time());
=======
        $this->params['date'] = getDate(strtotime('06.11.2014'));
        //$this->params['date'] = getdate(time());
>>>>>>> 25255b1... added language constants
=======
        // For testing
        //$this->params['date'] = getDate(strtotime('06.11.2014'));
        $this->params['date'] = getdate(time());
>>>>>>> 425c66e... moved field path add from form xml to model because there are more than one
        $this->_dbDate = date('Y-m-d', $this->params['date'][0]);
        $this->setSchedules();
        if (count($this->_schedules))
        {
            $this->getBlocks();
        }
        $this->setInformation();
        $this->setAppointments();
        $this->setUpcoming();
    }

    /**
     * Retireves schedules valid for the requested date
     *
     * @return  void
     */
     private function setSchedules()
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
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
        }
        
         if (empty($schedules))
         {
             JFactory::getApplication()->redirect('index.php', JText::_('COM_THM_ORGANIZER_MESSAGE_NO_SCHEDULES_FOR_DATE'), 'error');
         }

         foreach ($schedules as $key => $schedule)
         {
             $schedules[$key] = json_decode($schedule);
         }
         $this->_schedules = $schedules;
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
            if ($period->day == $this->params['date']['wday'])
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
        $lessonFound = false;
        $lessonTitle = $teacherText = '';
        foreach ($this->_schedules as $schedule)
        {
            // No need to reiterate
            if ($lessonFound)
            {
                break;
            }
            foreach ($schedule->calendar->{$this->_dbDate}->$blockID as $lessonID => $rooms)
            {
                // No need to reiterate
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
                    if ($gpuntisID == $this->params['gpuntisID'])
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
                            $lessonTitle .= $lessonName;
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
 
                            $teachers[$schedule->teachers->$key->surname] = $schedule->teachers->$key->surname;
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
            $this->blocks[$blockID]['title'] = JText::_('COM_THM_ORGANIZER_NO_INFORMATION_AVAILABLE');
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
        $longnameCondition = "r.longname = '{$this->params['roomName']}'";
        if (isset($this->blocks[$key]['teacherIDs']))
        {
            $tidCondition = "t.id IN ( '" . implode("', '", $this->blocks[$key]['teacherIDs']) . "' )";
            $query->where("($longnameCondition OR $tidCondition)");
        }
        else
        {
            $query->where($longnameCondition);
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
                    if ($this->params['layout']== 'registered' OR $this->params['layout']== 'events')
                    {
                        $this->blocks[$key]['title'] = substr($appointments[0]['title'], 0, 20);
                    }
                    else
                    {
                        $this->blocks[$key]['title'] = $appointments[0]['title'];
                    }
                    $this->blocks[$key]['extraInformation'] = $this->makeEventTime($appointments[0]);
                    $this->blocks[$key]['eventID'] = $appointments[0]['id'];
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
        $longnameCondition = "r.longname = '{$this->params['roomName']}'";
        if ($this->blocks[$key] != null AND isset($this->blocks[$key]['teacherIDs']))
        {
            $query->where("($longnameCondition OR t.id IN ( '" . implode("', '", $this->blocks[$key]['teacherIDs']) . "' ) )");
        }
        else
        {
            $query->where($longnameCondition);
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
            }
            if ($this->params['layout']!= 'default')
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
        $query->where("r.longname = '{$this->params['roomName']}'");
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
}
