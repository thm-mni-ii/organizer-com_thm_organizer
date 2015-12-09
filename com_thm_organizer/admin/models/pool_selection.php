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
        $select .= $query->concatenate($parts, "") . " AS link ";
        $query->select($select);

        $query->from('#__thm_organizer_pools AS p');
        $query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->leftJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');

        $searchColumns = array('name_de', 'short_name_de', 'abbreviation_de', 'description_de',
                                'name_en', 'short_name_en', 'abbreviation_en', 'description_en'
                            );
        $this->setSearchFilter($query, $searchColumns);
        $this->setLocalizedFilters($query, array('name'));
        $this->setValueFilters($query, array('fieldID'));

        // Only pools
        $query->where('m.programID IS NULL AND m.subjectID IS NULL');

        $programID = $this->state->get('filter.programID', '');

        // Program filter selection made
        if (!empty($programID))
        {
            // Pools unassociated with programs => no mappings
            if ($programID == -1)
            {
                $query->where("m.id IS NULL");
            }
            else
            {
                $this->setProgramFilter($query, $programID);
            }
        }

        // Mapping filters are irrelevant without mappings :)
        if ($programID != -1)
        {
            $this->setMappingFilters($query);
        }

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Sets the program id filter for a query. Used in pool manager and subject manager.
     *
     * @param   object  &$query            the query object
     * @param   int     $programID        the id of the resource from the filter
     *
     * @return  void  sets query object variables
     */
    public function setProgramFilter(&$query, $programID)
    {
        if (!is_numeric($programID))
        {
            return;
        }

        $ranges = THM_OrganizerHelperMapping::getResourceRanges('program', $programID);
        if (empty($ranges))
        {
            return;
        }

        // Specific association
        $query->where("m.lft > '{$ranges[0]['lft']}'");
        $query->where("m.rgt < '{$ranges[0]['rgt']}'");
    }

    /**
     * Sets exclusions for parent and child pools based on mapping values.
     *
     * @param   object  &$query  the query object
     */
    private function setMappingFilters(&$query)
    {
        $type = $this->state->{'list.type'};
        $resourceID = $this->state->{'list.id'};

        $invalid = (($type != 'program' AND $type != 'pool') OR $resourceID == 0);
        if ($invalid)
        {
            return;
        }

        $boundarySets = THM_OrganizerHelperMapping::getBoundaries($type, $resourceID);//echo "<pre>" . print_r($boundarySets, true) . "</pre>";
        if (empty($boundarySets))
        {
            return;
        }

        $newQuery = $this->_db->getQuery(true);
        $newQuery->select('poolID')->from('#__thm_organizer_mappings');
        $newQuery->where('poolID IS NOT NULL');
        foreach ($boundarySets as $boundarySet)
        {
            $newQuery->where("(lft BETWEEN '{$boundarySet['lft']}' AND '{$boundarySet['rgt']}')");
            $query->where("NOT (m.lft < '{$boundarySet['lft']}' AND m.rgt > '{$boundarySet['rgt']}')");
        }
        $query->where("p.id NOT IN (" . (string) $newQuery . ")");
    }

    /**
     * Overwrites the JModelList populateState function
     *
     * @param   string  $ordering   the column by which the table is should be ordered
     * @param   string  $direction  the direction in which this column should be ordered
     *
     * @return  void  sets object state variables
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $input = JFactory::getApplication()->input;
        $list = JFactory::getApplication()->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');

        $postType = $input->get('type', '');
        $type = empty($list['type'])? $postType : $list['type'];
        $this->setState('list.type', $type);

        $postID = $input->get('id', 0);
        $resourceID = empty($list['type'])? $postID : $list['id'];
        $this->setState('list.id', $resourceID);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        $data = parent::loadFormData();
        $data->list['type'] = $this->state->{'list.type'};
        $data->list['id'] = $this->state->{'list.id'};
        return $data;
    }

    /**
     * Method to get the total number of items for the data set.
     *
     * @param   string  $idColumn  the main id column of the list query
     *
     * @return  integer  The total number of items available in the data set.
     */
    public function getTotal()
    {
        return parent::getTotal('p.id');
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
