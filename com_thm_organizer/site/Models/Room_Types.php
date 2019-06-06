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

use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of room types.
 */
class Room_Types extends ListModel
{
    /**
     * Method to get all room types from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = Languages::getShortTag();

        $query = $this->_db->getQuery(true);
        $query->select("DISTINCT t.id, t.name_$shortTag AS name, t.min_capacity, t.max_capacity, t.untisID")
            ->select('count(r.typeID) AS roomCount')
            ->from('#__thm_organizer_room_types AS t')
            ->leftJoin('#__thm_organizer_rooms AS r on r.typeID = t.id')
            ->group('t.id');

        $this->setSearchFilter($query, ['untisID', 'name_de', 'name_en', 'min_capacity', 'max_capacity']);
        $this->setOrdering($query);

        return $query;
    }
}
