<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once 'list.php';

use THM_OrganizerHelperHTML as HTML;

/**
 * Class retrieves information for a filtered set of rooms.
 */
class THM_OrganizerModelRoom_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'r.longname';

    protected $defaultDirection = 'asc';

    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param array $config Configuration  (default: array)
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['r.longname', 'roomtype', 'buildingName'];
        }

        parent::__construct($config);
    }

    /**
     * Method to get all rooms from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = Languages::getShortTag();
        $query    = $this->_db->getQuery(true);

        $linkParts = ["'index.php?option=com_thm_organizer&view=room_edit&id='", 'r.id'];
        $query->select('r.id, r.gpuntisID, r.longname')
            ->select("t.id AS typeID, t.name_$shortTag AS type")
            ->select('b.id AS buildingID, b.name AS buildingName')
            ->select($query->concatenate($linkParts, '') . ' AS link')
            ->from('#__thm_organizer_rooms AS r')
            ->leftJoin('#__thm_organizer_room_types AS t ON r.typeID = t.id')
            ->leftJoin('#__thm_organizer_buildings AS b ON b.id = r.buildingID');

        $this->setSearchFilter($query, ['r.longname', 'b.name', 't.name_de', 't.name_en']);
        $this->setValueFilters($query, ['longname', 'buildingID', 'typeID']);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return array  an array of objects fulfilling the request criteria
     */
    public function getItems()
    {
        $items  = parent::getItems();
        $return = [];

        if (empty($items)) {
            return $return;
        }

        $index = 0;

        foreach ($items as $item) {
            $return[$index]               = [];
            $return[$index]['checkbox']   = HTML::_('grid.id', $index, $item->id);
            $return[$index]['longname']   = HTML::_('link', $item->link, $item->longname);
            $return[$index]['buildingID'] = HTML::_('link', $item->link, $item->buildingName);
            $return[$index]['typeID']     = HTML::_('link', $item->link, $item->type);
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
        $ordering  = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);
        $headers   = [];

        $headers['checkbox']   = '';
        $headers['longname']   = HTML::sort('DISPLAY_NAME', 'r.longname', $direction, $ordering);
        $headers['buildingID'] = HTML::sort('BUILDING', 'buildingName', $direction, $ordering);
        $headers['typeID']     = HTML::sort('TYPE', 'type', $direction, $ordering);

        return $headers;
    }
}
