<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelProgram_Manager
 * @description THM_OrganizerModelProgram_Manager component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelProgram_Manager for component com_thm_organizer
 *
 * Class provides methods to deal with majors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelProgram_Manager extends JModelList
{
    public $degrees = null;
 
    public $versions = null;

    public $fields = null;

    /**
     * Constructor to initialise the database and call the parent constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->degrees = $this->getDegrees();
        $this->versions = $this->getVersions();
        $this->fields = $this->getFields();
    }

    /**
     * Retrieves a list of degrees and their ids
     *
     * @return  array
     */
    private function getDegrees()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT d.id AS id, d.name AS name');
        $this->setFrom($query);
        $this->setSearch($query);
        $query->order('name ASC');
        $dbo->setQuery((string) $query);
        $degrees = $dbo->loadAssocList();
        return empty($degrees)? array() : $degrees;
    }

    /**
     * Retrieves a list of fields
     *
     * @return  array
     */
    private function getFields()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT f.id AS id, f.field AS field');
        $this->setFrom($query);
        $this->setSearch($query);
        $query->order('field ASC');
        $dbo->setQuery((string) $query);
        $fields = $dbo->loadAssocList();
        return empty($fields)? array() : $fields;
    }

    /**
     * Method to determine all majors
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);
        $select = "subject, abbreviation, version, lsfDegree, lsfFieldID, ";
        $select .= "dp.id as id, m.id AS mapping, field, color ";
        $query->select($select);

        $this->setFrom($query);

        $this->setSearch($query);

        $degree = $this->getState('filter.degree');
        if (is_numeric($degree))
        {
            $query->where("d.id = '$degree'");
        }

        $version = $this->getState('filter.version');
        if (is_numeric($version))
        {
            $query->where("version = '$version'");
        }

        $field = $this->getState('filter.field');
        if (is_numeric($field))
        {
            $query->where("f.id = '$field'");
        }

        $query->order("{$this->state->get('list.ordering')} {$this->state->get('list.direction')}");

        return $query;
    }

    /**
     * Retrieves a list of versions
     *
     * @return  array
     */
    private function getVersions()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT dp.version AS id, dp.version AS value');
        $this->setFrom($query);
        $this->setSearch($query);
        $query->order('version ASC');
        $dbo->setQuery((string) $query);
        $versions = $dbo->loadAssocList();
        return empty($versions)? array() : $versions;
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
        $orderBy = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'subject, abbreviation, version');
        $this->setState('list.ordering', $orderBy);

        $direction = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $direction);

        $filter = $this->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
        $this->setState('filter.search', $filter);
 
        $limit = $this->getUserStateFromRequest($this->context . '.limit', 'limit', '');
        $this->setState('list.limit', $limit);

        $degree = $this->getUserStateFromRequest($this->context . '.filter.degree', 'filter_degree');
        $this->setState('filter.degree', $degree);

        $version = $this->getUserStateFromRequest($this->context . '.filter.version', 'filter_version');
        $this->setState('filter.version', $version);

        $field = $this->getUserStateFromRequest($this->context . '.filter.field', 'filter_field');
        $this->setState('filter.field', $field);
    }

    /**
     * Sets the from clauses of the queries used
     * 
     * @param   object  &$query  the query object
     * 
     * @return  void
     */
    private function setFrom(&$query)
    {
        $query->from('#__thm_organizer_programs AS dp');
        $query->leftJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $query->leftJoin('#__thm_organizer_fields AS f ON dp.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
    }
    
    /**
     * Sets the search clause dependent upon user request
     * 
     * @param   object  &$query  the query object
     * 
     * @return  void
     */
    private function setSearch(&$query)
    {
        $clue = $this->getState('filter.search');
        if (isset($clue))
        {
            $clue = trim($clue);
            if (!empty($clue))
            {
                $search = '%' . $this->_db->getEscaped($clue, true) . '%';
                $whereClause = "( subject LIKE '$search' ";
                $whereClause .= "OR version LIKE '$search' ";
                $whereClause .= "OR d.name LIKE '$search' )";
                $query->where($whereClause);
            }
        }
    }
}
