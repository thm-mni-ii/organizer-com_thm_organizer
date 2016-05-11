<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelProgram_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
jimport('thm_core.helpers.corehelper');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class THM_OrganizerModelProgram_Manager for component com_thm_organizer
 *
 * Class provides methods to deal with majors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelProgram_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('dp.name', 'abbreviation', 'version', 'departmentID');
        }

        parent::__construct($config);
    }

    /**
     * Method to determine all majors
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = THM_CoreHelper::getLanguageShortTag();
        $query = $this->_db->getQuery(true);
        $select = "dp.name_$shortTag AS name, version, ";
        $select .= "dp.id AS id, d.abbreviation AS abbreviation, dpt.short_name_$shortTag AS departmentname, ";
        $parts = array("'index.php?option=com_thm_organizer&view=program_edit&id='","dp.id");
        $select .= $query->concatenate($parts, "") . "AS link ";
        $query->select($select);

        $query->from('#__thm_organizer_programs AS dp');
        $query->leftJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->leftJoin('#__thm_organizer_fields AS f ON dp.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_departments AS dpt ON dp.departmentID = dpt.id');

        $searchColumns = array('dp.name_de', 'dp.name_en', 'version', 'field', 'd.name', 'description_de', 'description_en');
        $this->setSearchFilter($query, $searchColumns);
        $this->setValueFilters($query, array( 'degreeID', 'version', 'departmentID'));
        $this->setLocalizedFilters($query, array('dp.name'));

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to create iterate table data
     *
     * @return  array  an array of arrays with preformatted teacher data
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
            $canEdit = THM_OrganizerHelperComponent::allowResourceManage('program', $item->id);
            if ($canEdit)
            {
                $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
                $return[$index]['dp.name'] = JHtml::_('link', $item->link, $item->name);
                $return[$index]['degreeID'] = JHtml::_('link', $item->link, $item->abbreviation);
                $return[$index]['version'] = JHtml::_('link', $item->link, $item->version);
                $return[$index]['departmentID'] = JHtml::_('link', $item->link, $item->departmentname);
            }
            else
            {
                $return[$index]['checkbox'] = '';
                $return[$index]['dp.name'] = $item->name;
                $return[$index]['degreeID'] = $item->abbreviation;
                $return[$index]['version'] = $item->version;
                $return[$index]['departmentID'] = $item->departmentname;
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
        $headers['dp.name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'dp.name', $direction, $ordering);
        $headers['degreeID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DEGREE', 'abbreviation', $direction, $ordering);
        $headers['version'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_VERSION', 'version', $direction, $ordering);
        $headers['departmentID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DEPARTMENT', 'departmentID', $direction, $ordering);

        return $headers;
    }
}
