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
 * Class retrieves information for a filtered set of plan (subject) pools.
 */
class Groups extends ListModel
{
    protected $defaultOrdering = 'ppl.gpuntisID';

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

        $select    = 'DISTINCT ppl.id, ppl.gpuntisID, ppl.full_name, ppl.name, ';
        $linkParts = ["'index.php?option=com_thm_organizer&view=group_edit&id='", 'ppl.id'];
        $select    .= $query->concatenate($linkParts, '') . ' AS link';

        $query->from('#__thm_organizer_plan_pools AS ppl');
        $query->leftJoin('#__thm_organizer_department_resources AS dr ON ppl.programID = dr.programID');

        $departmentID = $this->state->get('filter.departmentID');

        if ($departmentID and in_array($departmentID, $allowedDepartments)) {
            $query->where("dr.departmentID = '$departmentID'");
        } elseif ($departmentID == '-1') {
            $query->where('dr.departmentID IS NULL');
        } else {
            $query->where("dr.departmentID IN ('" . implode("', '", $allowedDepartments) . "')");
        }

        $programID = $this->state->get('filter.programID');

        if ($programID) {
            $select .= ', ppr.id as programID, ppr.name as programName';
            $query->innerJoin('#__thm_organizer_plan_programs AS ppr ON ppl.programID = ppr.id');
            $query->where("ppl.programID = '$programID'");
        }

        $query->select($select);

        $searchColumns = ['ppl.full_name', 'ppl.name', 'ppl.gpuntisID'];
        $this->setSearchFilter($query, $searchColumns);

        $this->setOrdering($query);

        return $query;
    }
}
