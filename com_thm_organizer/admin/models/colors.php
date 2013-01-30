<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerModelColors
 * @description THM_OrganizerModelColors component admin model
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelColors for component com_thm_organizer
 *
 * Class provides methods to deal with colors
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerModelColors extends JModelList
{
	/**
	 * Constructor to set the config array and call the parent constructor
	 *
	 * @param   Array  $config  Configuration  (default: Array)
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'id'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get all colors from the database
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db = JFactory::getDBO();

		// Get the filter values from the request
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$search = $this->state->get('filter');

		// Defailt ordering
		if ($orderCol == "")
		{
			$orderCol = "id";
			$orderDirn = "asc";
		}

		// Create the query
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_colors');
		$query->order($orderCol . " " . $orderDirn);

		return $query;
	}

	/**
	 * Method to get the populate state
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

		$this->setState('list.ordering', $order);
		$this->setState('list.direction', $dir);
		$this->setState('filter', $filter);
		$this->setState('limit', $limit);

		// Set the default ordering behaviour
		if ($order == '' && isset($order))
		{
			parent::populateState("id", "ASC");
		}
		else
		{
			parent::populateState($order, $dir);
		}

		parent::populateState($order, $dir);
	}
}
