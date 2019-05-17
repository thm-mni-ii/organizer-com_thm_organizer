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
 * Class retrieves information for a filtered set of fields (of expertise).
 */
class Fields extends ListModel
{
    protected $defaultOrdering = 'field';

    /**
     * Method to get all colors from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = Languages::getShortTag();

        // Create the query
        $query  = $this->_db->getQuery(true);
        $select = "f.id, gpuntisID, f.field_$shortTag AS field, c.name_$shortTag AS name, c.color, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=field_edit&id='", 'f.id'];
        $select .= $query->concatenate($parts, '') . ' AS link ';
        $query->select($select);
        $query->from('#__thm_organizer_fields AS f');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $this->setSearchFilter($query, ['field_de', 'field_en', 'gpuntisID', 'color']);
        $this->setValueFilters($query, ['colorID']);
        $this->setLocalizedFilters($query, ['field']);

        $this->setOrdering($query);

        return $query;
    }
}
