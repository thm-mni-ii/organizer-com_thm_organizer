<?php
/**
 * @version     v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.model
 * @name        THM_OrganizerModelVirtual_Schedule_Manager
 * @description Class to handle virtual schedules
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class THM_OrganizerModelVirtual_Schedule_Manager for component com_thm_organizer
 *
 * Class provides methods display a list of virtual schedules and perform actions on them
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.model
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class THM_OrganizerModelVirtual_Schedule_Manager extends JModel
{
	/**
	 * Total records
	 *
	 * @var    Integer
	 * @since  v0.0.1
	 */
	private $_total = null;

	/**
	 * Pagination object
	 *
	 * @var    Object
	 * @since  v0.0.1
	 */
	private $_pagination = null;

	/**
	 * Constructor that calls the parent constructor and intialise variables
	 *
	 * @since   v0.0.1
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		$mainframe = JFactory::getApplication("administrator");
		$option = $mainframe->scope;
		$view = JRequest::getString('view');

		// Get pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option . $view . '.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to build the sql query to get the virtual schedules
	 *
	 * @return	String	The sql query
	 */
	public function _buildQuery()
	{
		$mainframe = JFactory::getApplication("administrator");
		$option = $mainframe->scope;
		$view = JRequest::getString('view');

		$filter_order		= $mainframe->getUserStateFromRequest(
				"$option . $view . filter_order", 'filter_order',
				"#__thm_organizer_virtual_schedules.semesterID, #__thm_organizer_virtual_schedules.vid", 'string'
		);
		$filter_order_Dir	= $mainframe->getUserStateFromRequest("$option . $view . filter_order_Dir", 'filter_order_Dir', "", 'string');
		$filter_type		= $mainframe->getUserStateFromRequest("$option . $view . filter_type",	'filter_type', 0, 'string');
		$filter_logged		= $mainframe->getUserStateFromRequest("$option . $view . filter_logged", 'filter_logged', 0, 'int');
		$filter 			= $mainframe->getUserStateFromRequest($option . $view . '.filter', 'filter', '', 'int');
		$search 			= $mainframe->getUserStateFromRequest($option . $view . '.search', 'search', '', 'string');
		$groupFilter 		= $mainframe->getUserStateFromRequest($option . $view . '.groupFilters', 'groupFilters', '', 'int');
		$rolesFilter 		= $mainframe->getUserStateFromRequest($option . $view . '.rolesFilters', 'rolesFilters', '', 'int');
		$search 			= $this->_db->getEscaped(trim(JString::strtolower($search)));

		if (!$filter_order)
		{
			$filter_order = '#__thm_organizer_virtual_schedules.semesterID, #__thm_organizer_virtual_schedules.vid';
		}
		if (!$filter_order_Dir)
		{
			$filter_order_Dir = '';
		}

		$orderby     = "\n ORDER BY $filter_order $filter_order_Dir";

		$query = 'SELECT DISTINCT ' .
				'#__thm_organizer_virtual_schedules.id as id, #__thm_organizer_virtual_schedules.name,' .
				'#__thm_organizer_virtual_schedules.type, #__users.name as responsible,' .
				' department,' .
				'CONCAT(#__thm_organizer_semesters.organization, "-",#__thm_organizer_semesters.semesterDesc ) as semesterID' .
				' FROM #__thm_organizer_virtual_schedules' .
				' INNER JOIN #__thm_organizer_virtual_schedules_elements' .
				' ON #__thm_organizer_virtual_schedules.id = #__thm_organizer_virtual_schedules_elements.vid' .
				' INNER JOIN #__thm_organizer_curriculum_semesters' .
				' ON #__thm_organizer_virtual_schedules.semesterID = #__thm_organizer_semesters.id' .
				' INNER JOIN #__users' .
				' ON #__thm_organizer_virtual_schedules.responsible = #__users.username' .
				' WHERE #__thm_organizer_virtual_schedules.id = #__thm_organizer_virtual_schedules_elements.vid';

		$searchUm = str_replace("Ö", "&Ouml;", $search);
		$searchUm = str_replace("ö", "&öuml;", $searchUm);
		$searchUm = str_replace("Ä", "&Auml;", $searchUm);
		$searchUm = str_replace("ä", "&auml;", $searchUm);
		$searchUm = str_replace("Ü", "&Uuml;", $searchUm);
		$searchUm = str_replace("ü", "&uuml;", $searchUm);

		$searchUm2 = str_replace("Ã¶", "&Ouml;", $search);
		$searchUm2 = str_replace("Ã¶", "&öuml;", $searchUm2);
		$searchUm2 = str_replace("Ã¤", "&Auml;", $searchUm2);
		$searchUm2 = str_replace("Ã¤", "&auml;", $searchUm2);
		$searchUm2 = str_replace("Ã¼", "&Uuml;", $searchUm2);
		$searchUm2 = str_replace("Ã¼", "&uuml;", $searchUm2);

		$query .= ' AND (LOWER(#__thm_organizer_virtual_schedules.name) LIKE \'%' . $search . '%\' ';
		$query .= ' OR LOWER(#__thm_organizer_virtual_schedules.responsible) LIKE \'%' . $search . '%\' ';
		$query .= ' OR LOWER(#__thm_organizer_virtual_schedules.department) LIKE \'%' . $search . '%\' ';
		$query .= ' OR LOWER(#__thm_organizer_virtual_schedules.name) LIKE \'%' . $searchUm . '%\' ';
		$query .= ' OR LOWER(#__thm_organizer_virtual_schedules.responsible) LIKE \'%' . $searchUm . '%\' ';
		$query .= ' OR LOWER(#__thm_organizer_virtual_schedules.department) LIKE \'%' . $searchUm . '%\' ';
		$query .= ' OR LOWER(#__thm_organizer_virtual_schedules.name) LIKE \'%' . $searchUm2 . '%\' ';
		$query .= ' OR LOWER(#__thm_organizer_virtual_schedules.responsible) LIKE \'%' . $searchUm2 . '%\' ';
		$query .= ' OR LOWER(#__thm_organizer_virtual_schedules.department) LIKE \'%' . $searchUm2 . '%\') ';

		if ($groupFilter > 0)
		{ 
			$query .= ' AND #__thm_organizer_virtual_schedules.type = ' . $groupFilter . ' ';
		}

		if ($rolesFilter > 0)
		{
			$query .= ' AND #__thm_organizer_virtual_schedules.semesterID = ' . $rolesFilter . ' ';
		}

		$query .= $orderby;

		return $query;
	}

	/**
	 * Method to get data
	 *
	 * @return	Array	An Array with data
	 */
	public function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		if (!is_array($this->_data))
		{
			$this->_data = array();
		}
		return $this->_data;
	}

	/**
	 * Method to get the total number of records
	 *
	 * @return	Integer	 The total number of records
	 */
	public function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$db = & JFactory::getDBO();

			$query = 'SELECT count(*) as anzahl FROM #__thm_organizer_virtual_schedules';
			$db->setQuery($query);
			$rows = $db->loadObjectList();
		}
		return $rows[0]->anzahl;
	}

	/**
	 * Method to get the total number of records
	 *
	 * @return	Integer	 The total number of records
	 */
	public function getAnz()
	{
		$query = 'SELECT count(*) as anzahl FROM #__thm_organizer_virtual_schedules';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows[0]->anzahl;
	}

	/**
	 * Method to get the pagination
	 *
	 * @return	JPagination	 A JPagination Object
	 */
	public function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}

	/**
	 * Method to get the elements
	 *
	 * @return	JPagination	 A JPagination Object
	 */
	public function getElements()
	{
		$query = 'SELECT * FROM #__thm_organizer_virtual_schedules_elements';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}
}
