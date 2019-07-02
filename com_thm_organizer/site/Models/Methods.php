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
 * Class retrieves information for a filtered set of (lesson) methods.
 */
class Methods extends ListModel
{
    protected $defaultOrdering = 'abbreviation';

    /**
     * Method to get all methodss from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $tag   = Languages::getTag();
        $query = $this->_db->getQuery(true);

        $select = "id, abbreviation_$tag AS abbreviation, name_$tag AS name, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=method_edit&id='", 'id'];
        $select .= $query->concatenate($parts, '') . ' AS link';
        $query->select($select);
        $query->from('#__thm_organizer_methods');

        $this->setSearchFilter($query, ['name_de', 'name_en', 'abbreviation_de', 'abbreviation_en']);

        $this->setOrdering($query);

        return $query;
    }
}
