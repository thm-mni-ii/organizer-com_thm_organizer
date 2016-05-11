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
jimport('thm_core.list.model');
jimport('thm_core.helpers.corehelper');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class THM_OrganizerModelColors for component com_thm_organizer
 * Class provides methods to deal with colors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelField_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'field';

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
            $config['filter_fields'] = array('field','name');
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
        $shortTag = THM_CoreHelper::getLanguageShortTag();

        // Create the query
        $query = $this->_db->getQuery(true);
        $select = "f.id, f.field_$shortTag AS field, c.name_$shortTag AS name, c.color, ";
        $parts = array("'index.php?option=com_thm_organizer&view=field_edit&id='","f.id");
        $select .= $query->concatenate($parts, "") . "AS link ";
        $query->select($select);
        $query->from('#__thm_organizer_fields AS f');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $this->setSearchFilter($query, array('field_de', 'field_en', 'gpuntisID', 'color'));
        $this->setValueFilters($query, array('colorID'));
        $this->setLocalizedFilters($query, array('field'));

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
                $field = JHtml::_('link', $item->link, $item->field);
            }
            else
            {
                $field = $item->field;
            }

            $return[$index]['field'] = $field;
            $colorOutput = THM_OrganizerHelperComponent::getColorField($item->name, $item->color);
            $return[$index]['colorID'] = $colorOutput;
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

        $headers['field'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'f.field', $direction, $ordering);
        $headers['colorID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_COLOR', 'c.name', $direction, $ordering);

        return $headers;
    }
}
