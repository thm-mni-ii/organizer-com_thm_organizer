<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

defined('_JEXEC') or die;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class Plan_Pools
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
        if (empty(\Factory::getUser()->id)) {
            return false;
        }

        if (Access::isAdmin()) {
            return true;
        }

        if (empty($ppIDs)) {
            return false;
        }

        $ppIDs              = "'" . implode("', '", $ppIDs) . "'";
        $allowedDepartments = Access::getAccessibleDepartments('schedule');

        $dbo   = \Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT dr.id')
            ->from('#__thm_organizer_department_resources as dr')
            ->innerJoin('#__thm_organizer_plan_pools as ppl on ppl.programID = dr.programID')
            ->where("ppl.id IN ( $ppIDs )")
            ->where("departmentID IN ('" . implode("', '", $allowedDepartments) . "')");

        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadColumn', []);
    }

    /**
     * Validates the pools (classes) node
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the xml object being validated
     *
     * @return void
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->classes)) {
            $scheduleModel->scheduleErrors[] = \JText::_('COM_THM_ORGANIZER_ERROR_POOLS_MISSING');

            return;
        }

        $scheduleModel->schedule->pools = new \stdClass;

        foreach ($xmlObject->classes->children() as $poolNode) {
            self::validateIndividual($scheduleModel, $poolNode);
        }
    }

    /**
     * Checks whether pool nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$poolNode      the pool node to be validated
     *
     * @return void
     */
    private static function validateIndividual(&$scheduleModel, &$poolNode)
    {
        $internalID = trim((string)$poolNode[0]['id']);
        if (empty($internalID)) {
            if (!in_array(\JText::_('COM_THM_ORGANIZER_ERROR_POOL_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = \JText::_('COM_THM_ORGANIZER_ERROR_POOL_ID_MISSING');
            }

            return;
        }

        $internalID = str_replace('CL_', '', $internalID);
        $longName   = trim((string)$poolNode->longname);

        if (empty($longName)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                \JText::_('COM_THM_ORGANIZER_ERROR_POOL_LONGNAME_MISSING'),
                $internalID
            );

            return;
        }

        $restriction = trim((string)$poolNode->classlevel);
        if (empty($restriction)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                \JText::_('COM_THM_ORGANIZER_ERROR_NODE_NAME'),
                $longName,
                $internalID
            );

            return;
        }

        $degreeID = str_replace('DP_', '', trim((string)$poolNode->class_department[0]['id']));
        if (empty($degreeID)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                \JText::_('COM_THM_ORGANIZER_ERROR_POOL_DEGREE_MISSING'),
                $longName,
                $internalID
            );

            return;
        } elseif (empty($scheduleModel->schedule->degrees->$degreeID)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                \JText::_('COM_THM_ORGANIZER_ERROR_POOL_DEGREE_LACKING'),
                $longName,
                $internalID,
                $degreeID
            );

            return;
        }

        $grid = (string)$poolNode->timegrid;
        if (empty($grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                \JText::_('COM_THM_ORGANIZER_ERROR_POOL_GRID_MISSING'),
                $longName,
                $internalID
            );

            return;
        } elseif (empty($scheduleModel->schedule->periods->$grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                \JText::_('COM_THM_ORGANIZER_ERROR_POOL_GRID_LACKING'),
                $longName,
                $internalID,
                $grid
            );

            return;
        }

        $externalID = trim((string)$poolNode->external_name);
        if (!empty($externalID)) {
            $poolID = str_replace('CL_', '', $externalID);
        } else {
            $poolID = $internalID;
        }

        $pool     = new \stdClass;
        $longName = trim((string)$poolNode->longname);

        $pool->degree       = $degreeID;
        $pool->gpuntisID    = $poolID;
        $pool->localUntisID = str_replace('CL_', '', trim((string)$poolNode[0]['id']));
        $pool->longname     = $longName;
        $pool->name         = $poolID;
        $pool->restriction  = $restriction;
        $pool->grid         = $grid;
        $pool->gridID       = Grids::getID($grid);

        // This is dependent on degree, gridID, longname and restriction already being set => order important!
        $pool->id = Pools::getPlanResourceID($poolID, $pool);

        $scheduleModel->schedule->pools->$poolID = $pool;
    }
}
