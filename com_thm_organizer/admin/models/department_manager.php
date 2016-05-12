<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelField_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class THM_OrganizerModelColors for component com_thm_organizer
 * Class provides methods to deal with colors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelDepartment_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'short_name';

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
            $config['filter_fields'] = array('short_name','name');
        }

        parent::__construct($config);
    }

    /**
     * Method to get all colors from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        // Create the query
        $query = $this->_db->getQuery(true);
        $select = "d.id, d.short_name_$shortTag AS short_name, d.name_$shortTag AS name, a.rules, ";
        $parts = array("'index.php?option=com_thm_organizer&view=department_edit&id='","d.id");
        $select .= $query->concatenate($parts, "") . "AS link ";
        $query->select($select);
        $query->from('#__thm_organizer_departments AS d');
        $query->innerJoin('#__assets AS a ON d.asset_id = a.id');

        $this->setSearchFilter($query, array('short_name_de', 'name_de','short_name_en', 'name_en'));
        $this->setLocalizedFilters($query, array('short_name', 'name'));

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
            if ($this->actions->{'core.admin'})
            {
                $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
                $return[$index]['short_name'] = JHtml::_('link', $item->link, $item->short_name);
                $return[$index]['name'] = JHtml::_('link', $item->link, $item->name);
            }
            else
            {
                $return[$index]['short_name'] = $item->short_name;
                $return[$index]['name'] = $item->name;
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
        if ($this->actions->{'core.admin'})
        {
            $headers['checkbox'] = '';
        }

        $headers['short_name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_SHORT_NAME', 'f.field', $direction, $ordering);
        $headers['name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'c.name', $direction, $ordering);

        return $headers;
    }
}
