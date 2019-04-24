<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use \THM_OrganizerHelperHTML as HTML;

require_once 'list.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/mapping.php';

/**
 * Class retrieves information for a filtered set of (subject) pools.
 */
class THM_OrganizerModelPool_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    /**
     * constructor
     *
     * @param array $config configurations parameter
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['name', 'field'];
        }

        parent::__construct($config);
    }

    /**
     * Method to select the tree of a given major
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $allowedDepartments = THM_OrganizerHelperAccess::getAccessibleDepartments('document');
        $query              = $this->_db->getQuery(true);

        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $select   = "DISTINCT p.id, p.name_$shortTag AS name, field_$shortTag AS field, color, ";
        $parts    = ["'index.php?option=com_thm_organizer&view=pool_edit&id='", 'p.id'];
        $select   .= $query->concatenate($parts, '') . 'AS link ';
        $query->select($select);

        $query->from('#__thm_organizer_pools AS p');
        $query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where('(p.departmentID IN (' . implode(',', $allowedDepartments) . ') OR p.departmentID IS NULL)');

        $searchColumns = [
            'p.name_de',
            'short_name_de',
            'abbreviation_de',
            'description_de',
            'p.name_en',
            'short_name_en',
            'abbreviation_en',
            'description_en'
        ];
        $this->setSearchFilter($query, $searchColumns);
        $this->setLocalizedFilters($query, ['p.name']);
        $this->setValueFilters($query, ['fieldID']);

        $programID = $this->state->get('filter.programID', '');
        THM_OrganizerHelperMapping::setResourceIDFilter($query, $programID, 'program', 'pool');

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return array  an array of objects fulfilling the request criteria
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
            $return[$index]['name']      = HTML::_('link', $item->link, $item->name);
            $programName                 = THM_OrganizerHelperMapping::getProgramName('pool', $item->id);
            $return[$index]['programID'] = HTML::_('link', $item->link, $programName);
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
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering  = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);
        $headers   = [];

        $headers['checkbox']  = '';
        $headers['name']      = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['programID'] = \JText::_('COM_THM_ORGANIZER_PROGRAM');
        $headers['fieldID']   = HTML::sort('FIELD', 'field', $direction, $ordering);

        return $headers;
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

        return (int)THM_OrganizerHelperComponent::executeQuery('loadResult');
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

        $filter = THM_OrganizerHelperComponent::getApplication()->getUserStateFromRequest(
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
}
