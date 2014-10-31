<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
jimport('thm_core.helpers.corehelper');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class provides functions for displaying a list of pools
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'ASC';

    /**
     * constructor
     *
     * @param   array  $config  configurations parameter
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('name', 'field');
        }
        parent::__construct($config);
    }

    /**
     * Method to select the tree of a given major
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $shortTag = THM_CoreHelper::getLanguageShortTag();
        $select = "DISTINCT p.id, name_$shortTag AS name, field, color, ";
        $parts = array("'index.php?option=com_thm_organizer&view=pool_edit&id='","p.id");
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
        $this->setProgramIDFilter($query);

        $this->setOrdering($query);

        return $query;
    }

    private function setProgramIDFilter(&$query)
    {
        $programID = $this->state->get('filter.programID', '');

        if (empty($programID))
        {
            return;
        }

        $ranges = THM_OrganizerHelperMapping::getRanges('program', $programID);
        if (empty($ranges))
        {
            return;
        }

        // No program associations
        if ($programID == '-1')
        {
            $query->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');

            $conditions = array();
            foreach ($ranges as $range)
            {
                $conditions[] = "( lft NOT BETWEEN '{$range['lft']}' AND '{$range['rgt']}' )";
                $conditions[] = "( rgt NOT BETWEEN '{$range['lft']}' AND '{$range['rgt']}' )";
            }
            $where = implode(' AND ', $conditions);
            $query->where("( $where )");
            return;
        }

        // Specific program association
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
        $query->where("lft > '{$ranges[0]['lft']}'");
        $query->where("rgt < '{$ranges[0]['rgt']}'");
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return  array  an array of objects fulfilling the request criteria
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
            $return[$index]['name'] = JHtml::_('link', $item->link, $item->name);
            $programName = THM_OrganizerHelperMapping::getProgramName('pool', $item->id);
            $return[$index]['programID'] = JHtml::_('link', $item->link, $programName);
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
