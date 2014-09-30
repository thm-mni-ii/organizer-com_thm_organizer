<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelField_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class THM_OrganizerModelColors for component com_thm_organizer
 * Class provides methods to deal with colors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelField_Manager extends JModelList
{
    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Method to get all colors from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Create the query
        $query = $this->_db->getQuery(true);
        $query->select("f.id, gpuntisID, field, name, color");
        $query->from('#__thm_organizer_fields AS f');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $search = '%' . $this->_db->getEscaped($this->state->get('filter.search'), true) . '%';
        if ($search != '%%')
        {
            $query->where("field LIKE '$search' OR gpuntisID LIKE '$search'");
        }

        $query->order("{$this->state->get('list.ordering', 'field')} {$this->state->get('list.direction', 'ASC')}");

        return $query;
    }

    /**
     * Method to get the populate state
     *
     * @param   string  $ordering   the property by which the results should be ordered
     * @param   string  $direction  the direction in which results should be ordered
     *
     * @return  void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication('administrator');

        $ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'field');
        $this->setState('list.ordering', $ordering);

        $direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $direction);

        $search = $app->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
        $this->setState('filter.search', $search);

        $limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');
        $this->setState('list.limit', $limit);

        parent::populateState($ordering, $direction);
    }
}
