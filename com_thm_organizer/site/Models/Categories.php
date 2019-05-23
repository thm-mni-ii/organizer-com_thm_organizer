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
 * Class retrieves information for a filtered set of categories.
 */
class Categories extends ListModel
{
    protected $defaultOrdering = 'cat.untisID';

    /**
     * Method to get all categories from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $allowedDepartments = Access::getAccessibleDepartments('schedule');
        $shortTag           = Languages::getShortTag();
        $query              = $this->_db->getQuery(true);

        $select    = "DISTINCT cat.id, cat.untisID, cat.name, pr.name_$shortTag AS prName, pr.version, d.abbreviation AS abbreviation, ";
        $linkParts = ["'index.php?option=com_thm_organizer&view=category_edit&id='", 'cat.id'];
        $select    .= $query->concatenate($linkParts, '') . ' AS link';
        $query->select($select);

        $query->from('#__thm_organizer_categories AS cat');
        $query->leftJoin('#__thm_organizer_programs AS pr ON cat.programID = pr.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON pr.degreeID = d.id');

        $departmentID = $this->state->get('list.departmentID');
        $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = cat.id');

        if ($departmentID and in_array($departmentID, $allowedDepartments)) {
            $query->where("dr.departmentID = '$departmentID'");
        } else {
            $query->where("dr.departmentID IN ('" . implode("', '", $allowedDepartments) . "')");
        }

        $searchColumns = ['cat.name', 'cat.untisID'];
        $this->setSearchFilter($query, $searchColumns);

        $this->setOrdering($query);

        return $query;
    }
}
