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

require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';

/**
 * Class retrieves the data regarding a filtered set of buildings.
 */
class THM_OrganizerModelBuilding_Manager extends THM_OrganizerModelList
{
    const OWNED = 1;
    const RENTED = 2;
    const USED = 3;

    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    /**
     * Method to get all buildings from the database
     *
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $select = 'b.id, b.name, propertyType, campusID, c1.parentID, b.address, c1.city, c2.city as parentCity, ';
        $parts  = ["'index.php?option=com_thm_organizer&view=building_edit&id='", 'b.id'];
        $select .= $query->concatenate($parts, '') . ' AS link';
        $query->select($select);
        $query->from('#__thm_organizer_buildings as b');
        $query->innerJoin('#__thm_organizer_campuses as c1 on b.campusID = c1.id');
        $query->leftJoin('#__thm_organizer_campuses as c2 on c1.parentID = c2.id');

        $this->setSearchFilter($query, ['b.name', 'b.address', 'c1.city', 'c2.city']);
        $this->setValueFilters($query, ['propertyType']);
        $this->setCampusFilter($query);
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
            $return[$index]             = [];
            $return[$index]['checkbox'] = HTML::_('grid.id', $index, $item->id);
            $return[$index]['name']     = HTML::_('link', $item->link, $item->name);
            $campusName                 = THM_OrganizerHelperCampuses::getName($item->campusID);
            $return[$index]['campusID'] = HTML::_('link', $item->link, $campusName);

            switch ($item->propertyType) {
                case self::OWNED:
                    $propertyType = JText::_('COM_THM_ORGANIZER_OWNED');
                    break;

                case self::RENTED:
                    $propertyType = JText::_('COM_THM_ORGANIZER_RENTED');
                    break;

                case self::USED:
                    $propertyType = JText::_('COM_THM_ORGANIZER_USED');
                    break;

                default:
                    $propertyType = JText::_('COM_THM_ORGANIZER_UNKNOWN');
                    break;
            }

            $return[$index]['propertyType'] = HTML::_('link', $item->link, $propertyType);
            $return[$index]['address']      = HTML::_('link', $item->link, $item->address);
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
        $direction               = $this->state->get('list.direction', $this->defaultDirection);
        $headers                 = [];
        $headers['checkbox']     = '';
        $headers['name']         = HTML::sort('NAME', 'name', $direction, 'name');
        $headers['campusID']     = JText::_('COM_THM_ORGANIZER_CAMPUS');
        $headers['propertyType'] = JText::_('COM_THM_ORGANIZER_PROPERTY_TYPE');
        $headers['address']      = JText::_('COM_THM_ORGANIZER_ADDRESS');

        return $headers;
    }

    /**
     * Provides a default method for setting filters for non-unique values
     *
     * @param object &$query the query object
     *
     * @return void
     */
    private function setCampusFilter(&$query)
    {
        $value = $this->state->get('filter.campusID', '');

        if ($value === '') {
            return;
        }

        /**
         * Special value reserved for empty filtering. Since an empty is dependent upon the column default, we must
         * check against multiple 'empty' values. Here we check against empty string and null. Should this need to
         * be extended we could maybe add a parameter for it later.
         */
        if ($value == '-1') {
            $query->where('campusID IS NULL');

            return;
        }

        $query->where("(b.campusID = '$value' OR c1.parentID = '$value')");
    }
}
