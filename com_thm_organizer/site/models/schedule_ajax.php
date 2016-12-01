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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/pools.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/programs.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/rooms.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/schedule.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';

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
	 * Getter method for programs
	 *
	 * @return string  a json coded array of available program objects
	 */
	public function getPrograms()
	{
		$programs = THM_OrganizerHelperPrograms::getPlanPrograms();

		$results = array();
		foreach ($programs as $program)
		{
			$results[$program['name']] = $program['id'];
		}

		return empty($results) ? '[]' : json_encode($results);
	}

	/**
	 * Getter method for pools
	 *
	 * @return string  all pools in JSON format
	 */
	public function getPools()
	{
		$result = THM_OrganizerHelperPools::getPlanPools();

		return empty($result) ? '[]' : json_encode($result);
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

		return empty($result) ? '[]' : json_encode($result);
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

		return empty($result) ? '[]' : json_encode($result);
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
		$result = THM_OrganizerHelperTeachers::getPlanTeachers();

		return empty($result) ? '[]' : json_encode($result);
	}

	/**
	 * get lessons by chosen resource
	 *
	 * @return string JSON coded lessons
	 */
	public function getLessons()
	{
		$input       = JFactory::getApplication()->input;
		$inputParams = $input->getArray();
		$inputKeys   = array_keys($inputParams);
		$parameters  = array();
		foreach ($inputKeys as $key)
		{
			if ($key == 'poolIDs' || $key == 'teacherIDs' || $key == 'roomIDs')
			{
				$parameters[$key] = explode(',', $inputParams[$key]);
			}
		}

		$oneDay                        = $input->getString('oneDay', false);
		$parameters['dateRestriction'] = $oneDay ? 'day' : 'week';
		$parameters['date']            = $input->getString('date');
		$parameters['format']          = '';

		$lessons = THM_OrganizerHelperSchedule::getLessons($parameters, true);

		return empty($lessons) ? '[]' : json_encode($lessons);
	}

	/**
	 * saves lessons of a whole semester in the personal schedule of the logged in user
	 *
	 * @throws RuntimeException
	 * @return string
	 */
	public function saveLesson()
	{
		$input  = JFactory::getApplication()->input;
		$config = $input->getInt('config', LESSONS_OF_PERIOD);
		$ccmID  = $input->getString('ccmID');
		$userID   = JFactory::getUser()->id;

		/** no logged in user */
		if (empty($userID))
		{
			return '[]';
		}

		/** get configurations of selected lesson */
		$configurations = $this->getConfigurations($config, $ccmID);
		if (empty($configurations))
		{
			return '[]';
		}

		/** insert in database */
		$query = $this->_db->getQuery(true);

		$columns = array('userID', 'lessonID', 'user_date', 'configuration');
		$values  = array(
			$userID,
			$configurations['lessonID'],
			$this->_db->quote(date('Y-m-d H:i:s')),
			$this->_db->quote(json_encode($configurations['configurations']))
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
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		/** returns (js-)'id' of lesson in case of success */
		return $success ?
			$configurations['lessonID'] . '-' . $configurations['scheduleDate'] . '-' . $configurations['startTime']
			: '[]';
	}

	/**
	 * loads matching configurations of a lesson
	 *
	 * @param   int    $config global param like LESSONS_OF_SEMESTER
	 * @param   string $ccmID  calendar_configuration_map ID
	 *
	 * @throws RuntimeException
	 * @return array|bool array with lessonID and an array with configurationIDs
	 */
	private function getConfigurations($config, $ccmID)
	{
		$query = $this->_db->getQuery(true);
		$query->select('lessonID, startTime, endTime, schedule_date, DAYOFWEEK(cal.schedule_date) AS weekday')
			->from('#__thm_organizer_calendar_configuration_map AS map')
			->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
			->where("map.id = '$ccmID'")
			->where("delta != 'removed'");

		$query->order('map.id');
		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadObject();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return false;
		}

		if (empty($result))
		{
			return null;
		}

		/** get other matching configurations, depending on given config */
		$query = $this->_db->getQuery(true);
		$query->select('map.id')
			->from('#__thm_organizer_calendar_configuration_map AS map')
			->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
			->where("cal.lessonID = '$result->lessonID'")
			->where("delta != 'removed'");

		if ($config !== LESSONS_OF_SEMESTER)
		{
			/** lessons for same day of the week and same time */
			$query->where("cal.startTime = '$result->startTime'");
			$query->where("cal.endTime = '$result->endTime'");
			$query->where("DAYOFWEEK(cal.schedule_date) = '$result->weekday'");

			/** only the selected instance of lesson */
			if ($config == LESSONS_INSTANCE)
			{
				$query->where("cal.schedule_date = '$result->schedule_date'");
			}
		}

		$query->order('map.id');
		$this->_db->setQuery((string) $query);

		try
		{
			$results = $this->_db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return false;
		}

		if (empty($results))
		{
			return null;
		}

		/** collect configurations to encode them into json */
		$configurations = [];
		foreach ($results as $configuration)
		{
			$configurations[] = $configuration->id;
		}

		return array(
			'lessonID'       => $result->lessonID,
			'scheduleDate'   => $result->schedule_date,
			'startTime'      => $result->startTime,
			'endTime'        => $result->endTime,
			'configurations' => $configurations
		);
	}

	/**
	 * gets schedule of now logged in user
	 *
	 * @return string lessons in JSON format - empty in case of errors
	 */
	public function getUsersSchedule()
	{
		$input                         = JFactory::getApplication()->input;
		$parameters                    = array();
		$parameters['date']            = $input->getString('date');
		$oneDay                        = $input->getString('oneDay', false);
		$parameters['dateRestriction'] = $oneDay ? 'day' : 'week';
		$parameters['format']          = '';
		$parameters['mySchedule']      = "mySchedule";

		if (JFactory::getUser()->guest)
		{
			return '[]';
		}

		/** @var array $userLessons */
		$userLessons = THM_OrganizerHelperSchedule::getLessons($parameters, true);
		if (empty($userLessons))
		{
			return '[]';
		}

		$configurations = $this->getUserConfigurations();
		if (!$configurations)
		{
			return '[]';
		}

		/** filter lessons by users configuration */
		foreach ($userLessons as &$day)
		{
			foreach ($day as &$block)
			{
				foreach ($block as $lessonID => &$lesson)
				{
					if (!$configurations[$lessonID] OR !in_array($lesson['ccmID'], $configurations[$lessonID]))
					{
						unset($block[$lessonID]);
					}
				}
			}
		}

		return json_encode($userLessons);
	}

	/**
	 * Gets lessons configurations by logged in user
	 *
	 * @throws RuntimeException
	 * @return array|boolean|null result as object array, error = false, null = no results
	 */
	private function getUserConfigurations()
	{
		$query  = $this->_db->getQuery(true);
		$userID = JFactory::getUser()->id;
		if (!$userID)
		{
			return false;
		}

		$query->select("lessonID, configuration")
			->from('#__thm_organizer_user_lessons')
			->where("userID = $userID");

		$this->_db->setQuery((string) $query);

		try
		{
			$results = $this->_db->loadAssocList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return false;
		}

		if (empty($results))
		{
			return null;
		}

		$configurations = array();
		foreach ($results as $result)
		{
			$configurations[$result['lessonID']] = json_decode($result['configuration']);
		}

		return $configurations;
	}
}
