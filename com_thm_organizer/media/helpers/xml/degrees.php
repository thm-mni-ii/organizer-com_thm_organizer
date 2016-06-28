<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperXMLDegrees
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Provides validation methods for xml degree (department) objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperXMLDegrees
{
    /**
     * Validates the degrees (departments) node
     *
     * @param   object  &$scheduleModel  the validating schedule model
     * @param   object  &$xmlObject  the xml object being validated
     *
     * @return  void
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->departments))
        {
            $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_PROGRAMS_MISSING");
            return;
        }

        $scheduleModel->schedule->degrees = new stdClass;

        foreach ($xmlObject->departments->children() as $degreeNode)
        {
            self::validateIndividual($scheduleModel, $degreeNode);
        }
    }

    /**
     * Checks whether pool nodes have the expected structure and required
     * information
     *
     * @param   object  &$scheduleModel  the validating schedule model
     * @param   object  &$degreeNode     the degree (department) node to be validated
     *
     * @return void
     */
    private static function validateIndividual(&$scheduleModel, &$degreeNode)
    {
        $gpuntisID = trim((string) $degreeNode[0]['id']);
        if (empty($gpuntisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING"), $scheduleModel->scheduleErrors))
            {
                $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING");
            }

            return;
        }

        $degreeID = str_replace('DP_', '', $gpuntisID);
        $scheduleModel->schedule->degrees->$degreeID = new stdClass;
        $scheduleModel->schedule->degrees->$degreeID->gpuntisID = $gpuntisID;

        $degreeName = (string) $degreeNode->longname;
        if (!isset($degreeName))
        {
            $scheduleModel->scheduleErrors[] = JText::sprintf("COM_THM_ORGANIZER_ERROR_PROGRAM_NAME_MISSING", $degreeID);
            return;
        }

        $scheduleModel->schedule->degrees->$degreeID->name = $degreeName;
    }
}
