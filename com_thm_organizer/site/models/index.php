<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelIndex
 * @description THM_OrganizerModelIndex component site model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
jimport('joomla.filesystem.path');
jimport('joomla.application.component.modellist');
require_once JPATH_COMPONENT_SITE . DS . 'helper/module.php';
require_once JPATH_COMPONENT_SITE . DS . 'helper/lsfapi.php';
require_once JPATH_COMPONENT_SITE . DS . 'helper/ModuleList.php';
require_once JPATH_COMPONENT_SITE . DS . 'models/groups.php';
require_once JPATH_COMPONENT_SITE . DS . 'models/curriculum.php';

/**
 * Class THM_OrganizerModelIndex for component com_thm_organizer
 *
 * Class provides methods to display a list
 *
 * @category    Joomla.Component.Site
 * @package     thm_urriculum
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelIndex extends JModelList
{
	/**
	 * Data
	 *
	 * @var    Object
	 */
	private $_data;

	/**
	 * Database
	 *
	 * @var    Object
	 */
	protected $dbo = null;

	/**
	 * Global parameters
	 *
	 * @var    Object
	 */
	private $_globParams = null;

	/**
	 * Major
	 *
	 * @var    Object
	 */
	public $major = null;

	/**
	 * Configuration
	 *
	 * @var    Object
	 */
	private $_config = null;

	/**
	 * Constructor to set up the class variables and call the parent constructor
	 */
	public function __construct()
	{
		$this->dbo = JFactory::getDBO();
		$this->_globParams = JComponentHelper::getParams('com_thm_organizer');
		$this->groupsModel = $model = new THM_OrganizerModelGroups;
		$this->groupsCurriculum = $model = new THM_OrganizerModelCurriculum;
		$this->_config = $this->groupsModel->getLsfConfiguration();

		parent::__construct();
	}

	/**
	 * Method to get the major records by major id
	 *
	 * @param   String  $ordering   Ordering   (default: null)
	 * @param   String  $direction  Direction  (default: null)
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		$layout = JRequest::getVar('layout');
		if (!empty($layout))
		{
			$this->context .= '.' . $layout;
		}

		$ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '');
		$direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '');
		$filter = $app->getUserStateFromRequest($this->context . '.filter', 'filter', '');
		$limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');

		$this->setState('list.ordering', $ordering);
		$this->setState('list.direction', $direction);
		$this->setState('filter', $filter);
		$this->setState('list.limit', $limit);

		// Set the default ordering
		if ($ordering == '')
		{
			// @TODO: sortierung nach jeder id
			parent::populateState("lsf_course_code, his_course_code, lsf_course_id", "ASC");
		}
		else
		{
			parent::populateState($ordering, $direction);
		}
	}

	/**
	 * Method to build an SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$query = $this->dbo->getQuery(true);
		$query->select('*');
		$query->from('#__thm_organizer_assets');
		$query->where('lsf_course_id IN (' . self::getCourseIDList() . ')');

		$search = $this->state->get('filter');
		if (isset($search) && $search != "")
		{
			$query->where("title_de LIKE '%$search%'");
			$this->setState('filter', $search);
		}
		$orderDirn = $this->state->get('list.direction');
		$orderCol = implode(" " . $orderDirn . ",", explode(", ", $this->state->get('list.ordering')));
		$query->order("$orderCol $orderDirn");

		return (string) $query;
	}

	/**
	 * Method to build a SQL where clause which contains courses which are related to the current lsf major
	 *
	 * @return  String  WHERE clause
	 */
	public function getCourseIDList()
	{
		// Perform a soap request, in order to get all related courses
		$client = new THM_OrganizerLSFClient(
											 $this->_globParams->get('webserviceUri'),
											 $this->_globParams->get('webserviceUsername'),
											 $this->_globParams->get('webservicePassword')
											);

		$xml = $client->getModules(
								   $this->_config[0]->lsf_object,
								   $this->_config[0]->lsf_study_path,
								   $this->_config[0]->lsf_degree,
								   $this->_config[0]->po
								  );

		// Build the where clause
		$courseIDList = "";
		if (isset($xml))
		{
			// Iterate over each couse group
			foreach ($xml->gruppe as $gruppe)
			{
				if ($gruppe->modulliste->modul[0] == null)
				{
					$courseIDList .= "'$gruppe->pordid', ";
				}
				else
				{
					foreach ($gruppe->modulliste->modul as $modul)
					{
						$courseIDList .= "'$modul->modulid', ";
					}
				}
			}
			if (strlen($courseIDList) > 0)
			{
				return substr($courseIDList, 0, strlen($courseIDList) - 2);
			}
		}
		return $courseIDList;
	}

	/**
	 * Method to get the items
	 *
	 * @return  Object
	 */
	public function getItems()
	{
		$limit = $this->state->get('list.limit');
		$start = $this->state->get('list.start');

		$this->major = $this->groupsCurriculum->getMajorRecord($this->_config[0]->id);
		$curriculumModel = new THM_OrganizerModelCurriculum;
		$groupsModel = new THM_OrganizerModelGroups;

		if (empty($this->_data))
		{
			$query = $this->getListQuery();
			$this->_data = $this->_getList($query, $start, $limit);
		}

		// Iterate over each found course
		foreach ($this->_data as $key => $row)
		{
			// Apply the course filter
			if ($this->groupsModel->filter($row->lsf_course_code) || $this->groupsModel->filter($row->his_course_code))
			{
				unset($this->_data[$key]);
			}

			$row->courseDetailLink = $groupsModel->buildCourseDetailLink($row);
			$row->responsible_name = $curriculumModel->getLecturerName($row->lsf_course_id);
			$row->responsible_link = $curriculumModel->buildResponsibleLink($row->lsf_course_id);
			$row->scheduler = $groupsModel->getSchedulerTooltip(strtolower($row->lsf_course_code), $this->major[0]['organizer_major']);
		}

		self::setNavigationToSession($this->_data);

		return $this->_data;
	}

	/**
	 * Method to create the navigationbar in the module description and to store it in the session
	 *
	 * @param   mixed  $modules  Modules
	 *
	 * @return  Array
	 */
	public function setNavigationToSession($modules)
	{
		$navi = array();
		$session = & JFactory::getSession();
		foreach ($modules as $module)
		{
			$arr = array();
			$arr['id'] = $module->lsf_course_id;
			$arr['link'] = JRoute::_("index.php?option=com_thm_organizer&view=details&id=" . $module->lsf_course_id);
			array_push($navi, $arr);
		}
		$session->set('navi_json', json_encode($navi));
		$session->set('view_state', 'index');
		return $navi;
	}
}
