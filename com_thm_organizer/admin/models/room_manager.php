<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelRoom_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
/**
 * Class THM_OrganizerModelRooms for component com_thm_organizer
 * Class provides methods to deal with rooms
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelRoom_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'r.longname';

    protected $defaultDirection = 'ASC';

    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('r.longname', 'roomtype', 'r.name');
        }
        parent::__construct($config);
    }

    /**
     * Method to get all rooms from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);
        $select = "r.id, r.gpuntisID, r.name, r.longname, t.id AS typeID, ";
        $typeParts = array("t.type","', '", "t.subtype");
        $select .= $query->concatenate($typeParts, "") . " AS type, ";
        $linkParts = array("'index.php?option=com_thm_organizer&view=room_edit&id='", "r.id");
        $select .= $query->concatenate($linkParts, "") . " AS link";
        $query->select($select);
        $query->from('#__thm_organizer_rooms AS r');
        $query->leftJoin('#__thm_organizer_room_types AS t ON r.typeID = t.id');

        $this->addSearchFilter($query);

        $buildingFilter = $this->state->get('filter.building', '');
        if (!empty($buildingFilter))
        {
            $floorFilter = $this->state->get('filter.floor', '');
            $query->where("r.name LIKE '$buildingFilter.$floorFilter%'");
        }

        $this->addTypeFilter($query);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return  array  an array of objects fulfilling the request criteria
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
            $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['name'] = JHtml::_('link', $item->link, $item->name);
            $return[$index]['longname'] = JHtml::_('link', $item->link, $item->longname);
            $return[$index]['type'] = JHtml::_('link', $item->link, $item->type);
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
        $headers['checkbox'] = '';
        $headers['name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'r.name', $direction, $ordering);
        $headers['longname'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DISPLAY_NAME', 'r.longname', $direction, $ordering);
        $headers['type'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_TYPE', 'type', $direction, $ordering);

        return $headers;
    }

    /**
     * Retrieves an array of data items and builds filter arrays based upon
     * those items.
     * 
     * @return  mixed  An array of data items on success, false on failure.
     */
    private function setBuildings()
    {
        $query = $this->getListQuery();
        $query->clear('where');
        $this->addSearchFilter($query);
        $this->addTypeFilter($query);
        
        try 
        {
            $roomNames = $this->_db->setQuery((string) $query)->loadColumn(2);
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
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
    private function setFloors()
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
        
        try 
        {
            $roomNames = $this->_db->setQuery((string) $query)->loadColumn(2);
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
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
    private function setTypes()
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
        
        try 
        {
            $rooms = $this->_db->setQuery((string) $query)->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
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
     * Adds the search filter to the query
     * 
     * @param   object  &$query  the query to modify
     * 
     * @return  void
     */
    private function addSearchFilter(&$query)
    {
        $userInput = $this->state->get('filter.search', '');
        if (empty($userInput))
        {
            return;
        }
        $search = '%' . $this->_db->escape($userInput, true) . '%';
        $query->where("(r.name LIKE '$search' OR r.longname LIKE '$search')");
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
}
