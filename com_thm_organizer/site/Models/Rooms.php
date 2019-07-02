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
 * Class retrieves information for a filtered set of rooms.
 */
class Rooms extends ListModel
{
    protected $defaultOrdering = 'r.name';

    /**
     * Method to get all rooms from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $tag   = Languages::getTag();
        $query = $this->_db->getQuery(true);

        $linkParts = ["'index.php?option=com_thm_organizer&view=room_edit&id='", 'r.id'];
        $query->select('r.id, r.untisID, r.name')
            ->select("t.id AS typeID, t.name_$tag AS type")
            ->select('b.id AS buildingID, b.name AS buildingName')
            ->select($query->concatenate($linkParts, '') . ' AS link')
            ->from('#__thm_organizer_rooms AS r')
            ->leftJoin('#__thm_organizer_room_types AS t ON r.typeID = t.id')
            ->leftJoin('#__thm_organizer_buildings AS b ON b.id = r.buildingID');

        $this->setSearchFilter($query, ['r.name', 'b.name', 't.name_de', 't.name_en']);
        $this->setValueFilters($query, ['name', 'buildingID', 'typeID']);

        $this->setOrdering($query);

        return $query;
    }
}
