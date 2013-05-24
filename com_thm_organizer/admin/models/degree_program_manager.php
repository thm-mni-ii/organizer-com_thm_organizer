<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelDegree_Program_Manager
 * @description THM_OrganizerModelDegree_Program_Manager component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelDegree_Program_Manager for component com_thm_organizer
 *
 * Class provides methods to deal with majors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelDegree_Program_Manager extends JModelList
{
	/**
	 * Pagination. 
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
	 * Method to determine all majors
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);
		$select = "CONCAT( dp.subject, ', (', d.abbreviation, ' ', dp.version, ')') AS degreeProgram, ";
		$select .= "dp.id as id, lsfFieldID, lsfDegree, m.id AS mapping ";
		$query->select($select);
		$query->from('#__thm_organizer_degree_programs AS dp');
        $query->leftJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
		$query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
		$query->order("{$this->state->get('list.ordering')} {$this->state->get('list.direction')}");

		return $query;
	}

	/**
	 * Method to populate state
	 * 
	 * @param   string  $orderBy    An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($orderBy = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		$orderBy = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'degreeProgram');
		$this->setState('list.ordering', $orderBy);

		$direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
		$this->setState('list.direction', $direction);

		$limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');
		$this->setState('limit', $limit);

		parent::populateState($orderBy, $direction);
	}
}
