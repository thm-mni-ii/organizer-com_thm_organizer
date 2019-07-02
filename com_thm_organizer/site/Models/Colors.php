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
 * Class retrieves information for a filtered set of colors.
 */
class Colors extends ListModel
{
    /**
     * Method to get all colors from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = Languages::getShortTag();
        $query    = $this->_db->getQuery(true);

        $select = "id, name_$shortTag AS name, color, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=color_edit&id='", 'id'];
        $select .= $query->concatenate($parts, '') . ' AS link';
        $query->select($select)->from('#__thm_organizer_colors');

        $this->setSearchFilter($query, ['name_de', 'name_en', 'color']);
        $this->setValueFilters($query, ['color']);
        $this->setIDFilter($query, 'id', 'filter.name');

        $this->setOrdering($query);

        return $query;
    }
}
