<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerModelMajors
 * @description THM_OrganizerModelMajors component admin model
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelMajors for component com_thm_organizer
 *
 * Class provides methods to deal with majors
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerModelMajors extends JModelList
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
		$this->db = JFactory::getDBO();
		parent::__construct();
	}

	/**
	 * Method to determine all majors
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db = JFactory::getDBO();
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$search = $this->state->get('filter');

		// Build the query
		$query = $db->getQuery(true);
		$query->select("
				#__thm_organizer_majors.id as id,
				#__thm_organizer_degrees.name AS degree,
				#__thm_organizer_majors.subject,
				#__thm_organizer_majors.po,
				#__thm_organizer_majors.lsf_object as lsf_object,
				#__thm_organizer_majors.lsf_degree as lsf_degree,
				#__thm_organizer_majors.lsf_study_path as lsf_study_path
				");
		$query->from('#__thm_organizer_majors');
		$query->leftJoin('#__thm_organizer_degrees ON
				#__thm_organizer_degrees.id = #__thm_organizer_majors.degree_id
				');

		$query->order($orderCol . " " . $orderDirn);

		return $query;
	}

	/**
	 * Method to populate state
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
		if ($order == '')
		{
			parent::populateState("id", "ASC");
		}
		else
		{
			parent::populateState($order, $dir);
		}
	}

	/**
	 * Method to delete something
	 *
	 * @return  void
	 */
	public function delete()
	{

	}
}
