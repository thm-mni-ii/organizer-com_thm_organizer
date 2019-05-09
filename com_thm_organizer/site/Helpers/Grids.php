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

use stdClass;

defined('_JEXEC') or die;

/**
 * Class provides general functions for retrieving building data.
 */
class Grids implements XMLValidator
{
    /**
     * Retrieves the table id if existent.
     *
     * @param string $untisID the grid name in untis
     *
     * @return mixed int id on success, otherwise null
     */
    public static function getID($untisID)
    {
        $table  = OrganizerHelper::getTable('Grids');
        $data   = ['gpuntisID' => $untisID];
        $exists = $table->load($data);

        return empty ($exists) ? null : $table->id;
    }

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
        $gridID = self::getID($gpuntisID);
        if (!empty($gridID)) {
            return;
        }

        $grid->grid = json_encode($grid->grid);

        $gridTable = OrganizerHelper::getTable('Grids');
        $gridTable->save($grid);
    }

    /**
     * Sets grid entries for later storage in the database
     *
     * @param object $grids    the grids container object
     * @param string $gridName the name used for the grid in untis
     * @param int    $day      the day number
     * @param int    $periodNo the period number
     * @param int    $period   the period start time as a 4 digit number
     *
     * @return void modifies the grids object
     */
    private static function setGridEntry(&$grids, $gridName, $day, $periodNo, $period)
    {
        // Builds the object for the DB
        if (!isset($grids->$gridName)) {
            $grids->$gridName                = new stdClass;
            $grids->$gridName->gpuntisID     = $gridName;
            $grids->$gridName->name_de       = $gridName;
            $grids->$gridName->name_en       = $gridName;
            $grids->$gridName->grid          = new stdClass;
            $grids->$gridName->grid->periods = new stdClass;
        }

        $setStartDay = (empty($grids->$gridName->grid->startDay) or $grids->$gridName->grid->startDay > $day);
        if ($setStartDay) {
            $grids->$gridName->grid->startDay = $day;
        }

        $setEndDay = (empty($grids->$gridName->grid->endDay) or $grids->$gridName->grid->endDay < $day);
        if ($setEndDay) {
            $grids->$gridName->grid->endDay = $day;
        }

        $grids->$gridName->grid->periods->$periodNo = $period;
    }

    /**
     * Checks whether nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the object being validated
     *
     * @return void modifies &$scheduleModel
     */
    public static function validateCollection(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->timeperiods)) {
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_PERIODS_MISSING');

            return;
        }

        $scheduleModel->schedule->periods = new stdClass;
        $grids                            = new stdClass;

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
        $periodNo  = (int)$timePeriodNode->period;
        $startTime = trim((string)$timePeriodNode->starttime);
        $endTime   = trim((string)$timePeriodNode->endtime);

        $invalidKeys   = (empty($exportKey) or empty($gridName) or empty($periodNo));
        $invalidTimes  = (empty($day) or empty($startTime) or empty($endTime));
        $invalidPeriod = ($invalidKeys or $invalidTimes);

        if ($invalidPeriod) {
            if (!in_array(Languages::_('THM_ORGANIZER_ERROR_PERIODS_INCONSISTENT'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_PERIODS_INCONSISTENT');
            }

            return;
        }

        // Set the grid if not already existent
        if (empty($scheduleModel->schedule->periods->$gridName)) {
            $scheduleModel->schedule->periods->$gridName = new stdClass;
        }

        $scheduleModel->schedule->periods->$gridName->$periodNo            = new stdClass;
        $scheduleModel->schedule->periods->$gridName->$periodNo->startTime = $startTime;
        $scheduleModel->schedule->periods->$gridName->$periodNo->endTime   = $endTime;

        $label = (string)$timePeriodNode->label;
        if (!empty($label)) {
            $textual = preg_match("/[a-zA-ZäÄöÖüÜß]+/", $label);

            if ($textual) {
                $scheduleModel->schedule->periods->$gridName->$periodNo->label_de = $label;
                $scheduleModel->schedule->periods->$gridName->$periodNo->label_en = $label;

                // This is an assumption, which can later be rectified as necessary.
                $scheduleModel->schedule->periods->$gridName->$periodNo->type = 'break';
            }
        }

        self::setGridEntry($grids, $gridName, $day, $periodNo, $scheduleModel->schedule->periods->$gridName->$periodNo);
    }
}
