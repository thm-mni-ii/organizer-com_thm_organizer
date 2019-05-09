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

use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of room types.
 */
class Room_Type_Manager extends ListModel
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

        $select    = "t.id, t.name_$shortTag AS name, min_capacity, max_capacity, t.gpuntisID, count(r.typeID) AS roomCount, ";
        $linkParts = ["'index.php?option=com_thm_organizer&view=room_type_edit&id='", 't.id'];
        $select    .= $query->concatenate($linkParts, '') . ' AS link';
        $query->select($select);

        $query->from('#__thm_organizer_room_types AS t');
        $query->leftJoin('#__thm_organizer_rooms AS r on r.typeID = t.id');

        $this->setSearchFilter($query, ['gpuntisID', 'name_de', 'name_en', 'min_capacity', 'max_capacity']);

        $this->setOrdering($query);
        $query->group('t.id');

        return $query;
    }
}
