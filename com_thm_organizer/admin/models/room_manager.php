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

    protected $defaultDirection = 'asc';

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

        $this->setSearchFilter($query, array('name', 'longname'));
        $this->setValueFilters($query, array('name', 'longname', 'typeID'));

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
            if ($this->actions->{'core.edit'} OR $this->actions->{'core.delete'})
            {
                $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            }
            if ($this->actions->{'core.edit'})
            {
                $name = JHtml::_('link', $item->link, $item->name);
                $longname = JHtml::_('link', $item->link, $item->longname);
                $typeID = JHtml::_('link', $item->link, $item->type);
            }
            else
            {
                $name = $item->name;
                $longname = $item->longname;
                $typeID = $item->type;
            }
            $return[$index]['name'] = $name;
            $return[$index]['longname'] = $longname;
            $return[$index]['typeID'] = $typeID;
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
        if ($this->actions->{'core.edit'} OR $this->actions->{'core.delete'})
        {
            $headers['checkbox'] = '';
        }
        $headers['name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'r.name', $direction, $ordering);
        $headers['longname'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DISPLAY_NAME', 'r.longname', $direction, $ordering);
        $headers['typeID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_TYPE', 'type', $direction, $ordering);

        return $headers;
    }
}
