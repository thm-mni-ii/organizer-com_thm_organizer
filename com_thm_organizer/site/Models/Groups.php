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
        $query              = $this->_db->getQuery(true);

        if (empty($allowedDepartments)) {
            return $query;
        }

        $select    = 'DISTINCT gr.id, gr.untisID, gr.full_name, gr.name, ';
        $linkParts = ["'index.php?option=com_thm_organizer&view=group_edit&id='", 'gr.id'];
        $select    .= $query->concatenate($linkParts, '') . ' AS link';

        $query->from('#__thm_organizer_groups AS gr');
        $query->leftJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = gr.categoryID');

        $departmentID = $this->state->get('filter.departmentID');

        if ($departmentID and in_array($departmentID, $allowedDepartments)) {
            $query->where("dr.departmentID = '$departmentID'");
        } elseif ($departmentID == '-1') {
            $query->where('dr.departmentID IS NULL');
        } else {
            $query->where("dr.departmentID IN ('" . implode("', '", $allowedDepartments) . "')");
        }

        $categoryID = $this->state->get('filter.categoryID');

        if ($categoryID) {
            $select .= ', cat.id as categoryID, cat.name as categoryName';
            $query->innerJoin('#__thm_organizer_categories AS cat ON cat.id = gr.categoryID');
            $query->where("gr.categoryID = '$categoryID'");
        }

        $query->select($select);

        $searchColumns = ['gr.full_name', 'gr.name', 'gr.untisID'];
        $this->setSearchFilter($query, $searchColumns);

        $this->setOrdering($query);

        return $query;
    }
}
