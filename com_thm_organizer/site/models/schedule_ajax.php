<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSchedule_Ajax
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

define('LESSONS_OF_SEMESTER', 1);
define('LESSONS_OF_PERIOD', 2);
define('LESSONS_INSTANCE', 3);

/**
 * Class provides methods for retrieving program data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSchedule_Ajax extends JModelLegacy
{
	/**
	 * TODO: in helper auslagern bzw. von helper übernehmen
	 * Getter method for programs in database
	 * e.g. for selecting a schedule
	 *
	 * @throws RuntimeException
	 * @return string  a json coded array of available program objects
	 */
	public function getPrograms()
	{
		$languageTag  = THM_OrganizerHelperLanguage::getShortTag();
		$departmentID = JFactory::getApplication()->input->getInt('departmentID', 0);

		$query     = $this->_db->getQuery(true);
		$nameParts = array("program.name_$languageTag", "' ('", " d.abbreviation", "')'");
		$query->select('plan.id, program.version, ' . $query->concatenate($nameParts, "") . ' AS name')
			->from('#__thm_organizer_plan_programs AS plan')
			->leftJoin('#__thm_organizer_programs AS program ON plan.programID = program.id')
			->innerJoin('#__thm_organizer_degrees AS d ON d.id = program.degreeID');

		if ($departmentID != 0)
		{
			$query->where("program.departmentID = '$departmentID'");
		}

		$query->order('name');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($result))
		{
			return '[]';
		}

		return json_encode($result);
	}

	/**
	 * Getter method for pools in database
	 * e.g. for selecting a schedule
	 *
	 * @throws RuntimeException
	 * @return string  all pools in JSON format
	 */
	public function getPools()
	{
		$programInput = JFactory::getApplication()->input->getString('programIDs');
		$programIDs   = explode(',', $programInput);
		$conditions   = array();

		$query = $this->_db->getQuery(true);
		$query->select('id, name')
			->from('#__thm_organizer_plan_pools');

		foreach ($programIDs as $programID)
		{
			$conditions[] = "programID = '$programID'";
		}

		$query->where($conditions, 'OR');

		$query->order('name');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($result))
		{
			return '[]';
		}

		return json_encode($result);
	}

	/**
	 * Getter method for room types in database
	 * e.g. for selecting a schedule
	 *
	 * @throws RuntimeException
	 * @return string  all room types in JSON format
	 */
	public function getRoomTypes()
	{
		$languageTag = THM_OrganizerHelperLanguage::getShortTag();

		$query = $this->_db->getQuery(true);
		$query->select("id, name_$languageTag AS name")
			->from('#__thm_organizer_room_types AS type');

		$query->order('name');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($result))
		{
			return '[]';
		}

		return json_encode($result);
	}

	/**
	 * Getter method for rooms in database
	 * e.g. for selecting a schedule
	 *
	 * @throws RuntimeException
	 * @return string  all rooms in JSON format
	 */
	public function getRooms()
	{
		$departmentID = JFactory::getApplication()->input->getInt('departmentID');
		$typeID       = JFactory::getApplication()->input->getInt('typeID');

		$query = $this->_db->getQuery(true);
		$query->select("roo.id, roo.longname AS name")
			->from('#__thm_organizer_rooms AS roo');

		if ($departmentID != 0)
		{
			$query->leftJoin('#__thm_organizer_department_resources AS dr ON roo.id = dr.roomID');
			$query->where("dr.departmentID = $departmentID");
		}

		if ($departmentID != 0)
		{
			$query->where("roo.typeID = $typeID");
		}

		$query->order('name');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($result))
		{
			return '[]';
		}

		return json_encode($result);
	}

	/**
	 * Getter method for teachers in database
	 * e.g. for selecting a schedule
	 *
	 * @throws RuntimeException
	 * @return string  all teachers in JSON format
	 */
	public function getTeachers()
	{
		$departmentID = JFactory::getApplication()->input->getInt('departmentID');

		$query = $this->_db->getQuery(true);
		$query->select("tea.id, tea.surname AS name")
			->from('#__thm_organizer_teachers AS tea')
			->group('tea.id');

		if ($departmentID != 0)
		{
			$query->leftJoin('#__thm_organizer_department_resources AS dr ON tea.id = dr.teacherID');
			$query->where("dr.departmentID = $departmentID");
		}

		$query->order('name');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($result))
		{
			return '[]';
		}

		return json_encode($result);
	}

	/**
	 * Getter method for lessons in database
	 * e.g. for selecting a schedule
	 *
	 * @throws RuntimeException
	 * @return string  all lessons in JSON format
	 */
	public function getLessonsByPools()
	{
		$input      = JFactory::getApplication()->input;
		$dateString = $input->getString('date');
		$poolInput  = $input->getString('poolIDs');
		$oneDay     = $input->getString('oneDay', false);
		$poolIDs    = explode(',', $poolInput);
		$conditions = array();

		$query       = $this->_db->getQuery(true);
		$teacherName = $query->concatenate(array('SUBSTRING(tea.forename, 1, 1)', 'tea.surname'), '. ');
		$selection   = "less.id, subs.name AS subjectName, subs.subjectNo, ";
		$selection .= "tea.id AS teacherID, $teacherName AS teacherName, ";
		$selection .= "cal.startTime, cal.endTime, cal.schedule_date, cal.delta AS calendarDelta";
		$query->select($selection)
			->from('#__thm_organizer_plan_pools AS poo')
			->innerJoin('#__thm_organizer_lesson_pools AS lepo ON poo.id = lepo.poolID')
			->innerJoin('#__thm_organizer_lesson_subjects AS lesu ON lepo.subjectID = lesu.id')
			->innerJoin('#__thm_organizer_lessons AS less ON lesu.lessonID = less.id')
			->innerJoin('#__thm_organizer_calendar AS cal ON less.id = cal.lessonID')
			->leftJoin('#__thm_organizer_plan_subjects AS subs ON lesu.subjectID = subs.id')
			->leftJoin('#__thm_organizer_lesson_teachers AS letea ON lesu.id = letea.subjectID')
			->leftJoin('#__thm_organizer_teachers AS tea ON letea.teacherID = tea.id');

		foreach ($poolIDs as $poolID)
		{
			$conditions[] = "poo.id = $poolID";
		}

		$poolConditions = '(' . implode($conditions, ' OR ') . ')';
		$query->where($poolConditions);

		if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $dateString) !== 1)
		{
			return '[]';
		}

		if ($oneDay)
		{
			$query->where("cal.schedule_date = '$dateString'");
		}
		else
		{
			$selectedDate  = new DateTime($dateString);
			$dayNumber     = $selectedDate->format('N');
			$intervalAfter = 7 - $dayNumber;
			$maxDate       = $selectedDate->add(new DateInterval('P' . $intervalAfter . 'D'));
			$maxDateString = $maxDate->format('Y-m-d');

			$intervalBefore = $dayNumber;
			$selectedDate   = new DateTime($dateString);
			$minDate        = $selectedDate->sub(new DateInterval('P' . $intervalBefore . 'D'));
			$minDateString  = $minDate->format('Y-m-d');

			$query->where("cal.schedule_date < '$maxDateString'")
				->where("cal.schedule_date > '$minDateString'");
		}

		$query->where("cal.delta != 'removed'");

		$query->order('subjectName');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($result))
		{
			return '[]';
		}

		return json_encode($result);
	}

	/**
	 * Getter for lessons in database,
	 * filtered by a teacher, departmentID and date.
	 * e.g. for selecting a schedule
	 *
	 * @throws RuntimeException
	 * @return string  all lessons in JSON format
	 */
	public function getLessonsByTeacher()
	{
		$input        = JFactory::getApplication()->input;
		$departmentID = $input->getInt('departmentID');
		$teacherID    = $input->getInt('teacherID');
		$dateString   = $input->getString('date');
		$oneDay       = $input->getString('oneDay', false);

		$query       = $this->_db->getQuery(true);
		$teacherName = $query->concatenate(array('SUBSTRING(tea.forename, 1, 1)', 'tea.surname'), '. ');
		$selection   = "subs.id AS subjectID, subs.name AS subjectName, subs.subjectNo, less.delta AS lessonDelta, ";
		$selection .= "tea.id AS teacherID, $teacherName AS teacherName, ";
		$selection .= "cal.startTime, cal.endTime, cal.schedule_date";

		$query->select($selection)
			->from('#__thm_organizer_teachers AS tea')
			->innerJoin('#__thm_organizer_lesson_teachers AS letea ON tea.id = letea.teacherID')
			->innerJoin('#__thm_organizer_lesson_subjects AS lesu ON letea.subjectID = lesu.id')
			->innerJoin('#__thm_organizer_lessons AS less ON lesu.lessonID = less.id')
			->innerJoin('#__thm_organizer_calendar AS cal ON less.id = cal.lessonID')
			->leftJoin('#__thm_organizer_plan_subjects AS subs ON lesu.subjectID = subs.id');

		if ($departmentID != 0)
		{
			$query->leftJoin('#__thm_organizer_department_resources AS dr ON tea.id = dr.teacherID');
			$query->where("dr.departmentID = $departmentID");
		}

		if ($oneDay)
		{
			$query->where("cal.schedule_date = '$dateString'");
		}
		else
		{
			$selectedDate  = new DateTime($dateString);
			$dayNumber     = $selectedDate->format('N');
			$intervalAfter = 7 - $dayNumber;
			$maxDate       = $selectedDate->add(new DateInterval('P' . $intervalAfter . 'D'));
			$maxDateString = $maxDate->format('Y-m-d');

			$intervalBefore = $dayNumber;
			$selectedDate   = new DateTime($dateString);
			$minDate        = $selectedDate->sub(new DateInterval('P' . $intervalBefore . 'D'));
			$minDateString  = $minDate->format('Y-m-d');

			$query->where("cal.schedule_date < '$maxDateString'")
				->where("cal.schedule_date > '$minDateString'");
		}

		$query->where("cal.delta != 'removed'")
			->where("tea.id = $teacherID");

		$query->order('subjectName');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($result))
		{
			return '[]';
		}

		return json_encode($result);
	}

	/**
	 * Getter for lessons in database,
	 * filtered by a room, departmentID and date.
	 * e.g. for selecting a schedule
	 *
	 * @throws RuntimeException
	 * @return string  all lessons in JSON format
	 */
	public function getLessonsByRooms()
	{
		$input      = JFactory::getApplication()->input;
		$dateString = $input->getString('date');
		$oneDay     = $input->getString('oneDay', false);
		$roomInput  = $input->getString('roomIDs');
		$roomIDs    = explode(',', $roomInput);
		$conditions = array();

		$query       = $this->_db->getQuery(true);
		$teacherName = $query->concatenate(array('SUBSTRING(tea.forename, 1, 1)', 'tea.surname'), '. ');
		$selection   = "subs.id AS subjectID, subs.name AS subjectName, subs.subjectNo, less.delta AS lessonDelta, ";
		$selection .= "tea.id AS teacherID, $teacherName AS teacherName, ";
		$selection .= "cal.startTime, cal.endTime, cal.schedule_date";

		$query->select($selection)
			->from('#__thm_organizer_lessons AS less')
			->innerJoin('#__thm_organizer_lesson_configurations AS leco ON less.id = leco.lessonID')
			->innerJoin('#__thm_organizer_lesson_subjects AS lesu ON less.id = lesu.lessonID')
			->innerJoin('#__thm_organizer_calendar AS cal ON less.id = cal.lessonID')
			->leftJoin('#__thm_organizer_plan_subjects AS subs ON lesu.subjectID = subs.id')
			->leftJoin('#__thm_organizer_lesson_teachers AS letea ON lesu.id = letea.subjectID')
			->leftJoin('#__thm_organizer_teachers AS tea ON letea.teacherID = tea.id');

		foreach ($roomIDs as $roomID)
		{
			// Regex for e.g. "rooms":{"xyz123":"","roomID":""
			$regexp       = '[[.quotation-mark.]]rooms[[.quotation-mark.]][[.:.]][[.{.]]' .
				'([[.quotation-mark.]][[:alnum:]]*[[.quotation-mark.]][[.colon.]]?[[.comma.]]?)*' .
				'[[.quotation-mark.]]' . $roomID . '[[.quotation-mark.]][[.colon.]]' .
				'[[.quotation-mark.]][^removed]';
			$conditions[] = "leco.configuration REGEXP '$regexp'";
		}

		$roomConditions = '(' . implode($conditions, ' OR ') . ')';
		$query->where($roomConditions);

		if ($oneDay)
		{
			$query->where("cal.schedule_date = '$dateString'");
		}
		else
		{
			$selectedDate  = new DateTime($dateString);
			$dayNumber     = $selectedDate->format('N');
			$intervalAfter = 7 - $dayNumber;
			$maxDate       = $selectedDate->add(new DateInterval('P' . $intervalAfter . 'D'));
			$maxDateString = $maxDate->format('Y-m-d');

			$intervalBefore = $dayNumber;
			$selectedDate   = new DateTime($dateString);
			$minDate        = $selectedDate->sub(new DateInterval('P' . $intervalBefore . 'D'));
			$minDateString  = $minDate->format('Y-m-d');

			$query->where("cal.schedule_date < '$maxDateString'")
				->where("cal.schedule_date > '$minDateString'");
		}

		$query->where("cal.delta != 'removed'");

		$query->order('subjectName');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($result))
		{
			return '[]';
		}

		return json_encode($result);
	}

	/**
	 * saves lessons of a whole semester in the personal schedule of the logged in user
	 *
	 * @throws RuntimeException
	 * @return boolean
	 */
	public function saveLesson()
	{
		$input        = JFactory::getApplication()->input;
		$lessonID     = $input->getInt('lessonID');
		$config       = $input->getInt('config', LESSONS_OF_PERIOD);
		$dateInput    = $input->getString('date');
		$timeInput    = $input->getString('time');
		$scheduleDate = DateTime::createFromFormat('Y-m-d H:i:s', $dateInput . ' ' . $timeInput);
		$user         = JFactory::getUser();

		/** no logged in user */
		if (empty($user->id))
		{
			return false;
		}

		/** get configurations of selected lesson */
		$configurations = $this->getConfigurations($lessonID, $config, $scheduleDate);
		if (empty($configurations))
		{
			return false;
		}

		/** insert in database */
		$query = $this->_db->getQuery(true);

		$columns = array('userID', 'lessonID', 'user_date', 'configuration');
		$values  = array(
			$user->id,
			$lessonID,
			$this->_db->quote(date('Y-m-d H:i:s')),
			$this->_db->quote(json_encode($configurations))
		);

		$query->insert($this->_db->quoteName('#__thm_organizer_user_lessons'))
			->columns($this->_db->quoteName($columns))
			->values(implode(',', $values));
		$this->_db->setQuery((string) $query);

		try
		{
			$success = $this->_db->execute();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '[]';
		}

		/** returns (js-)'id' of lesson in case of success */
		return $success ? $lessonID . '-' . $dateInput . '-' . $timeInput : false;
	}

	/**
	 * loads matching configurations of a lesson
	 *
	 * @param   int      $lessonID     id of lesson
	 * @param   int      $config       global params like LESSONS_OF_SEMESTER
	 * @param   DateTime $scheduleDate date of lesson
	 *
	 * @throws RuntimeException
	 * @return array|boolean
	 */
	private function getConfigurations($lessonID, $config, $scheduleDate)
	{
		$query = $this->_db->getQuery(true);
		$query->select('map.id, cal.schedule_date, cal.startTime')
			->from('#__thm_organizer_calendar_configuration_map AS map')
			->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
			->where("lessonID = '$lessonID'")
			->where("delta != 'removed'");

		if ($config !== LESSONS_OF_SEMESTER)
		{
			$time    = $scheduleDate->format('H:i:s');
			$date    = $scheduleDate->format('Y-m-d');
			$weekday = ((int) $scheduleDate->format('N')) + 1;

			/** lessons for same day of the week and same time */
			$query->where("startTime = '$time'");
			$query->where("DAYOFWEEK(schedule_date) = '$weekday'");

			/** only the selected instance of lesson */
			if ($config == LESSONS_INSTANCE)
			{
				$query->where("schedule_date = '$date'");
			}
		}

		$query->order('id');
		$this->_db->setQuery((string) $query);

		try
		{
			$calendars = $this->_db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($calendars))
		{
			return false;
		}

		/** collect configurations to encode them into json */
		$configurations = [];
		foreach ($calendars as $configuration)
		{
			$configurations[] = $configuration->id;
		}

		return $configurations;
	}

	/**
	 * gets schedule of now logged in user.
	 * returns false in case no user is logged in.
	 *
	 * @return string lessons in JSON format - empty in case of errors
	 */
	public function getUsersSchedule()
	{
		$user = JFactory::getUser();

		if ($user->guest OR empty($user->id))
		{
			return '[]';
		}

		$query     = $this->_db->getQuery(true);
		$selection = "usle.lessonID AS id, less.delta AS lessonDelta, ";
		$selection .= "subs.id AS subjectID, subs.name AS subjectName, subs.subjectNo, ";
		$selection .= "cal.startTime, cal.endTime, cal.schedule_date";

		$query->select($selection)
			->from('#__thm_organizer_user_lessons AS usle')
			->innerJoin('#__thm_organizer_lessons AS less ON usle.lessonID = less.id')
			->innerJoin('#__thm_organizer_lesson_subjects AS lesu ON less.id = lesu.lessonID')
			->innerJoin('#__thm_organizer_lesson_configurations AS leco ON lesu.id = leco.lessonID')
			->innerJoin('#__thm_organizer_calendar AS cal ON less.id = cal.lessonID')
			->innerJoin('#__thm_organizer_plan_subjects AS subs ON lesu.subjectID = subs.id')
			->where("userID = $user->id");

		/* TODO: zweite query: map ids json decode - foreach filtern und damit teachers und rooms holen
		$teacherName = $query->concatenate(array('SUBSTRING(tea.forename, 1, 1)', 'tea.surname'), '. ');
		$selection .= "tea.id AS teacherID, $teacherName AS teacherName, ";
		 ->leftJoin('#__thm_organizer_lesson_teachers AS letea ON lesu.id = letea.subjectID')
		 ->leftJoin('#__thm_organizer_teachers AS tea ON letea.teacherID = tea.id')
		*/

		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($result))
		{
			return '[]';
		}

		return json_encode($result);
	}
}
