<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        monitor manager model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class compiling a list of saved monitors 
 * 
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerModelmonitor_manager extends JModelList
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
     * constructor
     */
    public function __construct()
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                                             'roomID', 'roomID',
                                             'room', 'name',
                                             'ip', 'ip',
                                             'display', 'display',
                                             'schedule_refresh', 'schedule_refresh',
                                             'content_refresh', 'content_refresh',
                                             'content', 'm.content'
                                            );
        }
        parent::__construct($config);
        $this->behaviours = array(
                                  1 => JText::_('COM_THM_ORGANIZER_MON_SCHEDULE'),
                                  2 => JText::_('COM_THM_ORGANIZER_MON_MIXED'),
                                  3 => JText::_('COM_THM_ORGANIZER_MON_CONTENT'),
                                  4 => JText::_('COM_THM_ORGANIZER_MON_EVENTS')
                                 );
        $this->rooms = $this->getRooms();
    }

    /**
     * builds the query used to compile the items for the lsit ouput
     * 
     * @return void
     */
    protected function getListQuery()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);

        $select = "m.id, roomID, ip, display, schedule_refresh, content_refresh, content, longname AS room, ";
        $select .= "CONCAT ('index.php?option=com_thm_organizer&view=monitor_edit&monitorID=', m.id) AS link ";
        $query->select($this->getState("list.select", $select));
        $query->from("#__thm_organizer_monitors AS m");
        $query->leftjoin("#__thm_organizer_rooms AS r ON r.id = m.roomID");

        $room = $this->getState('filter.room');
        if (is_numeric($room))
        {
            $query->where("m.roomID = $room");
        }

        $display = $this->getState('filter.display');
        if (is_numeric($display))
        {
            $query->where("m.display = $display");
        }

        $orderby = $dbo->getEscaped($this->getState('list.ordering', 'r.name'));
        $direction = $dbo->getEscaped($this->getState('list.direction', 'ASC'));
        $query->order("$orderby $direction");

        return $query;
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
        $room = $this->getUserStateFromRequest($this->context . '.filter.room', 'filter_room');
        $this->setState('filter.room', $room);

        $display = $this->getUserStateFromRequest($this->context . '.filter.display', 'filter_display');
        $this->setState('filter.display', $display);

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
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select('r.id, r.longname');
        $query->from("#__thm_organizer_rooms AS r");
        $query->innerJoin("#__thm_organizer_monitors AS m ON m.roomID = r.id");
        $query->order('r.longname ASC');
        $dbo->setQuery((string) $query);
        $results = $dbo->loadAssocList();
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
}
