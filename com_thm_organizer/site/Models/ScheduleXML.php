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

use Organizer\Helpers\Input;
use Organizer\Helpers\Validators as Validators;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use stdClass;

/**
 * Class which models, validates and compares schedule data to and from Untis XML exports.
 */
class ScheduleXML extends BaseModel
{
    /**
     * array to hold error strings relating to critical data inconsistencies
     *
     * @var array
     */
    public $errors = null;

    /**
     * array to hold warning strings relating to minor data inconsistencies
     *
     * @var array
     */
    public $warnings = null;

    /**
     * Object containing information from the actual schedule
     *
     * @var object
     */
    public $schedule = null;

    /**
     * Saves the term to the corresponding table if not already existent.
     *
     * @param string $termName  the abbreviation for the term
     * @param int    $startDate the integer value of the start date
     * @param int    $endDate   the integer value of the end date
     *
     * @return int id of database entry
     */
    public function getTermID($termName, $startDate, $endDate)
    {
        $data = [
            'startDate' => date('Y-m-d', $startDate),
            'endDate'   => date('Y-m-d', $endDate)
        ];

        $table = OrganizerHelper::getTable('Terms');
        if ($table->load($data)) {
            return $table->id;
        }

        $shortYear    = date('y', $endDate);
        $data['name'] = $termName . $shortYear;
        $table->save($data);

        return $table->id;
    }

    /**
     * Creates a status report based upon object error and warning messages
     *
     * @return void  outputs errors to the application
     */
    private function printStatusReport()
    {
        if (count($this->errors)) {
            $errorMessage = Languages::_('THM_ORGANIZER_STATUS_REPORT_HEADER') . '<br />';
            $errorMessage .= implode('<br />', $this->errors);
            OrganizerHelper::message($errorMessage, 'error');
        }

        if (count($this->warnings)) {
            OrganizerHelper::message(implode('<br />', $this->warnings), 'warning');
        }
    }

    /**
     * Checks a given schedule in gp-untis xml format for data completeness and
     * consistency and gives it basic structure
     *
     * @return bool true on successful validation w/o errors, false if the schedule was invalid or an error occurred
     */
    public function validate()
    {
        $formFiles   = Input::getInput()->files->get('jform', [], 'array');
        $xmlSchedule = simplexml_load_file($formFiles['file']['tmp_name']);

        $this->schedule = new stdClass;
        $this->errors   = [];
        $this->warnings = [];

        // Creation Date & Time
        $creationDate = trim((string)$xmlSchedule[0]['date']);
        $this->validateDate('creationDate', $creationDate, 'CREATION_DATE');
        $creationTime = trim((string)$xmlSchedule[0]['time']);
        $this->validateText('creationTime', $creationTime, 'CREATION_TIME');

        // School year dates
        $syStartDate = trim((string)$xmlSchedule->general->schoolyearbegindate);
        $this->validateDate('syStartDate', $syStartDate, 'SCHOOL_YEAR_START_DATE');
        $syEndDate = trim((string)$xmlSchedule->general->schoolyearenddate);
        $this->validateDate('syEndDate', $syEndDate, 'SCHOOL_YEAR_END_DATE');

        // Organizational Data
        $termName = trim((string)$xmlSchedule->general->footer);
        $termName = $this->validateText('Term', $termName, 'TERM_NAME', '/[\#\;]/');

        $this->schedule->departmentID = Input::getInt('departmentID');

        // Term start & end dates
        $startDate = trim((string)$xmlSchedule->general->termbegindate);
        $this->validateDate('startDate', $startDate, 'TERM_START_DATE');
        $endDate = trim((string)$xmlSchedule->general->termenddate);
        $this->validateDate('endDate', $endDate, 'TERM_END_DATE');

        // Checks if term and school year dates are consistent
        $this->validateTerm($startDate, $endDate, $syStartDate, $syEndDate, $termName);

        Validators\Categories::validateCollection($this, $xmlSchedule);
        Validators\Descriptions::validateCollection($this, $xmlSchedule);
        Validators\Grids::validateCollection($this, $xmlSchedule);

        Validators\Events::validateCollection($this, $xmlSchedule);
        Validators\Groups::validateCollection($this, $xmlSchedule);
        Validators\Persons::validateCollection($this, $xmlSchedule);
        unset($this->schedule->categories, $this->schedule->fields);

        Validators\Rooms::validateCollection($this, $xmlSchedule);
        unset($this->schedule->roomtypes);

        $this->schedule->calendar = new stdClass;

        Validators\Events::validateCollection($this, $xmlSchedule);
        $this->printStatusReport();

        if (count($this->errors)) {
            // Don't need the bloat if this won't be used.
            unset($this->schedule);

            return false;
        }

        // These items are now modeled in the database.
        unset(
            $this->schedule->courses,
            $this->schedule->departmentname,
            $this->schedule->degrees,
            $this->schedule->endDate,
            $this->schedule->groups,
            $this->schedule->methods,
            $this->schedule->periods,
            $this->schedule->persons,
            $this->schedule->rooms,
            $this->schedule->roomtypes,
            $this->schedule->startDate,
            $this->schedule->syEndDate,
            $this->schedule->syStartDate,
            $this->schedule->term
        );

        return true;
    }

    /**
     * Validates a date attribute. Setting it to a schedule property if valid.
     *
     * @param string $name     the attribute name
     * @param string $value    the attribute value
     * @param string $constant the unique text constant fragment
     *
     * @return void
     */
    private function validateDate($name, $value, $constant)
    {
        if (empty($value)) {
            $this->errors[] = Languages::_("THM_ORGANIZER_{$constant}_MISSING");

            return;
        }

        $this->schedule->$name = date('Y-m-d', strtotime($value));

        return;
    }

    /**
     * Validates a text attribute. Sets the attribute if valid.
     *
     * @param string $name     the attribute name
     * @param string $value    the attribute value
     * @param string $constant the unique text constant fragment
     * @param string $regex    the regex to check the text against
     *
     * @return mixed string the text if valid, otherwise bool false
     */
    private function validateText($name, $value, $constant, $regex = '')
    {
        if (empty($value)) {
            $this->errors[] = Languages::_("THM_ORGANIZER_{$constant}_MISSING");

            return false;
        }

        if (!empty($regex) and preg_match($regex, $value)) {
            $this->errors[] = Languages::_("THM_ORGANIZER_{$constant}_INVALID");

            return false;
        }

        $this->schedule->$name = $value;

        return $value;
    }

    /**
     * Validates the dates given to ensure term consistency. Sets the term ID if valid.
     *
     * @param string $startDate   the start date of the term in the format YMD
     * @param string $endDate     the end date of the term in the format YMD
     * @param string $syStartDate the start date of the school year in the format YMD
     * @param string $syEndDate   the end date of the school year in the format YMD
     * @param mixed  $termName    the abbreviated term name as a string if valid, otherwise false
     *
     * @return void set the schedule's term id as appropriate
     */
    private function validateTerm($startDate, $endDate, $syStartDate, $syEndDate, $termName)
    {
        $startTimeStamp = strtotime($startDate);
        $endTimeStamp   = strtotime($endDate);
        $invalidStart   = $startTimeStamp < strtotime($syStartDate);
        $invalidEnd     = $endTimeStamp > strtotime($syEndDate);
        $invalidPeriod  = $startTimeStamp >= $endTimeStamp;
        $invalid        = ($invalidStart or $invalidEnd or $invalidPeriod);

        if ($invalid) {
            $this->errors[] = Languages::_('THM_ORGANIZER_TERM_INVALID');

            return;
        }

        if ($termName) {
            $termID = $this->getTermID($termName, $startTimeStamp, $endTimeStamp);

            $this->schedule->termID = $termID;
        }
    }
}
