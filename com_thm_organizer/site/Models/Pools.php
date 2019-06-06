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
        $shortTag = Languages::getShortTag();
        $query    = $this->_db->getQuery(true);

        $query->select("DISTINCT p.id, p.name_$shortTag AS name, p.fieldID")
            ->from('#__thm_organizer_pools AS p');

        $allowedDepartments = Access::getAccessibleDepartments('document');
        $query->where('(p.departmentID IN (' . implode(',', $allowedDepartments) . ') OR p.departmentID IS NULL)');

        $searchColumns = [
            'p.name_de',
            'p.short_name_de',
            'p.abbreviation_de',
            'p.description_de',
            'p.name_en',
            'p.short_name_en',
            'p.abbreviation_en',
            'p.description_en'
        ];
        $this->setSearchFilter($query, $searchColumns);
        $this->setValueFilters($query, ['departmentID', 'fieldID']);

        $programID = $this->state->get('filter.programID', '');
        Mappings::setResourceIDFilter($query, $programID, 'program', 'pool');

        $this->setOrdering($query);

        return $query;
    }
}
