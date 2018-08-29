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
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class retrieves information for a filtered set of room types.
 */
class THM_OrganizerModelRoom_Type_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param array $config Configuration  (default: array)
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['name', 'min_capacity', 'max_capacity'];
        }

        parent::__construct($config);
    }

    /**
     * Method to get all room types from the database
     *
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        $query = $this->_db->getQuery(true);

        $select    = "t.id, t.name_$shortTag AS name, min_capacity, max_capacity, t.gpuntisID, count(r.typeID) AS roomCount, ";
        $linkParts = ["'index.php?option=com_thm_organizer&view=room_type_edit&id='", "t.id"];
        $select    .= $query->concatenate($linkParts, "") . " AS link";
        $query->select($select);

        $query->from('#__thm_organizer_room_types AS t');
        $query->leftJoin('#__thm_organizer_rooms AS r on r.typeID = t.id');

        $this->setSearchFilter($query, ['gpuntisID', 'name_de', 'name_en', 'min_capacity', 'max_capacity']);

        $this->setOrdering($query);
        $query->group('t.id');

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
            $return[$index]                 = [];
            $return[$index]['checkbox']     = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['gpuntisID']    = JHtml::_('link', $item->link, $item->gpuntisID);
            $return[$index]['name']         = JHtml::_('link', $item->link, $item->name);
            $return[$index]['min_capacity'] = $item->min_capacity;
            $return[$index]['max_capacity'] = $item->max_capacity;
            $return[$index]['roomCount']    = $item->roomCount;
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
        $ordering                = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction               = $this->state->get('list.direction', $this->defaultDirection);
        $headers                 = [];
        $headers['checkbox']     = '';
        $headers['gpuntisID']    = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_GPUNTISID', 'name', $direction,
            $ordering);
        $headers['name']         = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction,
            $ordering);
        $headers['min_capacity'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_MIN_CAPACITY', 'min_capacity',
            $direction, $ordering);
        $headers['max_capacity'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_MAX_CAPACITY', 'max_capacity',
            $direction, $ordering);
        $headers['roomCount']    = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_ROOM_COUNT', 'roomCount', $direction,
            $ordering);

        return $headers;
    }
}
