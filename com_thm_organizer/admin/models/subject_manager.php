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
    public $programs = null;

    public $pools = null;

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

        if (!$this->__state_set)
        {
            $this->populateState();
            $this->__state_set = true;
        }
        $this->setPrograms();
        $programID = $this->state->get('filter.program');
        if (!empty($programID))
        {
            $this->setPools($programID);
        }
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
        $select = 'DISTINCT s.id, lsfID, hisID, externalID, ';
        $select .= "name_{$language[0]} AS name, field, color";
        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_mappings AS m ON s.id = m.subjectID');
        $query->leftJoin('#__thm_organizer_fields AS f ON s.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

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

        $borders = $this->getListBorders();
        if (!empty($borders))
        {
            $query->where("lft > '{$borders['lft']}'");
            $query->where("rgt < '{$borders['rgt']}'");
        }

        $query->order("$orderCol $orderDir");

        return $query;
    }

    /**
     * Retrieves the left and right values for determining which subjects will
     * be displayed.
     *
     * @return  array  the mapping borders for the where clause, empty if not
     *                 applicable
     */
    private function getListBorders()
    {
        $poolID = $this->state->get('filter.pool');
        if (!empty($poolID) AND $poolID != -1)
        {
            $poolBorders = $this->getBorders($poolID, 'poolID');
        }

        $programID = $this->state->get('filter.program');
        if (!empty($programID))
        {
            $programBorders = $this->getBorders($programID, 'programID');
        }

        if (isset($poolBorders))
        {
            if ($poolBorders['lft'] > $programBorders['lft']
             AND $poolBorders['rgt'] < $programBorders['rgt'])
            {
                return $poolBorders;
            }
            else
            {
                return $programBorders;
            }
        }
        elseif (isset($programBorders))
        {
            return $programBorders;
        }
        else
        {
            return array();
        }
    }

    /**
     * Retrieves the mapped left and right values for the requested program
     *
     * @param   int     $resourceID      the id of the requested resource
     * @param   string  $resourceColumn  the column with the desired resource values
     *
     * @return  array contains the sought left and right values
     */
    private function getBorders($resourceID, $resourceColumn)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('lft, rgt')->from('#__thm_organizer_mappings')->where("$resourceColumn = '$resourceID'");
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

        $pool = $app->getUserStateFromRequest($this->context . '.filter_pool', 'filter_pool', '');
        $this->setState('filter.pool', $pool);

        parent::populateState($order, $dir);
    }

    /**
     * Retrieves a list of mapped pools
     *
     * @param   int  $programID  the id of the selected program
     *
     * @return  void
     */
    private function setPools($programID)
    {
        $borders = $this->getBorders($programID, 'programID');
        $language = explode('-', JFactory::getLanguage()->getTag());

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("p.id, level, name_{$language[0]} AS name");
        $query->from('#__thm_organizer_pools AS p');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
        $query->where("lft > '{$borders['lft']}'");
        $query->where("rgt < '{$borders['rgt']}'");
        $query->order('lft');
        $dbo->setQuery((string) $query);
        $pools = $dbo->loadAssocList();
 
        if (empty($pools))
        {
            $this->pools = array();
            return;
        }

        foreach ($pools as $key => $value)
        {
            $indent = '';
            $level = 1;
            while ($level < $value['level'])
            {
                $indent .= "&nbsp;&nbsp;&nbsp;";
                $level++;
            }
            $pools[$key]['name'] = $indent . "|_" . $pools[$key]['name'];
        }
        $this->pools = $pools;
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
