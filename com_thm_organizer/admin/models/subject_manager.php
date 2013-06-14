<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSubject_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Provides method for generating a list of subjects
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSubject_Manager extends JModelList
{
	/**
	 * Pagination. 
	 *
	 * @var    Object
	 */
	private $_pagination = null;
    
    public $programs = null;

	/**
	 * Constructor to set up the config array and call the parent constructor
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
        $this->setPrograms();
	}

	/**
	 * Method to select all existent assets from the database
	 *
	 * @return  Object  A query object
	 */
	protected function getListQuery()
	{
		$dbo = JFactory::getDBO();
        $language = explode('-', JFactory::getLanguage()->getTag());

		$orderCol = $this->state->get('list.ordering');
		$orderDir = $this->state->get('list.direction');

		// Create the sql query
		$query = $dbo->getQuery(true);
        $select = 's.id, lsfID, hisID, externalID, ';
        $select .= $language[0] == 'de'? 'name_de AS name' : 'name_en AS name';
        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_mappings AS m ON s.id = m.subjectID');

        $searchState = $this->state->get('filter.search');
        if (!empty($searchState))
        {
            $search = '%' . $dbo->getEscaped($searchState, true) . '%';
                    $searchClause = "(name_de LIKE '$search' ";
                    $searchClause .= "OR short_name_de LIKE '$search' ";
                    $searchClause .= "OR abbreviation_de LIKE '$search' ";
                    $searchClause .= "OR name_en LIKE '$search' ";
                    $searchClause .= "OR short_name_en LIKE '$search' ";
                    $searchClause .= "OR abbreviation_en LIKE '$search' ";
                    $searchClause .= "OR lsfID LIKE '$search' ";
                    $searchClause .= "OR hisID LIKE '$search' ";
                    $searchClause .= "OR externalID LIKE '$search') ";
            $query->where($searchClause);
        }
        $programID = $this->state->get('filter.program');
        if (!empty($programID))
        {
            $borders = $this->getProgramBorders($programID);
        }
        if (!empty($borders))
        {
            $query->where("lft > '{$borders['lft']}'");
            $query->where("rgt < '{$borders['rgt']}'");
        }
     
		$query->order("$orderCol $orderDir");

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
	 * Method to get the table
	 * 
	 * @param   string  $order  the property to order the list by
	 * @param   string  $dir    the direction in which the list is to be ordered
	 *
	 * @return  void
	 */
	protected function populateState($order = null, $dir = null)
	{
		$app = JFactory::getApplication();

		$order = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'name');
		$this->setState('list.ordering', $order);

		$dir = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
		$this->setState('list.direction', $dir);

		$filter = $app->getUserStateFromRequest($this->context . '.filter', 'filter', '');
		$this->setState('filter', $filter);

		$limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');
		$this->setState('limit', $limit);

		$search = $app->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
		$this->setState('filter.search', $search);

		$program = $app->getUserStateFromRequest($this->context . '.filter_program', 'filter_program', '');
		$this->setState('filter.program', $program);

		parent::populateState($order, $dir);
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
        $nameQuery->from('#__thm_organizer_programs AS dp');
        $nameQuery->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $nameQuery->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $nameQuery->order('name');
        $dbo->setQuery((string) $nameQuery);
        $this->programs = $dbo->loadAssocList();
    }
}
