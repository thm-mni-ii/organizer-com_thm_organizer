<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerModelLessons
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
	 * A unique identifier for the lesson across schedules. (dpt., sem., id). Only used in json schedules
	 *
	 * @var string
	 */
	private $lessonIndex = '';

	/**
	 * A unique identifier for the source plan in the form ORG-PP-YY, where ORG isthe abbreviation for the organization,
	 * PP the abbreviation for the planning period name and YY the short form for the year.
	 *
	 * @var bool
	 */
	private $planName = '';

	/**
	 * Whether or not rooms should produce blocking errors.
	 *
	 * @var bool
	 */
	private $roomsRequired = true;

	/**
	 * The schedule model.
	 *
	 * @var object
	 */
	private $scheduleModel = null;

	/**
	 * Creates the lesson model
	 *
	 * @param   object &$scheduleModel the model for the schedule
	 * @param   object &$xmlObject     the xml object being validated
	 */
	public function __construct(&$scheduleModel, &$xmlObject)
	{
		$this->scheduleModel = $scheduleModel;
		$this->_xmlObject    = $xmlObject;
		$formData            = JFactory::getApplication()->input->get('jform', array(), 'array');
		$this->roomsRequired = !empty($formData['rooms_required']);
		$this->setPlanName();
	}

	/**
	 * Determines how the missing room attribute will be handled
	 *
	 * @param   string $currentDT the timestamp of the date being iterated
	 * @param   string $period    the value of the period attribute
	 *
	 * @return  boolean  true if blocking and not set elsewhere, otherwise false
	 */
	private function handleMissingRooms($currentDT, $period)
	{
		$currentDate = date('Y-m-d', $currentDT);

		// Attribute has also not been set by any other lesson
		if (!isset($this->scheduleModel->schedule->calendar->$currentDate->$period->{$this->lessonIndex}))
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
			if (!in_array($error, $this->scheduleModel->scheduleErrors) AND !in_array($error, $this->scheduleModel->scheduleWarnings))
			{
				if ($this->roomsRequired)
				{
					$this->scheduleModel->scheduleErrors[] = $error;

					return true;
				}
				else
				{
					$this->scheduleModel->scheduleWarnings[] = $error;

					return false;
				}
			}
		}

		// Attribute has been set by another lesson
		return false;
	}

	/**
	 * Saves the lessons from the schedule object to the database and triggers functions for saving lesson associations.
	 *
	 * @return void saves lessons to the database
	 */
	public function saveLessons()
	{
		foreach ($this->scheduleModel->schedule->lessons as $lesson)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lessons', 'thm_organizerTable');

			$data              = array();
			$data['gpuntisID'] = $lesson->gpuntisID;
			$data['planName']  = $this->planName;
			$table->load($data);

			if (!empty($lesson->methodID))
			{
				$data['methodID'] = $lesson->methodID;
			}

			// Delta will be 'calculated' later but explicitly overwritten now irregardless
			$data['delta'] = '';

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}

			$this->saveLessonSubjects($table->id, $lesson->plan_subjects);
		}
	}

	/**
	 * Saves the lesson pools from the schedule object to the database and triggers functions for saving lesson associations.
	 *
	 * @param string $lessonSubjectID the db id of the lesson subject association
	 * @param object $pools           the pools associated with the subject
	 * @param string $subjectNo       the subject's id in documentation
	 *
	 * @return void saves lessons to the database
	 */
	private function saveLessonPools($lessonSubjectID, $pools, $subjectID, $subjectNo)
	{
		foreach ($pools as $poolID => $delta)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lesson_pools', 'thm_organizerTable');

			$data              = array();
			$data['subjectID'] = $lessonSubjectID;
			$data['poolID']    = $poolID;
			$table->load($data);

			// Delta will be 'calculated' later but explicitly overwritten now irregardless
			$data['delta'] = '';

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}

			if (!empty($subjectNo))
			{
				$this->savePlanSubjectMapping($subjectID, $poolID, $subjectNo);
			}
		}
	}

	/**
	 * Saves the lesson subjectss from the schedule object to the database and triggers functions for saving lesson
	 * associations.
	 *
	 * @param string $lessonID the db id of the lesson subject association
	 * @param object $subjects the subjects associated with the lesson
	 *
	 * @return void saves lessons to the database
	 */
	private function saveLessonSubjects($lessonID, $subjects)
	{
		foreach ($subjects as $subjectID => $abstractConfig)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lesson_subjects', 'thm_organizerTable');

			$data              = array();
			$data['lessonID']  = $lessonID;
			$data['subjectID'] = $subjectID;
			$table->load($data);

			// Delta will be 'calculated' later but explicitly overwritten now irregardless
			$data['delta'] = '';

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}

			$subjectNo = empty($abstractConfig->subjectNo) ? null : $abstractConfig->subjectNo;
			$this->saveLessonPools($table->id, $abstractConfig->pools, $subjectID, $subjectNo);
			$this->saveLessonTeachers($table->id, $abstractConfig->teachers);
		}
	}

	/**
	 * Saves the lesson pools from the schedule object to the database and triggers functions for saving lesson associations.
	 *
	 * @param string $subjectID the db id of the lesson subject association
	 * @param object $teachers  the teacherss associated with the subject
	 *
	 * @return void saves lessons to the database
	 */
	private function saveLessonTeachers($subjectID, $teachers)
	{
		foreach ($teachers as $teacherID => $delta)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lesson_teachers', 'thm_organizerTable');

			$data              = array();
			$data['subjectID'] = $subjectID;
			$data['teacherID'] = $teacherID;
			$table->load($data);

			// Delta will be 'calculated' later but explicitly overwritten now irregardless
			$data['delta'] = '';

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}
		}
	}

	/**
	 * Attempts to associate subjects used in scheduling with their documentation
	 *
	 * @param string $planSubjectID the id of the subject in the plan_subjects table
	 * @param string $poolID        the id of the pool in the plan_pools table
	 * @param string $subjectNo     the subject id used in documentation
	 *
	 * @return void saves/updates a database entry
	 */
	private function savePlanSubjectMapping($planSubjectID, $poolID, $subjectNo)
	{
		$dbo = JFactory::getDbo();

		// Get the mapping boundaries for the program
		$boundariesQuery = $dbo->getQuery(true);
		$boundariesQuery->select('lft, rgt')
			->from('#__thm_organizer_mappings as m')
			->innerJoin('#__thm_organizer_programs as prg on m.programID = prg.id')
			->innerJoin('#__thm_organizer_plan_programs as p_prg on prg.id = p_prg.programID')
			->innerJoin('#__thm_organizer_plan_pools as p_pool on p_prg.id = p_pool.programID')
			->where("p_pool.id = '$poolID'");
		$dbo->setQuery((string) $boundariesQuery);

		try
		{
			$boundaries = $dbo->loadAssoc();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return;
		}

		if (empty($boundaries))
		{
			return;
		}

		// Get the id for the subject documentation
		$subjectQuery = $dbo->getQuery(true);
		$subjectQuery->select('subjectID')
			->from('#__thm_organizer_mappings as m')
			->innerJoin('#__thm_organizer_subjects as s on m.subjectID = s.id')
			->where("m.lft > '{$boundaries['lft']}'")
			->where("m.rgt < '{$boundaries['rgt']}'")
			->where("s.externalID = '$subjectNo'");
		$dbo->setQuery((string) $subjectQuery);

		try
		{
			$subjectID = $dbo->loadResult();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return;
		}

		if (empty($subjectID))
		{
			return;
		}

		$data  = array('subjectID' => $subjectID, 'plan_subjectID' => $planSubjectID);
		$table = JTable::getInstance('subject_mappings', 'thm_organizerTable');
		$table->load($data);
		$table->save($data);
	}

	/**
	 * Creates a unique identifier for the source schedule. As lesson ids are numeric this is necessary to identify the
	 * lessons across organizations, planning periods and years.
	 *
	 * @return void  sets $this->planName
	 */
	private function setPlanName()
	{
		$planName = $this->scheduleModel->schedule->departmentname;
		$planName .= "-" . $this->scheduleModel->schedule->semestername;
		$planName .= "-" . substr($this->scheduleModel->schedule->endDate, 2, 2);
		$this->planName = $planName;
	}

	/**
	 * Checks whether subject nodes have the expected structure and required information
	 *
	 * @return void
	 */
	public function validate()
	{
		if (empty($this->_xmlObject->lessons))
		{
			$this->scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_LESSONS_MISSING");

			return;
		}

		$this->scheduleModel->schedule->lessons = new stdClass;

		foreach ($this->_xmlObject->lessons->children() as $lessonNode)
		{
			$this->validateIndividual($lessonNode);
		}

		if (!empty($this->scheduleWarnings['LESSON-METHOD']))
		{
			$warningCount = $this->scheduleWarnings['LESSON-METHOD'];
			unset($this->scheduleWarnings['LESSON-METHOD']);
			$this->scheduleWarnings[] = JText::sprintf('COM_THM_ORGANIZER_WARNING_METHODID', $warningCount);
		}

		return;
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
		$this->lessonIndex = '';
		$this->lessonName  = '';

		$this->lessonID = $this->validateUntisID(trim((string) $lessonNode[0]['id']));
		if (empty($this->lessonID))
		{
			return;
		}

		$department        = $this->scheduleModel->schedule->departmentname;
		$semester          = $this->scheduleModel->schedule->semestername;
		$this->lessonIndex = $department . $semester . "_" . $this->lessonID;

		if (!isset($this->scheduleModel->schedule->lessons->{$this->lessonIndex}))
		{
			$this->scheduleModel->schedule->lessons->{$this->lessonIndex} = new stdClass;
		}

		$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->gpuntisID = $this->lessonID;

		$subjectID  = str_replace('SU_', '', trim((string) $lessonNode->lesson_subject[0]['id']));
		$lessonName = $this->validateSubjects($subjectID, $department);
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

		$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->name = $lessonName;

		$subjectIndex = $department . "_" . $subjectID;
		$teacherID    = str_replace('TR_', '', trim((string) $lessonNode->lesson_teacher[0]['id']));
		$teacherValid = $this->validateTeacher($teacherID, $subjectIndex);
		if (!$teacherValid)
		{
			return;
		}

		$possibleGrid                                                       = (string) $lessonNode->timegrid;
		$grid                                                               = empty($possibleGrid) ? 'Haupt-Zeitraster' : $possibleGrid;
		$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->grid = $grid;

		$poolIDs    = (string) $lessonNode->lesson_classes[0]['id'];
		$poolsValid = $this->validatePools($poolIDs, $grid, $subjectIndex);
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

		$rawOccurrences = trim((string) $lessonNode->occurence);
		$occurrences    = $this->validateRawOccurrences($rawOccurrences, $startDT, $endDT);

		$comment = trim((string) $lessonNode->text);

		/**
		 * Ensures that the comment is set and empty. '.' has been used to ensure that a comment is correctly associated
		 * with the correct lesson in Untis print views.
		 */
		if (empty($comment) OR $comment == '.')
		{
			$comment = '';
		}

		$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->comment = $comment;

		$times = $lessonNode->xpath("times/time");

		// Cannot produce blocking errors
		$this->validateOccurrences($occurrences, $startDT, $times, $grid);
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

		if (!isset($this->scheduleModel->schedule->lessons->{$this->lessonIndex}->subjects))
		{

			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->subjects      = new stdClass;
			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->plan_subjects = new stdClass;
		}

		$subjectID = $this->scheduleModel->schedule->subjects->$subjectIndex->id;
		$subjectNo = $this->scheduleModel->schedule->subjects->$subjectIndex->subjectNo;

		if (!empty($gpuntisID)
			AND !array_key_exists($subjectIndex, $this->scheduleModel->schedule->lessons->{$this->lessonIndex}->subjects)
		)
		{
			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->subjects->$subjectIndex              = '';
			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->plan_subjects->$subjectID            = new stdClass;
			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->plan_subjects->$subjectID->subjectNo = $subjectNo;
			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->plan_subjects->$subjectID->pools     = new stdClass;
			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->plan_subjects->$subjectID->teachers  = new stdClass;
		}

		$subjectIndexes = array_keys((array) $this->scheduleModel->schedule->lessons->{$this->lessonIndex}->subjects);
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
		$methodID = str_replace('DS_', '', trim((string) $lessonNode->lesson_description));

		$invalidMethod = (empty($methodID) OR empty($this->scheduleModel->schedule->methods->$methodID));
		if ($invalidMethod)
		{
			$this->scheduleModel->scheduleWarnings['LESSON-METHOD']
				= empty($this->scheduleModel->scheduleWarnings['LESSON-METHOD']) ?
				1 : $this->scheduleModel->scheduleWarnings['LESSON-METHOD'] + 1;

			return '';
		}

		$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->description = $methodID;
		$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->methodID
		                                                                           = $this->scheduleModel->schedule->methods->$methodID->id;

		return $methodID;
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
		$teacherFound = false;
		if (empty($gpuntisID))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_TEACHER_MISSING', $this->lessonName, $this->lessonID);

			return false;
		}

		$teacherID = null;
		foreach ($this->scheduleModel->schedule->teachers as $teacherKey => $teacher)
		{
			if ($teacher->localUntisID == $gpuntisID)
			{
				$teacherFound = true;
				$gpuntisID    = $teacherKey;
				$teacherID    = $teacher->id;
				break;
			}
		}

		if (!$teacherFound)
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_TEACHER_LACKING', $this->lessonName, $this->lessonID, $gpuntisID);

			return false;
		}

		if (!isset($this->scheduleModel->schedule->lessons->{$this->lessonIndex}->teachers))
		{
			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->teachers = new stdClass;
		}

		if (!array_key_exists($gpuntisID, $this->scheduleModel->schedule->lessons->{$this->lessonIndex}->teachers))
		{
			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->teachers->$gpuntisID = '';
		}

		$subjectID = $this->scheduleModel->schedule->subjects->$subjectIndex->id;
		if (!empty($subjectID))
		{
			$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->plan_subjects->$subjectID->teachers->$teacherID = '';
		}

		return true;
	}

	/**
	 * Validates the pools attribute and sets corresponding schedule elements
	 *
	 * @param   string $gpuntisIDs   the ids of the associated pools as string
	 * @param   string $grid         the name of the grid in which this lesson should be displayed
	 * @param   string $subjectIndex the unique organizational subject id
	 *
	 * @return  boolean  true if valid, otherwise false
	 */
	private function validatePools($gpuntisIDs, $grid, $subjectIndex)
	{
		if (empty($gpuntisIDs) AND !isset($this->scheduleModel->schedule->lessons->{$this->lessonIndex}->pools))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf("COM_THM_ORGANIZER_ERROR_LESSON_POOL_MISSING", $this->lessonName, $this->lessonID);

			return false;
		}
		elseif (!empty($gpuntisIDs))
		{
			if (!isset($this->scheduleModel->schedule->lessons->{$this->lessonIndex}->pools))
			{
				$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->pools = new stdClass;
			}

			$gpuntisIDs = explode(" ", $gpuntisIDs);
			$gridFound  = false;
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
						$poolID    = $pool->id;
						if ($grid == $pool->grid)
						{
							$gridFound = true;
						}

						break;
					}
				}

				if (!$poolFound)
				{
					$this->scheduleModel->scheduleErrors[]
						= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_POOL_LACKING', $this->lessonName, $this->lessonID, $gpuntisID);

					return false;
				}

				$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->pools->$gpuntisID = '';

				$subjectID = $this->scheduleModel->schedule->subjects->$subjectIndex->id;
				if (!empty($subjectID))
				{
					$this->scheduleModel->schedule->lessons->{$this->lessonIndex}->plan_subjects->$subjectID->pools->$poolID = '';
				}
			}

			if (!$gridFound)
			{
				$this->scheduleModel->scheduleErrors[]
					= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_POOL_GRID_INCONSISTENT', $this->lessonName, $this->lessonID, $grid);

				return false;
			}
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
	private function validateRawOccurrences($raw, $start, $end)
	{
		if (empty($raw))
		{
			$this->scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_OCC_MISSING', $this->lessonName, $this->lessonID);

			return false;
		}

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
	 * @param   array  $occurrences an array of 'occurrences'
	 * @param   int    $currentDT   the starting timestamp
	 * @param   array  &$instances  the object containing the instances
	 * @param   string $grid        the grid used by the lesson
	 *
	 * @return  void
	 */
	private function validateOccurrences($occurrences, $currentDT, &$instances, $grid)
	{
		if (count($instances) == 0)
		{
			return;
		}

		foreach ($occurrences as $occurrence)
		{
			$currentDate      = date('Y-m-d', $currentDT);
			$outOfBounds      = empty($this->scheduleModel->schedule->calendar->$currentDate);
			$notPlannedOnDate = ($occurrence == '0' OR $occurrence == 'F');
			if ($outOfBounds OR $notPlannedOnDate)
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

		// Sporadic lessons occur on specific dates
		$assigned_date = strtotime(trim((string) $instance->assigned_date));

		// The lesson is sporadic and does not occur on the date being currently iterated
		if (!empty($assigned_date) AND $assigned_date != $currentDT)
		{
			return true;
		}

		$day      = trim((string) $instance->assigned_day);
		$validDay = $this->validateInstanceDay($day, $currentDT);

		// The lesson does not occur on the day (true) or the day is invalid (false)
		if ($validDay === true OR $validDay === false)
		{
			return $validDay;
		}

		$period = $this->validatePeriod(trim((string) $instance->assigned_period), $currentDate, $grid);
		if (!$period)
		{
			return false;
		}

		$roomAttribute = trim((string) $instance->assigned_room[0]['id']);
		if (empty($roomAttribute))
		{
			$throwError = $this->handleMissingRooms($currentDT, $period);
			if ($throwError)
			{
				return false;
			}
		}
		else
		{
			$roomsValid = $this->validateRooms($roomAttribute, $currentDT, $period);
			if (!$roomsValid)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Validates whether the instance day attribute
	 *
	 * @param   string $day       the numeric day of the week
	 * @param   int    $currentDT the current date time in the iteration
	 *
	 * @return  mixed  boolean false if the day is missing, true if the lesson
	 *                 does not occur on the given day, otherwise the integer dow
	 */
	private function validateInstanceDay($day, $currentDT)
	{
		if (empty($day))
		{
			$error = JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_PERIOD_DAY_MISSING', $this->lessonName, $this->lessonID);
			if (!in_array($error, $this->scheduleModel->scheduleErrors))
			{
				$this->scheduleModel->scheduleErrors[] = $error;

				return false;
			}
		}

		if ($day != date('w', $currentDT))
		{
			// Does not occur on this date, no error
			return true;
		}

		return $day;
	}

	/**
	 * Validates the period attribute of an instance
	 *
	 * @param   string $period      the period attribute
	 * @param   string $currentDate the date in the current iteration
	 * @param   string $grid        the grid used by the lesson
	 *
	 * @return  boolean  true on success,
	 */
	private function validatePeriod($period, $currentDate, $grid)
	{
		if (empty($period))
		{
			$error = JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_PERIOD_NUMBER_MISSING', $this->lessonName, $this->lessonID);
			if (!in_array($error, $this->scheduleModel->scheduleErrors))
			{
				$this->scheduleModel->scheduleErrors[] = $error;

				return false;
			}
		}

		if (!isset($this->scheduleModel->schedule->periods->$grid))
		{
			$error = JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_GRID_INCONSISTENT', $this->lessonName, $this->lessonID, $grid);
			if (!in_array($error, $this->scheduleModel->scheduleErrors))
			{
				$this->scheduleModel->scheduleErrors[] = $error;

				return false;
			}
		}

		if (!isset($this->scheduleModel->schedule->periods->$grid->$period))
		{
			$error = JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_GRID_PERIOD_INCONSISTENT', $this->lessonName, $this->lessonID, $period, $grid);
			if (!in_array($error, $this->scheduleModel->scheduleErrors))
			{
				$this->scheduleModel->scheduleErrors[] = $error;

				return false;
			}
		}

		// Should not occur, but creates the period anyway
		if (!isset($this->scheduleModel->schedule->calendar->$currentDate->$period))
		{
			$this->scheduleModel->schedule->calendar->$currentDate->$period = new stdClass;
		}

		return $period;
	}

	/**
	 * Validates the room attribute
	 *
	 * @param   string $roomAttribute the room attribute
	 * @param   int    $currentDT     the timestamp of the date being iterated
	 * @param   string $period        the period attribute
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	private function validateRooms($roomAttribute, $currentDT, $period)
	{
		$currentDate = date('Y-m-d', $currentDT);

		$roomIDs = explode(' ', str_replace('RM_', '', $roomAttribute));
		foreach ($roomIDs as $roomID)
		{
			$roomFound = false;
			foreach ($this->scheduleModel->schedule->rooms as $roomKey => $room)
			{
				if ($room->localUntisID == $roomID)
				{
					$roomFound = true;
					$roomID    = $roomKey;
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
			else
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

		return true;
	}
}