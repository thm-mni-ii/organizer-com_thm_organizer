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
        $query    = $this->_db->getQuery(true);

        $query->select("f.id, untisID, f.field_$shortTag AS field, f.colorID")
            ->from('#__thm_organizer_fields AS f')
            ->select("c.name_$shortTag AS color")
            ->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $this->setSearchFilter($query, ['field_de', 'field_en', 'untisID', 'color']);
        $this->setValueFilters($query, ['colorID']);

        $this->setOrdering($query);

        return $query;
    }
}
