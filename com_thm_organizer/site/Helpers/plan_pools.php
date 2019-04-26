<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once 'OrganizerHelper.php';

use Joomla\CMS\Factory;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class THM_OrganizerHelperPlan_Pools
{
    /**
     * Checks whether the given plan pool is associated with an allowed department
     *
     * @param array $ppIDs the ids of the plan pools being checked
     *
     * @return bool  true if the plan pool is associated with an allowed department, otherwise false
     */
    public static function allowEdit($ppIDs)
    {
        if (empty(Factory::getUser()->id)) {
            return false;
        }

        if (THM_OrganizerHelperAccess::isAdmin()) {
            return true;
        }

        if (empty($ppIDs)) {
            return false;
        }

        $ppIDs              = "'" . implode("', '", $ppIDs) . "'";
        $allowedDepartments = THM_OrganizerHelperAccess::getAccessibleDepartments('schedule');

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT dr.id')
            ->from('#__thm_organizer_department_resources as dr')
            ->innerJoin('#__thm_organizer_plan_pools as ppl on ppl.programID = dr.programID')
            ->where("ppl.id IN ( $ppIDs )")
            ->where("departmentID IN ('" . implode("', '", $allowedDepartments) . "')");

        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadColumn', []);
    }
}
