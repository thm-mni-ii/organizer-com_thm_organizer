<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSoapqueries
 * @description THM_OrganizerModelSoapqueries component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelSoapqueries for component com_thm_organizer
 *
 * Class provides methods to deal with soap queries
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSoapqueries extends JModelList
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
	 * Method to determine all stored soap queries
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$dbo = JFactory::getDBO();

		// Get the filter options
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		// Build the sql statement
		$query = $dbo->getQuery(true);
		$query->select('*');
		$query->from('#__thm_organizer_soap_queries');
		$query->order($orderCol . " " . $orderDirn);
		return $query;
	}

	/**
	 * Method to populate state
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

		$this->setState('list.ordering', $order);
		$this->setState('list.direction', $dir);
		$this->setState('filter', $filter);
		$this->setState('limit', $limit);

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
