<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
require_once JPATH_COMPONENT . '/assets/helpers/mapping.php';

/**
 * Class provides functions for displaying a list of pools
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
        $this->programs = THM_OrganizerHelperMapping::getPrograms();
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return  array  an array of objects fullfilling the request criteria
     */
    public function getItems()
    {
        $pools = parent::getItems();
        if (!empty($pools))
        {
            foreach ($pools as $key => $pool)
            {
                $pools[$key]->program = $this->getProgram($pool->id);
            }
        }
        $programvalue = $this->state->get('filter.program');
        if (!empty($programvalue))
        {
            $this->setProgramName();
        }
        return $pools;
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
        $select .= 'p.id, lsfID, hisID, externalID, minCrP, maxCrP, f.field, color';
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
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
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
     * @param   int  $poolID  the id of the pool
     *
     * @return  array contains the sought left and right values
     */
    private function getPoolBorders($poolID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT lft, rgt')->from('#__thm_organizer_mappings')->where("poolID = '$poolID'");
        $dbo->setQuery((string) $query);
        return $dbo->loadAssocList();
    }

    /**
     * Retrieves the names of the programs to which a pool is ordered
     *
     * @param   array  $poolBorders  the left and right values of the pool's
     *                               mappings
     *
     * @return  array  the names of the programs to which the pool is ordered
     */
    private function getPoolPrograms($poolBorders)
    {
        $bordersClauses = array();
        foreach ($poolBorders AS $border)
        {
            $bordersClauses[] = "( lft < '{$border['lft']}' AND rgt > '{$border['rgt']}')";
        }
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT CONCAT( dp.subject, ' (', d.abbreviation, ' ', dp.version, ')') AS name");
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $query->where($bordersClauses, 'OR');
        $query->order('name');
        $dbo->setQuery((string) $query);
        $programs = $dbo->loadResultArray();
        return $programs;
    }

    /**
     * Retrieves a string value representing the degree programs to which the
     * pool is ordered.
     *
     * @param   int  $poolID  the id of the pool
     *
     * @return  string  string representing the associated program
     */
    private function getProgram($poolID)
    {
        $poolBorders = $this->getPoolBorders($poolID);
        if (empty($poolBorders))
        {
            return JText::_('COM_THM_ORGANIZER_POM_NO_MAPPINGS');
        }
        $programs = $this->getPoolPrograms($poolBorders);
        if (count($programs) === 1)
        {
            return $programs[0];
        }
        else
        {
            return JText::_('COM_THM_ORGANIZER_POM_MULTIPLE_MAPPINGS');
        }
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
     * Sets the program name for title bar
     *
     * @return  void
     */
    private function setProgramName()
    {
        $dbo = JFactory::getDbo();
        $nameQuery = $dbo->getQuery(true);
        $nameQuery->select("CONCAT( dp.subject, ' (', d.abbreviation, ' ', dp.version, ')') AS name");
        $nameQuery->from('#__thm_organizer_programs AS dp');
        $nameQuery->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $nameQuery->where("dp.id = '{$this->state->get('filter.program')}'");
        $dbo->setQuery((string) $nameQuery);
        $this->programName = $dbo->loadResult();
    }
}
