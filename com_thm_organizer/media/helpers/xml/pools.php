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

require_once 'grids.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/grids.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/pools.php';
require_once 'programs.php';

/**
 * Provides functions for XML (subject) pool validation and modeling.
 */
class THM_OrganizerHelperXMLPools
{
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
            $scheduleModel->scheduleErrors[] = JText::_('COM_THM_ORGANIZER_ERROR_POOLS_MISSING');

            return;
        }

        $scheduleModel->schedule->pools = new stdClass;

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
            if (!in_array(JText::_('COM_THM_ORGANIZER_ERROR_POOL_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = JText::_('COM_THM_ORGANIZER_ERROR_POOL_ID_MISSING');
            }

            return;
        }

        $internalID = str_replace('CL_', '', $internalID);
        $longName   = trim((string)$poolNode->longname);

        if (empty($longName)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                JText::_('COM_THM_ORGANIZER_ERROR_POOL_LONGNAME_MISSING'),
                $internalID
            );

            return;
        }

        $restriction = trim((string)$poolNode->classlevel);
        if (empty($restriction)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                JText::_('COM_THM_ORGANIZER_ERROR_NODE_NAME'),
                $longName,
                $internalID
            );

            return;
        }

        $degreeID = str_replace('DP_', '', trim((string)$poolNode->class_department[0]['id']));
        if (empty($degreeID)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                JText::_('COM_THM_ORGANIZER_ERROR_POOL_DEGREE_MISSING'),
                $longName,
                $internalID
            );

            return;
        } elseif (empty($scheduleModel->schedule->degrees->$degreeID)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                JText::_('COM_THM_ORGANIZER_ERROR_POOL_DEGREE_LACKING'),
                $longName,
                $internalID,
                $degreeID
            );

            return;
        }

        $grid = (string)$poolNode->timegrid;
        if (empty($grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                JText::_('COM_THM_ORGANIZER_ERROR_POOL_GRID_MISSING'),
                $longName,
                $internalID
            );

            return;
        } elseif (empty($scheduleModel->schedule->periods->$grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                JText::_('COM_THM_ORGANIZER_ERROR_POOL_GRID_LACKING'),
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

        $pool     = new stdClass;
        $longName = trim((string)$poolNode->longname);

        $pool->degree       = $degreeID;
        $pool->gpuntisID    = $poolID;
        $pool->localUntisID = str_replace('CL_', '', trim((string)$poolNode[0]['id']));
        $pool->longname     = $longName;
        $pool->name         = $poolID;
        $pool->restriction  = $restriction;
        $pool->grid         = $grid;
        $pool->gridID       = THM_OrganizerHelperGrids::getID($grid);

        // This is dependent on degree, gridID, longname and restriction already being set => order important!
        $pool->id = THM_OrganizerHelperPools::getPlanResourceID($poolID, $pool);

        $scheduleModel->schedule->pools->$poolID = $pool;
    }
}
