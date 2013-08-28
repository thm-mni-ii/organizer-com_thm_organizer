<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelTeacher_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelTeachers for component com_thm_organizer
 * Class provides methods to deal with teachers
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelTeacher_Manager extends JModelList
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
     * Method to get all teachers from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $dbo = JFactory::getDBO();

        // Create the query
        $query = $dbo->getQuery(true);
        $query->select("*, t.id AS id, t.gpuntisID AS gpuntisID");
        $query->from('#__thm_organizer_teachers AS t');
        $query->leftJoin('#__thm_organizer_fields AS f ON t.fieldID = f.id');

        $searchFilter = $this->state->get('filter.search');
        if (!empty($searchFilter))
        {
            $search = '%' . $dbo->getEscaped($this->state->get('filter.search'), true) . '%';
            $whereClause = "(surname LIKE '$search'";
            $whereClause .= "OR forename LIKE '$search')";
            $query->where($whereClause);
        }

        $orderBy = $this->state->get('list.ordering', 'surname');
        $orderDir = $this->state->get('list.direction', 'ASC');
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

        $orderBy = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'surname');
        $this->setState('list.ordering', $orderBy);

        $orderDir = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $orderDir);

        $filter = $app->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
        $this->setState('filter.search', $filter);

        $limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');
        $this->setState('limit', $limit);

        parent::populateState($orderBy, $orderDir);
    }
}
