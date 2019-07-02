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

/**
 * Class retrieves information for a filtered set of schedules.
 */
class Schedules extends ListModel
{
    protected $defaultOrdering = 'created';

    protected $defaultDirection = 'DESC';

    /**
     * generates the query to be used to fill the output list
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $dbo   = $this->getDbo();
        $tag   = Languages::getTag();
        $query = $dbo->getQuery(true);

        $createdParts = ['s.creationDate', 's.creationTime'];
        $query->select('s.id, s.active, s.creationDate, s.creationTime')
            ->select($query->concatenate($createdParts, ' ') . ' AS created ')
            ->select("d.id AS departmentID, d.short_name_$tag AS departmentName")
            ->select('term.id AS termID, term.name AS termName')
            ->select('u.name AS userName')
            ->from('#__thm_organizer_schedules AS s')
            ->innerJoin('#__thm_organizer_departments AS d ON s.departmentID = d.id')
            ->innerJoin('#__thm_organizer_terms AS term ON term.id = s.termID')
            ->leftJoin('#__users AS u ON u.id = s.userID');

        $allowedDepartments = implode(', ', Access::getAccessibleDepartments('schedule'));
        $query->where("d.id IN ($allowedDepartments)");

        $this->setValueFilters($query, ['departmentID', 'termID', 'active']);

        $this->setOrdering($query);

        return $query;
    }
}
