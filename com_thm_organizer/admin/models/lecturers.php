<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelLecturers
 * @description THM_OrganizerModelLecturers component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelLecturers for component com_thm_organizer
 *
 * Class provides methods to deal with lecturers
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelLecturers extends JModelList
{
	/**
	 * Data
	 *
	 * @var    Object
	 */
	private $_data;

	/**
	 * Pagination
	 *
	 * @var    Object
	 */
	private $_pagination = null;

	/**
	 * Constructor to initialise the database and call the parent constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Method to determine the saved lecturers from the database
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);
		$query->select('*');
		$query->from('#__thm_organizer_lecturers');

		$search = $dbo->Quote('%' . $dbo->getEscaped($this->state->get('filter.search'), true) . '%');
		$whereClause = "(userid LIKE '$search' ";
		$whereClause .= "OR surname LIKE '$search'";
		$whereClause .= "OR forename LIKE '$search'";
		$whereClause .= "OR academic_title LIKE '$search')";
		$query->where($whereClause);

		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol == "")
		{
			$orderCol = "id";
			$orderDirn = "asc";
		}
		$query->order($orderCol . " " . $orderDirn);

		return $query;
	}

	/**
	 * Method to set the populate state
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		$layout = JRequest::getVar('layout');
		if (!empty($layout))
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
