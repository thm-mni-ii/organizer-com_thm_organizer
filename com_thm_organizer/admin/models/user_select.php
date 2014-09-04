<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        user select model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class compiling a list of users
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerModelUser_Select extends JModelList
{
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
            $config['filter_fields'] = array( 'username', 'name');
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
        $this->headers = $this->getHeaders();
        return $this->processItems($items);
    }

    /**
     * Generates the headers to be used by the output table
     *
     * @return  array  the table headers
     */
    private function getHeaders()
    {
        $orderby = $this->getState('list.ordering', 'name');
        $direction = $this->getState('list.direction', 'ASC');
        $headers = array();
        $headers[0] = '';
        $headers[1] = JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'name', $direction, $orderby);
        $headers[2] = JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_USERNAME'), 'username', $direction, $orderby);
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
        $query->select("id, name, username");
        $query->from('#__users');
        $subQuery = $this->_db->getQuery(true);
        $subQuery->select("id");
        $subQuery->from('#__thm_organizer_users');
        $query->where('id NOT IN (' . (string) $subQuery . ')');

        $search = $this->getState('filter.user');
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

        $orderby = $this->_db->escape($this->getState('list.ordering', 'name'));
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
        $search = $this->getUserStateFromRequest($this->context . '.filter.user', 'filter_user', '');
        $this->setState('filter.user', $search);

        $orderBy = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'user');
        $this->setState('list.ordering', $orderBy);

        $direction = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $direction);

        parent::populateState($ordering, $direction);
    }
}
