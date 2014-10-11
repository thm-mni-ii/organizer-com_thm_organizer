<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelUser_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');

/**
 * Class compiling a list of users
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerModelUser_Manager extends THM_CoreModelList
{
    public $filters;

    public $headers;

    /**
     * sets variables and configuration data
     *
     * @param   array  $config  the configuration parameters
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array( 'username', 'name', 'program_manager', 'planner' );
        }
        parent::__construct($config);
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   11.1
     */
    public function getItems()
    {
        $items = parent::getItems();
        return $this->processItems($items);
    }

    /**
     * Generates the filters to be used in the form
     *
     * @return  array  an array of filters
     */
    public function getFilters()
    {
        $filters = array();
        $role = $this->state->get('filter.role', '*');
        $options = array();
        $options[] = array('value' => '*', 'text' => JText::_('COM_THM_ORGANIZER_USM_SELECT_ROLE'));
        $options[] = array('value' => '*', 'text' => JText::_('COM_THM_ORGANIZER_USM_SELECT_ALL_ROLES'));
        $options[] = array('value' => '1', 'text' => JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER'));
        $options[] = array('value' => '2', 'text' => JText::_('COM_THM_ORGANIZER_PLANNER'));
        $attribs = array('onChange' => "this.form.submit();");
        $filters[] = JHtml::_('select.genericlist', $options, 'filter_role', $attribs, 'value', 'text', $role);
        return $filters;
    }

    /**
     * Generates the headers to be used by the output table
     *
     * @params   int  $count  the number of displayed items
     *
     * @return  array  the table headers
     */
    public function getHeaders($count = 0)
    {
        $ordering = $this->state->get('list.ordering', 'name');
        $direction = $this->state->get('list.direction', 'ASC');
        $headers = array();
        $headers[0] = "<input type='checkbox' name='toggle' value='' onclick='checkAll($count)' />";
        $headers[1] = JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'name', $direction, $ordering);
        $headers[2] = JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_USERNAME'), 'username', $direction, $ordering);
        $headers[3] = JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER'), 'program_manager', $direction, $ordering);
        $headers[4] = JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_PLANNER'), 'planner', $direction, $ordering);
        return $headers;
    }

    /**
     * Processes the items transforming them from objects to array entries suitable for output in HTML
     *
     * @param   array  $items  the array of items
     *
     * @return  array  an array of HTML data
     */
    private function processItems($items)
    {
        $return = array();
        if (empty($items))
        {
            return $return;
        }
        $index = 0;
        foreach ($items as $item)
        {
            $return[$index][0] = JHtml::_('grid.id', $index, $item->id);
            $return[$index][1] = $item->name;
            $return[$index][2] = $item->username;
            $return[$index][3] = $this->getToggle($item->id, $item->program_manager, 'user', JText::_('COM_THM_ORGANIZER_USM_ROLE_TOGGLE'), 'program_manager');
            $return[$index][4] = $this->getToggle($item->id, $item->planner, 'user', JText::_('COM_THM_ORGANIZER_USM_ROLE_TOGGLE'), 'planner');
            $index++;
        }
        return $return;
    }

    /**
     * Generates the query to be used to fill the output list
     *
     * @return  object  the JDatabaseQuery object
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);
        $query->select("u.id, name, username, program_manager, planner");
        $query->from('#__thm_organizer_users AS ou');
        $query->innerJoin('#__users AS u ON u.id = ou.id');

        $search = $this->state->get('filter.search');
        $searchParts = explode(' ', $search);
        if (!empty($search))
        {
            $qwhery = array();
            foreach ($searchParts AS $part)
            {
                $qwhery[] = "name LIKE '%$part%' OR username LIKE '%$part%'";
            }
            $query->where("( " . implode(' OR ', $qwhery) . " )");
        }

        $role = $this->state->get('filter.role', '*');
        if ($role !== '*')
        {
            if ($role === '1')
            {
                $query->where("program_manager = 1");
            }
            elseif ($role === '2')
            {
                $query->where("planner = 1");
            }
        }

        $ordering = $this->_db->escape($this->state->get('list.ordering', 'username'));
        $direction = $this->_db->escape($this->state->get('list.direction', 'ASC'));
        $query->order("$ordering $direction");
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
        $app = JFactory::getApplication('administrator');
        $dbo = JFactory::getDbo();

        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '');
        $this->setState('filter.search', $search);

        $ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'username');
        $this->setState('list.ordering', $ordering);

        $direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $direction);

        $limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');
        $this->setState('limit', $limit);

        $role = $dbo->escape($app->getUserStateFromRequest($this->context . '.filter.role', 'filter_role', '*'));
        $this->setState('filter.role', $role);

        parent::populateState($ordering, $direction);
    }
}
