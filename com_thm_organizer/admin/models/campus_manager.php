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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class retrieves information for a filtered set of campuses.
 */
class THM_OrganizerModelCampus_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    /**
     * Method to get all campuses from the database
     *
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $query    = $this->_db->getQuery(true);

        $select = "c1.id, c1.name_$shortTag as name, c2.id as parentID, c2.name_$shortTag as parentName, ";
        $select .= 'c1.address, c1.city, c1.zipCode, c1.location, ';
        $select .= 'c2.address as parentAddress, c2.city as parentCity, c2.zipCode as parentZIPCode, ';
        $select .= "g1.id as gridID, g1.name_$shortTag as gridName, ";
        $select .= "g2.id as parentGridID, g2.name_$shortTag as parentGridName, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=campus_edit&id='", 'c1.id'];
        $select .= $query->concatenate($parts, '') . ' AS link';
        $query->select($select);
        $query->from('#__thm_organizer_campuses as c1');
        $query->leftJoin('#__thm_organizer_grids as g1 on c1.gridID = g1.id');
        $query->leftJoin('#__thm_organizer_campuses as c2 on c1.parentID = c2.id');
        $query->leftJoin('#__thm_organizer_grids as g2 on c2.gridID = g2.id');

        $searchColumns = [
            'c1.name_de',
            'c1.name_en',
            'c1.city',
            'c1.address',
            'c1.zipCode',
            'c2.city',
            'c2.address',
            'c2.zipCode'
        ];
        $this->setSearchFilter($query, $searchColumns);
        $this->setCityFilter($query);
        $this->setGridFilter($query);

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

        foreach ($items as $item) {
            if (empty($item->parentID)) {
                $index = $item->name;
                $name  = $item->name;
            } else {
                $index = "{$item->parentName}-{$item->name}";
                $name  = "|&nbsp;&nbsp;-&nbsp;{$item->name}";
            }

            $return[$index]             = [];
            $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['name']     = JHtml::_('link', $item->link, $name);

            $address    = '';
            $ownAddress = (!empty($item->address) or !empty($item->city) or !empty($item->zipCode));

            if ($ownAddress) {
                $addressParts   = [];
                $addressParts[] = empty($item->address) ? empty($item->parentAddress) ? '' : $item->parentAddress : $item->address;
                $addressParts[] = empty($item->city) ? empty($item->parentCity) ? '' : $item->parentCity : $item->city;
                $addressParts[] = empty($item->zipCode) ? empty($item->parentZIPCode) ? '' : $item->parentZIPCode : $item->zipCode;
                $address        = implode(' ', $addressParts);
            }

            $return[$index]['address']  = $address;
            $return[$index]['location'] = THM_OrganizerHelperCampuses::getLocation($item->id);

            if (!empty($item->gridName)) {
                $gridName = $item->gridName;
            } elseif (!empty($item->parentGridName)) {
                $gridName = $item->parentGridName;
            } else {
                $gridName = JText::_('JNONE');
            }
            $return[$index]['gridID'] = $gridName;
        }

        asort($return);

        return $return;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $headers             = [];
        $headers['checkbox'] = '';
        $headers['name']     = JText::_('COM_THM_ORGANIZER_NAME');
        $headers['address']  = JText::_('COM_THM_ORGANIZER_ADDRESS');
        $headers['location'] = JText::_('COM_THM_ORGANIZER_LOCATION');
        $headers['gridID']   = JText::_('COM_THM_ORGANIZER_GRID');

        return $headers;
    }

    /**
     * Filters according to the selected city.
     *
     * @param object &$query the query object
     *
     * @return void
     */
    private function setCityFilter(&$query)
    {
        $value = $this->state->get('list.city', '');

        if ($value === '') {
            return;
        }

        /**
         * Special value reserved for empty filtering. Since an empty is dependent upon the column default, we must
         * check against multiple 'empty' values. Here we check against empty string and null. Should this need to
         * be extended we could maybe add a parameter for it later.
         */
        if ($value == '-1') {
            $query->where("city = ''");

            return;
        }

        $query->where("(c1.city = '$value' OR (c1.city = '' AND c2.city = '$value'))");
    }

    /**
     * Filters according to the selected grid.
     *
     * @param object &$query the query object
     *
     * @return void
     */
    private function setGridFilter(&$query)
    {
        $value = $this->state->get('filter.gridID', '');

        if ($value === '') {
            return;
        }

        /**
         * Special value reserved for empty filtering. Since an empty is dependent upon the column default, we must
         * check against multiple 'empty' values. Here we check against empty string and null. Should this need to
         * be extended we could maybe add a parameter for it later.
         */
        if ($value == '-1') {
            $query->where('g1.id IS NULL and g2.id IS NULL');

            return;
        }

        $query->where("(g1.id = '$value' OR (g1.id IS NULL AND g2.id = '$value'))");
    }
}
