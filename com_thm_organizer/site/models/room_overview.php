<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelRoom_Overview
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Retrieves lesson and event data for a single room and day
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelRoom_Overview extends JModelLegacy
{
    public $startDate = array();

    public $endDate = array();

    private $_scheduleIDs = array();

    private $_currentSchedule = array();

    public $grid = array();

    public $data = array();

    public $rooms = array();

    public $types = array();

    public $selectedRooms = array();

    /**
     * Constructor
     *
     * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
     *
     * @since   12.2
     * @throws  Exception
     */
    public function __construct($config = array())
    {
        parent::__construct();
        $this->populateState();
        $this->getRoomData();
        $this->getData();
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();
        $input = $app->input;

        $menuID = $input->getInt('Itemid');
        $this->state->set('menuID', $menuID);

        $formData = $input->get('jform', array(), 'array');
        $this->cleanFormData($formData);
        $this->state->set('template', $formData['template']);
        $this->state->set('date', $formData['date']);
        $this->state->set('types', $formData['types']);
        $this->state->set('rooms', $formData['rooms']);
    }

    /**
     * Cleans form data.
     *
     * @param   array  &$data  the data received from the form
     *
     * @return  void  modifies &$data
     */
    private function cleanFormData(&$data)
    {
        $format = JFactory::getApplication()->getParams()->get('dateFormat', 'd.m.Y');
        $defaultDate = date($format);

        if (empty($data))
        {
            $data['template'] = 1;
            $data['date'] = $defaultDate;
            $data['types'] = array('-1');
            $data['rooms'] = array('-1');
            return;
        }

        $validTemplates = array(1, 2, 3);
        $reqTemplate = empty($data['template'])? 1 : $data['template'];
        $validTemplate = (is_numeric($reqTemplate) AND in_array($reqTemplate, $validTemplates));
        $data['template'] = $validTemplate? $reqTemplate : 1;

        $reqDate = empty($data['date'])? $defaultDate : $data['date'];
        $validDate = strtotime($reqDate) !== false;
        $data['startDate'] = $validDate? date($format, strtotime($reqDate)) : $defaultDate;

        if (empty($data['types']))
        {
            $data['types'] = array('-1');
        }

        if (empty($data['rooms']))
        {
            $data['rooms'] = array('-1');
        }
    }

    /**
     * Sets the data object variable with corresponding room information
     *
     * @return  void  modifies the object data variable
     */
    private function getData()
    {
        $template = $this->state->get('template');
        $date = THM_OrganizerHelperComponent::standardizeDate($this->state->get('date'));
        switch ($template)
        {
            case DAY:
                $this->startDate = $this->endDate = $date;
                break;

            case WEEK:
                $this->startDate = date('Y-m-d', strtotime('monday this week',strtotime($date)));
                $this->endDate = date('Y-m-d', strtotime('sunday this week',strtotime($date)));
                break;
        }

        $this->setScheduleIDs();
        $this->setGrid();

        switch ($template)
        {
            case DAY:
                $this->getDay($date);
                break;

            case WEEK:
                $this->getInterval();
                break;
        }
    }

    /**
     * Finds and sets the relevant schedule IDs in the corresponding object variable
     *
     * @return  void  sets the object variable $_scheduleIDs
     */
    private function setScheduleIDs()
    {
        // All active schedules which overlap the given date interval
        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT id')->from('#__thm_organizer_schedules');
        $query->where("active = '1'");
        $query->where("term_startdate <= '$this->startDate'");
        $query->where("term_enddate >= '$this->endDate'");
        $this->_db->setQuery((string) $query);

        try
        {
            $this->_scheduleIDs = $this->_db->loadColumn();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            $this->_scheduleIDs = array();
        }
    }

    /**
     * Gets the main grid from the first schedule
     *
     * @return  void  sets the object grid variable
     */
    private function setGrid()
    {
        $schedule = $this->getSchedule($this->_scheduleIDs[0]);

        if (empty($schedule))
        {
            // $_grid is already an empty array per default
            return;
        }

        $rawGrid = (array) $schedule->periods->{'Haupt-Zeitraster'};
        $grid = array();
        foreach ($rawGrid as $block => $times)
        {
            $grid[$block] = (array) $times;
        }

        $this->grid = $grid;
    }

    /**
     * Retrieves a schedule object
     *
     * @param   int  $scheduleID  the id of the schedule to be retrieved
     *
     * @return  mixed  object on success, otherwise null
     */
    private function getSchedule($scheduleID)
    {
        $query = $this->_db->getQuery(true);
        $query->select('schedule')->from('#__thm_organizer_schedules');
        $query->where("id = '$scheduleID'");
        $this->_db->setQuery((string) $query);

        try
        {
            $rawSchedule = $this->_db->loadResult();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return null;
        }

        return json_decode($rawSchedule);
    }

    /**
     * Gets the room information for a week
     *
     * @return  array  room information for the given day
     */
    private function getInterval()
    {
        $dateFormat = JFactory::getApplication()->getParams()->get('dateFormat', 'Y-m-d');
        $currentDT = strtotime($this->startDate);
        $endDT =strtotime($this->endDate);
        for ($currentDT; $currentDT <= $endDT; $currentDT = strtotime('+1 day', $currentDT))
        {
            $currentDate = THM_OrganizerHelperComponent::standardizeDate(date($dateFormat, $currentDT));
            $this->getDay($currentDate);
        }
    }

    /**
     * Gets the room information for a day
     *
     * @param   string  $date  the date string
     *
     * @return  void  room information for the given day is added to the $blocks object variable
     */
    private function getDay($date)
    {
        $blocks = $this->grid;
        foreach ($this->_scheduleIDs as $scheduleID)
        {
            $this->_currentSchedule = $this->getSchedule($scheduleID);
            if (empty($this->_currentSchedule->calendar->$date))
            {
                continue;
            }

            $dateBlocks = (array) $this->_currentSchedule->calendar->$date;
            foreach ($dateBlocks as $blockNo => $events)
            {
                $this->getEvents($blocks, $blockNo, $events);
            }

            unset($this->_currentSchedule);
        }

        $eventsExist = $this->postProcessBlocks($blocks);
        if ($eventsExist)
        {
            $this->data[$date] = $blocks;
            return;
        }

        // Adds empty business days and sundays when explicitly called
        $template = $this->state->get('template');
        $isSunday = date('l', strtotime($date)) == 'Sunday';
        $makeBlank = (!$isSunday OR ($template == DAY AND $isSunday));
        if ($makeBlank)
        {
            $this->data[$date] = $blocks;
        }
    }

    /**
     * Sets event information for the given block in the given schedule
     *
     * @param   array   &$blocks    the array where the information is stored
     * @param   int     $blockNo    the index of the block being iterated
     * @param   object  $events     the events in the block being iterated
     *
     * @return  void  modifies &$blocks
     */
    private function getEvents(&$blocks, $blockNo, $events)
    {
        foreach ($events as $eventNo => $rooms)
        {
            $eventRemoved = (!empty($rooms->delta) AND $rooms->delta == 'removed');
            if ($eventRemoved)
            {
                continue;
            }

            foreach ($rooms as $roomNo => $delta)
            {
                // Name != Display Name :(
                $requested = array_key_exists($roomNo, $this->selectedRooms);

                $roomRemoved = (!empty($delta) AND $delta == 'removed');
                $irrelevant = (!$requested OR $roomRemoved OR $roomNo == 'delta');
                if ($irrelevant)
                {
                    continue;
                }

                $this->getEvent($blocks, $blockNo, $eventNo, $roomNo);
            }
        }
    }

    /**
     * Sets event information for the individual room
     *
     * @param   array   &$blocks  the array where the information is stored
     * @param   int     $blockNo  the index of the block being iterated
     * @param   string  $eventNo  the event identifier
     * @param   string  $roomNo   the room no. in which the event takes place
     *
     * @return  void  modifies &$blocks
     */
    private function getEvent(&$blocks, $blockNo, $eventNo, $roomNo)
    {
        $eventObject = $this->_currentSchedule->lessons->$eventNo;
        $eventArray = array();
        $eventArray['department'] = $this->_currentSchedule->departmentname;
        $eventArray['title'] = $eventObject->name;
        $eventArray['speakers'] = $this->getSpeakers($eventObject->teachers);
        $eventArray['comment'] = $eventObject->comment;
        $roomName = $this->_currentSchedule->rooms->$roomNo->longname;

        if ($eventObject->grid == 'Haupt-Zeitraster')
        {
            if (!isset($blocks[$blockNo][$roomName]))
            {
                $blocks[$blockNo][$roomName] = array();
            }

            $blocks[$blockNo][$roomName][$eventNo] = $eventArray;
            return;
        }

        $eventArray['eventNo'] = $eventNo;
        $eventArray['roomName'] = $roomName;
        $eventArray['grid'] = $eventObject->grid;
        $this->setDivEvent($blocks, $blockNo, $eventArray);
    }

    /**
     * Sets information for events whose times are divergent from the display grid.
     *
     * @param   array  &$blocks    the array where the information is stored
     * @param   int    $blockNo    the index of the block being iterated
     * @param   array  $eventArray
     *
     * @return  void  modifies &$blocks
     */
    private function setDivEvent(&$blocks, $blockNo, &$eventArray)
    {
        // Event has the same block number, but does not belong to the same grid
        $grid = $this->_currentSchedule->periods->{$eventArray['grid']};
        $eventNo = $eventArray['eventNo'];
        $roomName = $eventArray['roomName'];
        $gStartTime = $grid->$blockNo->starttime;
        $stText = THM_OrganizerHelperComponent::formatTime($gStartTime);
        $gEndTime = $grid->$blockNo->endtime;
        $etText = THM_OrganizerHelperComponent::formatTime($gEndTime);
        $timeText = "$stText - $etText";

        $blocksCount = count($blocks);
        for ($blockIndex = 1; $blockIndex <= $blocksCount; $blockIndex++)
        {
            $bStartTime = $blocks[$blockIndex]['starttime'];
            $bEndTime = $blocks[$blockIndex]['endtime'];
            $beginsIn = ($gStartTime == $bStartTime OR ($gStartTime > $bStartTime AND $gStartTime < $bEndTime));
            $endsIn = ($gEndTime == $bEndTime OR ($gEndTime > $bStartTime AND $gEndTime < $bEndTime));
            $overlaps = ($gStartTime < $bStartTime AND $gEndTime > $bEndTime);
            $relevant = ($beginsIn OR $endsIn OR $overlaps);

            if (!$relevant)
            {
                continue;
            }

            if (!isset($blocks[$blockIndex][$roomName]))
            {
                $blocks[$blockIndex][$roomName] = array();
            }

            // Doesn't already exist
            if (!isset($blocks[$blockIndex][$roomName][$eventNo]))
            {
                $blocks[$blockIndex][$roomName][$eventNo] = $eventArray;
                $blocks[$blockIndex][$roomName][$eventNo]['divTime'] = $timeText;
                continue;
            }

            // The case that a divergent lesson exists but has no time text should not actually occur
            if (empty($blocks[$blockIndex][$roomName][$eventNo]['divTime']))
            {
                $blocks[$blockIndex][$roomName][$eventNo]['divTime'] = $timeText;
                continue;
            }

            // Append existing entry if two divergent blocks have relevance for the
            $append = strpos($blocks[$blockIndex][$roomName][$eventNo]['divTime'], $timeText) === false;
            if ($append)
            {
                $blocks[$blockIndex][$roomName][$eventNo]['divTime'] .= ", $timeText";
            }
        }
    }

    /**
     * Gets speaker information for the event being iterated
     *
     * @param   object  $speakers  the object containing the speaker references
     *
     * @return  string  contains teacher information for output
     */
    private function getSpeakers($speakers)
    {
        $speakersArray = array();
        foreach ($speakers as $key => $delta)
        {
            $speakerRemoved = (!empty($delta) AND $delta == 'removed');
            $speakerExists = !empty($speakersArray[$key]);
            $irrelevant = ($speakerRemoved OR $speakerExists);
            if ($irrelevant)
            {
                continue;
            }

            $speaker = $this->_currentSchedule->teachers->$key;
            $speakerText = $speaker->surname;
            $speakerText .= empty($speaker->forename)? '' : ', ' . $speaker->forename;
            $speakerText .= empty($speaker->title)? '' : ', ' . $speaker->title;

            $speakersArray[$key] = $speakerText;
        }

        return implode(' / ', $speakersArray);
    }

    /**
     * Sorts the rooms in the blocks and compiles a list of rooms that are used.
     *
     * @param   array  &$blocks  the container for daily events
     *
     * @return  void  modifies &$blocks and the object variable $rooms
     */
    private function postProcessBlocks(&$blocks)
    {
        $eventsExist = false;
        foreach ($blocks as $blockNo => $rooms)
        {
            unset($rooms['delta']);
            unset($rooms['endtime']);
            unset($rooms['starttime']);
            if (!count($rooms))
            {
                continue;
            }

            $eventsExist = true;
            ksort($rooms);
            $blocks[$blockNo] = $rooms;
            $roomKeys = array_keys($rooms);
            $this->rooms = array_unique(array_merge($this->rooms, $roomKeys));
            asort($this->rooms);
        }

        return $eventsExist;
    }

    /**
     * Gets the rooms and relevant room types
     *
     * @return  void  sets the rooms and types object variables
     */
    private function getRoomData()
    {
        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT r.id AS roomID, r.name, r.longname, t.id AS typeID, t.type, t.subtype');
        $query->from('#__thm_organizer_room_types AS t');
        $query->innerJoin('#__thm_organizer_rooms AS r ON r.typeID = t.id');
        $query->order('r.longname ASC');
        $this->_db->setQuery((string) $query);

        try
        {
            $results = $this->_db->loadAssocList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            $this->rooms = array();
            $this->types = array();
            return;
        }

        $allTypes = in_array('-1', $this->state->types);
        $filterTypes = (!$allTypes AND count($this->state->types) > 0)? $this->state->types : false;
        $allRooms = in_array('-1', $this->state->rooms);
        $filterRooms = (!$allRooms AND count($this->state->rooms) > 0)? $this->state->rooms : false;
        $rooms = $types = array();
        foreach ($results as $room)
        {
            /**
             * Some types will be overwritten, but checking if the index/value is already set is unnecessary.
             *
             * Types are not further filtered.
             */
            $typeText = $room['type'];
            $typeText .= empty($room['subtype'])? '' : ', ' .$room['subtype'];
            $types[$room['typeID']] = $typeText;

            $this->filterRoom($room, $filterTypes, $filterRooms, $rooms);
        }

        // Rooms were sorted in the query
        asort($types);
        $this->types = $types;
        $this->rooms = $rooms;
    }

    /**
     * Filters rooms then adds them to the array as appropriate
     *
     * @param   array  &$room        the room being iterated
     * @param   mixed  $filterTypes  array of type IDs to be filtered against, otherwise false
     * @param   mixed  $filterRooms  array of room IDs to be filtered against, otherwise false
     * @param   array  &$rooms       the array containing the filter results
     *
     * @return  void  modifies &$rooms
     */
    private function filterRoom(&$room, $filterTypes, $filterRooms, &$rooms)
    {
        $typeOK = $roomOK = true;
        if ($filterTypes AND !in_array($room['typeID'], $filterTypes))
        {
            $typeOK = false;
        }
        elseif (!$filterTypes AND $filterRooms)
        {
            $typeOK = false;
        }

        if ($filterRooms AND !in_array($room['name'], $filterRooms))
        {
            $roomOK = false;
        }
        elseif (!$filterRooms AND $filterTypes)
        {
            $roomOK = false;
        }

        $rooms[$room['name']] = $room['longname'];

        $add = ($typeOK OR $roomOK);
        if ($add)
        {
            $this->selectedRooms[$room['name']] = $room['longname'];
        }
    }
}
