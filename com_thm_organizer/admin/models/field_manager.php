<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelField_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class THM_OrganizerModelColors for component com_thm_organizer
 * Class provides methods to deal with colors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelField_Manager extends THM_CoreModelList
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
            $config['filter_fields'] = array('f.field','f.gpuntisID','c.name');
        }
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
        $query->select("f.id, f.gpuntisID, f.field, c.name, c.color");
        $query->from('#__thm_organizer_fields AS f');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $search = '%' . $this->_db->escape($this->state->get('filter.search'), true) . '%';
        if ($search != '%%')
        {
            $query->where("field LIKE '$search' OR gpuntisID LIKE '$search'");
        }

        $ordering = $this->_db->escape($this->state->get('list.ordering', $this->defaultOrdering));
        $direction = $this->_db->escape($this->state->get('list.direction', $this->defaultDirection));
        $query->order("$ordering $direction");

        return $query;
    }

    /**
     * Function to feed the data in the table body correctly to the list view
     *
     * @return array consisting of items in the body
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
            $return[$index][0] = JHtml::_('grid.id', $index, $item->id);
            $return[$index][1] = JHtml::_('link', $item->link, $item->ectitle);
            $globalTip = JTEXT::_('COM_THM_ORGANIZER_CATEGORY_MANAGER_TOGGLE_GLOBAL');
            $return[$index][2] = $this->getToggle($item->id, $item->global, 'category', $globalTip, 'global');
            $reservesTip = JTEXT::_('COM_THM_ORGANIZER_CATEGORY_MANAGER_TOGGLE_RESERVES');
            $return[$index][3] = $this->getToggle($item->id, $item->reserves, 'category', $reservesTip, 'reserves');
            $return[$index][4] = JHtml::_('link', $item->link, $item->cctitle);
            $index++;
        }
        return $return;
    }

}
