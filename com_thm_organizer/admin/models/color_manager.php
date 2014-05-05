<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelColor_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelColors for component com_thm_organizer
 * Class provides methods to deal with colors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelColor_Manager extends JModelList
{
    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Method to get all colors from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);
        $query->select("*");
        $query->from('#__thm_organizer_colors');

        $search = '%' . $this->_db->escape($this->state->get('filter.search'), true) . '%';
        if ($search != '%%')
        {
            $query->where("name LIKE '$search' OR color LIKE '$search'");
        }

        $query->order("{$this->state->get('list.ordering', 'name')} {$this->state->get('list.direction', 'ASC')}");

        return $query;
    }

    /**
     * Method to populate state
     *
     * @param   string  $orderBy   An optional ordering field.
     * @param   string  $orderDir  An optional direction (asc|desc).
     *
     * @return  void
     */
    protected function populateState($orderBy = null, $orderDir = null)
    {
        $orderBy = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'id');
        $this->setState('list.ordering', $orderBy);

        $orderDir = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $orderDir);

        $search = $this->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
        $this->setState('filter.search', $search);

        $limit = $this->getUserStateFromRequest($this->context . '.limit', 'limit', '');
        $this->setState('list.limit', $limit);
    }
}
