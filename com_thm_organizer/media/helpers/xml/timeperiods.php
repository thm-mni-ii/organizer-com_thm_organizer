<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperXMLTimePeriods
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Provides validation methods for xml time period objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperXMLTimePeriods
{
    /**
     * Validates the timeperiods node
     *
     * @param   object  &$scheduleModel  the validating schedule model
     * @param   object  &$xmlObject  the xml object being validated
     *
     * @return  void
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->timeperiods))
        {
            $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_PERIODS_MISSING");
            return;
        }

        $scheduleModel->schedule->periods = new stdClass;

        foreach ($xmlObject->timeperiods->children() as $timePeriodNode)
        {
            self::validateIndividual($scheduleModel, $timePeriodNode);
        }
    }

    /**
     * Checks whether pool nodes have the expected structure and required
     * information
     *
     * @param   object  &$scheduleModel   the validating schedule model
     * @param   object  &$timePeriodNode  the pool node to be validated
     *
     * @return void
     */
    private static function validateIndividual(&$scheduleModel, &$timePeriodNode)
    {
        // Not actually referenced but evinces data inconsistencies in Untis
        $gpuntisID = trim((string) $timePeriodNode[0]['id']);
        $day = (int) $timePeriodNode->day;
        $period = (int) $timePeriodNode->period;
        $startTime = trim((string) $timePeriodNode->starttime);
        $endTime = trim((string) $timePeriodNode->endtime);

        $invalidPeriod = (empty($gpuntisID) OR empty($day) OR empty($period) OR empty($startTime) OR empty($endTime));
        if ($invalidPeriod AND !in_array(JText::_("COM_THM_ORGANIZER_ERROR_PERIODS_INCONSISTENT"), $scheduleModel->scheduleErrors))
        {
            $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_PERIODS_INCONSISTENT");
        }

        $grid = (string) $timePeriodNode->timegrid;

        // For backward-compatibility a default grid name is set
        if (empty($grid))
        {
            $grid = 'Haupt-Zeitraster';
        }

        // Set the grid if not already existent
        if (empty($scheduleModel->schedule->periods->$grid))
        {
            $scheduleModel->schedule->periods->$grid = new stdClass;
        }

        $scheduleModel->schedule->periods->$grid->$period = new stdClass;
        $scheduleModel->schedule->periods->$grid->$period->starttime = $startTime;
        $scheduleModel->schedule->periods->$grid->$period->endtime = $endTime;
    }
}
