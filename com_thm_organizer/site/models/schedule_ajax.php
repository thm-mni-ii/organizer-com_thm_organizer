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
	 * Getter method for all grids in database
	 *
	 * @return string all grids in JSON format
	 *
	 * @throws RuntimeException
	 */
	public function getGrids()
	{
		$this->_db   = JFactory::getDbo();
		$languageTag = THM_OrganizerHelperLanguage::getShortTag();
		$query       = $this->_db->getQuery(true);
		$query->select("name_$languageTag, grid")
			->from('#__thm_organizer_grids');
		$this->_db->setQuery((string) $query);

		try
		{
			$grids = $this->_db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '{}';
		}

		return json_encode($grids);
	}

	/**
	 * Getter method for programs in database
	 * e.g. for selecting a schedule
	 *
	 * @return string  all programs in JSON format
	 *
	 * @throws RuntimeException
	 */
	public function getPrograms()
	{
		$this->_db    = JFactory::getDbo();
		$languageTag  = THM_OrganizerHelperLanguage::getShortTag();
		$departmentID = JFactory::getApplication()->input->getInt('departmentID', 0);

		$query     = $this->_db->getQuery(true);
		$nameParts = array("program.name_$languageTag", "' ('", " d.abbreviation", "')'");
		$query->select('plan.id, program.version, ' . $query->concatenate($nameParts, "") . ' AS name')
			->from('#__thm_organizer_plan_programs AS plan')
			->innerJoin('#__thm_organizer_programs AS program ON plan.programID = program.id')
			->leftJoin('#__thm_organizer_degrees AS d ON d.id = program.degreeID');

		if ($departmentID != 0)
		{
			$query->where("program.departmentID = '$departmentID'");
		}

		/** this MySQL regexp means: [0-9A-Za-z]+[.][0-9A-Za-z]+ */
		$query->where("plan.gpuntisID REGEXP '^[[:alnum:]]+[[.period.]][[:alnum:]]+'");

		$query->order('name');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '{}';
		}

		return json_encode($result);
	}

	/**
	 * Getter method for pools in database
	 * e.g. for selecting a schedule
	 *
	 * @return string  all pools in JSON format
	 *
	 * @throws RuntimeException
	 */
	public function getPools()
	{
		$this->_db    = JFactory::getDbo();
		$programInput = JFactory::getApplication()->input->getString('programIDs');
		$programIDs   = explode(',', $programInput);
		$conditions   = array();

		$query = $this->_db->getQuery(true);
		$query->select('id, name, gpuntisID')
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
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '{}';
		}

		return json_encode($result);
	}

	/**
	 * Getter method for rooms in database
	 * e.g. for selecting a schedule
	 *
	 * @return string  all rooms in JSON format
	 *
	 * @throws RuntimeException
	 */
	public function getRooms()
	{
		$this->_db    = JFactory::getDbo();
		$departmentID = JFactory::getApplication()->input->getInt('departmentID');

		$query = $this->_db->getQuery(true);
		$query->select("roo.id, roo.longname AS name")
			->from('#__thm_organizer_rooms AS roo');

		if ($departmentID != 0)
		{
			$query->leftJoin('#__thm_organizer_department_resources AS dr ON roo.id = dr.roomID');
			$query->where("dr.departmentID = $departmentID");
		}

		$query->order('name');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '{}';
		}

		return json_encode($result);
	}

	/**
	 * Getter method for teachers in database
	 * e.g. for selecting a schedule
	 *
	 * @return string  all teachers in JSON format
	 *
	 * @throws RuntimeException
	 */
	public function getTeachers()
	{
		$this->_db    = JFactory::getDbo();
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
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '{}';
		}

		return json_encode($result);
	}

	/**
	 * Getter method for lessons in database
	 * e.g. for selecting a schedule
	 *
	 * @return string  all lessons in JSON format
	 *
	 * @throws RuntimeException
	 */
	public function getLessonsByPools()
	{
		$this->_db  = JFactory::getDbo();
		$chosenDate = JFactory::getApplication()->input->getString('date');
		$poolInput  = JFactory::getApplication()->input->getString('poolIDs');
		$poolIDs    = explode(',', $poolInput);
		$conditions = array();

		$query       = $this->_db->getQuery(true);
		$teacherName = $query->concatenate(array('SUBSTRING(tea.forename, 1, 1)', 'tea.surname'), '. ');
		$query->select(
			"subs.id AS subjectID, subs.name AS subjectName, subs.subjectNo, less.delta AS lessonDelta, 
			tea.id AS teacherID, $teacherName AS teacherName,
			cal.startTime, cal.endTime, cal.schedule_date, cal.delta AS calendarDelta"
		)
			->from('#__thm_organizer_plan_pools AS poo')
			->innerJoin('#__thm_organizer_lesson_pools AS lepo ON poo.id = lepo.poolID')
			->innerJoin('#__thm_organizer_lesson_subjects AS lesu ON lepo.subjectID = lesu.id')
			->innerJoin('#__thm_organizer_lessons AS less ON lesu.lessonID = less.id')
			->innerJoin('#__thm_organizer_calendar AS cal ON less.id = cal.lessonID')
			->leftJoin('#__thm_organizer_plan_subjects AS subs ON lesu.subjectID = subs.id')
			->leftJoin('#__thm_organizer_planning_periods AS pp ON pp.id = less.planningPeriodID')
			->leftJoin('#__thm_organizer_lesson_teachers AS letea ON lesu.id = letea.subjectID')
			->leftJoin('#__thm_organizer_teachers AS tea ON letea.teacherID = tea.id');

		foreach ($poolIDs as $poolID)
		{
			$conditions[] = "poo.id = $poolID";
		}

		$poolConditions = '(' . implode($conditions, ' OR ') . ')';
		$query->where($poolConditions);

		if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $chosenDate) === 1)
		{
			$query->where("pp.startDate <= '$chosenDate' AND pp.endDate >= '$chosenDate'");
		}
		else
		{
			$query->where("pp.startDate <= CURDATE() AND pp.endDate >= CURDATE()");
		}

		$query->where("cal.schedule_date <= DATE_ADD('$chosenDate', INTERVAL 3 DAY)")
			->where("cal.schedule_date >= DATE_SUB('$chosenDate', INTERVAL 3 DAY)")
			->where("cal.delta != 'removed'");

		$query->order('subjectName');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '{}';
		}

		return json_encode($result);
	}

	/**
	 * Getter for lessons in database,
	 * filtered by a teacher, departmentID and date.
	 * e.g. for selecting a schedule
	 *
	 * @return string  all lessons in JSON format
	 *
	 * @throws RuntimeException
	 */
	public function getLessonsByTeacher()
	{
		$this->_db    = JFactory::getDbo();
		$departmentID = JFactory::getApplication()->input->getInt('departmentID');
		$teacherID    = JFactory::getApplication()->input->getInt('teacherID');
		$chosenDate   = JFactory::getApplication()->input->getString('date');

		$query       = $this->_db->getQuery(true);
		$teacherName = $query->concatenate(array('SUBSTRING(tea.forename, 1, 1)', 'tea.surname'), '. ');
		$query->select(
			"subs.id AS subjectID, subs.name AS subjectName, subs.subjectNo, less.delta AS lessonDelta, 
			tea.id AS teacherID, $teacherName AS teacherName,
			cal.startTime, cal.endTime, cal.schedule_date"
		)
			->from('#__thm_organizer_teachers AS tea')
			->innerJoin('#__thm_organizer_lesson_teachers AS letea ON tea.id = letea.teacherID')
			->innerJoin('#__thm_organizer_lesson_subjects AS lesu ON letea.subjectID = lesu.id')
			->innerJoin('#__thm_organizer_lessons AS less ON lesu.lessonID = less.id')
			->innerJoin('#__thm_organizer_calendar AS cal ON less.id = cal.lessonID')
			->leftJoin('#__thm_organizer_plan_subjects AS subs ON lesu.subjectID = subs.id')
			->leftJoin('#__thm_organizer_planning_periods AS pp ON pp.id = less.planningPeriodID');

		if ($departmentID != 0)
		{
			$query->leftJoin('#__thm_organizer_department_resources AS dr ON tea.id = dr.teacherID');
			$query->where("dr.departmentID = $departmentID");
		}

		if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $chosenDate) === 1)
		{
			$query->where("pp.startDate <= '$chosenDate' AND pp.endDate >= '$chosenDate'");
		}
		else
		{
			$query->where("pp.startDate <= CURDATE() AND pp.endDate >= CURDATE()");
		}

		$query->where("cal.schedule_date <= DATE_ADD('$chosenDate', INTERVAL 3 DAY)")
			->where("cal.schedule_date >= DATE_SUB('$chosenDate', INTERVAL 3 DAY)")
			->where("cal.delta != 'removed'")
			->where("tea.id = $teacherID");

		$query->order('subjectName');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '{}';
		}

		return json_encode($result);
	}

	/**
	 * Getter for lessons in database,
	 * filtered by a room, departmentID and date.
	 * e.g. for selecting a schedule
	 *
	 * @return string  all lessons in JSON format
	 *
	 * @throws RuntimeException
	 */
	public function getLessonsByRoom()
	{
		$this->_db  = JFactory::getDbo();
		$roomID     = JFactory::getApplication()->input->getInt('roomID');
		$chosenDate = JFactory::getApplication()->input->getString('date');

		$query       = $this->_db->getQuery(true);
		$teacherName = $query->concatenate(array('SUBSTRING(tea.forename, 1, 1)', 'tea.surname'), '. ');
		$query->select(
			"subs.id AS subjectID, subs.name AS subjectName, subs.subjectNo, less.delta AS lessonDelta,
			tea.id AS teacherID, $teacherName AS teacherName,
			cal.startTime, cal.endTime, cal.schedule_date"
		)
			->from('#__thm_organizer_lessons AS less')
			->innerJoin('#__thm_organizer_lesson_configurations AS leco ON less.id = leco.lessonID')
			->innerJoin('#__thm_organizer_lesson_subjects AS lesu ON less.id = lesu.lessonID')
			->innerJoin('#__thm_organizer_calendar AS cal ON less.id = cal.lessonID')
			->leftJoin('#__thm_organizer_plan_subjects AS subs ON lesu.subjectID = subs.id')
			->leftJoin('#__thm_organizer_planning_periods AS pp ON pp.id = less.planningPeriodID')
			->leftJoin('#__thm_organizer_lesson_teachers AS letea ON lesu.id = letea.subjectID')
			->leftJoin('#__thm_organizer_teachers AS tea ON letea.teacherID = tea.id');

		// Regex for e.g. "rooms":{"xyz123":"","roomID":""
		$regexp = '[[.quotation-mark.]]rooms[[.quotation-mark.]][[.:.]][[.{.]]' .
			'([[.quotation-mark.]][[:alnum:]]*[[.quotation-mark.]][[.colon.]]?[[.comma.]]?)*' .
			'[[.quotation-mark.]]' . $roomID . '[[.quotation-mark.]][[.colon.]]' .
			'[[.quotation-mark.]][^removed]';
		$query->where("leco.configuration REGEXP '$regexp'");

		if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $chosenDate) === 1)
		{
			$query->where("pp.startDate <= '$chosenDate' AND pp.endDate >= '$chosenDate'");
		}
		else
		{
			$query->where("pp.startDate <= CURDATE() AND pp.endDate >= CURDATE()");
		}

		$query->where("cal.schedule_date <= DATE_ADD('$chosenDate', INTERVAL 3 DAY)")
			->where("cal.schedule_date >= DATE_SUB('$chosenDate', INTERVAL 3 DAY)")
			->where("cal.delta != 'removed'");

		$query->order('subjectName');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '{}';
		}

		return json_encode($result);
	}
}
