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
use Organizer\Helpers\Mappings;

/**
 * Class retrieves information for a filtered set of (subject) pools.
 */
class Pools extends ListModel
{
    /**
     * Method to select the tree of a given major
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $allowedDepartments = Access::getAccessibleDepartments('document');
        $query              = $this->_db->getQuery(true);

        $shortTag = Languages::getShortTag();
        $select   = "DISTINCT p.id, p.name_$shortTag AS name, field_$shortTag AS field, color, ";
        $parts    = ["'index.php?option=com_thm_organizer&view=pool_edit&id='", 'p.id'];
        $select   .= $query->concatenate($parts, '') . 'AS link ';
        $query->select($select);

        $query->from('#__thm_organizer_pools AS p');
        $query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where('(p.departmentID IN (' . implode(',', $allowedDepartments) . ') OR p.departmentID IS NULL)');

        $searchColumns = [
            'p.name_de',
            'short_name_de',
            'abbreviation_de',
            'description_de',
            'p.name_en',
            'short_name_en',
            'abbreviation_en',
            'description_en'
        ];
        $this->setSearchFilter($query, $searchColumns);
        $this->setLocalizedFilters($query, ['p.name']);
        $this->setValueFilters($query, ['fieldID']);

        $programID = $this->state->get('filter.programID', '');
        Mappings::setResourceIDFilter($query, $programID, 'program', 'pool');

        $this->setOrdering($query);

        return $query;
    }
}
