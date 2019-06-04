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
 * Class retrieves information for a filtered set of (degree) programs.
 */
class Programs extends ListModel
{
    /**
     * Method to determine all majors
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $allowedDepartments = Access::getAccessibleDepartments('document');
        $shortTag           = Languages::getShortTag();

        $query  = $this->_db->getQuery(true);
        $select = "dp.name_$shortTag AS name, version, ";
        $select .= "dp.id AS id, d.abbreviation AS abbreviation, dpt.short_name_$shortTag AS departmentname, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=program_edit&id='", 'dp.id'];
        $select .= $query->concatenate($parts, '') . ' AS link ';
        $query->select($select);

        $query->from('#__thm_organizer_programs AS dp');
        $query->leftJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->leftJoin('#__thm_organizer_fields AS f ON dp.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_departments AS dpt ON dp.departmentID = dpt.id');
        $query->where('(dp.departmentID IN (' . implode(',', $allowedDepartments) . ') OR dp.departmentID IS NULL)');

        $searchColumns = ['dp.name_de', 'dp.name_en', 'version', 'd.name', 'description_de', 'description_en'];
        $this->setSearchFilter($query, $searchColumns);
        $this->setValueFilters($query, ['degreeID', 'version', 'departmentID']);

        $this->setOrdering($query);

        return $query;
    }
}
