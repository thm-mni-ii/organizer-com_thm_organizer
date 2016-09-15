<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelRoom_Overview
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';

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
	 * @param   array $config An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct();
		$this->populateState();
		$this->setRoomData();
		$this->setGrid();
		$this->setData();
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string $ordering  An optional ordering field.
	 * @param   string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app   = JFactory::getApplication();
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
	 * @param   array &$data the data received from the form
	 *
	 * @return  void  modifies &$data
	 */
	private function cleanFormData(&$data)
	{
		$format      = JFactory::getApplication()->getParams()->get('dateFormat', 'd.m.Y');
		$defaultDate = date($format);

		if (empty($data))
		{
			$data['template'] = 1;
			$data['date']     = $defaultDate;
			$data['types']    = array('-1');
			$data['rooms']    = array('-1');

			return;
		}

		$validTemplates   = array(1, 2, 3);
		$reqTemplate      = empty($data['template']) ? 1 : $data['template'];
		$validTemplate    = (is_numeric($reqTemplate) AND in_array($reqTemplate, $validTemplates));
		$data['template'] = $validTemplate ? $reqTemplate : 1;

		$reqDate           = empty($data['date']) ? $defaultDate : $data['date'];
		$validDate         = strtotime($reqDate) !== false;
		$data['startDate'] = $validDate ? date($format, strtotime($reqDate)) : $defaultDate;

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
	private function setData()
	{
		$template = $this->state->get('template');
		$date     = THM_OrganizerHelperComponent::standardizeDate($this->state->get('date'));
		switch ($template)
		{
			case DAY:
				$this->startDate = $this->endDate = $date;
				break;

			case WEEK:
				$this->startDate = date('Y-m-d', strtotime('monday this week', strtotime($date)));
				$this->endDate   = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
				break;
		}

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
		$query->where("startDate <= '$this->startDate'");
		$query->where("endDate >= '$this->endDate'");
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
		$query = $this->_db->getQuery(true);
		$query->select('grid')->from('#__thm_organizer_grids')->where("defaultGrid = '1'");
		$this->_db->setQuery($query);

		try
		{
			$rawGrid = $this->_db->loadResult();;
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return;
		}

		$this->grid = json_decode($rawGrid, true);
	}

	/**
	 * Gets the room information for a week
	 *
	 * @return  array  room information for the given day
	 */
	private function getInterval()
	{
		$dateFormat = JFactory::getApplication()->getParams()->get('dateFormat', 'Y-m-d');
		$endDT      = strtotime($this->endDate);
		for ($currentDT = strtotime($this->startDate); $currentDT <= $endDT; $currentDT = strtotime('+1 day', $currentDT))
		{
			$currentDate = THM_OrganizerHelperComponent::standardizeDate(date($dateFormat, $currentDT));
			$this->getDay($currentDate);
		}
	}

	/**
	 * Gets the room information for a day
	 *
	 * @param   string $date the date string
	 *
	 * @return  void  room information for the given day is added to the $blocks object variable
	 */
	private function getDay($date)
	{
		$isSunday  = date('l', strtotime($date)) == 'Sunday';
		if ($isSunday)
		{
			$template  = $this->state->get('template');
			$getNext = ($template == DAY AND $isSunday);
			if ($getNext)
			{
				$date = date('Y-m-d', strtotime("$date + 1 days"));
			}
			else
			{
				return;
			}
		}

		$events = $this->getEvents($date);
		$blocks = $this->processBlocks($events);

		if (count($blocks))
		{
			$this->data[$date] = $blocks;

			return;
		}
	}

	/**
	 * Sets event information for the given block in the given schedule
	 *
	 * @param   array  &$blocks the array where the information is stored
	 * @param   int    $blockNo the index of the block being iterated
	 * @param   object $events  the events in the block being iterated
	 *
	 * @return  void  modifies &$blocks
	 */
	private function getEvents($date)
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();

		$query = $this->_db->getQuery(true);

		$select = "DISTINCT conf.id, conf.configuration, cal.startTime, cal.endTime, ";
		$select .= "d.short_name_$shortTag AS department, d.id AS departmentID, ";
		$select .= "l.id as lessonID, l.comment, m.abbreviation_$shortTag AS method, ";
		$select .= "ps.name AS psName, s.name_$shortTag AS sName";
		$query->select($select)
			->from('#__thm_organizer_calendar AS cal')
			->innerJoin('#__thm_organizer_calendar_configuration_map AS ccm ON ccm.calendarID = cal.id')
			->innerJoin('#__thm_organizer_lesson_configurations AS conf ON ccm.configurationID = conf.id')
			->innerJoin('#__thm_organizer_lessons AS l ON cal.lessonID = l.id')
			->innerJoin('#__thm_organizer_departments AS d ON l.departmentID = d.id')
			->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id AND conf.lessonID = ls.id')
			->innerJoin('#__thm_organizer_plan_subjects AS ps ON ls.subjectID = ps.id')
			->leftJoin('#__thm_organizer_methods AS m ON l.methodID = m.id')
			->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id')
			->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id')
			->where("cal.schedule_date = '$date'")
			->where("cal.delta != 'removed'")
			->where("l.delta != 'removed'")
			->where("ls.delta != 'removed'");
		$this->_db->setQuery($query);

		try
		{
			$results = $this->_db->loadAssocList();
		}
		catch (Exception $exception)
		{
			JFactory::getApplication()->enqueueMessage(JText::_(), 'error');
			return array();
		}

		$events = array();
		foreach ($results as $result)
		{
			$startTime = substr(str_replace(':', '', $result['startTime']), 0, 4);
			$endTime = substr(str_replace(':', '', $result['endTime']), 0, 4);
			$times = "$startTime-$endTime";

			if (empty($events[$times]))
			{
				$events[$times] = array();
			}

			$lessonID = $result['lessonID'];

			if (empty($events[$startTime][$lessonID]))
			{
				$events[$times][$lessonID] = array();
				$events[$times][$lessonID]['departments'] = array();
				$events[$times][$lessonID]['titles'] = array();
				$events[$times][$lessonID]['speakers'] = array();
				$events[$times][$lessonID]['rooms'] = array();
				$events[$times][$lessonID]['method'] = empty($result['method'])? '' : " - {$result['method']}";
				$events[$times][$lessonID]['startTime'] = $startTime;
				$events[$times][$lessonID]['endTime'] = substr($result['endTime'], 0, 5);
			}

			$events[$times][$lessonID]['departments'][$result['departmentID']] = $result['department'];
			$events[$times][$lessonID]['comment'] = $result['comment'];

			$title = empty($result['sName'])? $result['psName'] : $result['sName'];

			if (!in_array($title, $events[$times][$lessonID]['titles']))
			{
				$events[$times][$lessonID]['titles'][] = $title;
			}

			$configuration = json_decode($result['configuration'], true);

			foreach ($configuration['teachers'] AS $teacherID => $delta)
			{
				$addSpeaker = ($delta != 'removed' AND empty($events[$times][$lessonID]['speakers'][$teacherID]));

				if ($addSpeaker)
				{
					$events[$times][$lessonID]['speakers'][$teacherID] = THM_OrganizerHelperTeachers::getLNFName($teacherID);
				}
			}

			foreach ($configuration['rooms'] AS $roomID => $delta)
			{
				$nonExistent = empty($events[$times][$lessonID]['rooms'][$roomID]);
				$requested = !empty($this->rooms[$roomID]);
				$addRoom = ($delta != 'removed' AND $nonExistent AND $requested);

				if ($addRoom)
				{
					$events[$times][$lessonID]['rooms'][$roomID] = $this->rooms[$roomID];
				}
			}
		}

		return $events;
	}

	/**
	 * Resolves the daily events to their respective grid blocks
	 *
	 * @param array $events the events for the given day
	 *
	 * @return array the blocks
	 */
	private function processBlocks($events)
	{
		$blocks = array();
		foreach ($this->grid['periods'] AS $blockNo => $block)
		{
			$blocks[$blockNo] = array();
			$blockStartTime = $block['startTime'];
			$blockEndTime = $block['endTime'];
			$blocks[$blockNo]['startTime'] = $blockStartTime;
			$blocks[$blockNo]['endTime'] = $blockEndTime;

			foreach ($events as $times => $eventInstances)
			{
				list($eventStartTime, $eventEndTime) = explode('-', $times);
				$before = $eventEndTime < $blockStartTime;
				$after = $eventStartTime > $blockEndTime;

				if ($before OR $after)
				{
					continue;
				}

				$divTime = '';
				$startSynch = $blockStartTime == $eventStartTime;
				$endSynch = $blockEndTime == $eventEndTime;

				if (!$startSynch or !$endSynch)
				{
					$divTime .= THM_OrganizerHelperComponent::formatTime($eventStartTime);
					$divTime .= ' - ';
					$divTime .= THM_OrganizerHelperComponent::formatTime($eventEndTime);
				}

				foreach ($eventInstances as $eventID => $eventInstance)
				{
					$instance = array();
					$instance['department'] = implode(' / ', $eventInstance['departments']);
					$instance['speakers'] = implode(' / ', $eventInstance['speakers']);
					$instance['title'] = implode(' / ', $eventInstance['titles']);
					$instance['title'] .= $eventInstance['method'];
					$instance['comment'] = $eventInstance['comment'];
					$instance['divTime'] = $divTime;

					foreach ($eventInstance['rooms'] as $roomID => $roomName)
					{
						$blocks[$blockNo][$roomID][$eventID] = $instance;
					}
				}
			}
		}
		return $blocks;
	}

	/**
	 * Gets the rooms and relevant room types
	 *
	 * @return  void  sets the rooms and types object variables
	 */
	private function setRoomData()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$query    = $this->_db->getQuery(true);
		$query->select("DISTINCT r.id AS roomID, r.name, r.name AS longname, t.id AS typeID, t.name_$shortTag AS type");
		$query->from('#__thm_organizer_room_types AS t');
		$query->innerJoin('#__thm_organizer_rooms AS r ON r.typeID = t.id');
		$query->order('longname ASC');
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

		$allTypes    = in_array('-1', $this->state->types);
		$filterTypes = (!$allTypes AND count($this->state->types) > 0) ? $this->state->types : false;
		$allRooms    = in_array('-1', $this->state->rooms);
		$filterRooms = (!$allRooms AND count($this->state->rooms) > 0) ? $this->state->rooms : false;
		$rooms       = $types = array();

		foreach ($results as $room)
		{
			/**
			 * Some types will be overwritten, but checking if the index/value is already set is unnecessary.
			 *
			 * Types are not further filtered.
			 */
			$typeText               = $room['type'];
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
	 * @param   array &$room       the room being iterated
	 * @param   mixed $filterTypes array of type IDs to be filtered against, otherwise false
	 * @param   mixed $filterRooms array of room IDs to be filtered against, otherwise false
	 * @param   array &$rooms      the array containing the filter results
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

		if ($filterRooms AND !in_array($room['roomID'], $filterRooms))
		{
			$roomOK = false;
		}
		elseif (!$filterRooms AND $filterTypes)
		{
			$roomOK = false;
		}

		$rooms[$room['roomID']] = $room['longname'];

		$add = ($typeOK OR $roomOK);
		if ($add)
		{
			$this->selectedRooms[$room['roomID']] = $room['longname'];
		}
	}
}
