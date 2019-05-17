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
        $allowedDepartments = Access::getAccessibleDepartments('schedule');
        $shortTag           = Languages::getShortTag();
        $dbo                = $this->getDbo();
        $query              = $dbo->getQuery(true);

        $select       = 's.id, s.active, s.creationDate, s.creationTime, ';
        $select       .= "d.id AS departmentID, d.short_name_$shortTag AS departmentName, ";
        $select       .= 'pp.id AS planningPeriodID, pp.name AS planningPeriodName, ';
        $select       .= 'u.name AS userName, ';
        $createdParts = ['s.creationDate', 's.creationTime'];
        $select       .= $query->concatenate($createdParts, ' ') . ' AS created ';

        $query->select($select)
            ->from('#__thm_organizer_schedules AS s')
            ->innerJoin('#__thm_organizer_departments AS d ON s.departmentID = d.id')
            ->innerJoin('#__thm_organizer_planning_periods AS pp ON s.planningPeriodID = pp.id')
            ->leftJoin('#__users AS u ON u.id = s.userID')
            ->where('d.id IN (' . implode(', ', $allowedDepartments) . ')');

        $this->setValueFilters($query, ['departmentID', 'planningPeriodID', 'active']);

        $this->setOrdering($query);

        return $query;
    }
}
