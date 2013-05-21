<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelPool_Manager for component com_thm_organizer
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool_Manager extends JModelList
{

	/**
	 * Constructor to initialise the database and call the parent constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
        $app = JFactory::getApplication();
		$ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'name');
		$this->setState('list.ordering', $ordering);
		$direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
		$this->setState('list.direction', $direction);
		$filter = $app->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
		$this->setState('filter.search', $filter);
		$limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');
		$this->setState('limit', $limit);
	}

	/**
	 * Method to select the tree of a given major
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

        $language = explode('-', JFactory::getLanguage()->getTag());
        $select = ($language[0] == 'de')? 'name_de AS name, ' : 'name_en AS name, ';
		$select .= 'p.id, lsfID, hisID, externalID, minCrP, maxCrP, f.field';
		$orderBy = $this->state->get('list.ordering', 'name');
		$orderDir = $this->state->get('list.direction', 'ASC');
		$search = '%' . $dbo->getEscaped($this->state->get('filter.search'), true) . '%';
        if ($language[0] == 'de')
        {
            $whereClause = "(name_de LIKE '$search' ";
            $whereClause .= "OR short_name_de LIKE '$search' ";
            $whereClause .= "OR abbreviation_de LIKE '$search')";
        }
        else
        {
            $whereClause = "(name_en LIKE '$search' ";
            $whereClause .= "OR short_name_en LIKE '$search' ";
            $whereClause .= "OR abbreviation_en LIKE '$search')";
        }
        
		$query->select($select);
		$query->from('#__thm_organizer_pools AS p');
		$query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
		$query->where($whereClause);
		$query->order("$orderBy $orderDir");
		
		return $query;
	}

	/**
	 * Method to overwrite the getItems method in order to set the correct
	 *
	 * @return  Object
	 */
	public function getItems()
	{
		$pools = parent::getItems();
		return $pools;
	}
}
