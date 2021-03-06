<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Organizer\Helpers\Dates;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Persons;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Tables\Monitors as MonitorsTable;
use Organizer\Tables\Rooms as RoomsTable;

/**
 * Class retrieves information about upcoming events for display on monitors.
 */
class EventList extends FormModel
{
	public $events = [];

	public $params = [];

	public $rooms = [];

	private $columnMap = [
		'course'     => 'co.id',
		'department' => 'd.id',
		'category'   => 'cat.id',
		'group'      => 'gr.id'
	];

	private $dates = [];

	private $days = [];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->setParams();
		$this->populateState();
		$this->setRooms();
		$this->setDates();
		$this->setEvents();
	}

	/**
	 * Sets the parameters used to configure the display
	 *
	 * @return void
	 */
	private function setParams()
	{
		$this->params           = Input::getParams();
		$this->params['layout'] = empty($this->isRegistered()) ? 'default' : 'registered';

		if (!isset($this->params['show_page_heading']))
		{
			$this->params['show_page_heading'] = true;
		}

		$resources = array();
		foreach (array_keys($this->columnMap) as $resource)
		{
			$resourceIDs = Input::getFilterIDs($resource);
			if (count($resourceIDs))
			{
				foreach ($resourceIDs as $resourceID)
				{
					$resources[$resource] = $resourceID;
				}
			}
		}

		$this->params['resources'] = $resources;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return void populates state properties
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$formData = Input::getFormItems();

		$menuStartDate      = $this->params->get('startDate');
		$menuEndDate        = $this->params->get('endDate');
		$defaultDate        = empty($menuStartDate) ? date('Y-m-d', getdate(time())[0]) : $menuStartDate;
		$defaultRestriction = empty($menuEndDate) ? 'month' : '';

		if ($formData->count())
		{
			$startDate = $formData->get('startDate');
			if (empty($startDate) or strtotime($startDate) === false)
			{
				$this->state->set('startDate', $defaultDate);
			}
			else
			{
				$this->state->set('startDate', $startDate);
			}

			$intervals = ['day', 'week', 'month', 'semester', 'custom'];
			$interval  = $formData->get('interval');
			if (empty($interval) or !in_array($interval, $intervals))
			{
				$this->state->set('interval', $defaultRestriction);
			}
			else
			{
				$this->state->set('interval', $interval);
			}
		}
		else
		{
			$this->state->set('startDate', $defaultDate);
			$this->state->set('interval', $defaultRestriction);
		}

		if (empty($this->state->get('interval')))
		{
			$this->state->set('endDate', $menuEndDate);
		}
	}

	/**
	 * Aggregates events as appropriate
	 *
	 * @return void modifies the class's events property
	 */
	private function aggregateEvents()
	{
		foreach ($this->events as $date => $dailyEvents)
		{
			$hAggregatedEvents   = $this->aggregateConcurrent($dailyEvents);
			$vAggregatedEvents   = $this->aggregateSequential($hAggregatedEvents);
			$this->events[$date] = $vAggregatedEvents;
		}
	}

	/**
	 * Aggregates events belonging to the same lesson occuring at the same time
	 *
	 * @param   array  $events  the previous event results
	 *
	 * @return array the horizontally aggregated events
	 */
	private function aggregateConcurrent($events)
	{
		$aggregatedEvents = [];

		foreach ($events as $event)
		{
			$lessonID  = $event['lessonID'];
			$title     = empty($event['sName']) ? $event['courseName'] : $event['sName'];
			$startTime = substr(str_replace(':', '', $event['startTime']), 0, 4);
			$endTime   = substr(str_replace(':', '', $event['endTime']), 0, 4);
			$times     = "$startTime-$endTime";

			if (empty($aggregatedEvents[$times]))
			{
				$aggregatedEvents[$times] = [];
			}

			if (empty($aggregatedEvents[$times][$lessonID]))
			{
				$aggregatedEvents[$times][$lessonID]              = [];
				$aggregatedEvents[$times][$lessonID]['titles']    = [$title];
				$aggregatedEvents[$times][$lessonID]['method']    = empty($event['method']) ? '' : $event['method'];
				$aggregatedEvents[$times][$lessonID]['comment']   = empty($event['comment']) ? '' : $event['comment'];
				$aggregatedEvents[$times][$lessonID]['rooms']     = $event['rooms'];
				$aggregatedEvents[$times][$lessonID]['persons']   = $event['persons'];
				$aggregatedEvents[$times][$lessonID]['startTime'] = $event['startTime'];
				$aggregatedEvents[$times][$lessonID]['endTime']   = $event['endTime'];
			}
			else
			{
				if (!in_array($title, $aggregatedEvents[$times][$lessonID]['titles']))
				{
					$aggregatedEvents[$times][$lessonID]['titles'][] = $title;
				}
				$aggregatedEvents[$times][$lessonID]['rooms']
					= array_unique(array_merge($aggregatedEvents[$times][$lessonID]['rooms'], $event['rooms']));
				$aggregatedEvents[$times][$lessonID]['persons']
					= array_unique(array_merge($aggregatedEvents[$times][$lessonID]['persons'], $event['persons']));
			}
			$aggregatedEvents[$times][$lessonID]['departments'][$event['departmentID']] = $event['department'];
		}

		ksort($aggregatedEvents);

		return $aggregatedEvents;
	}

	/**
	 * Aggregates events belonging to the same lesson occurring at the same time
	 *
	 * @param   array &$blockEvents  the events aggregated by their times
	 *
	 * @return array the vertically aggregated events
	 */
	private function aggregateSequential(&$blockEvents)
	{
		foreach ($blockEvents as $outerTimes => $outerEvents)
		{
			foreach ($outerEvents as $lessonID => $outerLesson)
			{
				$outerStart = $outerLesson['startTime'];
				$outerEnd   = $outerLesson['endTime'];

				foreach ($blockEvents as $innerTimes => $innerEvents)
				{
					// Identity or no need for comparison
					if ($innerTimes == $outerTimes or empty($innerEvents[$lessonID]))
					{
						continue;
					}

					$innerLesson = $innerEvents[$lessonID];
					$sameRooms   = $innerLesson['rooms'] == $outerLesson['rooms'];
					$samePersons = $innerLesson['persons'] == $outerLesson['persons'];
					$divergent   = (!$sameRooms or !$samePersons);

					if ($divergent)
					{
						continue;
					}

					$innerStart    = $innerLesson['startTime'];
					$innerEnd      = $innerLesson['endTime'];
					$relevantTimes = $this->getSequentialRelevance($outerStart, $outerEnd, $innerStart, $innerEnd);

					if (empty($relevantTimes))
					{
						continue;
					}

					$outerLesson['startTime'] = $relevantTimes['startTime'];
					$outerLesson['endTime']   = $relevantTimes['endTime'];
					$outerStart               = $relevantTimes['startTime'];
					$outerEnd                 = $relevantTimes['endTime'];

					unset($blockEvents[$innerTimes][$lessonID]);
				}

				$startTime = substr(str_replace(':', '', $outerStart), 0, 4);
				$endTime   = substr(str_replace(':', '', $outerEnd), 0, 4);
				$newTimes  = "$startTime-$endTime";

				unset($blockEvents[$outerTimes][$lessonID]);

				if (empty($blockEvents[$newTimes]))
				{
					$blockEvents[$newTimes] = [];
				}

				$blockEvents[$newTimes][$lessonID] = $outerLesson;
			}
		}

		ksort($blockEvents);

		return $blockEvents;
	}

	/**
	 * Removes indexes which are no longer used after sequential aggregation
	 *
	 * @return void modifies object variable
	 */
	private function cleanEvents()
	{
		foreach ($this->events as $date => $times)
		{
			foreach ($times as $index => $lessons)
			{
				if (empty($lessons))
				{
					unset($this->events[$date][$index]);
				}
			}
			if (empty($times))
			{
				unset($this->events[$date]);
			}
		}
	}

	/**
	 * Retrieves all roomIDs
	 *
	 * @return mixed  array of roomIDS on success, otherwise false
	 */
	private function getAllRoomIDs()
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('id')->from('#__thm_organizer_rooms');
		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadColumn', []);
	}

	/**
	 * Adds the room names to the room instances index, if the room was requested.
	 *
	 * @param   array  $instanceRooms  the rooms associated with the instance
	 *
	 * @return array $rooms
	 */
	private function getEventRooms(&$instanceRooms)
	{
		$rooms = [];
		foreach ($instanceRooms as $roomID => $delta)
		{
			if ($delta == 'removed' or empty($this->rooms[$roomID]))
			{
				unset($instanceRooms[$roomID]);
				continue;
			}

			$rooms[$roomID] = $this->rooms[$roomID];
		}
		asort($rooms);

		return $rooms;
	}

	/**
	 * Filters the database query depending on $this->params['resources'] and the user input
	 *
	 * @param   \JDatabaseQuery  $query  the database query
	 */
	private function filterEvents(&$query)
	{
		foreach ($this->params['resources'] as $resource => $value)
		{
			$query->where("{$this->columnMap[$resource]} = {$value}");
		}

		if (!empty($this->params['myFinals']) && (boolean) $this->params['myFinals'])
		{
			$this->params['mySchedule'] = 1;
			$query->where("m.id = 5");
		}

		if (!empty($this->params['mySchedule']) && (boolean) $this->params['mySchedule'])
		{
			$userID      = Factory::getUser()->id;
			$personQuery = "";
			$personID    = Persons::getIDByUserID($userID);

			if ($personID !== 0)
			{
				$query->leftJoin('#__thm_organizer_user_lessons AS ul ON l.id = ul.lessonID');
				$regexp      = '"persons":\\{[^\}]*"' . $personID . '"';
				$personQuery = " OR conf.configuration REGEXP '$regexp'";
			}
			else
			{
				$query->innerJoin('#__thm_organizer_user_lessons AS ul ON l.id = ul.lessonID');
			}

			$query->where("(ul.userID = {$userID}" . $personQuery . ')');
		}
	}

	/**
	 * Gets the raw events from the database
	 *
	 * @return void sets the object variable events
	 */
	private function getEvents()
	{
		$tag   = Languages::getTag();
		$query = $this->_db->getQuery(true);

		$select = 'DISTINCT conf.id, conf.configuration, cal.startTime, cal.endTime, cal.schedule_date, ';
		$select .= "d.shortName_$tag AS department, d.id AS departmentID, ";
		$select .= "l.id as lessonID, l.comment, m.abbreviation_$tag AS method, ";
		$select .= "co.name AS courseName, s.name_$tag AS sName";
		$query->select($select)
			->from('#__thm_organizer_calendar AS cal')
			->innerJoin('#__thm_organizer_calendar_configuration_map AS ccm ON ccm.calendarID = cal.id')
			->innerJoin('#__thm_organizer_lesson_configurations AS conf ON ccm.configurationID = conf.id')
			->innerJoin('#__thm_organizer_lessons AS l ON cal.lessonID = l.id')
			->innerJoin('#__thm_organizer_departments AS d ON l.departmentID = d.id')
			->innerJoin('#__thm_organizer_lesson_courses AS lcrs ON lcrs.lessonID = l.id')
			->innerJoin('#__thm_organizer_courses AS co ON co.id = lcrs.courseID')
			->innerJoin('#__thm_organizer_lesson_groups AS lg ON lg.lessonCourseID = lcrs.id')
			->innerJoin('#__thm_organizer_groups AS gr ON gr.id = lp.groupID')
			->innerJoin('#__thm_organizer_categories AS cat ON cat.id = gr.categoryID')
			->leftJoin('#__thm_organizer_group_publishing AS gp ON gp.groupID = gr.id AND gp.termID = l.termID')
			->leftJoin('#__thm_organizer_methods AS m ON l.methodID = m.id')
			->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.courseID = co.id')
			->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id')
			->where("cal.schedule_date IN ($this->dates)")
			->where("cal.delta != 'removed'")
			->where("l.delta != 'removed'")
			->where("lcrs.delta != 'removed'")
			->where("(gp.published IS NULL OR gp.published = '1')")
			->order('cal.schedule_date');

		$this->filterEvents($query);
		$this->_db->setQuery($query);

		$events = OrganizerHelper::executeQuery('loadAssocList');

		if (!empty($events))
		{
			foreach ($events as $index => $event)
			{
				$configuration = json_decode($event['configuration'], true);
				$rooms         = $this->getEventRooms($configuration['rooms']);

				if (count($rooms))
				{
					$events[$index]['rooms']   = $rooms;
					$events[$index]['persons'] = $this->getEventPersons($configuration['persons']);
					unset($events[$index]['configuration']);
				}
				else
				{
					unset($events[$index]);
				}
			}

			foreach ($events as $event)
			{
				$date = $event['schedule_date'];
				if (!array_key_exists($date, $this->events))
				{
					$this->events[$date] = array();
				}

				$this->events[$date][] = $event;
			}
		}
	}

	/**
	 * Adds the person names to the person instances index.
	 *
	 * @param   array  $instancePersons  the persons associated with the instance
	 *
	 * @return array an array of persons in the form id => 'forename(s) surname(s)'
	 */
	private function getEventPersons(&$instancePersons)
	{
		$persons = [];

		foreach ($instancePersons as $personID => $delta)
		{
			if ($delta == 'removed')
			{
				unset($instancePersons[$personID]);
				continue;
			}

			$persons[$personID] = Persons::getDefaultName($personID);
		}

		asort($persons);

		return $persons;
	}

	/**
	 * Checks whether the accessing agent is a monitor
	 *
	 * @return mixed  int roomID on success, otherwise boolean false
	 */
	private function isRegistered()
	{
		$remoteAddress = Input::getInput()->server->getString('REMOTE_ADDR', '');
		$monitorsTable = new MonitorsTable;
		if (!$monitorsTable->load(['ip' => $remoteAddress]) or !$roomID = $monitorsTable->roomID)
		{
			return false;
		}

		$templateSet = Input::getCMD('tmpl') == 'component';
		if (!$templateSet)
		{
			$base  = Uri::root() . 'index.php?';
			$query = Input::getInput()->server->get('QUERY_STRING', '', 'raw');
			$query .= (strpos($query, 'com_thm_organizer') !== false) ? '' : '&option=com_thm_organizer';
			$query .= (strpos($query, 'event_list') !== false) ? '' : '&view=event_list';
			$query .= '&tmpl=component';
			OrganizerHelper::getApplication()->redirect($base . $query);
		}

		$this->rooms = [$roomID];
		$this->days  = [1, 2, 3, 4, 5, 6];

		return true;
	}

	/**
	 * Determines the sequential relevance of two lesson blocks.
	 *
	 * @param   string  $startOuter  the start time for the lesson in the outer loop
	 * @param   string  $endOuter    the end time for the lesson in the outer loop
	 * @param   string  $startInner  the start time for the lesson in the inner loop
	 * @param   string  $endInner    the end time for the lesson in the inner loop
	 *
	 * @return array|bool the new start and end times if relevant, otherwise false
	 */
	private function getSequentialRelevance($startOuter, $endOuter, $startInner, $endInner)
	{
		// The maximum tolerance (break time) allowed for sequential aggregation
		$tolerance = 61;

		// Inner lesson ended before outer began
		$before = $endInner < $startOuter;

		if ($before)
		{
			$firstTime  = strtotime($endInner);
			$secondTime = strtotime($startOuter);
			$difference = ($secondTime - $firstTime) / 60;
			$relevant   = $difference <= $tolerance;

			return $relevant ? ['startTime' => $startInner, 'endTime' => $endOuter] : false;
		}

		// Outer lesson ended before inner began
		$after = $endOuter < $startInner;

		if ($after)
		{
			$firstTime  = strtotime($endOuter);
			$secondTime = strtotime($startInner);
			$difference = ($secondTime - $firstTime) / 60;
			$relevant   = $difference <= $tolerance;

			return $relevant ? ['startTime' => $startOuter, 'endTime' => $endInner] : false;
		}

		// Overlapping lessons
		$startTime = $startOuter < $startInner ? $startOuter : $startInner;
		$endTime   = $endOuter > $endInner ? $endOuter : $endInner;

		return ['startTime' => $startTime, 'endTime' => $endTime];
	}

	/**
	 * Sets the dates used
	 *
	 * @return void
	 */
	private function setDates()
	{
		$isRegistered     = ($this->params['layout'] == 'registered');
		$invalidSelection = (
			empty($this->params['days'])
			or (count($this->params['days']) === 1 and empty($this->params['days'][0]))
		);
		if ($isRegistered or $invalidSelection)
		{
			$days = [1, 2, 3, 4, 5, 6];
		}
		else
		{
			$days = $this->params['days'];
		}

		$startDT = strtotime($this->state->get('startDate'));
		$date    = date('Y-m-d', $startDT);
		$endDT   = $startDT;
		switch ($this->state->get('interval'))
		{
			case 'day':
				$endDT = $startDT;
				break;
			case 'week':
				$dates = Dates::getWeek($date);
				break;
			case 'month':
				$dates = Dates::getMonth($date);
				break;
			case 'semester':
				$dates = Dates::getSemester($date);
				break;
			default:
				$endDT = strtotime($this->state->get('endDate'));
				break;
		}

		if (!empty($dates))
		{
			$startDT = strtotime($dates[0]);
			$endDT   = strtotime($dates[1]);
		}

		for ($currentDT = $startDT; $currentDT <= $endDT; $currentDT = strtotime('+1 day', $currentDT))
		{
			$currentDOW = date('w', $currentDT);
			if (in_array($currentDOW, $days))
			{
				$this->dates[] = "'" . date('Y-m-d', $currentDT) . "'";
			}
		}

		$this->dates = implode(',', $this->dates);
		if (strlen($this->dates) === 0)
		{
			$this->dates = "NULL";
		}
	}

	/**
	 * Sets the events for display
	 *
	 * @return void  sets object variables
	 */
	private function setEvents()
	{
		$this->getEvents();
		$this->aggregateEvents();
		$this->cleanEvents();
	}

	/**
	 * Retrieves the name and id of the room
	 *
	 * @return void  sets object variables
	 */
	private function setRooms()
	{
		if (empty($this->rooms))
		{
			$invalidSelection = (
				empty($this->params['rooms'])
				or (count($this->params['rooms']) === 1 and empty($this->params['rooms'][0]))
			);

			// All rooms
			if ($invalidSelection)
			{
				$this->rooms = $this->getAllRoomIDs();
			}
			else
			{
				$this->rooms = $this->params['rooms'];
			}
		}

		$rooms      = [];
		$roomsTable = new RoomsTable;

		// The current values are meaningless and will be overwritten here
		foreach ($this->rooms as $roomID)
		{
			try
			{
				$roomsTable->load($roomID);
			}
			catch (Exception $exc)
			{
				OrganizerHelper::message($exc->getMessage(), 'error');
				unset($this->rooms[$roomID]);
			}

			$roomName       = $roomsTable->name;
			$rooms[$roomID] = $roomName;
		}

		if ($this->params['layout'] == 'registered')
		{
			$roomValues               = array_values($rooms);
			$this->params['roomName'] = array_shift($roomValues);
		}

		asort($rooms);
		$this->rooms = $rooms;
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm(
			'com_thm_organizer.event_list',
			'event_list',
			['control' => 'jform', 'load_data' => true]
		);

		return !empty($form) ? $form : false;
	}
}
