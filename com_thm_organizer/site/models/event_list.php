<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelRoom_display
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

define('SCHEDULE', 1);
define('ALTERNATING', 2);
define('CONTENT', 3);

/**
 * Retrieves lesson and event data for a single room and day
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelEvent_List extends JModelLegacy
{
    public $params = array();

    public $events = array();

    public $rooms = array();

    private $_days = array();

    private $_schedules;

    private $_currentSchedule;

    private $_currentDate;

    private $_currentBlock;

    private $_currentEvent;

    private $_dbDate;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $app = JFactory::getApplication();
        $this->params = $app->getParams();


        $registeredRoom = $this->hasRegisteredRoom();
        if (!empty($registeredRoom))
        {
            // Registered views should always be displayed in the component 'template'
            $templateSet = $app->input->getString('tmpl', '') == 'component';
            if (!$templateSet)
            {
                $this->redirectToComponentTemplate();
            }
            $this->params['layout'] = 'registered';
            $this->rooms = array($registeredRoom);
            $this->_days = array(1, 2, 3, 4, 5, 6, 0);
        }
        else
        {
            $this->params['layout'] = 'default';

            // All rooms
            if (count($this->params['rooms']) === 1 AND empty($this->params['rooms'][0]))
            {
                $this->rooms = false;
            }
            else
            {
                $this->rooms = $this->params['rooms'];
            }

            // All days
            if (count($this->params['days']) === 1 AND empty($this->params['days'][0]))
            {
                $this->_days = array(1, 2, 3, 4, 5, 6, 0);
            }
            else
            {
                $this->_days = $this->params['days'];
            }
        }

        $this->setEvents();
    }

    /**
     * Checks whether the accessing agent is a registered monitor
     *
     * @return  mixed  int roomID on success, otherwise boolean false
     */
    private function hasRegisteredRoom()
    {
        $ipData = array('ip' => JFactory::getApplication()->input->server->getString('REMOTE_ADDR', ''));
        $monitorEntry = JTable::getInstance('monitors', 'thm_organizerTable');
        $registered = $monitorEntry->load($ipData);
        if (!$registered)
        {
            return false;
        }
        $roomID = $monitorEntry->roomID;
        if (empty($roomID))
        {
            return false;
        }
        return $roomID;
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
     * Sets the events for display
     *
     * @return  void  sets object variables
     */
    private function setEvents()
    {
        $this->setRoomInformation();
        $this->setDate();
        $this->setSchedules();
        $this->events = array();
        if (empty($this->rooms) OR empty($this->_schedules))
        {
            return;
        }

        foreach ($this->_schedules AS $key => $schedule)
        {
            $this->_currentSchedule = $key;
            $this->setScheduleData();
        }
        $this->cleanEventBlockData();
    }

    /**
     * Retrieves the name and id of the room
     *
     * @return  void  sets object variables
     */
    private function setRoomInformation()
    {
        if (empty($this->rooms))
        {
            $roomIDs = $this->getAllRoomIDs();
            if (empty($roomIDs))
            {
                return;
            }
            $this->rooms = $roomIDs;
        }

        // Flips the array so that more room information can be associated with the roomIDS
        $this->rooms = array_flip($this->rooms);

        $roomEntry = JTable::getInstance('rooms', 'thm_organizerTable');
        $rooms = array();
        foreach ($this->rooms AS $roomID => $roomValue)
        {
            try
            {
                $roomEntry->load($roomID);
                $untisID = strpos($roomEntry->gpuntisID, 'RM_') === 0? substr($roomEntry->gpuntisID, 3) : $roomEntry->gpuntisID;
                $roomName = $roomEntry->longname;
                $rooms[$untisID] = $roomName;
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
                unset($this->rooms[$roomID]);
            }
        }
        $this->rooms = $rooms;
    }

    /**
     * Retrieves all roomIDs
     *
     * @return  mixed  array of roomIDS on success, otherwise false
     */
    private function getAllRoomIDs()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')->from('#__thm_organizer_rooms');
        $dbo->setQuery((string) $query);
        return $dbo->loadColumn();
    }

    /**
     * Sets the date used for comparison
     */
    private function setDate()
    {
        $date = getdate(time());
        $this->_dbDate = date('Y-m-d', $date[0]);
    }

    /**
     * Retrieves schedules valid for the requested date
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
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return;
        }

        if (empty($schedules))
        {
            JFactory::getApplication()->redirect('index.php', JText::_('COM_THM_ORGANIZER_MESSAGE_NO_SCHEDULES_FOR_DATE'), 'error');
            return;
        }

        foreach ($schedules as $key => $schedule)
        {
            $schedules[$key] = json_decode($schedule);
        }
        $this->_schedules = $schedules;
    }

    /**
     * Starts the process of setting events from a schedule
     *
     * @return  void  sets object variables
     */
    private function setScheduleData()
    {
        $calendar = $this->_schedules[$this->_currentSchedule]->calendar;
        $currentStamp = strtotime($this->_dbDate);
        foreach ($calendar AS $date => $blocks)
        {
            // We aren't interested in past dates
            $dateStamp = strtotime($date);
            if ($dateStamp < $currentStamp)
            {
                continue;
            }

            // We are only interested in selected weekdays
            $dowNumber = date('w', $dateStamp);
            if (!in_array($dowNumber, $this->_days))
            {
                continue;
            }

            $this->_currentDate = $date;

            $this->setDateData();
        }
    }

    /**
     * Processes schedule information for the date being iterated
     *
     * @return  void  sets object variables
     */
    private function setDateData()
    {
        $schedule = $this->_schedules[$this->_currentSchedule];
        $dateEvents = $schedule->calendar->{$this->_currentDate};
        foreach ($dateEvents as $block => $events)
        {
            if (empty($events))
            {
                continue;
            }

            $this->_currentBlock = $block;
            $this->setBlockData();
        }
    }

    /**
     * 
     */
    private function setBlockData()
    {
        $schedule = $this->_schedules[$this->_currentSchedule];
        $blockEvents = $schedule->calendar->{$this->_currentDate}->{$this->_currentBlock};
        foreach ($blockEvents as $event => $rooms)
        {
            if (empty($rooms))
            {
                continue;
            }

            $this->_currentEvent = $event;
            $this->setEventData();
        }
    }

    /**
     * Sets the event data for the current date
     *
     * @return  void  sets object variables
     */
    private function setEventData()
    {
        $schedule = $this->_schedules[$this->_currentSchedule];
        $event = $schedule->calendar->{$this->_currentDate}->{$this->_currentBlock}->{$this->_currentEvent};

        // The event block has been removed
        $irrelevant = (!empty($event->delta) AND $event->delta == 'removed');
        if ($irrelevant)
        {
            return;
        }

        $blockRooms = $this->getRelevantBlockRooms();

        // No block rooms were relevant
        if (empty($blockRooms))
        {
            return;
        }

        $event = $this->_schedules[$this->_currentSchedule]->lessons->{$this->_currentEvent};

        // Events which have been removed are not relevant.
        if (!empty($event->delta) AND $event->delta == 'removed')
        {
            return;
        }

        if (empty($this->events[$this->_currentDate]))
        {
            $this->events[$this->_currentDate] = array();
        }

        if (empty($this->events[$this->_currentDate][$this->_currentEvent]))
        {
            $this->events[$this->_currentDate][$this->_currentEvent] = array();

            // These need only be called once per lesson
            $this->events[$this->_currentDate][$this->_currentEvent]['organization'] = $schedule->departmentname;
            $this->setEventName();
            $this->setEventType();
            $this->setEventDescription();
            $this->setEventGrid();
            $this->events[$this->_currentDate][$this->_currentEvent]['blocks'] = array();
        }

        $this->setEventBlockData($blockRooms);
    }

    /**
     * Gets the event rooms which are relevant for the view
     *
     * @return  array  empty if no rooms are relevant in the iterated block
     */
    private function getRelevantBlockRooms()
    {
        $event = $this->_schedules[$this->_currentSchedule]->calendar->{$this->_currentDate}->{$this->_currentBlock}->{$this->_currentEvent};

        $relevantRooms = array();
        foreach ($event as $roomKey => $delta)
        {
            //  The delta index is not a valid room. Rooms which have been removed fom events are not relevant in this view.
            if ($roomKey == 'delta' OR $delta == 'removed')
            {
                continue;
            }

            if (!empty($this->rooms[$roomKey]))
            {
                $relevantRooms[$roomKey] = $this->rooms[$roomKey];
            }
        }
        return $relevantRooms;
    }

    /**
     * Sets the event name
     *
     * @return  void  sets an object variable
     */
    private function setEventName()
    {
        $event = $this->_schedules[$this->_currentSchedule]->lessons->{$this->_currentEvent};
        $names = $this->_schedules[$this->_currentSchedule]->subjects;

        $name = '';
        foreach ($event->subjects AS $nameKey => $delta)
        {
            if (!empty($delta) AND $delta == 'removed')
            {
                continue;
            }

            if (!empty($names->$nameKey) AND !empty($names->$nameKey->longname))
            {
                if (!empty($name))
                {
                    $name .= ' / ';
                }

                $name .= $names->$nameKey->longname;
            }
        }
        $this->events[$this->_currentDate][$this->_currentEvent]['name'] = $name;
    }

    /**
     * Sets the event type
     *
     * @return  void  sets an object variable
     */
    private function setEventType()
    {
        $event = $this->_schedules[$this->_currentSchedule]->lessons->{$this->_currentEvent};
        $types = $this->_schedules[$this->_currentSchedule]->lessontypes;
        $invalidType = (empty($event->description) OR empty($types->{$event->description}) OR empty($types->{$event->description}->name));
        if ($invalidType)
        {
            $type = '';
        }
        else
        {
            $type = $types->{$event->description}->name;
        }
        $this->events[$this->_currentDate][$this->_currentEvent]['type'] = $type;
    }

    /**
     * Sets the event description
     *
     * @return  void  sets an object variable
     */
    private function setEventDescription()
    {
        $event = $this->_schedules[$this->_currentSchedule]->lessons->{$this->_currentEvent};
        $description = empty($event->comment)? '' : $event->comment;
        $this->events[$this->_currentDate][$this->_currentEvent]['description'] = $description;
    }

    /**
     * Sets the event grid. Used later for determining block times.
     *
     * @return  void  sets an object variable
     */
    private function setEventGrid()
    {
        $event = $this->_schedules[$this->_currentSchedule]->lessons->{$this->_currentEvent};
        $grid = empty($event->grid)? 'Haupt-Zeitraster' : $event->grid;
        $this->events[$this->_currentDate][$this->_currentEvent]['grid'] = $grid;
    }

    /**
     * Sets block specific data such as times and rooms.
     *
     * @param  $blockRooms  the rooms found by the previous relevance check
     *
     * @return  void  sets object variables
     */
    private function setEventBlockData($blockRooms)
    {
        $blockData = array();
        $gridName = $this->events[$this->_currentDate][$this->_currentEvent]['grid'];
        $gridBlock = $this->_schedules[$this->_currentSchedule]->periods->{$gridName}->{$this->_currentBlock};
        $blockData['starttime'] = $gridBlock->starttime;
        $blockData['endtime'] = $gridBlock->endtime;
        $blockData['rooms'] = $blockRooms;
        $blockData['speakers'] = $this->getEventBlockTeachers();
        $this->events[$this->_currentDate][$this->_currentEvent]['blocks'][$this->_currentBlock] = $blockData;
    }

    /**
     * Gets the speakers for the iterated block. This is done by the block in the assumption that Untis will eventually
     * have this feature for sporadic events.
     *
     * @return  array  the teachers for the given block
     */
    private function getEventBlockTeachers()
    {
        $event = $this->_schedules[$this->_currentSchedule]->lessons->{$this->_currentEvent};
        $allSpeakers = $this->_schedules[$this->_currentSchedule]->teachers;

        $speakers = array();
        foreach ($event->teachers AS $speakerKey => $delta)
        {
            if (!empty($delta) AND $delta == 'removed')
            {
                continue;
            }

            if (!empty($allSpeakers->$speakerKey))
            {
                $speakers[$speakerKey]['surname'] = $allSpeakers->$speakerKey->surname;
                $speakers[$speakerKey]['forename'] = $allSpeakers->$speakerKey->forename;
            }
        }
        return $speakers;
    }

    private function cleanEventBlockData()
    {
        foreach ($this->events AS $date => $events)
        {
            foreach ($events AS $key => $value)
            {
                if (count($value['blocks'] === 1))
                {
                    continue;
                }

                $comparisonKey = 0;
                $comparisonValue = array();
                foreach ($value['blocks'] AS $number => $data)
                {
                    // Initialize
                    if (empty($comparisonValue))
                    {
                        $comparisonKey = $number;
                        $comparisonValue = $data;
                        continue;
                    }

                    $sameRooms = $comparisonValue['rooms'] === $data['rooms'];
                    $sameSpeakers = $comparisonValue['speakers'] === $data['speakers'];
                    if (!$sameRooms OR !$sameSpeakers)
                    {
                        $comparisonKey = $number;
                        $comparisonValue = $data;
                        continue;
                    }

                    if (($number -1) == $comparisonKey)
                    {
                        $comparisonKey = $number;
                        $comparisonValue['endtime'] = $data['endtime'];
                        continue;
                    }
                }
            }
        }
    }
}
