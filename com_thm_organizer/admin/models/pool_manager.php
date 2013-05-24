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
    public $programName = '';
    
    public $programs = null;

	/**
	 * Constructor to initialise the database and call the parent constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

    /**
     * Retrieves a list of mapped programs
     * 
     * @return  void
     */
    private function setPrograms()
    {
        $dbo = JFactory::getDbo();
        $nameQuery = $dbo->getQuery(true);
        $nameQuery->select("dp.id, CONCAT( dp.subject, ', (', d.abbreviation, ' ', dp.version, ')') AS name");
        $nameQuery->from('#__thm_organizer_degree_programs AS dp');
        $nameQuery->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $nameQuery->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $nameQuery->order('name');
        $dbo->setQuery((string) $nameQuery);
        $this->programs = $dbo->loadAssocList();
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
		$search = $app->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
		$this->setState('filter.search', $search);
		$formProgram = $app->getUserStateFromRequest($this->context . '.filter_program', 'filter_program', '');
        $requestProgram = JRequest::getInt('programID');
		$this->setState('filter.program', (empty($formProgram) OR $formProgram == '-1')? $requestProgram : $formProgram);
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
        if ($search != '%%')
        {
            if ($language[0] == 'de')
            {
                $searchClause = "(name_de LIKE '$search' ";
                $searchClause .= "OR short_name_de LIKE '$search' ";
                $searchClause .= "OR abbreviation_de LIKE '$search')";
            }
            else
            {
                $searchClause = "(name_en LIKE '$search' ";
                $searchClause .= "OR short_name_en LIKE '$search' ";
                $searchClause .= "OR abbreviation_en LIKE '$search')";
            }
        }
        $programID = $this->state->get('filter.program');
        if (!empty($programID))
        {
            $borders = $this->getProgramBorders($programID);
        }
        
		$query->select($select);
		$query->from('#__thm_organizer_pools AS p');
        if (!empty($borders))
        {
            $query->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
        }
		$query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        if (!empty($searchClause))
        {
            $query->where($searchClause);
        }
        if (!empty($borders))
        {
            $query->where("lft > '{$borders['lft']}'");
            $query->where("rgt < '{$borders['rgt']}'");
        }
		$query->order("$orderBy $orderDir");
		
		return $query;
	}

    /**
     * Retrieves the mapped left and right values for the requested program
     * 
     * @param   int  $programID  the id of the requested program
     * 
     * @return  array contains the sought left and right values
     */
    private function getProgramBorders($programID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('lft, rgt')->from('#__thm_organizer_mappings')->where("programID = '$programID'");
        $dbo->setQuery((string) $query);
        return $dbo->loadAssoc();
    }

	/**
	 * Method to overwrite the getItems method in order to set the correct
	 *
	 * @return  array  an array of objects fullfilling the request criteria
	 */
	public function getItems()
	{
		$pools = parent::getItems();
        $this->setPrograms();
        $programFilter = 'filter.program';
        if (!empty($this->state->$programFilter))
        {
            $this->setProgramName();
        }
		return $pools;
	}

    /**
     * Sets the program name for title bar
     * 
     * @return  void
     */
    private function setProgramName()
    {
        $dbo = JFactory::getDbo();
        $nameQuery = $dbo->getQuery(true);
        $nameQuery->select("CONCAT( dp.subject, ', (', d.abbreviation, ' ', dp.version, ')') AS name");
        $nameQuery->from('#__thm_organizer_degree_programs AS dp');
        $nameQuery->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $nameQuery->where("dp.id = '{$this->state->get('filter.program')}'");
        $dbo->setQuery((string) $nameQuery);
        $this->programName = $dbo->loadResult();
    }
}
