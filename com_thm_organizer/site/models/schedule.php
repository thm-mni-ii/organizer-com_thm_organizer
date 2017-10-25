<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        schedule model
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';

/**
 * Class THM_OrganizerModelSchedule for loading the chosen schedule from the database
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSchedule extends JModelLegacy
{
	public $grids;

	public $departments;

	public $displayName = '';

	public $params;

	/**
	 * THM_OrganizerModelSchedule constructor.
	 *
	 * @param array $config options
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);
		$this->setParams();
		$this->grids = $this->getGrids();

		if (empty($this->params['departmentID']) AND !isset($this->params['resourcesRequested']))
		{
			$this->departments = THM_OrganizerHelperDepartments::getPlanDepartments(true);
		}
	}

	/**
	 * Getter method for all grids in database
	 *
	 * @return array
	 */
	public function getGrids()
	{
		$languageTag = THM_OrganizerHelperLanguage::getShortTag();
		$query       = $this->_db->getQuery(true);
		$query->select("id, name_$languageTag AS name, grid, defaultGrid")
			->from('#__thm_organizer_grids');
		$this->_db->setQuery($query);

		try
		{
			$grids = $this->_db->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($grids))
		{
			return '[]';
		}

		return $grids;
	}

	/**
	 * gets the first default grid from all grid objects in database
	 *
	 * @return object JSON grid
	 */
	public function getDefaultGrid()
	{
		$defaultGrids = array_filter(
			$this->grids,
			function ($var) {
				return $var->defaultGrid;
			}
		);

		if (empty($defaultGrids))
		{
			return $this->getGridFallback();
		}
		else
		{
			return $defaultGrids[0];
		}
	}

	/**
	 * example and fallback of a default time grid
	 *
	 * @return object (json)
	 */
	private function getGridFallback()
	{
		$fallback = '{
				"periods": {
				    "1":{
				        "startTime":"0800",
			            "endTime":"0930"
			        },
			        "2": {
				        "startTime":"0950",
			            "endTime":"1120"},
			        "3": {
				        "startTime":"1130",
			            "endTime":"1300"
			        },
			        "4": {
				        "startTime":"1400",
			            "endTime":"1530"},
			        "5": {
				        "startTime":"1545",
			            "endTime":"1715"},
			        "6": {
				        "startTime":"1730",
			            "endTime":"1900"
			        }
			    },
			    "startDay":1,
			    "endDay":6
			}';

		return json_decode($fallback);
	}

	/**
	 * Sets the parameters used to configure the display
	 *
	 * @return void
	 */
	private function setParams()
	{
		$input        = JFactory::getApplication()->input;
		$params       = JFactory::getApplication()->getParams();

		$this->params = [];

		$allowedIDs = THM_OrganizerHelperComponent::getAccessibleDepartments();

		// Don't even set the variable if the action is implausible
		if (!empty($allowedIDs))
		{
			$this->params['showUnpublished'] = $params->get('showUnpublished', 0);
		}

		// No explicit resource selection was made check if departments were requested
		$this->params['departmentID']  = $input->getInt('departmentID', $params->get('departmentID', 0));
		$this->params['showPrograms']  = $input->getInt('showPrograms', $params->get('showPrograms', 1));
		$this->params['showPools']     = $input->getInt('showPools', $params->get('showPools', 1));
		$this->params['showRooms']     = $input->getInt('showRooms', $params->get('showRooms', 1));
		$this->params['showRoomTypes'] = $input->getInt('showRoomTypes', $params->get('showRoomTypes', 1));
		$this->params['showTeachers']  = $input->getInt('showTeachers', $params->get('showTeachers', 1));
		$this->params['deltaDays']     = $input->getInt('deltaDays', $params->get('deltaDays', 5));

		// Menu title requested
		if (!empty($params->get('show_page_heading')))
		{
			$this->displayName .= $params->get('page_title');
		}

		$setTitle = empty($this->displayName);

		// Explicit setting of resources is done in the priority of resource type and is mutually exclusive
		if ($this->params['showPools'])
		{
			$this->setResourceArray('pool');
		}

		if (!empty($this->params['poolIDs']))
		{
			$this->params['showPrograms']  = 0;
			$this->params['showRooms']     = 0;
			$this->params['showRoomTypes'] = 0;
			$this->params['showTeachers']  = 0;

			if (count($this->params['poolIDs']) === 1 AND $setTitle)
			{
				/** @noinspection PhpIncludeInspection */
				require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/pools.php';
				$this->displayName           .= THM_OrganizerHelperPools::getFullName($this->params['poolIDs'][0]);
				$this->params['displayName'] = $this->displayName;
			}

			return;
		}

		if ($this->params['showTeachers'])
		{
			$this->setResourceArray('teacher');
		}

		if (!empty($this->params['teacherIDs']))
		{
			$this->params['showPools']     = 0;
			$this->params['showPrograms']  = 0;
			$this->params['showRooms']     = 0;
			$this->params['showRoomTypes'] = 0;

			if (count($this->params['teacherIDs']) === 1 AND $setTitle)
			{
				/** @noinspection PhpIncludeInspection */
				require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/teachers.php';
				$this->displayName           .= THM_OrganizerHelperTeachers::getDefaultName($this->params['teacherIDs'][0]);
				$this->params['displayName'] = $this->displayName;
			}

			return;
		}

		if ($this->params['showRooms'])
		{
			$this->setResourceArray('room');
		}

		if (!empty($this->params['roomIDs']))
		{
			$this->params['showPools']    = 0;
			$this->params['showPrograms'] = 0;
			$this->params['showTeachers'] = 0;

			if (count($this->params['roomIDs']) === 1 AND $setTitle)
			{
				/** @noinspection PhpIncludeInspection */
				require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/rooms.php';
				$this->displayName           .= JText::_('COM_THM_ORGANIZER_ROOM') . ' ';
				$this->displayName           .= THM_OrganizerHelperRooms::getName($this->params['roomIDs'][0]);
				$this->params['displayName'] = $this->displayName;
			}

			return;
		}

		if ($this->params['showRoomTypes'])
		{
			$this->setResourceArray('roomType');
		}

		if (!empty($this->params['roomTypeIDs']))
		{
			$this->params['showPools']    = 0;
			$this->params['showPrograms'] = 0;
			$this->params['showTeachers'] = 0;

			if (count($this->params['roomTypeIDs']) === 1 AND $setTitle)
			{
				/** @noinspection PhpIncludeInspection */
				require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/room_types.php';
				$this->displayName           .= JText::_('COM_THM_ORGANIZER_ROOM_TYPE') . ' ';
				$this->displayName           .= THM_OrganizerHelperRoomTypes::getName($this->params['roomTypeIDs'][0]);
				$this->params['displayName'] = $this->displayName;
			}

			return;
		}

		// Program as the last setting, because the others lead directly to a schedule and program is just a form value
		if ($this->params['showPrograms'])
		{
			$this->setResourceArray('program');
		}

		if (!empty($this->params['programIDs']))
		{
			$this->params['showRooms']     = 0;
			$this->params['showRoomTypes'] = 0;
			$this->params['showTeachers']  = 0;

			if (count($this->params['programIDs']) === 1 AND $setTitle)
			{
				/** @noinspection PhpIncludeInspection */
				require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/programs.php';
				$this->displayName           .= THM_OrganizerHelperPrograms::getName($this->params['programIDs'][0], 'plan');
				$this->params['displayName'] = $this->displayName;
			}

			return;
		}

		// This will only be requested by URL so there is no need to check params or $setTitle
		$this->setResourceArray('subject');

		if (!empty($this->params['subjectIDs']))
		{
			$this->params['showPools']     = 0;
			$this->params['showPrograms']  = 0;
			$this->params['showRooms']     = 0;
			$this->params['showRoomTypes'] = 0;
			$this->params['showTeachers']  = 0;

			// There can be only one.
			$this->params['subjectIDs'] = array_shift($this->params['subjectIDs']);

			/** @noinspection PhpIncludeInspection */
			require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/subjects.php';
			$this->displayName           .= THM_OrganizerHelperSubjects::getName($this->params['subjectIDs'], 'plan');
			$this->params['displayName'] = $this->displayName;

			return;
		}

		// In the last instance the department name is used if nothing else was requested
		if ($setTitle)
		{
			/** @noinspection PhpIncludeInspection */
			require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';
			$this->displayName .= JText::_('COM_THM_ORGANIZER_DEPARTMENT') . ' ';
			$this->displayName .= THM_OrganizerHelperDepartments::getName($this->params['departmentID']);
		}
	}

	/**
	 * Checks for ids for a given resource type and sets them in the parameters
	 *
	 * @param string $resourceName the name of the resource type
	 *
	 * @return void sets object variable indexes
	 */
	private function setResourceArray($resourceName)
	{
		$rawResourceIDs = JFactory::getApplication()->input->get("{$resourceName}IDs", [], 'raw');

		if (empty($rawResourceIDs))
		{
			$rawResourceIDs = JFactory::getApplication()->getParams()->get("{$resourceName}IDs");
		}

		if (!empty($rawResourceIDs))
		{
			if (is_array($rawResourceIDs))
			{
				$filteredArray = Joomla\Utilities\ArrayHelper::toInteger(array_filter($rawResourceIDs));
				if (!empty($filteredArray))
				{
					$this->params["{$resourceName}IDs"] = $filteredArray;
				}
			}
			elseif (is_int($rawResourceIDs))
			{
				$this->params["{$resourceName}IDs"] = Joomla\Utilities\ArrayHelper::toInteger([$rawResourceIDs]);
			}
			elseif (is_string($rawResourceIDs))
			{
				$this->params["{$resourceName}IDs"] = Joomla\Utilities\ArrayHelper::toInteger(explode(',', $rawResourceIDs));
			}

			$this->params['resourcesRequested'] = $resourceName;
		}
	}
}
