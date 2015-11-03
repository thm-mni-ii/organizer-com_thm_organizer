<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool_Selection
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
jimport('thm_core.helpers.corehelper');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class THM_OrganizerModelPool_Selection for component com_thm_organizer
 * Class provides methods to deal with adding pools
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
*/
class THM_OrganizerModelPool_Selection extends THM_CoreModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';


    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $shortTag = THM_CoreHelper::getLanguageShortTag();
        $select = "DISTINCT p.id, name_$shortTag AS name, field, color, ";
        $parts = array("'index.php?option=com_thm_organizer&view=pool_selection&id='","p.id");
        $select .= $query->concatenate($parts, "") . "AS link ";
        $query->select($select);

        $query->from('#__thm_organizer_pools AS p');
        $query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $searchColumns = array('name_de', 'short_name_de', 'abbreviation_de', 'description_de',
                                'name_en', 'short_name_en', 'abbreviation_en', 'description_en'
                            );
        $this->setSearchFilter($query, $searchColumns);
        $this->setLocalizedFilters($query, array('name'));
        $this->setValueFilters($query, array('fieldID'));

        $programID = $this->state->get('filter.programID', '');
        THM_OrganizerHelperMapping::setResourceIDFilter($query, $programID, 'program', 'pool');

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
            $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['name'] = $item->name;
            $programName = THM_OrganizerHelperMapping::getProgramName('pool', $item->id);
            $return[$index]['programID'] = $programName;
            if (!empty($item->field))
            {
                if (!empty($item->color))
                {
                    $return[$index]['fieldID'] = THM_OrganizerHelperComponent::getColorField($item->field, $item->color);
                }
                else
                {
                    $return[$index]['fieldID'] = $item->field;
                }
            }
            else
            {
                $return[$index]['fieldID'] = '';
            }
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
        $headers['checkbox'] = '';
        $headers['name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction, $ordering);
        $headers['programID'] = JText::_('COM_THM_ORGANIZER_PROGRAM');
        $headers['fieldID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_FIELD', 'field', $direction, $ordering);

        return $headers;
    }
}