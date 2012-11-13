<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerModelLecturers
 * @description THM_OrganizerModelLecturers component admin model
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelLecturers for component com_thm_organizer
 *
 * Class provides methods to deal with lecturers
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerModelLecturers extends JModelList
{
	/**
	 * Database
	 *
	 * @var    Object
	 * @since  1.0
	 */
	protected $db = null;

	/**
	 * Data
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_data;

	/**
	 * Pagination
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_pagination = null;

	/**
	 * Constructor to initialise the database and call the parent constructor
	 */
	public function __construct()
	{
		$this->db = &JFactory::getDBO();
		parent::__construct();
	}

	/**
	 * Method to determine the saved lecturers from the database
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db = JFactory::getDBO();

		// Get the filter options
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$search = $this->state->get('filter.search');

		// Defailt ordering
		if ($orderCol == "")
		{
			$orderCol = "id";
			$orderDirn = "asc";
		}

		// Configure the sql query
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__thm_organizer_lecturers');

		$search = $db->Quote('%' . $db->getEscaped($search, true) . '%');
		$query->where('(userid LIKE ' . $search . ' OR surname LIKE ' . $search . ' OR forename LIKE ' .
				$search . ' OR academic_title LIKE ' . $search . ')');

		$query->order($orderCol . " " . $orderDirn);

		return $query;
	}

	/**
	 * Method to set the populate state
	 *
	 * @param   String  $ordering   Ordering   (default: null)
	 * @param   String  $direction  Direction  (default: null)
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		if ($layout = JRequest::getVar('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$order = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '');
		$dir = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '');
		$filter = $app->getUserStateFromRequest($this->context . '.filter', 'filter', '');
		$limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');
		$search = $app->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');

		$this->setState('list.ordering', $order);
		$this->setState('list.direction', $dir);
		$this->setState('filter', $filter);
		$this->setState('limit', $limit);
		$this->setState('filter.search', $search);

		// Set the default ordering behaviour
		if ($order == '')
		{
			parent::populateState("id", "ASC");
		}
		else
		{
			parent::populateState($order, $dir);
		}
	}
}
