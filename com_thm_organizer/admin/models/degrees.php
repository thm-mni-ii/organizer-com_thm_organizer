<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelDegrees
 * @description THM_OrganizerModelDegrees component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelDegrees for component com_thm_organizer
 *
 * Class provides methods to deal with degrees
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
*/
class THM_OrganizerModelDegrees extends JModelList
{
	/**
	 * Constructor to set up the configuration and call the parent constructor
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
	 * Method to select all degree rows from the database
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$dbo = JFactory::getDBO();

		// Get the filter data
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		// Defailt ordering
		if ($orderCol == "")
		{
			$orderCol = "id";
			$orderDirn = "asc";
		}

		// Perform the database request
		$query = $dbo->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_degrees');
		$query->order($orderCol . " " . $orderDirn);

		return $query;
	}

	/**
	 * Method to set the populate state
	 * 
	 * @param   string  $order  the property by which results should be ordered
	 * @param   string  $dir    the direction in which results should be ordered
	 *
	 * @return  void
	 */
	protected function populateState($order = null, $dir = null)
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
