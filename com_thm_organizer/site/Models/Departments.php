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

use Organizer\Helpers\Access;
use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of departments.
 */
class Departments extends ListModel
{
    protected $defaultOrdering = 'short_name';

    /**
     * Method to get all colors from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $allowedDepartments = Access::getAccessibleDepartments();
        $shortTag           = Languages::getShortTag();

        // Create the query
        $query  = $this->_db->getQuery(true);
        $select = "d.id, d.short_name_$shortTag AS short_name, d.name_$shortTag AS name, a.rules, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=department_edit&id='", 'd.id'];
        $select .= $query->concatenate($parts, '') . ' AS link ';
        $query->select($select);
        $query->from('#__thm_organizer_departments AS d');
        $query->innerJoin('#__assets AS a ON d.asset_id = a.id');
        $query->where('d.id IN (' . implode(',', $allowedDepartments) . ')');

        $this->setSearchFilter($query, ['short_name_de', 'name_de', 'short_name_en', 'name_en']);

        $this->setOrdering($query);

        return $query;
    }
}
