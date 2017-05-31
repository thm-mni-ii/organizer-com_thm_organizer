<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperXMLLessons
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class encapsulating data abstraction and business logic for lessons.
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperXMLLessons
{
	private $configurations = array();

	private $lessonID;

	private $lessonName;

	private $pools;

	private $scheduleModel = null;

	private $subjectID;

	private $subjectUntisID;

	private $teacherID;

	private $xmlObject = null;

	/**
	 * Creates the lesson model
	 *
	 * @param object &$scheduleModel the model for the schedule
	 * @param object &$xmlObject     the xml object being validated
	 */
	public function __construct(&$scheduleModel, &$xmlObject)
	{
		$this->scheduleModel = $scheduleModel;
		$this->xmlObject     = $xmlObject;
	}

	/**
	 * Processes instance information for the new schedule format
	 *
	 * @param string $currentDate the date being iterated
	 * @param object $period      the period information from the grid
	 * @param object $roomIDs     the room ids assigned to the instance
	 *
	 * @return void
	 */
	private function processInstance($currentDate, $period, $roomIDs)
	{
		// New format calendar items are created as necessary
		if (!isset($this->scheduleModel->newSchedule->calendar->$currentDate))
		{
			$this->scheduleModel->newSchedule->calendar->$currentDate = new stdClass;
		}

		$times = $period->startTime . '-' . $period->endTime;
		if (!isset($this->scheduleModel->newSchedule->calendar->$currentDate->$times))
		{
			$this->scheduleModel->newSchedule->calendar->$currentDate->$times = new stdClass;
		}

		$lessonID = $this->lessonID;

		if (!isset($this->scheduleModel->newSchedule->calendar->$currentDate->$times->$lessonID))
		{
			$this->scheduleModel->newSchedule->calendar->$currentDate->$times->$lessonID                 = new stdClass;
			$this->scheduleModel->newSchedule->calendar->$currentDate->$times->$lessonID->delta          = '';
			$this->scheduleModel->newSchedule->calendar->$currentDate->$times->$lessonID->configurations = array();
		}

		$config                               = new stdClass;
		$config->lessonID                     = $this->lessonID;
		$config->subjectID                    = $this->subjectID;
		$config->teachers                     = new stdClass;
		$config->teachers->{$this->teacherID} = '';
		$config->rooms                        = $roomIDs;
		$existingIndex                        = null;

		if (!empty($this->scheduleModel->newSchedule->calendar->$currentDate->$times->$lessonID->configurations))
		{
			$compConfig = null;
			foreach ($this->scheduleModel->newSchedule->calendar->$currentDate->$times->$lessonID->configurations as $configIndex)
			{
				$tempConfig = json_decode($this->scheduleModel->newSchedule->configurations[$configIndex]);

				if ($tempConfig->subjectID == $this->subjectID)
				{
					$compConfig    = $tempConfig;
					$existingIndex = $configIndex;
					break;
				}
			}

			if (!empty($compConfig))
			{
				foreach ($compConfig->teachers as $teacherID => $emptyDelta)
				{
					$config->teachers->$teacherID = $emptyDelta;
				}

				foreach ($compConfig->rooms as $roomID => $emptyDelta)
				{
					$config->rooms->$roomID = $emptyDelta;
				}
			}
		}

		$this->createConfig($config, $currentDate, $times, $existingIndex);

		return;

	}

	/**
	 * Creates a new configuration
	 *
	 * @param object $config        the configuration object
	 * @param string $date          the date to which the configuration should be referenced
	 * @param string $times         the times used for indexing blocks in the calendar
	 * @param int    $existingIndex the existing index of the configuration if existent
	 *
	 * @return void
	 */
	private function createConfig($config, $date, $times, $existingIndex)
	{
		$jsonConfig = json_encode($config);

		if (!empty($existingIndex))
		{
			$this->scheduleModel->newSchedule->configurations[$existingIndex] = $jsonConfig;

			return;
		}

		$configIndex = array_search($jsonConfig, $this->scheduleModel->newSchedule->configurations);
		if (empty($configIndex))
		{
			$this->scheduleModel->newSchedule->configurations[] = $jsonConfig;

			$configIndex = array_search($jsonConfig, $this->scheduleModel->newSchedule->configurations);
		}
		$this->scheduleModel->newSchedule->calendar->$date->$times->{$this->lessonID}->configurations[] = $configIndex;
	}

	/**
	 * Determines how the missing room attribute will be handled
	 *
	 * @param string $currentDT the timestamp of the date being iterated
	 * @param string $period    the value of the period attribute
	 *
	 * @return  boolean  true if blocking and not set elsewhere, otherwise false
	 */
	private function createMissingRoomMessage($currentDT, $period)
	{
		$pools        = implode(', ', $this->pools);
		$dow          = strtoupper(date('l', $currentDT));
		$localizedDoW = JText::_($dow);
		$error        = JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_ROOM_MISSING',
			$this->lessonName,
			$this->lessonID,
			$pools,
			$localizedDoW,
			$period
		);

		if (!in_array($error, $this->scheduleModel->scheduleWarnings))
		{
			$this->scheduleModel->scheduleWarnings[] = $error;
		}
	}

	/**
	 * Checks whether subject nodes have the expected structure and required information
	 *
	 * @return void
	 */
	public function validate()
	{
		if (empty($this->xmlObject->lessons))
		{
			$this->scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_LESSONS_MISSING");

			return;
		}

		$this->scheduleModel->newSchedule->configurations = array();
		$this->scheduleModel->newSchedule->lessons        = new stdClass;

		foreach ($this->xmlObject->lessons->children() as $lessonNode)
		{
			$this->validateIndividual($lessonNode);
		}

		if (!empty($this->scheduleModel->scheduleWarnings['LESSON-METHOD']))
		{
			$warningCount = $this->scheduleModel->scheduleWarnings['LESSON-METHOD'];
			unset($this->scheduleModel->scheduleWarnings['LESSON-METHOD']);
			$this->scheduleModel->scheduleWarnings[] = JText::sprintf('COM_THM_ORGANIZER_WARNING_METHODID', $warningCount);
		}
	}

	/**
	 * Checks whether lesson nodes have the expected structure and required information
	 *
	 * @param object &$lessonNode a SimpleXML object modeling the lesson node to be validated
	 *
	 * @return void
	 */
	private function validateIndividual(&$lessonNode)
	{
		$effBeginDT  = isset($lessonNode->begindate)?
            strtotime(trim((string) $lessonNode->begindate)) :
            strtotime(trim((string) $lessonNode->effectivebegindate));
		$termBeginDT = strtotime($this->scheduleModel->newSchedule->startDate);
		$effEndDT    = isset($lessonNode->enddate)?
            strtotime(trim((string) $lessonNode->enddate)) :
            strtotime(trim((string) $lessonNode->effectiveenddate));
		$termEndDT   = strtotime($this->scheduleModel->newSchedule->endDate);

		// Lesson is not relevant for the uploaded schedule (starts after term ends or ends before term begins)
		if ($effBeginDT > $termEndDT OR $effEndDT < $termBeginDT)
		{
			return;
		}

		// Reset variables passed through the object
		$this->lessonID  = $this->validateUntisID(trim((string) $lessonNode[0]['id']));
		$this->subjectID = '';
		$this->teacherID = '';

		if (empty($this->lessonID))
		{
			return;
		}

		if (!isset($this->scheduleModel->newSchedule->lessons->{$this->lessonID}))
		{
			$this->scheduleModel->newSchedule->lessons->{$this->lessonID} = new stdClass;
		}

		if (!$this->validateSubject($lessonNode))
		{
			return;
		}

		$this->validateMethod($lessonNode);

		if (!$this->validatePools($lessonNode))
		{
			return;
		}

		if (!$this->validateTeacher($lessonNode))
		{
			return;
		}

		if (!$this->validateDates($effBeginDT, $effEndDT))
		{
			return;
		}

		$comment = trim((string) $lessonNode->text);

		if (empty($comment) OR $comment == '.')
		{
			$comment = '';
		}

		$this->scheduleModel->newSchedule->lessons->{$this->lessonID}->comment = $comment;

		$rawInstances = trim((string) $lessonNode->occurence);
		$startDT      = $effBeginDT < $termBeginDT ? $termBeginDT : $effBeginDT;
		$endDT        = $termEndDT < $effEndDT ? $termEndDT : $effEndDT;

		// Adjusted dates are used because effective dts are not always accurate for the time frame
		$potentialInstances = $this->truncateInstances($rawInstances, $startDT, $endDT);

		$times = $lessonNode->xpath("times/time");

		$gridName = empty((string) $lessonNode->timegrid) ? 'Haupt-Zeitraster' : (string) $lessonNode->timegrid;

		// Cannot produce blocking errors
		$this->validateInstances($potentialInstances, $startDT, $times, $gridName);
	}

	/**
	 * Checks if the untis id is valid
	 *
	 * @param string $rawUntisID the untis lesson id
	 *
	 * @return  mixed  string if valid, otherwise false
	 */
	private function validateUntisID($rawUntisID)
	{
		$withoutPrefix = str_replace("LS_", '', $rawUntisID);
		$untisID       = substr($withoutPrefix, 0, strlen($withoutPrefix) - 2);

		if (empty($untisID))
		{
			if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_LESSON_ID_MISSING"), $this->scheduleModel->scheduleErrors))
			{
				$this->scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_LESSON_ID_MISSING");
			}

			return false;
		}

		return $untisID;
	}

	/**
	 * Validates the subjectID and builds dependant structural elements
	 *
	 * @param object &$lessonNode the lesson node
	 *
	 * @return  mixed  string the name of the lesson (subjects) on success,
	 *                 otherwise boolean false
	 */
	private function validateSubject(&$lessonNode)
	{
		$untisID = str_replace('SU_', '', trim((string) $lessonNode->lesson_subject[0]['id']));

		if (empty($untisID))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf("COM_THM_ORGANIZER_ERROR_LESSON_SUBJECT_MISSING", $this->lessonID);

			return false;
		}

		$this->subjectUntisID = $untisID;
		$subjectIndex         = $this->scheduleModel->newSchedule->departmentname . "_" . $untisID;

		if (empty($this->scheduleModel->newSchedule->subjects->$subjectIndex))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf("COM_THM_ORGANIZER_ERROR_LESSON_SUBJECT_LACKING", $this->lessonID, $this->subjectUntisID);

			return false;
		}

		// Used for error reporting
		$this->lessonName = $this->subjectUntisID;

		if (!isset($this->scheduleModel->newSchedule->lessons->{$this->lessonID}->subjects))
		{
			$this->scheduleModel->newSchedule->lessons->{$this->lessonID}->subjects = new stdClass;
		}

		// Used in configurations, teachers and pools
		$this->subjectID = $this->scheduleModel->newSchedule->subjects->$subjectIndex->id;

		if (!isset($this->scheduleModel->newSchedule->lessons->{$this->lessonID}->subjects->{$this->subjectID}))
		{
			$newSubject            = new stdClass;
			$newSubject->delta     = '';
			$newSubject->subjectNo = $this->scheduleModel->newSchedule->subjects->$subjectIndex->subjectNo;
			$newSubject->pools     = new stdClass;
			$newSubject->teachers  = new stdClass;

			$this->scheduleModel->newSchedule->lessons->{$this->lessonID}->subjects->{$this->subjectID} = $newSubject;
		}

		return true;
	}

	/**
	 * Validates the description
	 *
	 * @param object &$lessonNode the lesson node
	 *
	 * @return  boolean  true if valid, otherwise false
	 */
	private function validateMethod(&$lessonNode)
	{
		$untisID       = str_replace('DS_', '', trim((string) $lessonNode->lesson_description));
		$invalidMethod = (empty($untisID) OR empty($this->scheduleModel->newSchedule->methods->$untisID));

		if ($invalidMethod)
		{
			$this->scheduleModel->scheduleWarnings['LESSON-METHOD'] = empty($this->scheduleModel->scheduleWarnings['LESSON-METHOD']) ?
				1 : $this->scheduleModel->scheduleWarnings['LESSON-METHOD'] + 1;

			return;
		}

		$this->lessonName .= " - $untisID";

		$this->scheduleModel->newSchedule->lessons->{$this->lessonID}->methodID
			= $this->scheduleModel->newSchedule->methods->$untisID->id;

		return;
	}

	/**
	 * Validates the teacher attribute and sets corresponding schedule elements
	 *
	 * @param object &$lessonNode the lesson node
	 *
	 * @return  boolean  true if valid, otherwise false
	 */
	private function validateTeacher(&$lessonNode)
	{
		$untisID = str_replace('TR_', '', trim((string) $lessonNode->lesson_teacher[0]['id']));

		if (empty($untisID))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_TEACHER_MISSING', $this->lessonName, $this->lessonID);

			return false;
		}

		$teacherFound = false;
		$teacherID    = null;

		foreach ($this->scheduleModel->newSchedule->teachers as $teacherKey => $teacher)
		{
			if ($teacher->localUntisID == $untisID)
			{
				// Existent but invalid teacher
				if (empty($teacher->id))
				{
					break;
				}

				$teacherFound = true;

				// Used for configurations
				$this->teacherID = $teacher->id;

				break;
			}
		}

		if (!$teacherFound)
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_TEACHER_LACKING', $this->lessonName, $this->lessonID, $untisID);

			return false;
		}

		if (!empty($this->subjectID))
		{
			$this->scheduleModel->newSchedule->lessons->{$this->lessonID}->subjects->{$this->subjectID}->teachers->{$this->teacherID} = '';
		}

		return true;
	}

	/**
	 * Validates the pools attribute and sets corresponding schedule elements
	 *
	 * @param object &$lessonNode the lesson node
	 *
	 * @return  boolean  true if valid, otherwise false
	 */
	private function validatePools(&$lessonNode)
	{
		$rawUntisIDs = str_replace('CL_', '', (string) $lessonNode->lesson_classes[0]['id']);

		if (empty($rawUntisIDs))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf("COM_THM_ORGANIZER_ERROR_LESSON_POOL_MISSING", $this->lessonName, $this->lessonID);

			return false;
		}

		$untisIDs    = explode(" ", $rawUntisIDs);
		$this->pools = array();

		foreach ($untisIDs as $untisID)
		{
			$poolFound = false;
			$poolID    = null;

			foreach ($this->scheduleModel->newSchedule->pools as $poolKey => $pool)
			{
				if ($pool->localUntisID == $untisID)
				{
					// The pool is existent but invalid
					if (empty($pool->id))
					{
						break;
					}

					$poolFound = true;
					$poolID    = $pool->id;

					break;
				}
			}

			if (!$poolFound)
			{
				$this->scheduleModel->scheduleErrors[]
					= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_POOL_LACKING', $this->lessonName, $this->lessonID, $untisID);

				return false;
			}

			$this->scheduleModel->newSchedule->lessons->{$this->lessonID}->subjects->{$this->subjectID}->pools->$poolID = '';

			$this->pools[$untisID] = $untisID;
		}

		return true;
	}

	/**
	 * Checks for the validity and consistency of date values
	 *
	 * @param int $startDT the start date as integer
	 * @param int $endDT   the end date as integer
	 *
	 * @return  boolean  true if dates are valid, otherwise false
	 */
	private function validateDates($startDT, $endDT)
	{

		if (empty($startDT))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_START_DATE_MISSING', $this->lessonName, $this->lessonID);

			return false;
		}

		$syStartTime     = strtotime($this->scheduleModel->newSchedule->syStartDate);
		$syEndTime       = strtotime($this->scheduleModel->newSchedule->syEndDate);
		$lessonStartDate = date('Y-m-d', $startDT);

		$validStartDate = ($startDT >= $syStartTime AND $startDT <= $syEndTime);
		if (!$validStartDate)
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_START_DATE_INVALID', $this->lessonName, $this->lessonID, $lessonStartDate);

			return false;
		}

		if (empty($endDT))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_END_DATE_MISSING', $this->lessonName, $this->lessonID);

			return false;
		}

		$lessonEndDate = date('Y-m-d', $endDT);

		$validEndDate = ($endDT >= $syStartTime AND $endDT <= $syEndTime);
		if (!$validEndDate)
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_END_DATE_INVALID', $this->lessonName, $this->lessonID, $lessonEndDate);

			return false;
		}

		// Checks if start date is before end date
		if ($endDT < $startDT)
		{
			$this->scheduleModel->scheduleErrors[] =
				JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_DATES_INCONSISTENT',
					$this->lessonName,
					$this->lessonID,
					$lessonStartDate,
					$lessonEndDate
				);

			return false;
		}

		return true;
	}

	/**
	 * Validates the occurrences attribute
	 *
	 * @param string $raw   the string containing the occurrences
	 * @param int    $start the timestamp of the lesson's begin
	 * @param int    $end   the timestamp of the lesson's end
	 *
	 * @return  mixed   array if valid, otherwise false
	 */
	private function truncateInstances($raw, $start, $end)
	{
		// Increases the end value one day (Untis uses inclusive dates)
		$end = strtotime('+1 day', $end);

		// 86400 is the number of seconds in a day 24 * 60 * 60
		$offset = floor(($start - strtotime($this->scheduleModel->newSchedule->syStartDate)) / 86400);
		$length = floor(($end - $start) / 86400);

		// Change occurrences from a string to an array of the appropriate length for iteration
		return str_split(substr($raw, $offset, $length));
	}

	/**
	 * Iterates over possible occurrences and validates them
	 *
	 * @param array  $potentialInstances an array of 'occurrences'
	 * @param int    $currentDT          the starting timestamp
	 * @param array  &$instances         the object containing the instances
	 * @param string $grid               the grid used by the lesson
	 *
	 * @return  void
	 */
	private function validateInstances($potentialInstances, $currentDT, &$instances, $grid)
	{
		if (count($instances) == 0)
		{
			return;
		}

		foreach ($potentialInstances as $potentialInstance)
		{
			// Untis uses F for vacation days and 0 for any other date restriction
			$notAllowed = ($potentialInstance == '0' OR $potentialInstance == 'F');

			if ($notAllowed)
			{
				$currentDT = strtotime('+1 day', $currentDT);
				continue;
			}

			foreach ($instances as $instance)
			{
				if (!$this->validateInstance($instance, $currentDT, $grid))
				{
					return;
				}
			}

			$currentDT = strtotime('+1 day', $currentDT);
		}

		return;
	}

	/**
	 * Validates a lesson instance
	 *
	 * @param object &$instance the lesson instance
	 * @param int    $currentDT the current date time in the iteration
	 * @param string $grid      the grid used by the lesson
	 *
	 * @return  boolean  true if valid, otherwise false
	 */
	private function validateInstance(&$instance, $currentDT, $grid)
	{
		$assigned_day = trim((string) $instance->assigned_day);
		$dow          = date('w', $currentDT);

		if ($assigned_day != $dow)
		{
			return true;
		}

		// Sporadic events have specific dates assigned to them.
		$assigned_date = strtotime(trim((string) $instance->assigned_date));

		// The event is sporadic and does not occur on the date being currently iterated
		if (!empty($assigned_date) AND $assigned_date != $currentDT)
		{
			return true;
		}

		$periodNo      = trim((string) $instance->assigned_period);
		$roomAttribute = trim((string) $instance->assigned_room[0]['id']);

		if (empty($roomAttribute))
		{
			$this->createMissingRoomMessage($currentDT, $periodNo);
			return false;
		}

		$roomsIDs = $this->validateRooms($roomAttribute, $currentDT, $periodNo);

		if ($roomsIDs === false)
		{
			return false;
		}

		$currentDate = date('Y-m-d', $currentDT);
		$period = $this->scheduleModel->newSchedule->periods->$grid->$periodNo;
		$this->processInstance($currentDate, $period, $roomsIDs);

		return true;
	}

	/**
	 * Validates the room attribute
	 *
	 * @param string $roomAttribute the room attribute
	 * @param int    $currentDT     the timestamp of the date being iterated
	 * @param string $period        the period attribute
	 *
	 * @return  array  the roomIDs on success, otherwise false
	 */
	private function validateRooms($roomAttribute, $currentDT, $period)
	{
		$roomIDs = new stdClass;
		$roomUntisIDs = explode(' ', str_replace('RM_', '', $roomAttribute));

		foreach ($roomUntisIDs as $roomID)
		{
			$roomFound = false;
			foreach ($this->scheduleModel->newSchedule->rooms as $roomKey => $room)
			{
				if ($room->localUntisID == $roomID)
				{
					// Existent but invalid
					if (empty($room->id))
					{
						break;
					}

					$roomFound            = true;
					$roomID               = $roomKey;
					$roomIDs->{$room->id} = '';
					break;
				}
			}

			if (!$roomFound)
			{
				$pools        = implode(', ', $this->pools);
				$dow          = strtoupper(date('l', $currentDT));
				$localizedDoW = JText::_($dow);
				$error        = JText::sprintf(
					'COM_THM_ORGANIZER_ERROR_LESSON_ROOM_LACKING',
					$this->lessonName, $this->lessonID, $pools,
					$localizedDoW, $period, $roomID
				);
				if (!in_array($error, $this->scheduleModel->scheduleErrors))
				{
					$this->scheduleModel->scheduleErrors[] = $error;
				}

				return false;
			}
		}

		return $roomIDs;
	}
}