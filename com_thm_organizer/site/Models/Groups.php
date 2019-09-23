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

/**
 * Class retrieves information for a filtered set of groups.
 */
class Groups extends ListModel
{
    protected $defaultOrdering = 'gr.untisID';

    /**
     * Method to get all groups from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $allowedDepartments = Access::getAccessibleDepartments('schedule');

        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT gr.id, gr.untisID, gr.fullName, gr.name, gr.categoryID, gr.gridID')
            ->select('dr.departmentID')
            ->from('#__thm_organizer_groups AS gr')
            ->innerJoin('#__thm_organizer_categories AS cat ON cat.id = gr.categoryID')
            ->leftJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = gr.categoryID')
            ->where('(dr.departmentID IN (' . implode(',', $allowedDepartments) . ') OR dr.departmentID IS NULL)');

        $this->setSearchFilter($query, ['gr.fullName', 'gr.name', 'gr.untisID']);
        $this->setValueFilters($query, ['gr.categoryID', 'dr.departmentID', 'gr.gridID']);

        $this->setOrdering($query);

        return $query;
    }
}
