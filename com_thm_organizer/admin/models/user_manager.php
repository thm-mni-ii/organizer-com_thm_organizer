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
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'ASC';

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
     * Generates the headers to be used by the output table
     *
     * @return  array  the table headers
     */
    public function getHeaders()
    {
        $ordering = $this->state->get('list.ordering', 'name');
        $direction = $this->state->get('list.direction', 'ASC');
        $headers = array();
        $headers['checkbox'] = '';
        $headers['name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction, $ordering);
        $headers['username'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_USERNAME', 'username', $direction, $ordering);
        $headers['program_manager'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_PROGRAM_MANAGER', 'program_manager', $direction, $ordering);
        $headers['planner'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_PLANNER', 'planner', $direction, $ordering);
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
            $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['name'] = $item->name;
            $return[$index]['username'] = $item->username;
            $return[$index]['program_manager'] = $this->getToggle($item->id, $item->program_manager, 'user', JText::_('COM_THM_ORGANIZER_TOGGLE_ROLE'), 'program_manager');
            $return[$index]['planner'] = $this->getToggle($item->id, $item->planner, 'user', JText::_('COM_THM_ORGANIZER_TOGGLE_ROLE'), 'planner');
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
        $query->innerJoin('#__users AS u ON ou.userID = u.id');

        $this->setSearchFilter($query, array('name', 'username'));
        $this->setValueFilters($query, array('name', 'username', 'program_manager', 'planner'));

        $this->setOrdering($query);

        return $query;
    }
}
