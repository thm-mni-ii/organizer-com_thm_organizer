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
	/**
	 * The uploaded XML Object
	 *
	 * @var string
	 */
	private $xmlObject = null;

	/**
	 * The name of the lessonm, containing the subject names (or abbreviations) and the abbreviation for the method of
	 * instruction.
	 *
	 * @var string
	 */
	private $lessonName = '';

	/**
	 * The lesson's id.
	 *
	 * @var string
	 */
	private $lessonID = '';

	/**
	 * The subject's id.
	 *
	 * @var string
	 */
	private $subjectID = '';

	/**
	 * The teacher's id.
	 *
	 * @var string
	 */
	private $teacherID = '';

	/**
	 * A unique identifier for the lesson across schedules. (dpt., sem., id). Only used in json schedules
	 *
	 * @var string
	 */
	private $lessonIndex = '';

	/**
	 * The schedule model.
	 *
	 * @var object
	 */
	private $scheduleModel = null;

	/**
	 * The configurations
	 * @var array
	 */
	private $configurations = array();

	/**
	 * Creates the lesson model
	 *
	 * @param   object &$scheduleModel the model for the schedule
	 * @param   object &$xmlObject     the xml object being validated
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

		$existingIndex = null;
		if (!empty($this->scheduleModel->newSchedule->calendar->$currentDate->$times->$lessonID->configurations))
		{
			$compConfig = null;
			foreach ($this->scheduleModel->newSchedule->calendar->$currentDate->$times->$lessonID->configurations as $configIndex)
			{
				$tempConfig = json_decode($this->scheduleModel->newSchedule->configurations[$configIndex]);
				if ($tempConfig->subjectID = $this->subjectID)
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
			$configIndex                                        = array_search($jsonConfig, $this->scheduleModel->newSchedule->configurations);
		}
		$this->scheduleModel->newSchedule->calendar->$date->$times->{$this->lessonID}->configurations[] = $configIndex;
	}

	/**
	 * Determines how the missing room attribute will be handled
	 *
	 * @param   string $currentDT the timestamp of the date being iterated
	 * @param   string $period    the value of the period attribute
	 *
	 * @return  boolean  true if blocking and not set elsewhere, otherwise false
	 */
	private function createMissingRoomMessage($currentDT, $period)
	{
		$pools        = implode(', ', array_keys(get_object_vars($this->scheduleModel->schedule->lessons->{$this->lessonIndex}->pools)));
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

		$this->scheduleModel->schedule->lessons           = new stdClass;
		$this->scheduleModel->newSchedule->configurations = array();
		$this->scheduleModel->newSchedule->lessons        = new stdClass;

		foreach ($this->xmlObject->lessons->children() as $lessonNode)
		{
			$this->validateIndividual($lessonNode);
		}

		if (!empty($this->scheduleWarnings['LESSON-METHOD']))
		{
			$warningCount = $this->scheduleWarnings['LESSON-METHOD'];
			unset($this->scheduleWarnings['LESSON-METHOD']);
			$this->scheduleWarnings[] = JText::sprintf('COM_THM_ORGANIZER_WARNING_METHODID', $warningCount);
		}
	}

	/**
	 * Checks whether lesson nodes have the expected structure and required
	 * information
	 *
	 * @param   object &$lessonNode a SimpleXML object modeling the lesson node to be validated
	 *
	 * @return void
	 */
	private function validateIndividual(&$lessonNode)
	{
		// Reset variables passed through the object
		$this->lessonID    = '';
		$this->lessonIndex = '';
		$this->lessonName  = '';
		$this->subjectID   = '';
		$this->teacherID   = '';

		$lessonID       = $this->validateUntisID(trim((string) $lessonNode[0]['id']));
		$this->lessonID = $lessonID;
		if (empty($this->lessonID))
		{
			return;
		}

		$department        = $this->scheduleModel->schedule->departmentname;
		$semester          = $this->scheduleModel->schedule->semestername;
		$lessonIndex       = $department . $semester . "_" . $lessonID;
		$this->lessonIndex = $lessonIndex;

		if (!isset($this->scheduleModel->schedule->lessons->$lessonIndex))
		{
			$this->scheduleModel->schedule->lessons->$lessonIndex = new stdClass;
		}

		if (!isset($this->scheduleModel->newSchedule->lessons->$lessonID))
		{
			$this->scheduleModel->newSchedule->lessons->$lessonID = new stdClass;
		}

		$this->scheduleModel->schedule->lessons->$lessonIndex->gpuntisID = $lessonID;

		$subjectGPUntisID = str_replace('SU_', '', trim((string) $lessonNode->lesson_subject[0]['id']));
		$lessonName       = $this->validateSubjects($subjectGPUntisID, $department);
		if (!$lessonName)
		{
			return;
		}

		// Set before completion so that the error message is built correctly
		$this->lessonName = $lessonName;

		$methodID = $this->validateMethod($lessonNode);
		if (!empty($methodID))
		{
			$lessonName .= " - $methodID";
		}

		$this->scheduleModel->schedule->lessons->$lessonIndex->name = $lessonName;

		$subjectIndex = $department . "_" . $subjectGPUntisID;
		$teacherID    = str_replace('TR_', '', trim((string) $lessonNode->lesson_teacher[0]['id']));
		$teacherValid = $this->validateTeacher($teacherID, $subjectIndex);
		if (!$teacherValid)
		{
			return;
		}

		$gridName = empty((string) $lessonNode->timegrid) ? 'Haupt-Zeitraster' : (string) $lessonNode->timegrid;

		$this->scheduleModel->schedule->lessons->$lessonIndex->grid = $gridName;

		$poolIDs    = (string) $lessonNode->lesson_classes[0]['id'];
		$poolsValid = $this->validatePools($poolIDs);
		if (!$poolsValid)
		{
			return;
		}

		$startDT    = strtotime(trim((string) $lessonNode->effectivebegindate));
		$endDT      = strtotime(trim((string) $lessonNode->effectiveenddate));
		$datesValid = $this->validateDates($startDT, $endDT);
		if (!$datesValid)
		{
			return;
		}

		$rawInstances       = trim((string) $lessonNode->occurence);
		$potentialInstances = $this->truncateInstances($rawInstances, $startDT, $endDT);

		$comment = trim((string) $lessonNode->text);

		/**
		 * Ensures that the comment is set and empty. '.' has sometimes been used to ensure that a comment is correctly
		 * associated with the correct lesson in Untis print views.
		 */
		if (empty($comment) OR $comment == '.')
		{
			$comment = '';
		}

		$this->scheduleModel->schedule->lessons->$lessonIndex->comment = $comment;
		$this->scheduleModel->newSchedule->lessons->$lessonID->comment = $comment;

		$times = $lessonNode->xpath("times/time");

		// Cannot produce blocking errors
		$this->validateInstances($potentialInstances, $startDT, $times, $gridName);
	}

	/**
	 * Checks if the untis id is valid
	 *
	 * @param   string $rawUntisID the untis lesson id
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
	 * @param   string $gpuntisID  the id of the subject
	 * @param   string $department the name of the department
	 *
	 * @return  mixed  string the name of the lesson (subjects) on success,
	 *                 otherwise boolean false
	 */
	private function validateSubjects($gpuntisID, $department)
	{
		if (empty($gpuntisID))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf("COM_THM_ORGANIZER_ERROR_LESSON_SUBJECT_MISSING", $this->lessonID);

			return false;
		}

		$subjectIndex = $department . "_" . $gpuntisID;

		if (empty($this->scheduleModel->schedule->subjects->$subjectIndex))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf("COM_THM_ORGANIZER_ERROR_LESSON_SUBJECT_LACKING", $this->lessonID, $gpuntisID);

			return false;
		}

		$lessonIndex = $this->lessonIndex;

		if (!isset($this->scheduleModel->schedule->lessons->$lessonIndex->subjects))
		{
			$this->scheduleModel->schedule->lessons->$lessonIndex->subjects = new stdClass;
		}

		$lessonID = $this->lessonID;

		if (!isset($this->scheduleModel->newSchedule->lessons->$lessonID->subjects))
		{
			$this->scheduleModel->newSchedule->lessons->$lessonID->subjects = new stdClass;
		}

		if (!isset($this->scheduleModel->schedule->lessons->$lessonIndex->subjects->$subjectIndex))
		{
			$this->scheduleModel->schedule->lessons->$lessonIndex->subjects->$subjectIndex = '';
		}

		$subjectID       = $this->scheduleModel->schedule->subjects->$subjectIndex->id;
		$this->subjectID = $subjectID;
		$subjectNo       = $this->scheduleModel->schedule->subjects->$subjectIndex->subjectNo;

		if (!isset($this->scheduleModel->newSchedule->lessons->$lessonID->subjects->$subjectID))
		{
			$this->scheduleModel->newSchedule->lessons->$lessonID->subjects->$subjectID            = new stdClass;
			$this->scheduleModel->newSchedule->lessons->$lessonID->subjects->$subjectID->delta     = '';
			$this->scheduleModel->newSchedule->lessons->$lessonID->subjects->$subjectID->subjectNo = $subjectNo;
			$this->scheduleModel->newSchedule->lessons->$lessonID->subjects->$subjectID->pools     = new stdClass;
			$this->scheduleModel->newSchedule->lessons->$lessonID->subjects->$subjectID->teachers  = new stdClass;
		}

		$subjectIndexes = array_keys((array) $this->scheduleModel->schedule->lessons->$lessonIndex->subjects);
		$lessonName     = implode(' / ', $subjectIndexes);

		return str_replace($department . '_', '', $lessonName);
	}

	/**
	 * Validates the description
	 *
	 * @param   object &$lessonNode the lesson node
	 *
	 * @return  boolean  string the methodID on success, otherwise false
	 */
	private function validateMethod(&$lessonNode)
	{
		$gpuntisID = str_replace('DS_', '', trim((string) $lessonNode->lesson_description));

		$invalidMethod = (empty($gpuntisID) OR empty($this->scheduleModel->schedule->methods->$gpuntisID));
		if ($invalidMethod)
		{
			$this->scheduleModel->scheduleWarnings['LESSON-METHOD']
				= empty($this->scheduleModel->scheduleWarnings['LESSON-METHOD']) ?
				1 : $this->scheduleModel->scheduleWarnings['LESSON-METHOD'] + 1;

			return '';
		}

		$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->description = $gpuntisID;

		$methodID                                                               = $this->scheduleModel->schedule->methods->$gpuntisID->id;
		$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->methodID = $methodID;
		$this->scheduleModel->newSchedule->lessons->{$this->lessonID}->methodID = $methodID;

		return $gpuntisID;
	}

	/**
	 * Validates the teacher attribute and sets corresponding schedule elements
	 *
	 * @param   string $gpuntisID    the teacher id
	 * @param   string $subjectIndex the unique organizational subject id
	 *
	 * @return  boolean  true if valid, otherwise false
	 */
	private function validateTeacher($gpuntisID, $subjectIndex)
	{
		$lessonID     = $this->lessonID;
		$lessonIndex  = $this->lessonIndex;
		$lessonName   = $this->lessonName;
		$teacherFound = false;

		if (empty($gpuntisID))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_TEACHER_MISSING', $lessonName, $lessonID);

			return false;
		}

		$teacherID = null;
		foreach ($this->scheduleModel->schedule->teachers as $teacherKey => $teacher)
		{
			if ($teacher->localUntisID == $gpuntisID)
			{
				$teacherFound    = true;
				$gpuntisID       = $teacherKey;
				$this->teacherID = $teacher->id;
				break;
			}
		}

		if (!$teacherFound)
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_TEACHER_LACKING', $lessonName, $lessonID, $gpuntisID);

			return false;
		}

		if (!isset($this->scheduleModel->schedule->lessons->$lessonIndex->teachers))
		{
			$this->scheduleModel->schedule->lessons->$lessonIndex->teachers = new stdClass;
		}

		if (!isset($this->scheduleModel->schedule->lessons->$lessonIndex->teachers->$gpuntisID))
		{
			$this->scheduleModel->schedule->lessons->$lessonIndex->teachers->$gpuntisID = '';
		}

		if (!empty($this->subjectID))
		{
			$this->scheduleModel->newSchedule->lessons->$lessonID->subjects->{$this->subjectID}->teachers->{$this->teacherID} = '';
		}

		return true;
	}

	/**
	 * Validates the pools attribute and sets corresponding schedule elements
	 *
	 * @param   string $gpuntisIDs the ids of the associated pools as string
	 *
	 * @return  boolean  true if valid, otherwise false
	 */
	private function validatePools($gpuntisIDs)
	{
		if (empty($gpuntisIDs))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf("COM_THM_ORGANIZER_ERROR_LESSON_POOL_MISSING", $this->lessonName, $this->lessonID);

			return false;
		}

		// This is set for the new format in validate subject
		if (!isset($this->scheduleModel->schedule->lessons->{$this->lessonIndex}->pools))
		{
			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->pools = new stdClass;
		}

		$gpuntisIDs = explode(" ", $gpuntisIDs);
		foreach ($gpuntisIDs as $gpuntisID)
		{
			$gpuntisID = str_replace('CL_', '', $gpuntisID);
			$poolID    = null;
			$poolFound = false;
			foreach ($this->scheduleModel->schedule->pools as $poolKey => $pool)
			{
				if ($pool->localUntisID == $gpuntisID)
				{
					$poolFound = true;
					$gpuntisID = $poolKey;

					// The pool was invalid and was not saved to the database.
					if (empty($pool->id))
					{
						return false;
					}

					$poolID    = $pool->id;

					break;
				}
			}

			if (!$poolFound)
			{
				$this->scheduleModel->scheduleErrors[]
					= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_POOL_LACKING', $this->lessonName, $this->lessonID, $gpuntisID);

				return false;
			}

			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->pools->$gpuntisID                            = '';
			$this->scheduleModel->newSchedule->lessons->{$this->lessonID}->subjects->{$this->subjectID}->pools->$poolID = '';
		}

		return true;
	}

	/**
	 * Checks for the validity and consistency of date values
	 *
	 * @param   int $startDT the start date as integer
	 * @param   int $endDT   the end date as integer
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

		$syStartTime     = strtotime($this->scheduleModel->schedule->syStartDate);
		$syEndTime       = strtotime($this->scheduleModel->schedule->syEndDate);
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
	 * @param   string $raw   the string containing the occurrences
	 * @param   int    $start the timestamp of the lesson's begin
	 * @param   int    $end   the timestamp of the lesson's end
	 *
	 * @return  mixed   array if valid, otherwise false
	 */
	private function truncateInstances($raw, $start, $end)
	{
		// Increases the end value one day (Untis uses inclusive dates)
		$end = strtotime('+1 day', $end);

		// 86400 is the number of seconds in a day 24 * 60 * 60
		$offset = floor(($start - strtotime($this->scheduleModel->schedule->syStartDate)) / 86400);
		$length = floor(($end - $start) / 86400);

		// Change occurrences from a string to an array of the appropriate length for iteration
		return str_split(substr($raw, $offset, $length));
	}

	/**
	 * Iterates over possible occurrences and validates them
	 *
	 * @param   array  $potentialInstances an array of 'occurrences'
	 * @param   int    $currentDT          the starting timestamp
	 * @param   array  &$instances         the object containing the instances
	 * @param   string $grid               the grid used by the lesson
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
			$notAllowed  = ($potentialInstance == '0' OR $potentialInstance == 'F');
			if ($notAllowed)
			{
				$currentDT = strtotime('+1 day', $currentDT);
				continue;
			}

			foreach ($instances as $instance)
			{
				$valid = $this->validateInstance($instance, $currentDT, $grid);
				if (!$valid)
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
	 * @param   object &$instance the lesson instance
	 * @param   int    $currentDT the current date time in the iteration
	 * @param   string $grid      the grid used by the lesson
	 *
	 * @return  boolean  true if valid, otherwise false
	 */
	private function validateInstance(&$instance, $currentDT, $grid)
	{
		$currentDate = date('Y-m-d', $currentDT);

		$assigned_day = trim((string) $instance->assigned_day);
		$dow          = date('w', $currentDT);
		$wrongWeekday = $assigned_day != $dow;
		if ($wrongWeekday)
		{
			return true;
		}

		// Sporadic lessons occur on specific dates
		$assigned_date = strtotime(trim((string) $instance->assigned_date));

		// The lesson is sporadic and does not occur on the date being currently iterated
		if (!empty($assigned_date) AND $assigned_date != $currentDT)
		{
			return true;
		}

		$periodNo      = trim((string) $instance->assigned_period);
		$roomAttribute = trim((string) $instance->assigned_room[0]['id']);
		if (empty($roomAttribute))
		{
			$this->createMissingRoomMessage($currentDT, $periodNo);
		}

		$roomsIDs = $this->validateRooms($roomAttribute, $currentDT, $periodNo);
		if ($roomsIDs === false)
		{
			return false;
		}

		$period = $this->scheduleModel->schedule->periods->$grid->$periodNo;
		$this->processInstance($currentDate, $period, $roomsIDs);

		return true;
	}

	/**
	 * Validates the room attribute
	 *
	 * @param   string $roomAttribute the room attribute
	 * @param   int    $currentDT     the timestamp of the date being iterated
	 * @param   string $period        the period attribute
	 *
	 * @return  array  the roomIDs on success, otherwise false
	 */
	private function validateRooms($roomAttribute, $currentDT, $period)
	{
		$currentDate = date('Y-m-d', $currentDT);

		$roomIDs = new stdClass;

		if (empty($roomAttribute))
		{
			return $roomIDs;
		}

		$roomGPUntisIDs = explode(' ', str_replace('RM_', '', $roomAttribute));

		foreach ($roomGPUntisIDs as $roomID)
		{
			$roomFound = false;
			foreach ($this->scheduleModel->schedule->rooms as $roomKey => $room)
			{
				if ($room->localUntisID == $roomID)
				{
					$roomFound            = true;
					$roomID               = $roomKey;
					$roomIDs->{$room->id} = '';
					break;
				}
			}

			if (!$roomFound)
			{
				$pools        = implode(', ', array_keys($this->scheduleModel->schedule->lessons->{$this->lessonIndex}->pools));
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

			if (isset($this->scheduleModel->schedule->calendar->$currentDate))
			{
				if (!isset($this->scheduleModel->schedule->calendar->$currentDate->$period->{$this->lessonIndex}))
				{
					$this->scheduleModel->schedule->calendar->$currentDate->$period->{$this->lessonIndex} = new stdClass;
				}

				$lessonIndexes = get_object_vars($this->scheduleModel->schedule->calendar->$currentDate->$period->{$this->lessonIndex});
				if (!empty($roomID) AND !in_array($roomID, $lessonIndexes))
				{
					$this->scheduleModel->schedule->calendar->$currentDate->$period->{$this->lessonIndex}->$roomID = '';
				}
			}
		}

		return $roomIDs;
	}
}