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
    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                    'id', 'id'
            );
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
        $dbo = JFactory::getDBO();

        // Get the filter values from the request
        $orderBy = $this->state->get('list.ordering');
        $orderDir = $this->state->get('list.direction');

        // Defailt ordering
        if ($orderBy == "")
        {
            $orderBy = "r.longname";
            $orderDir = "ASC";
        }

        // Create the query
        $query = $dbo->getQuery(true);
        $select = "r.id, r.gpuntisID, r.name, r.longname, ";
        $select .= "CONCAT(t.type,', ', t.subtype) AS type";
        $query->select($select);
        $query->from('#__thm_organizer_rooms AS r');
        $query->leftJoin('#__thm_organizer_room_types AS t ON r.typeID = t.id');

        $search = '%' . $dbo->getEscaped($this->state->get('filter.search'), true) . '%';
        $whereClause = "(r.name LIKE '$search'";
        $whereClause .= "OR r.longname LIKE '$search')";
        $query->where($whereClause);

        $query->order("$orderBy $orderDir");

        return $query;
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
 
        $filter = $app->getUserStateFromRequest($this->context . '.filter', 'filter', '');
        $this->setState('filter', $filter);

        parent::populateState($orderBy, $orderDir);
    }
}
