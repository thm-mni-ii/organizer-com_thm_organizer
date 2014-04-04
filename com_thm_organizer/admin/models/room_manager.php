<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelRoom_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelRooms for component com_thm_organizer
 * Class provides methods to deal with rooms
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelRoom_Manager extends JModelList
{
    public $buildings = null;

    public $floors = null;

    public $types = null;

    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->populateState();
        $this->setBuildings();
        $this->setFloors();
        $this->setTypes();
    }

    /**
     * Retrieves an array of data items and builds filter arrays based upon
     * those items.
     * 
	 * @return  mixed  An array of data items on success, false on failure.
     */
    public function setBuildings()
    {
        $query = $this->getListQuery();
        $query->clear('where');
        $this->addSearchFilter($query);
        $this->addTypeFilter($query);
        $roomNames = $this->_db->setQuery((string) $query)->loadColumn(2);
        if (!empty($roomNames))
        {
            $this->buildings = array();
            foreach ($roomNames as $roomName)
            {
                $roomNameParts = explode('.', $roomName);
                if (count($roomNameParts) == 3)
                {
                    $this->buildings[$roomNameParts[0]] = $roomNameParts[0];
                }
            }
            asort($this->buildings);
        }
    }

    /**
     * Retrieves an array of data items and builds filter arrays based upon
     * those items.
     * 
	 * @return  mixed  An array of data items on success, false on failure.
     */
    public function setFloors()
    {
        if ($this->state->get('filter.building') == '*')
        {
            return;
        }

        $query = $this->getListQuery();
        $query->clear('where');
        $this->addSearchFilter($query);
        if ($this->state->get('filter.building') != '*')
        {
            $query->where("r.name LIKE '{$this->state->get('filter.building')}.%'");
        }
        $this->addTypeFilter($query);
        $roomNames = $this->_db->setQuery((string) $query)->loadColumn(2);
        if (!empty($roomNames))
        {
            $floorNames = array(
                'U' => JText::_('COM_THM_ORGANIZER_BASEMENT'),
                '0' => JText::_('COM_THM_ORGANIZER_0_FLOOR'),
                '1' => JText::_('COM_THM_ORGANIZER_1_FLOOR'),
                '2' => JText::_('COM_THM_ORGANIZER_2_FLOOR'),
                '3' => JText::_('COM_THM_ORGANIZER_3_FLOOR'),
                '4' => JText::_('COM_THM_ORGANIZER_4_FLOOR'),
                '5' => JText::_('COM_THM_ORGANIZER_5_FLOOR'),
                '6' => JText::_('COM_THM_ORGANIZER_6_FLOOR'),
                '7' => JText::_('COM_THM_ORGANIZER_7_FLOOR'),
                '8' => JText::_('COM_THM_ORGANIZER_8_FLOOR'),
                '9' => JText::_('COM_THM_ORGANIZER_9_FLOOR'),
                '10' => JText::_('COM_THM_ORGANIZER_10_FLOOR'),
                '11' => JText::_('COM_THM_ORGANIZER_11_FLOOR'),
                '12' => JText::_('COM_THM_ORGANIZER_12_FLOOR'),
                '13' => JText::_('COM_THM_ORGANIZER_13_FLOOR'),
                '14' => JText::_('COM_THM_ORGANIZER_14_FLOOR'),
                '15' => JText::_('COM_THM_ORGANIZER_15_FLOOR')
            );
            $floors = array();
            foreach ($roomNames as $roomName)
            {
                $roomNameParts = explode('.', $roomName);
                if (count($roomNameParts) == 3 AND key_exists($roomNameParts[1], $floorNames))
                {
                    $floors[$roomNameParts[1]] = $roomNameParts[1];
                }
            }
            $this->floors = array_intersect_key($floorNames, $floors);
        }
    }

    /**
     * Retrieves an array of data items and builds filter arrays based upon
     * those items.
     * 
	 * @return  mixed  An array of data items on success, false on failure.
     */
    public function setTypes()
    {
        $query = $this->getListQuery();
        $query->clear('where');
        $this->addSearchFilter($query);
        if ($this->state->get('filter.building') != '*')
        {
            $locationFilter = $this->state->get('filter.building') . '.';
            $locationFilter .= $this->state->get('filter.floor') != '*'? $this->state->get('filter.floor') : '';
            $query->where("r.name LIKE '$locationFilter%'");
        }
        $rooms = $this->_db->setQuery((string) $query)->loadObjectList();
        if (!empty($rooms))
        {
            foreach ($rooms as $room)
            {
                $validType = (!empty($room->typeID) AND !empty($room->type));
                if ($validType)
                {
                    $this->types[$room->typeID] = $room->type;
                }
            }
            asort($this->types);
        }
    }

    /**
     * Method to get all rooms from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);
        $select = "r.id, r.gpuntisID, r.name, r.longname, ";
        $select .= "t.id AS typeID, CONCAT(t.type, ', ', t.subtype) AS type ";
        $query->select($select);
        $query->from('#__thm_organizer_rooms AS r');
        $query->leftJoin('#__thm_organizer_room_types AS t ON r.typeID = t.id');

        $this->addSearchFilter($query);

        $buildingFilter = $this->state->get('filter.building');
        if (!empty($buildingFilter) AND $buildingFilter != '*')
        {
            $locationFilter = $this->state->get('filter.building') . '.';
            $locationFilter .= $this->state->get('filter.floor') != '*'? $this->state->get('filter.floor') : '';
            $query->where("r.name LIKE '$locationFilter%'");
        }

        $this->addTypeFilter($query);

        // Get the filter values from the request
        $orderBy = $this->state->get('list.ordering', 'r.name');
        $orderDir = $this->state->get('list.direction', 'ASC');
        $query->order("$orderBy $orderDir");

        return $query;
    }

    /**
     * Adds the search filter to the query
     * 
     * @param   object  &$query  the query to modify
     * 
     * @return  void
     */
    private function addSearchFilter(&$query)
    {
        $search = '%' . $this->_db->getEscaped($this->state->get('filter.search'), true) . '%';
        if ($search != '%%')
        {
            $query->where("(r.name LIKE '$search' OR r.longname LIKE '$search')");
        }
    }

    /**
     * Adds the search filter to the query
     * 
     * @param   object  &$query  the query to modify
     * 
     * @return  void
     */
    private function addTypeFilter(&$query)
    {
        $typeFilter = $this->state->get('filter.type');
        if (!empty($typeFilter) AND $typeFilter != '*')
        {
            $query->where("r.typeID = '{$this->state->get('filter.type')}'");
        }
    }

    /**
     * Method to get the populate state
     *
     * @param   string  $orderBy   the property by which the results should be ordered
     * @param   string  $orderDir  the direction in which results should be ordered
     *
     * @return  void
     */
    protected function populateState($orderBy = null, $orderDir = null)
    {
        $app = JFactory::getApplication('administrator');

        $orderBy = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'longname');
        $this->setState('list.ordering', $orderBy);

        $orderDir = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $orderDir);

        $filter = $app->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
        $this->setState('filter.search', $filter);

        $limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');
        $this->setState('limit', $limit);

        $building = $app->getUserStateFromRequest($this->context . '.filter_building', 'filter_building', '');
        $this->setState('filter.building', $building);

        $resetFloor = $building == $app->input->get('oldBuilding');
        $floor = $app->getUserStateFromRequest($this->context . '.filter_floor', 'filter_floor', '');
        $this->setState('filter.floor', $resetFloor? '' : $floor);

        $type = $app->getUserStateFromRequest($this->context . '.filter_type', 'filter_type', '');
        $this->setState('filter.type', $type);

        $subtype = $app->getUserStateFromRequest($this->context . '.filter_subtype', 'filter_subtype', '');
        $this->setState('filter.subtype', $subtype);

        parent::populateState($orderBy, $orderDir);
    }
}
