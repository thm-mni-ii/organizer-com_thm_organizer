<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelDegrees
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');

/**
 * Class THM_OrganizerModelDegrees for component com_thm_organizer
 * Class provides methods to deal with degrees
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
*/
class THM_OrganizerModelDegree_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'ASC';

    /**
     * Constructor to set up the configuration and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('name', 'abbreviation', 'lsfDegree');
        }
        parent::__construct($config);
    }

    /**
     * Method to select all degree rows from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Get the list data
        $ordering = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);

        // Perform the database request
        $query = $this->_db->getQuery(true);
        $select = 'id, name, abbreviation, lsfDegree, ';
        $parts = array("'index.php?option=com_thm_organizer&view=degree_edit&id='", "id");
        $select .= $query->concatenate($parts) . " AS link";
        $query->select($select);
        $query->from('#__thm_organizer_degrees');
        $query->order($ordering . " " . $direction);
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
            $return[$index][1] = JHtml::_('link', $item->link, $item->name);
            $return[$index][2] = JHtml::_('link', $item->link, $item->abbreviation);
            $return[$index][3] = JHtml::_('link', $item->link, $item->lsfDegree);
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
        $headers[] = '';
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_ABBREVIATION', 'abbreviation', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_LSF_DEGREE', 'lsfDegree', $direction, $ordering);

        return $headers;
    }
}
