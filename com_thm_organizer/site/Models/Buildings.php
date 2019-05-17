<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

/**
 * Class retrieves the data regarding a filtered set of buildings.
 */
class Buildings extends ListModel
{
    /**
     * Method to get all buildings from the database
     *
     * @return \JDatabaseQuery
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
