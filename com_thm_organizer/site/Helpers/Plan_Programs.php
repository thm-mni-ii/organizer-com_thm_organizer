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
class Plan_Programs
{
    /**
     * Checks whether the given plan program is associated with an allowed department
     *
     * @param array $ppIDs the ids of the plan programs being checked
     *
     * @return bool  true if the plan program is associated with an allowed department, otherwise false
     */
    public static function allowEdit($ppIDs)
    {
        $user = \Factory::getUser();

        if (empty($user->id)) {
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
        $query->select('DISTINCT id')
            ->from('#__thm_organizer_department_resources')
            ->where("programID IN ( $ppIDs )")
            ->where("departmentID IN ('" . implode("', '", $allowedDepartments) . "')");

        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Validates the resource collection node
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the xml object being validated
     *
     * @return void
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->departments)) {
            $scheduleModel->scheduleErrors[] = \JText::_('COM_THM_ORGANIZER_ERROR_PROGRAMS_MISSING');

            return;
        }

        $scheduleModel->schedule->degrees = new \stdClass;

        foreach ($xmlObject->departments->children() as $degreeNode) {
            self::validateIndividual($scheduleModel, $degreeNode);
        }
    }

    /**
     * Checks whether program nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$programNode   the degree (program/department) node to be validated
     *
     * @return void
     */
    private static function validateIndividual(&$scheduleModel, &$programNode)
    {
        $programID = trim((string)$programNode[0]['id']);
        if (empty($programID)) {
            if (!in_array(\JText::_('COM_THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = \JText::_('COM_THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING');
            }

            return;
        }

        $programID = str_replace('DP_', '', $programID);

        $programName = (string)$programNode->longname;
        if (!isset($programName)) {
            $scheduleModel->scheduleErrors[]
                = sprintf(\JText::_('COM_THM_ORGANIZER_ERROR_PROGRAM_NAME_MISSING'), $programID);

            return;
        }

        $program            = new \stdClass;
        $program->gpuntisID = $programID;
        $program->name      = $programName;
        $program->id        = Programs::getPlanResourceID($program);
        Departments::setDepartmentResource($program->id, 'programID');

        $scheduleModel->schedule->degrees->$programID = $program;
    }
}
