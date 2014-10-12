<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
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
        $query = $this->_db->getQuery(true);

        $language = explode('-', JFactory::getLanguage()->getTag());
        $select = "DISTINCT p.id, name_{$language[0]} AS name, lsfID, hisID, ";
        $select .= 'externalID, minCrP, maxCrP, f.field, color';
        $query->select($select);

        $query->from('#__thm_organizer_pools AS p');
        $query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $programID = $this->state->get('filter.program', '-1');
        if (!empty($programID) OR $programID != '-1')
        {
            if ($programID == '-2')
            {
                $where = "p.id NOT IN ( ";
                $where .= "SELECT poolID FROM #__thm_organizer_mappings ";
                $where .= "WHERE poolID IS NOT null )";
                $query->where($where);
            }
            else
            {
                $borders = $this->getBorders($programID, 'program');
                if (!empty($borders))
                {
                    $query->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
                    $query->where("lft > '{$borders['lft']}'");
                    $query->where("rgt < '{$borders['rgt']}'");
                }
            }
        }

        $search = '%' . $this->_db->escape($this->state->get('filter.search'), true) . '%';
        if ($search != '%%')
        {
            $searchClause = "(name_{$language[0]} LIKE '$search' ";
            $searchClause .= "OR short_name_{$language[0]} LIKE '$search' ";
            $searchClause .= "OR abbreviation_{$language[0]} LIKE '$search')";
            $query->where($searchClause);
        }

        $query->order("{$this->state->get('list.ordering', 'name')} {$this->state->get('list.direction', 'ASC')}");
 
        return $query;
    }

    /**
     * Retrieves the mapped left and right values for the requested program
     *
     * @param   int     $resourceID  the id of the pool
     * @param   string  $type        the type of resource being checked
     *
     * @return  array contains the sought left and right values
     */
    private function getBorders($resourceID, $type = 'pool')
    {
        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT lft, rgt')->from('#__thm_organizer_mappings');
        $query->where("{$type}ID = '$resourceID'");
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $borders = $type == 'pool'? $this->_db->loadAssocList() : $this->_db->loadAssoc();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return $borders;
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

        $language = explode('-', JFactory::getLanguage()->getTag());
        $query = $this->_db->getQuery(true);
        $parts = array("dp.subject_{$language[0]}","' ('", "d.abbreviation", "' '", "dp.version", "')'");
        $select = "DISTINCT " . $query->concatenate($parts, "") . " As name";
        $query->select($select);       
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $query->where($bordersClauses, 'OR');
        $query->order('name');
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $programs = $this->_db->loadColumn();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
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
        $poolBorders = $this->getBorders($poolID);
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
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication('administrator');

        $ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'name');
        $this->setState('list.ordering', $ordering);

        $direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $direction);

        $search = $app->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
        $this->setState('filter.search', $search);

        $formProgram = $app->getUserStateFromRequest($this->context . '.filter_program', 'filter_program', '');
        $requestProgram = $app->input->getInt('programID', '-1');
        $this->setState('filter.program', (empty($formProgram) OR $formProgram == '-1')? $requestProgram : $formProgram);

        $limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit');
        $this->setState('list.limit', $limit);

        parent::populateState($ordering, $direction);
    }

    /**
     * Sets the program name for title bar
     *
     * @return  void
     */
    private function setProgramName()
    {
        $language = explode('-', JFactory::getLanguage()->getTag());
        $nameQuery = $this->_db->getQuery(true);
        $parts = array("dp.subject_{$language[0]}","' ('", "d.abbreviation", "' '", "dp.version", "')'");
        $select = $nameQuery->concatenate($parts, "") . "AS name ";
        $nameQuery->select($select);
        $nameQuery->from('#__thm_organizer_programs AS dp');
        $nameQuery->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $nameQuery->where("dp.id = '{$this->state->get('filter.program')}'");
        $this->_db->setQuery((string) $nameQuery);
        
        try 
        {
            $this->programName = $this->_db->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
    }
}
