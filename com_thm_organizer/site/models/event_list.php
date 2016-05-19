<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelRoom_display
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
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

    private $_startDate;

    private $_endDate;

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
        $query = $app->input->server->get('QUERY_STRING', '', 'raw');
        $query .= (strpos($query, 'com_thm_organizer') !== false)? '' : '&option=com_thm_organizer';
        $query .= (strpos($query, 'event_list') !== false)? '' : '&view=event_list';
        $query .='&tmpl=component';
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
        $this->setDates();
        $this->setScheduleIDs();
        $this->events = array();
        if (empty($this->rooms) OR empty($this->_schedules))
        {
            return;
        }

        foreach ($this->_schedules AS $id)
        {
            $scheduleEntry = JTable::getInstance('schedules', 'thm_organizerTable');
            $scheduleEntry->load($id);
            $this->_currentSchedule = json_decode($scheduleEntry->schedule);
            $this->setScheduleData();
        }

        ksort($this->events);
        $this->cleanEventBlockData();
        $this->sortEventBlockData();
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
        foreach (array_keys($this->rooms) AS $roomID)
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

        if ( $this->params['layout'] == 'registered')
        {
            $this->params['roomName'] = array_shift(array_values($rooms));
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
     * Sets the dates used
     *
     * @return  void  sets object variables $_startDate and $_endDate
     */
    private function setDates()
    {
        $date = getdate(time());
        $startDate = $this->params->get('startdate', '');
        $this->_startDate = empty($startDate)? date('Y-m-d', $date[0]) : $startDate;
        $endDate = $this->params->get('enddate', '');
        $this->_endDate = empty($endDate)? '' : $endDate;
    }

    /**
     * Retrieves schedules valid for the requested date
     *
     * @return  void
     */
    private function setScheduleIDs()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__thm_organizer_schedules");
        $query->where("startdate <= '$this->_startDate'");
        $qEndDate = empty($this->_endDate)? $this->_startDate : $this->_endDate;
        $query->where("enddate >= '$qEndDate'");
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

        $this->_schedules = $schedules;
    }

    /**
     * Starts the process of setting events from a schedule
     *
     * @return  void  sets object variables
     */
    private function setScheduleData()
    {
        $calendar = $this->_currentSchedule->calendar;
        $startStamp = strtotime($this->_startDate);
        $endStamp = empty($this->_endDate)? '' : strtotime($this->_endDate);
        foreach (array_keys((array) $calendar) AS $date)
        {
            // We aren't interested in past dates
            $dateStamp = strtotime($date);
            if ($dateStamp < $startStamp)
            {
                continue;
            }

            if (!empty($endStamp) AND $dateStamp > $endStamp)
            {
                break;
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
        $dateEvents = $this->_currentSchedule->calendar->{$this->_currentDate};
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
     * Sets the event data for the block being iterated
     *
     * @return  void  sets object variables
     */
    private function setBlockData()
    {
        $blockEvents = $this->_currentSchedule->calendar->{$this->_currentDate}->{$this->_currentBlock};
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
        $schedule = $this->_currentSchedule;
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

        $event = $this->_currentSchedule->lessons->{$this->_currentEvent};

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
            $this->setEventComment();
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
        $event = $this->_currentSchedule->calendar->{$this->_currentDate}->{$this->_currentBlock}->{$this->_currentEvent};

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
        $event = $this->_currentSchedule->lessons->{$this->_currentEvent};
        $names = $this->_currentSchedule->subjects;

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
        $event = $this->_currentSchedule->lessons->{$this->_currentEvent};

        $types = $this->_currentSchedule->methods;
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
    private function setEventComment()
    {
        $event = $this->_currentSchedule->lessons->{$this->_currentEvent};
        $comment = empty($event->comment)? '' : $event->comment;
        $this->events[$this->_currentDate][$this->_currentEvent]['comment'] = $comment;
    }

    /**
     * Sets the event grid. Used later for determining block times.
     *
     * @return  void  sets an object variable
     */
    private function setEventGrid()
    {
        $event = $this->_currentSchedule->lessons->{$this->_currentEvent};
        $grid = empty($event->grid)? 'Haupt-Zeitraster' : $event->grid;
        $this->events[$this->_currentDate][$this->_currentEvent]['grid'] = $grid;
    }

    /**
     * Sets block specific data such as times and rooms.
     *
     * @param   array  $blockRooms  the rooms found by the previous relevance check
     *
     * @return  void  sets object variables
     */
    private function setEventBlockData($blockRooms)
    {
        $blockData = array();
        $gridName = $this->events[$this->_currentDate][$this->_currentEvent]['grid'];
        $gridBlock = $this->_currentSchedule->periods->{$gridName}->{$this->_currentBlock};
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
        $event = $this->_currentSchedule->lessons->{$this->_currentEvent};
        $allSpeakers = $this->_currentSchedule->teachers;

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

    /**
     * Consolidates events that take place in multiple sequential blocks
     *
     * @return  void  sets object variables
     */
    private function cleanEventBlockData()
    {
        foreach ($this->events AS $date => $events)
        {
            foreach ($events AS $key => $value)
            {
                if (count($value['blocks']) === 1)
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

                    // The block data for the events is divergent
                    if (!$sameRooms OR !$sameSpeakers)
                    {
                        $comparisonKey = $number;
                        $comparisonValue = $data;
                        continue;
                    }

                    // Events are sequential
                    if (($number - 1) == $comparisonKey)
                    {
                        $comparisonValue['endtime'] = $data['endtime'];

                        // Update the item
                        $this->events[$date][$key]['blocks'][$number] = $comparisonValue;

                        // Remove the item being compared to
                        unset($this->events[$date][$key]['blocks'][$comparisonKey]);

                        $comparisonKey = $number;
                        continue;
                    }
                }
            }
        }
    }

    /**
     * Sorts the events according to their starting time and rooms
     *
     * @return  void  sets object variables
     */
    private function sortEventBlockData()
    {
        /**
         * Compares the values of two arrays
         *
         * @param   array  $one  the first array
         * @param   array  $two  the second array
         *
         * @return  int  see strcmp
         */
        function compareEvents($one, $two)
        {
            $oneBlocks = $one["blocks"];
            $oneBlock = array_shift($oneBlocks);
            $twoBlocks = $two["blocks"];
            $twoBlock = array_shift($twoBlocks);
            $startTimeComparison = strcmp($oneBlock['starttime'], $twoBlock["starttime"]);
            if ($startTimeComparison !== 0)
            {
                return $startTimeComparison;
            }

            $oneRooms = implode(', ', $oneBlock['rooms']);
            $twoRooms = implode(', ', $twoBlock['rooms']);
            return strcmp($oneRooms, $twoRooms);
        }

        foreach ($this->events AS $date => $events)
        {
            uasort($events, 'compareEvents');
            $this->events[$date] = $events;
        }
    }
}
