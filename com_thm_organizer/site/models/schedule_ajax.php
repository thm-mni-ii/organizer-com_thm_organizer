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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
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
			->from('#__thm_organizer_plan_pools')
			->where("gpuntisID REGEXP '^[[:alnum:]]+[[.period.]][[:alnum:]]+'");

		foreach ($programIDs as $programID)
		{
			$conditions[] = "programID = '$programID'";
		}

		// Implode, because where-clause is already set and does not accept a new 'glue' (default = 'AND')
		$programCondition = implode(' OR ', $conditions);

		$query->where($programCondition);

		$query->order('name');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return $e->getMessage();
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
	 * TODO
	 *
	 * Getter method for lessons in database
	 * e.g. for selecting a schedule
	 *
	 * @return string  all lessons in JSON format
	 *
	 * @throws RuntimeException
	 */
	public function getLessons()
	{
		$this->_db    = JFactory::getDbo();
		$languageTag  = THM_OrganizerHelperLanguage::getShortTag();
		$departmentID = JFactory::getApplication()->input->getInt('departmentID');
		$poolID       = JFactory::getApplication()->input->getInt('poolID');
		$chosenDate   = JFactory::getApplication()->input->getString('date');

		$query = $this->_db->getQuery(true);
		$query->select("less.id, sub.short_name_$languageTag AS name, meth.abbreviation_$languageTag")
			->from('#__thm_organizer_lessons AS less')
			->innerJoin('#__thm_organizer_lesson_subjects AS lesu ON lesu.lessonID = less.id');

		if ($poolID != 0)
		{
			$query->where("lepo.poolID = $poolID");
		}

		if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $chosenDate) === 1)
		{
			$query->where("pp.startDate <= $chosenDate AND pp.endDate >= $chosenDate");
		}
		else
		{
			$query->where("pp.startDate <= CURDATE() AND pp.endDate >= CURDATE()");
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

			return $e->getMessage();
		}

		return json_encode($result);
	}
}
