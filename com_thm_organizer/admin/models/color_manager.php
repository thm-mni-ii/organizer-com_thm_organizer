<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelColor_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class THM_OrganizerModelColors for component com_thm_organizer
 * Class provides methods to deal with colors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelColor_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    /**
     * Method to get all colors from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $select = 'id, name, color, ';
        $parts = array("'index.php?option=com_thm_organizer&view=color_edit&id='", "id");
        $select .= $query->concatenate($parts, "") . " AS link";
        $query->select($select);
        $query->from('#__thm_organizer_colors');

        $columns = array('name', 'color');
        $this->setSearchFilter($query, $columns);
        $this->setValueFilters($query, $columns);

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
            }
            else
            {
                $name = $item->name;
            }
            $return[$index]['name'] = $name;
            $return[$index]['color'] = THM_OrganizerHelperComponent::getColorField($item->color, $item->color);
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
        $headers['color'] = JText::_('COM_THM_ORGANIZER_COLOR');

        return $headers;
    }
}
