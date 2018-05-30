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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/grids.php';

/**
 * Provides functions for XML grid validation and modeling.
 */
class THM_OrganizerHelperXMLGrids
{
    /**
     * Saves the grid to the corresponding table if not already existent.
     *
     * @param string $gpuntisID the gpuntis name for the grid
     * @param object $grid      the object modelling the grid information
     *
     * @return void creates database entries
     */
    private static function saveGridEntry($gpuntisID, $grid)
    {
        $gridID = THM_OrganizerHelperGrids::getID($gpuntisID);
        if (!empty($gridID)) {
            return;
        }

        $grid->grid = json_encode($grid->grid);

        $gridTable = JTable::getInstance('grids', 'thm_organizerTable');
        $gridTable->save($grid);
    }

    /**
     * Sets grid entries for later storage in the database
     *
     * @param object $grids     the grids container object
     * @param string $gpuntisID the name used for the grid in untis
     * @param int    $day       the day number
     * @param int    $period    the period number
     * @param int    $startTime the period start time as a 4 digit number
     * @param int    $endTime   the period end time as a 4 digit number
     *
     * @return void modifies the grids object
     */
    private static function setGridEntry(&$grids, $gpuntisID, $day, $period, $startTime, $endTime)
    {
        // Builds the object for the DB
        if (!isset($grids->$gpuntisID)) {
            $grids->$gpuntisID                = new stdClass;
            $grids->$gpuntisID->gpuntisID     = $gpuntisID;
            $grids->$gpuntisID->name_de       = $gpuntisID;
            $grids->$gpuntisID->name_en       = $gpuntisID;
            $grids->$gpuntisID->grid          = new stdClass;
            $grids->$gpuntisID->grid->periods = new stdClass;
        }

        $setStartDay = (empty($grids->$gpuntisID->grid->startDay) or $grids->$gpuntisID->grid->startDay > $day);
        if ($setStartDay) {
            $grids->$gpuntisID->grid->startDay = $day;
        }

        $setEndDay = (empty($grids->$gpuntisID->grid->endDay) or $grids->$gpuntisID->grid->endDay < $day);
        if ($setEndDay) {
            $grids->$gpuntisID->grid->endDay = $day;
        }

        if (!isset($grids->$gpuntisID->blocks->$period)) {
            $grids->$gpuntisID->grid->periods->$period            = new stdClass;
            $grids->$gpuntisID->grid->periods->$period->startTime = $startTime;
            $grids->$gpuntisID->grid->periods->$period->endTime   = $endTime;
        }
    }

    /**
     * Validates the timeperiods node
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the xml object being validated
     *
     * @return void
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->timeperiods)) {
            $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_PERIODS_MISSING");

            return;
        }

        $scheduleModel->newSchedule->periods = new stdClass;
        $grids                               = new stdClass;

        foreach ($xmlObject->timeperiods->children() as $timePeriodNode) {
            self::validateIndividual($scheduleModel, $timePeriodNode, $grids);
        }

        foreach ($grids as $gpuntisID => $grid) {
            self::saveGridEntry($gpuntisID, $grid);
        }
    }

    /**
     * Checks whether pool nodes have the expected structure and required
     * information
     *
     * @param object &$scheduleModel  the validating schedule model
     * @param object &$timePeriodNode the time period node to be validated
     * @param object &$grids          the container for grids
     *
     * @return void
     */
    private static function validateIndividual(&$scheduleModel, &$timePeriodNode, &$grids)
    {
        // Not actually referenced but evinces data inconsistencies in Untis
        $exportKey = trim((string)$timePeriodNode[0]['id']);
        $gridName  = (string)$timePeriodNode->timegrid;
        $day       = (int)$timePeriodNode->day;
        $period    = (int)$timePeriodNode->period;
        $startTime = trim((string)$timePeriodNode->starttime);
        $endTime   = trim((string)$timePeriodNode->endtime);

        $invalidKeys   = (empty($exportKey) or empty($gridName) or empty($period));
        $invalidTimes  = (empty($day) or empty($startTime) or empty($endTime));
        $invalidPeriod = ($invalidKeys or $invalidTimes);

        if ($invalidPeriod) {
            if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_PERIODS_INCONSISTENT"), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_PERIODS_INCONSISTENT");
            }

            return;
        }

        // Set the grid if not already existent
        if (empty($scheduleModel->newSchedule->periods->$gridName)) {
            $scheduleModel->newSchedule->periods->$gridName = new stdClass;
        }

        $scheduleModel->newSchedule->periods->$gridName->$period            = new stdClass;
        $scheduleModel->newSchedule->periods->$gridName->$period->startTime = $startTime;
        $scheduleModel->newSchedule->periods->$gridName->$period->endTime   = $endTime;

        $label = (string)$timePeriodNode->label;
        if (!empty($label)) {
            $textual = preg_match("/[a-zA-ZäÄöÖüÜß]+/", $label, $output_array);

            if ($textual) {
                $scheduleModel->newSchedule->periods->$gridName->$period->label_de = $label;
                $scheduleModel->newSchedule->periods->$gridName->$period->label_en = $label;

                // This is an assumption, which can later be rectified as necessary.
                $scheduleModel->newSchedule->periods->$gridName->$period->type = 'break';
            }
        }

        self::setGridEntry($grids, $gridName, $day, $period, $startTime, $endTime);
    }
}
