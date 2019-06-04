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
        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT cat.id, cat.untisID, cat.name, cat.programID')
            ->from('#__thm_organizer_categories AS cat')
            ->innerJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = cat.id');

        $allowedDepartments = implode(",", Access::getAccessibleDepartments('schedule'));
        $query->where("dr.departmentID IN ($allowedDepartments)");

        $this->setSearchFilter($query, ['cat.name', 'cat.untisID']);
        $this->setValueFilters($query, ['departmentID', 'programID']);
        $this->setOrdering($query);

        return $query;
    }
}
