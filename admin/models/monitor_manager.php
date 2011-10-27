<?php
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.modellist' );
class thm_organizersModelmonitor_manager extends JModelList
{
    public $display_behaviours = null;
    public $rooms = null;

    public function __construct()
    {
        $this->initializeRooms();
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] =
                array(
                    'roomID', 'm.roomID',
                    'room', 'r.name',
                    'ip', 'm.ip',
                    'display', 'd.behaviour',
                    'interval', 'm.interval',
                    'content', 'm.content'
                );
        }
        parent::__construct($config);
        $this->behaviours = $this->getDisplayBehaviours();
        $this->rooms = $this->getRooms();
    }

    /**
     * initializeRooms
     *
     * the rooms are initially installed with names instead of ids since they
     * dont exist at the time of installation. this makes sure the room id is
     * properly set should resources be available.
     *
     * @param array $monitor the index for which had no associated room resource
     */
    private function initializeRooms()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("monitorID, roomID");
        $query->from("#__thm_organizer_monitors");
        $query->where("roomID NOT IN ( SELECT id FROM #__thm_organizer_rooms )");
        $dbo->setQuery((string)$query);
        $unsetIDs = $dbo->loadAssocList();

        if(count($unsetIDs))
        {
            foreach($unsetIDs as $unsetID)
            {
                $query = $dbo->getQuery(true);
                $query->select("id");
                $query->from("#__thm_organizer_rooms");
                $query->where("name = '{$unsetID['roomID']}'");
                $dbo->setQuery((string)$query);
                $roomID = $dbo->loadResult();

                if(isset($roomID))
                {
                    $query = $dbo->getQuery(true);
                    $query->update("#__thm_organizer_monitors");
                    $query->where("monitorID = '{$unsetID['monitorID']}'");
                    $query->set("roomID = '$roomID'");
                    $dbo->setQuery((string)$query);
                    $dbo->query();
                }
            }
        }

    }

    protected function getListQuery()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);

        $select = "m.monitorID, m.roomID, m.ip, m.interval, m.content, ";
        $select .= "r.name AS room, d.behaviour ";
        $query->select($this->getState("list.select", $select));
        $query->from("#__thm_organizer_monitors AS m");
        $query->leftjoin("#__thm_organizer_rooms AS r ON r.id = m.roomID");
        $query->innerJoin("#__thm_organizer_display_behaviours AS d ON m.display = d.id");

        $room = $this->getState('filter.room');
        if(is_numeric($room)) $query->where("m.roomID = $room");

        $display = $this->getState('filter.display');
        if(is_numeric($display)) $query->where("m.display = $display");

        $orderby = $dbo->getEscaped($this->getState('list.ordering', 'r.name'));
        $direction = $dbo->getEscaped($this->getState('list.direction', 'ASC'));
        $query->order("$orderby $direction");

        return $query;
    }

    /**
     *
     * @param string $ordering
     * @param string $direction
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $room = $this->getUserStateFromRequest($this->context.'.filter.room', 'filter_room');
        $this->setState('filter.room', $room);

        $display = $this->getUserStateFromRequest($this->context.'.filter.display', 'filter_display');
        $this->setState('filter.display', $display);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * getDisplayBehaviours
     *
     * builds an array of display behaviours
     *
     * @return array
     */
    private function getDisplayBehaviours()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, behaviour');
        $query->from("#__thm_organizer_display_behaviours");
        $dbo->setQuery((string)$query);
        $results = $dbo->loadAssocList();
        $behaviours = array();
        if(count($results))
            foreach($results as $result)$behaviours[$result['id']]= JText::_($result['behaviour']);
        return $behaviours;
    }

    /**
     * getRooms
     *
     * retrieves a list of rooms and their ids which are currently in use by the
     * monitors
     *
     * @return array associative array id => room name
     */
    private function getRooms()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, name');
        $query->from("#__thm_organizer_rooms AS r");
        $query->innerJoin("#__thm_organizer_monitors AS m ON m.roomID = r.id");
        $dbo->setQuery((string)$query);
        $results = $dbo->loadAssocList();
        $rooms = array();
        if(count($results))
            foreach($results as $result)$rooms[$result['id']]= $result['name'];
        return $rooms;
    }
}