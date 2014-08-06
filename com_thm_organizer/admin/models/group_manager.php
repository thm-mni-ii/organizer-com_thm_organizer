<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        group manager model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class compiling a list of saved event categories
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerModelGroup_Manager extends JModelList
{
    /**
     * sets variables and configuration data
     *
     * @param   array  $config  the configuration parameters
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array( 'user', 'program_manager', 'planner' );
        }
        parent::__construct($config);
    }

    /**
     * Generates the query to be used to fill the output list
     *
     * @return  object  the jdatabasequery object
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);
        $query->select("'*'");
        $query->from('#__thm_organizer_groups');

        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            $query->where("(ec.title LIKE '%" . implode("%' OR ec.title LIKE '%", explode(' ', $search)) . "%')");
        }

        $group = $this->getState('filter.group', '*');
        if ($group !== '*')
        {
            if ($group === '1')
            {
                $query->where("program_manager = 1");
            }
            elseif ($group === '2')
            {
                $query->where("planner = 1");
            }
        }

        $orderby = $this->_db->escape($this->getState('list.ordering', 'user'));
        $direction = $this->_db->escape($this->getState('list.direction', 'ASC'));
        $query->order("$orderby $direction");

        return $query;
    }

    /**
     * Takes user filter parameters and adds them to the view state
     *
     * @param   string  $ordering   the filter parameter to be used for ordering
     * @param   string  $direction  the direction in which results are to be ordered
     *
     * @return  void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $dbo = JFactory::getDbo();

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '');
        $this->setState('filter.search', $search);

        $group = $dbo->escape($this->getUserStateFromRequest($this->context . '.filter.group', 'filter_group', '*'));
        $this->setState('filter.group', $group);

        $orderBy = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'user');
        $this->setState('list.ordering', $orderBy);

        $direction = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $direction);

        parent::populateState($ordering, $direction);
    }
}
