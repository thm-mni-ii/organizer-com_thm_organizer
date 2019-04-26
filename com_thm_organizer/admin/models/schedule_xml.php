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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/descriptions.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/grids.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/lessons.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/pools.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/programs.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/rooms.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/schedules.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/subjects.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/teachers.php';

use THM_OrganizerHelperDescriptions as Descriptions;
use THM_OrganizerHelperGrids as Grids;
use THM_OrganizerHelperLessons as Lessons;
use THM_OrganizerHelperPools as Pools;
use THM_OrganizerHelperPrograms as Programs;
use THM_OrganizerHelperRooms as Rooms;
use THM_OrganizerHelperSchedules as Schedules;
use THM_OrganizerHelperSubjects as Subjects;
use THM_OrganizerHelperTeachers as Teachers;

/**
 * Class which models, validates and compares schedule data to and from Untis XML exports.
 */
class THM_OrganizerModelSchedule_XML extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * array to hold error strings relating to critical data inconsistencies
     *
     * @var array
     */
    public $scheduleErrors = null;

    /**
     * array to hold warning strings relating to minor data inconsistencies
     *
     * @var array
     */
    public $scheduleWarnings = null;

    /**
     * Object containing information from the actual schedule
     *
     * @var object
     */
    public $schedule = null;

    /**
     * Creates a status report based upon object error and warning messages
     *
     * @return void  outputs errors to the application
     */
    private function printStatusReport()
    {
        if (count($this->scheduleErrors)) {
            $errorMessage = Languages::_('THM_ORGANIZER_ERROR_HEADER') . '<br />';
            $errorMessage .= implode('<br />', $this->scheduleErrors);
            OrganizerHelper::message($errorMessage, 'error');
        }

        if (count($this->scheduleWarnings)) {
            OrganizerHelper::message(implode('<br />', $this->scheduleWarnings), 'warning');
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
        $input       = OrganizerHelper::getInput();
        $formFiles   = $input->files->get('jform', [], 'array');
        $file        = $formFiles['file'];
        $xmlSchedule = simplexml_load_file($file['tmp_name']);

        $this->schedule         = new \stdClass;
        $this->scheduleErrors   = [];
        $this->scheduleWarnings = [];

        // Creation Date & Time
        $creationDate = trim((string)$xmlSchedule[0]['date']);
        $this->validateDateAttribute('creationDate', $creationDate, 'CREATION_DATE', 'error');
        $creationTime = trim((string)$xmlSchedule[0]['time']);
        $this->validateTextAttribute('creationTime', $creationTime, 'CREATION_TIME', 'error');

        // School year dates
        $syStartDate = trim((string)$xmlSchedule->general->schoolyearbegindate);
        $this->validateDateAttribute('syStartDate', $syStartDate, 'SCHOOL_YEAR_START_DATE', 'error');
        $syEndDate = trim((string)$xmlSchedule->general->schoolyearenddate);
        $this->validateDateAttribute('syEndDate', $syEndDate, 'SCHOOL_YEAR_END_DATE', 'error');

        // Organizational Data
        $departmentName = trim((string)$xmlSchedule->general->header1);
        $this->validateTextAttribute('departmentname', $departmentName, 'ORGANIZATION', 'error', '/[\#\;]/');
        $semesterName = trim((string)$xmlSchedule->general->footer);
        $validSemesterName
                      = $this->validateTextAttribute('semestername', $semesterName, 'TERM_NAME', 'error', '/[\#\;]/');

        $form = $input->get('jform', [], 'array');

        $this->schedule->departmentID = $form['departmentID'];

        // Planning period start & end dates
        $startDate = trim((string)$xmlSchedule->general->termbegindate);
        $this->validateDateAttribute('startDate', $startDate, 'TERM_START_DATE');
        $endDate = trim((string)$xmlSchedule->general->termenddate);
        $this->validateDateAttribute('endDate', $endDate, 'TERM_END_DATE');

        // Checks if planning period and school year dates are consistent
        $startTimeStamp = strtotime($startDate);
        $endTimeStamp   = strtotime($endDate);
        $invalidStart   = $startTimeStamp < strtotime($syStartDate);
        $invalidEnd     = $endTimeStamp > strtotime($syEndDate);
        $invalidPeriod  = $startTimeStamp >= $endTimeStamp;
        $invalid        = ($invalidStart or $invalidEnd or $invalidPeriod);

        if ($invalid) {
            $this->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_TERM_WRONG');
        } elseif ($validSemesterName) {
            $planningPeriodID = Schedules::getPlanningPeriodID($semesterName, $startTimeStamp, $endTimeStamp);

            $this->schedule->planningPeriodID = $planningPeriodID;
        }

        Grids::validate($this, $xmlSchedule);
        Descriptions::validate($this, $xmlSchedule);
        Programs::validate($this, $xmlSchedule);
        Pools::validate($this, $xmlSchedule);
        Rooms::validate($this, $xmlSchedule);
        Subjects::validate($this, $xmlSchedule);
        Teachers::validate($this, $xmlSchedule);

        $this->schedule->calendar = new \stdClass;

        $lessonsHelper = new Lessons($this, $xmlSchedule);
        $lessonsHelper->validate();
        $this->printStatusReport();

        if (count($this->scheduleErrors)) {
            // Don't need the bloat if this won't be used.
            unset($this->schedule);

            return false;
        }

        // These items are now modeled in the database.
        unset(
            $this->schedule->departmentname,
            $this->schedule->degrees,
            $this->schedule->endDate,
            $this->schedule->fields,
            $this->schedule->methods,
            $this->schedule->periods,
            $this->schedule->pools,
            $this->schedule->programs,
            $this->schedule->room_types,
            $this->schedule->rooms,
            $this->schedule->semestername,
            $this->schedule->subjects,
            $this->schedule->startDate,
            $this->schedule->syEndDate,
            $this->schedule->syStartDate,
            $this->schedule->teachers
        );

        return true;
    }

    /**
     * Validates a date attribute
     *
     * @param string $name     the attribute name
     * @param string $value    the attribute value
     * @param string $constant the unique text constant fragment
     * @param string $severity the severity of the item being inspected
     *
     * @return void
     */
    public function validateDateAttribute($name, $value, $constant, $severity = 'error')
    {
        if (empty($value)) {
            if ($severity == 'error') {
                $this->scheduleErrors[] = Languages::_("THM_ORGANIZER_ERROR_{$constant}_MISSING");

                return;
            }

            if ($severity == 'warning') {
                $this->scheduleWarnings[] = Languages::_("THM_ORGANIZER_ERROR_{$constant}_MISSING");
            }
        }

        $this->schedule->$name = date('Y-m-d', strtotime($value));

        return;
    }

    /**
     * Validates a text attribute
     *
     * @param string $name     the attribute name
     * @param string $value    the attribute value
     * @param string $constant the unique text constant fragment
     * @param string $severity the severity of the item being inspected
     * @param string $regex    the regex to check the text against
     *
     * @return bool false if blocking errors were found, otherwise true
     */
    private function validateTextAttribute($name, $value, $constant, $severity = 'error', $regex = '')
    {
        if (empty($value)) {
            if ($severity == 'error') {
                $this->scheduleErrors[] = Languages::_("THM_ORGANIZER_ERROR_{$constant}_MISSING");

                return false;
            }

            if ($severity == 'warning') {
                $this->scheduleWarnings[] = Languages::_("THM_ORGANIZER_ERROR_{$constant}_MISSING");
            }
        }

        if (!empty($regex) and preg_match($regex, $value)) {
            if ($severity == 'error') {
                $this->scheduleErrors[] = Languages::_("THM_ORGANIZER_ERROR_{$constant}_INVALID");

                return false;
            }

            if ($severity == 'warning') {
                $this->scheduleWarnings[] = Languages::_("THM_ORGANIZER_ERROR_{$constant}_INVALID");
            }
        }

        $this->schedule->$name = $value;

        return true;
    }
}
