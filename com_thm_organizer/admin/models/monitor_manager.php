<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMonitor_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');

define('DAILY', 1);
define('MIXED', 2);
define('CONTENT', 3);
define('EVENTS', 4);

/**
 * Class compiling a list of saved monitors 
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelMonitor_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'r.longname';

    protected $defaultDirection = 'ASC';

    public $displayBehaviour = array();
    /**
     * constructor
     * 
     * @param   array  $config  configurations parameter
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('r.longname', 'm.ip', 'm.useDefaults', 'm.display', 'm.content');
        }
        $this->displayBehaviour[DAILY] = JText::_('COM_THM_ORGANIZER_DAILY_PLAN');
        $this->displayBehaviour[MIXED] = JText::_('COM_THM_ORGANIZER_MIXED_PLAN');
        $this->displayBehaviour[CONTENT] = JText::_('COM_THM_ORGANIZER_CONTENT_DISPLAY');
        $this->displayBehaviour[EVENTS] = JText::_('COM_THM_ORGANIZER_EVENT_PLAN');
        parent::__construct($config);
    }

    /**
     * builds the query used to compile the items for the lsit ouput
     * 
     * @return  object
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $select = "m.id, r.longname, m.ip, m.useDefaults, m.display, m.content, ";
        $parts = array("'index.php?option=com_thm_organizer&view=monitor_edit&id='","m.id");
        $select .= $query->concatenate($parts, "") . "AS link ";
        $query->select($this->state->get("list.select", $select));
        $query->from("#__thm_organizer_monitors AS m");
        $query->innerjoin("#__thm_organizer_rooms AS r ON r.id = m.roomID");

        $this->setWhere($query);

        $ordering = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);
        $query->order("$ordering $direction");
        return $query;
    }

    /**
     * Sets the query's where clause
     * 
     * @param   object  &$query  the query to be modified
     * 
     * @return  void
     */
    private function setWhere(&$query)
    {
        $filterSearch = '%' . $this->_db->escape($this->state->get('filter.search'), true) . '%';
        $useFilterSearch = $filterSearch != '%%';
        $filterRoom = $this->state->get('filter.room', '');
        $useFilterRoom = is_numeric($filterRoom);
        $filterDisplay = $this->state->get('filter.display');
        $useFilterDisplay = is_numeric($filterDisplay);
        $contentID = $this->state->get('filter.content');
        $filterContent = is_numeric($contentID)? $this->contents[$contentID] : '';
        $useFilterContent = !empty($filterContent);

        $useFilters = ($useFilterSearch OR $useFilterRoom OR $useFilterDisplay OR $useFilterContent);
        if (!$useFilters)
        {
            return;
        }

        if ($useFilterDisplay OR $useFilterContent)
        {
            $this->addDisplayFilter($query);
        }

        if ($useFilterRoom)
        {
            $query->where("m.roomID = '$filterRoom'");
        }

        if ($useFilterSearch)
        {
            $query->where("longname LIKE '$filterSearch' OR ip LIKE '$filterSearch'");
        }
    }

    /**
     * Adds the filter settings for display behaviour and displayed content
     *
     * @param   object  &$query  the query object
     *
     * @return  void
     */
    private function addDisplayFilter(&$query)
    {
        $filterDisplay = $this->state->get('filter.display', '1');
        $useFilterDisplay = is_numeric($filterDisplay);
        $contentID = $this->state->get('filter.content', '');
        $filterContent = is_numeric($contentID)? $this->contents[$contentID] : '';
        $useFilterContent = !empty($filterContent);

        $componentDisplay = JComponentHelper::getParams('com_thm_organizer')->get('display', '1');
        $useComponentDisplay = ($useFilterDisplay AND $filterDisplay == $componentDisplay);
        $componentContent = JComponentHelper::getParams('com_thm_organizer')->get('content', '');
        $useComponentContent = ($useFilterContent AND $filterContent == $componentContent);

        $rowWhere = '';
        if ($useFilterDisplay)
        {
            $rowWhere .= "m.display ='$filterDisplay'";
        }

        if ($useFilterContent)
        {
            $rowWhere .= $useFilterDisplay? " AND " : '';
            $rowWhere .= "m.content ='$filterDisplay' ";
        }

        /**
         * One:    Both display and content are being filtered, and the component settings match both
         * Two:    Display alone is being filtered and matches component settings
         * Three:  Content alone is being filtered and matches component settings
         **/
        $conditionOne = ($useComponentDisplay AND $useComponentContent);
        $conditionTwo = ($useComponentDisplay AND !$useFilterContent);
        $conditionThree = (!$useFilterDisplay AND $useComponentContent);

        $useComponent = ($conditionOne OR $conditionTwo OR $conditionThree);
        $componentWhere = $useComponent? "OR m.useDefaults ='1'" : '';

        $query->where("( ( $rowWhere ) $componentWhere )");
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return  array  an array of objects fullfilling the request criteria
     */
    public function getItems()
    {
        $items = parent::getItems();
        $return = array();
        if (empty($items))
        {
            return $return;
        }

        $index = 0;
        foreach ($items as $item)
        {
            $return[$index] = array();
            $return[$index][0] = JHtml::_('grid.id', $index, $item->id);
            $return[$index][1] = JHtml::_('link', $item->link, $item->longname);
            $return[$index][2] = JHtml::_('link', $item->link, $item->ip);
            $controller = 'monitor';
            $tip = JText::_('COM_THM_ORGANIZER_MONITOR_MANAGER_TOGGLE_COMPONENT_SETTINGS');
            $return[$index][3] = $this->getToggle($item->id, $item->useDefaults, $controller, $tip);
            $return[$index][4] = JHtml::_('link', $item->link, $this->displayBehaviour[$item->display]);
            $return[$index][5] = JHtml::_('link', $item->link, $item->content);
            $index++;
        }
        return $return;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);

        $headers = array();
        $headers[] = '';
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_ROOM', 'r.longname', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_IP', 'm.ip', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_COMPONENT_SETTINGS', 'm.useDefault', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DISPLAY_BEHAVIOUR', 'm.display', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DISPLAY_CONTENT', 'm.content', $direction, $ordering);

        return $headers;
    }
}
