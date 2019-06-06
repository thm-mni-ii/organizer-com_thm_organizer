<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use JDatabaseQuery;
use Organizer\Helpers\Access;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class contains functions for department filtering.
 */
trait DepartmentFilters
{
    /**
     * Restricts the query by the departmentIDs for which the user has the given access right.
     *
     * @param JDatabaseQuery &$query  the query to modify
     * @param string          $alias  the alias being used for the resource table
     * @param string          $action the access right to be filtered against
     */
    public function addDeptAccessFilter(&$query, $alias, $action)
    {
        $allowedDepartments = implode(',', Access::getAccessibleDepartments($action));
        $query->where("$alias.departmentID IN ($allowedDepartments)");
    }

    /**
     * Adds a selected department filter to the query.
     *
     * @param JDatabaseQuery &$query the query to be modified
     * @param string          $alias the alias being used for the resource table
     *
     * @return void modifies the query
     */
    public function addDeptSelectionFilter(&$query, $alias)
    {
        $filters = OrganizerHelper::getInput()->get('filter', [], 'array');
        if (!empty($filters['departmentID'])) {
            $departmentID = (int)$filters['departmentID'];
            $query->where("$alias.departmentID = $departmentID");
        }
    }
}