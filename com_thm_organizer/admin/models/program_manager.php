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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class retrieves information for a filtered set of (degree) programs.
 */
class THM_OrganizerModelProgram_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param array $config Configuration  (default: array)
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['dp.name', 'abbreviation', 'version', 'departmentID'];
        }

        parent::__construct($config);
    }

    /**
     * Method to determine all majors
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $allowedDepartments = THM_OrganizerHelperAccess::getAccessibleDepartments('document');
        $shortTag           = THM_OrganizerHelperLanguage::getShortTag();

        $query  = $this->_db->getQuery(true);
        $select = "dp.name_$shortTag AS name, version, ";
        $select .= "dp.id AS id, d.abbreviation AS abbreviation, dpt.short_name_$shortTag AS departmentname, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=program_edit&id='", 'dp.id'];
        $select .= $query->concatenate($parts, '') . ' AS link ';
        $query->select($select);

        $query->from('#__thm_organizer_programs AS dp');
        $query->leftJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->leftJoin('#__thm_organizer_fields AS f ON dp.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_departments AS dpt ON dp.departmentID = dpt.id');
        $query->where('(dp.departmentID IN (' . implode(',', $allowedDepartments) . ') OR dp.departmentID IS NULL)');

        $searchColumns = ['dp.name_de', 'dp.name_en', 'version', 'd.name', 'description_de', 'description_en'];
        $this->setSearchFilter($query, $searchColumns);
        $this->setValueFilters($query, ['degreeID', 'version', 'departmentID']);
        $this->setLocalizedFilters($query, ['dp.name']);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to create iterate table data
     *
     * @return array  an array of arrays with preformatted teacher data
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
            $return[$index]['checkbox']     = HTML::_('grid.id', $index, $item->id);
            $return[$index]['dp.name']      = HTML::_('link', $item->link, $item->name);
            $return[$index]['degreeID']     = HTML::_('link', $item->link, $item->abbreviation);
            $return[$index]['version']      = HTML::_('link', $item->link, $item->version);
            $return[$index]['departmentID'] = HTML::_('link', $item->link, $item->departmentname);
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

        $headers['checkbox']     = '';
        $headers['dp.name']      = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['degreeID']     = HTML::sort('DEGREE', 'abbreviation', $direction, $ordering);
        $headers['version']      = HTML::sort('VERSION', 'version', $direction, $ordering);
        $headers['departmentID'] = HTML::sort('DEPARTMENT', 'departmentID', $direction, $ordering);

        return $headers;
    }
}
