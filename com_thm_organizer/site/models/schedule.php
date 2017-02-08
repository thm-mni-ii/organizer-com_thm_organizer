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
	/**
	 * time grids for displaying the schedules
	 *
	 * @var array
	 */
	public $grids;

	/**
	 * Departments for selecting schedules
	 *
	 * @var array
	 */
	public $departments;

	/**
	 * name of active department
	 *
	 * @var array
	 */
	public $departmentName;

	/**
	 * THM_OrganizerModelSchedule constructor.
	 *
	 * @param array $config options
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);
		$this->grids          = $this->getGrids();
		$this->departments    = $this->getDepartments();
		$this->departmentName = $this->getDepartmentName();
	}

	/**
	 * getter for the default time grid out of database
	 *
	 * @return false|string
	 */
	public function getDepartmentName()
	{
		$languageTag        = THM_OrganizerHelperLanguage::getShortTag();
		$this->params       = JFactory::getApplication()->getParams();
		$this->departmentID = $this->params->get('departmentID', 0);

		$query = $this->_db->getQuery(true);
		$query
			->select("name_$languageTag as name")
			->from('#__thm_organizer_departments')
			->where("id = $this->departmentID");

		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadResult();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return false;
		}

		return $result;
	}

	/**
	 * Getter method for all grids in database
	 *
	 * @return mixed  array | empty in case of errors or no results
	 */
	public function getGrids()
	{
		$languageTag = THM_OrganizerHelperLanguage::getShortTag();
		$query       = $this->_db->getQuery(true);
		$query->select("id, name_$languageTag AS name, grid, defaultGrid")
			->from('#__thm_organizer_grids');
		$this->_db->setQuery((string) $query);

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
			function ($var)
			{
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
	 * Gets all available department names and IDs
	 *
	 * @return array
	 */
	public function getDepartments()
	{
		return THM_OrganizerHelperDepartments::getPlanDepartments(true);
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
}
