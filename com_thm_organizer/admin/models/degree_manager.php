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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

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
        THM_OrganizerHelperComponent::addActions($this);
    }

    /**
     * Method to select all degree rows from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Perform the database request
        $query = $this->_db->getQuery(true);
        $select = 'id, name, abbreviation, lsfDegree, ';
        $parts = array("'index.php?option=com_thm_organizer&view=degree_edit&id='", "id");
        $select .= $query->concatenate($parts) . " AS link";
        $query->select($select);
        $query->from('#__thm_organizer_degrees');
        $columns = array('name', 'abbreviation', 'lsfDegree');
        $this->setSearchFilter($query, $columns);
        $idColumns = array('filter.name', 'filter.abbreviation', 'filter.lsfDegree');
        $this->setIDFilter($query, 'id', $columns);
        $this->setOrdering($query);
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
            if ($this->actions->{'core.edit'} OR $this->actions->{'core.delete'})
            {
                $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            }
            if ($this->actions->{'core.edit'})
            {
                $name = JHtml::_('link', $item->link, $item->name);
                $abbreviation = JHtml::_('link', $item->link, $item->abbreviation);
                $lsfDegree = JHtml::_('link', $item->link, $item->lsfDegree);
            }
            else
            {
                $name = $item->name;
                $abbreviation = $item->abbreviation;
                $lsfDegree = $item->lsfDegree;
            }
            $return[$index]['name'] = $name;
            $return[$index]['abbreviation'] = $abbreviation;
            $return[$index]['lsfDegree'] = $lsfDegree;
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
        $headers['name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction, $ordering);
        $headers['abbreviation'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_ABBREVIATION', 'abbreviation', $direction, $ordering);
        $headers['lsfDegree'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_LSF_DEGREE', 'lsfDegree', $direction, $ordering);

        return $headers;
    }
}
