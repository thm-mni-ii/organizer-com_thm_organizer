<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerSubject_Selection
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class THM_OrganizerModelSubject_Selection for component com_thm_organizer
 * Class provides methods to deal adding subjects
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
*/
class THM_OrganizerModelSubject_Selection extends THM_CoreModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';


    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $select = 'id, name_de, externalID, ';
        $parts = array("'index.php?option=com_thm_organizer&view=pool_edit&id='", "0");
        $select .= $query->concatenate($parts, "") . " AS link";
        $query->select($select);
        $query->from('#__thm_organizer_subjects');
        $query->order('name_de ASC');

        $columns = array('id', 'name_de', 'externalID');
        $this->setSearchFilter($query, $columns);
        $this->setValueFilters($query, $columns);

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
            $return[$index]['name'] = $item->name_de;
            $return[$index]['modulId'] = $item->externalID;
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
        $headers['name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction, $ordering);
        $headers['externalID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_EXTERNAL_ID', 'externalID', $direction, $ordering);

        return $headers;
    }
}
