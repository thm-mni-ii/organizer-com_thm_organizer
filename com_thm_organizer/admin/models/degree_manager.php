<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelDegrees
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class THM_OrganizerModelDegrees for component com_thm_organizer
 * Class provides methods to deal with degrees
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelDegree_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    /**
     * Constructor to set up the configuration and call the parent constructor
     *
     * @param array $config the configuration  (default: array)
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['name', 'abbreviation', 'code'];
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
        $query  = $this->_db->getQuery(true);
        $select = "id, name, abbreviation, code, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=degree_edit&id='", "id"];
        $select .= $query->concatenate($parts) . " AS link";
        $query->select($select);
        $query->from('#__thm_organizer_degrees');
        $columns = ['name', 'abbreviation', 'code'];
        $this->setSearchFilter($query, $columns);
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
        $items  = parent::getItems();
        $return = [];

        if (empty($items)) {
            return $return;
        }

        $index = 0;

        foreach ($items as $item) {
            $return[$index]                 = [];
            $return[$index]['checkbox']     = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['name']         = JHtml::_('link', $item->link, $item->name);
            $return[$index]['abbreviation'] = JHtml::_('link', $item->link, $item->abbreviation);
            $return[$index]['code']         = JHtml::_('link', $item->link, $item->code);
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
        $ordering                = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction               = $this->state->get('list.direction', $this->defaultDirection);
        $headers                 = [];
        $headers['checkbox']     = '';
        $headers['name']         = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction,
            $ordering);
        $headers['abbreviation'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_ABBREVIATION', 'abbreviation',
            $direction, $ordering);
        $headers['code']         = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DEGREE_CODE', 'code', $direction,
            $ordering);

        return $headers;
    }
}
