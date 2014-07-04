<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMonitor_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
jimport('joomla.filesystem.folder');

/**
 * Class compiling a list of saved monitors 
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelMonitor_Manager extends JModelList
{
    /**
     * An array holding the text constants used for the different display types
     * 
     * @var array 
     */
    public $behaviours = null;

    /**
     * Array holding the ids and names of the monitor associated rooms for the
     * selection box
     * 
     * @var array 
     */
    public $rooms = null;

    /**
     * Array holding indexes and names of files for the selection box
     * 
     * @var array 
     */
    public $contents = null;

    /**
     * constructor
     * 
     * @param   array  $config  configurations parameter
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                                             'roomID', 'roomID',
                                             'room', 'name',
                                             'ip', 'ip',
                                             'useDefaults', 'useDefaults',
                                             'display', 'display',
                                             'schedule_refresh', 'schedule_refresh',
                                             'content_refresh', 'content_refresh',
                                             'content', 'm.content'
                                            );
        }
        parent::__construct($config);
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return  array  an array of objects fullfilling the request criteria
     */
    public function getItems()
    {
        $this->rooms = $this->getRooms();
        $this->behaviours = array(
                                  1 => JText::_('COM_THM_ORGANIZER_MON_SCHEDULE'),
                                  2 => JText::_('COM_THM_ORGANIZER_MON_MIXED'),
                                  3 => JText::_('COM_THM_ORGANIZER_MON_CONTENT'),
                                  4 => JText::_('COM_THM_ORGANIZER_MON_EVENTS')
                                 );
        $this->contents = $this->getContents();
        return parent::getItems();
    }

    /**
     * builds the query used to compile the items for the lsit ouput
     * 
     * @return  object
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $select = "m.id, roomID, ip, useDefaults, display, schedule_refresh, content_refresh, content, longname AS room, ";
        $parts = array("'index.php?option=com_thm_organizer&view=monitor_edit&monitorID='","m.id");
        $select .= $query->concatenate($parts, "") . "AS link ";
        $query->select($this->getState("list.select", $select));
        $query->from("#__thm_organizer_monitors AS m");
        $query->leftjoin("#__thm_organizer_rooms AS r ON r.id = m.roomID");

        $this->setWhere($query);

        $orderby = $this->_db->escape($this->getState('list.ordering', 'r.name'));
        $direction = $this->_db->escape($this->getState('list.direction', 'ASC'));
        $query->order("$orderby $direction");
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
        $filterRoom = $this->getState('filter.room');
        $useFilterRoom = is_numeric($filterRoom);
        $filterDisplay = $this->getState('filter.display');
        $useFilterDisplay = is_numeric($filterDisplay);
        $contentID = $this->getState('filter.content');
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
        $filterDisplay = $this->getState('filter.display', '1');
        $useFilterDisplay = is_numeric($filterDisplay);
        $contentID = $this->getState('filter.content', '');
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
     * Loads view specific filter parameters into the state object
     * 
     * @param   string  $ordering   the filter parameter to be used to sort by
     * @param   string  $direction  the direction in which the sort is to take place
     * 
     * @return void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
        $this->setState('filter.search', $search);

        $room = $this->getUserStateFromRequest($this->context . '.filter.room', 'filter_room');
        $this->setState('filter.room', $room);

        $display = $this->getUserStateFromRequest($this->context . '.filter.display', 'filter_display');
        $this->setState('filter.display', $display);

        $content = $this->getUserStateFromRequest($this->context . '.filter.content', 'filter_content');
        $this->setState('filter.content', $content);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * retrieves a list of rooms and their ids which are currently in use by the
     * monitors
     *
     * @return array associative array id => room name
     */
    private function getRooms()
    {
        $query = $this->_db->getQuery(true);
        $query->select('r.id, r.longname');
        $query->from("#__thm_organizer_rooms AS r");
        $query->innerJoin("#__thm_organizer_monitors AS m ON m.roomID = r.id");
        $query->order('r.longname ASC');
        $this->_db->setQuery((string) $query);
        
        try
        {
            $results = $this->_db->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        $rooms = array();
        if (count($results))
        {
            foreach ($results as $result)
            {
                $rooms[$result['id']] = $result['longname'];
            }
        }
        return $rooms;
    }

    /**
     * Gets the files uploaded for the component
     * 
     * @return  array  an array of files
     */
    private function getContents()
    {
        return JFolder::files(JPATH_ROOT . '/images/thm_organizer');
    }
}
