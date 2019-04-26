<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once 'list.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/mapping.php';

use THM_OrganizerHelperHTML as HTML;

/**
 * Class retrieves information for a filtered set of (subject) pools. Modal view.
 */
class THM_OrganizerModelPool_Selection extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    /**
     * Method to get a \JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return \JDatabaseQuery   A \JDatabaseQuery object to retrieve the data set.
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $shortTag = Languages::getShortTag();
        $select   = "DISTINCT p.id, p.name_$shortTag AS name, field_$shortTag as field, color, ";
        $parts    = ["'index.php?option=com_thm_organizer&view=pool_selection&id='", 'p.id'];
        $select   .= $query->concatenate($parts, '') . ' AS link ';
        $query->select($select);

        $query->from('#__thm_organizer_pools AS p');
        $query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->leftJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');

        $searchColumns = [
            'name_de',
            'short_name_de',
            'abbreviation_de',
            'description_de',
            'name_en',
            'short_name_en',
            'abbreviation_en',
            'description_en'
        ];
        $this->setSearchFilter($query, $searchColumns);
        $this->setLocalizedFilters($query, ['p.name']);
        $this->setValueFilters($query, ['fieldID']);

        // Only pools
        $query->where('m.programID IS NULL AND m.subjectID IS NULL');

        $programID = $this->state->get('filter.programID', '');

        // Program filter selection made
        if (!empty($programID)) {
            // Pools unassociated with programs => no mappings
            if ($programID == -1) {
                $query->where('m.id IS NULL');
            } else {
                $this->setProgramFilter($query, $programID);
            }
        }

        // Mapping filters are irrelevant without mappings :)
        if ($programID != -1) {
            $this->setMappingFilters($query);
        }

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering  = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);

        $headers              = [];
        $headers['checkbox']  = '';
        $headers['name']      = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['programID'] = Languages::_('THM_ORGANIZER_PROGRAM');
        $headers['fieldID']   = HTML::sort('FIELD', 'field', $direction, $ordering);

        return $headers;
    }

    /**
     * Function to feed the data in the table body correctly to the list view
     *
     * @return array consisting of items in the body
     */
    public function getItems()
    {
        $items  = parent::getItems();
        $return = [];
        if (empty($items)) {
            return $return;
        }

        $index = 0;
        foreach ($items as $item) {
            $return[$index]              = [];
            $return[$index]['checkbox']  = HTML::_('grid.id', $index, $item->id);
            $return[$index]['name']      = $item->name;
            $programName                 = THM_OrganizerHelperMapping::getProgramName('pool', $item->id);
            $return[$index]['programID'] = $programName;
            if (!empty($item->field)) {
                if (!empty($item->color)) {
                    $return[$index]['fieldID'] = HTML::colorField(
                        $item->field,
                        $item->color
                    );
                } else {
                    $return[$index]['fieldID'] = $item->field;
                }
            } else {
                $return[$index]['fieldID'] = '';
            }

            $index++;
        }

        return $return;
    }

    /**
     * Method to get the total number of items for the data set.
     *
     * @param string $idColumn not used
     *
     * @return integer  The total number of items available in the data set.
     */
    public function getTotal($idColumn = null)
    {
        $query = $this->getListQuery();
        $query->clear('select');
        $query->clear('order');
        $query->select('COUNT(DISTINCT p.id)');
        $dbo = \JFactory::getDbo();
        $dbo->setQuery($query);

        return (int)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed  The data for the form.
     */
    protected function loadFormData()
    {
        $data               = parent::loadFormData();
        $data->list['type'] = $this->state->{'list.type'};
        $data->list['id']   = $this->state->{'list.id'};

        return $data;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param string $ordering  An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     *
     * @return void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $input = OrganizerHelper::getInput();
        $list  = OrganizerHelper::getApplication()->getUserStateFromRequest($this->context . '.list',
            'list', [], 'array');

        $postType = $input->get('type', '');
        $type     = empty($list['type']) ? $postType : $list['type'];
        $this->setState('list.type', $type);

        $postID     = $input->get('id', 0);
        $resourceID = empty($list['type']) ? $postID : $list['id'];
        $this->setState('list.id', $resourceID);

        $filter = OrganizerHelper::getApplication()->getUserStateFromRequest(
            $this->context . '.filter',
            'filter',
            [],
            'array'
        );

        if (!empty($filter['name'])) {
            $this->setState('filter.p.name', $filter['name']);
        } else {
            $pname = 'filter.p.name';
            unset($this->state->$pname);
        }
    }

    /**
     * Sets exclusions for parent and child pools based on mapping values.
     *
     * @param object &$query the query object
     */
    private function setMappingFilters(&$query)
    {
        $type       = $this->state->{'list.type'};
        $resourceID = $this->state->{'list.id'};

        $invalid = (($type != 'program' and $type != 'pool') or $resourceID == 0);
        if ($invalid) {
            return;
        }

        $boundarySets = THM_OrganizerHelperMapping::getBoundaries($type, $resourceID);
        if (empty($boundarySets)) {
            return;
        }

        $newQuery = $this->_db->getQuery(true);
        $newQuery->select('poolID')->from('#__thm_organizer_mappings');
        $newQuery->where('poolID IS NOT NULL');
        foreach ($boundarySets as $boundarySet) {
            $newQuery->where("(lft BETWEEN '{$boundarySet['lft']}' AND '{$boundarySet['rgt']}')");
            $query->where("NOT (m.lft < '{$boundarySet['lft']}' AND m.rgt > '{$boundarySet['rgt']}')");
        }

        $query->where('p.id NOT IN (' . (string)$newQuery . ')');
    }

    /**
     * Sets the program id filter for a query. Used in pool manager and subject manager.
     *
     * @param object &$query     the query object
     * @param int     $programID the id of the resource from the filter
     *
     * @return void  sets query object variables
     */
    public function setProgramFilter(&$query, $programID)
    {
        if (!is_numeric($programID)) {
            return;
        }

        $ranges = THM_OrganizerHelperMapping::getResourceRanges('program', $programID);
        if (empty($ranges)) {
            return;
        }

        // Specific association
        $query->where("m.lft > '{$ranges[0]['lft']}'");
        $query->where("m.rgt < '{$ranges[0]['rgt']}'");
    }
}
