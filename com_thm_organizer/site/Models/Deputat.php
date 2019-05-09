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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Organizer\Helpers\Teachers;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which calculates the number of hours teachers taught individual lessons.
 */
class Deputat extends BaseModel
{
    public $scheduleID = null;

    public $schedule = null;

    public $reset = false;

    public $lessonValues = null;

    public $deputat = null;

    public $selected = [];

    public $teachers = [];

    public $irrelevant = [];

    public $departmentName = '';

    /**
     * Sets construction model properties
     *
     * @param array $config An array of configuration options (name, state, dbo, table_path, ignore_request).
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->setObjectProperties();

        if (!empty($this->schedule)) {
            $this->calculateDeputat();
            $this->teachers = $this->getTeacherNames();
            $this->setSelected();
            $this->restrictDeputat();
        }
    }

    /**
     * Calculates resource consumption from a schedule
     *
     * @return void sets the instance's lesson values variable
     */
    public function calculateDeputat()
    {
        $this->lessonValues = [];

        $startDate = (!empty($this->schedule->startDate)) ? $this->schedule->startDate : $this->schedule->syStartDate;
        $endDate   = (!empty($this->schedule->endDate)) ? $this->schedule->endDate : $this->schedule->syEndDate;

        foreach ($this->schedule->calendar as $day => $blocks) {
            if ($day < $startDate or $day > $endDate) {
                continue;
            }

            $this->resolveTime($this->schedule, $day, $blocks);
        }

        $teacherIDs = array_keys((array)$this->schedule->teachers);
        $this->checkOtherSchedules($teacherIDs, $startDate, $endDate);
        $this->convertLessonValues();
    }

    /**
     * Checks for the cross department deputat of teachers belonging to the department
     *
     * @param array  $teachers  the teachers listed in the original schedule
     * @param string $startDate the start date of the original schedule
     * @param string $endDate   the end date of the original schedule
     *
     * @return void  adds deputat to the lesson values array
     */
    private function checkOtherSchedules($teachers, $startDate, $endDate)
    {
        $schedulesIDs = $this->getPlausibleScheduleIDs($startDate, $endDate);
        if (empty($schedulesIDs)) {
            return;
        }

        foreach ($schedulesIDs as $scheduleID) {
            $schedule = $this->getSchedule($scheduleID);
            foreach ($schedule->calendar as $day => $blocks) {
                if ($day < $startDate or $day > $endDate) {
                    continue;
                }

                $this->resolveTime($schedule, $day, $blocks, $teachers);
            }

            unset($schedule);
        }
    }

    /**
     * Converts the individual lessons into the actual deputat
     *
     * @return void  sets the deputat object variable
     */
    private function convertLessonValues()
    {
        $this->deputat = [];

        // Ensures unique ids for block lessons
        $blockCounter = 1;
        $skipValues   = [];

        foreach ($this->lessonValues as $lessonID => $teacherIDs) {
            foreach ($teacherIDs as $teacherID => $lessonValues) {
                if (in_array($lessonID, $skipValues)) {
                    continue;
                }

                if (empty($this->deputat[$teacherID])) {
                    $this->deputat[$teacherID]         = [];
                    $this->deputat[$teacherID]['name'] = $lessonValues['teacherName'];
                }

                if ($lessonValues['type'] == 'tally') {
                    $this->setTallyDeputat($teacherID, $lessonValues);
                    unset($this->lessonValues[$lessonID]);
                    continue;
                }

                $subjectIndex = "{$lessonValues['subjectName']}-{$lessonValues['lessonType']}";

                // Current threshhold for a block lesson is 20 scholastic hours per week (10 periods)
                if (count($lessonValues['periods']) > 20) {
                    $blockIndex = "$subjectIndex-$blockCounter";
                    $this->setSummaryDeputat($teacherID, $lessonValues, $blockIndex);

                    // Block lessons are listed individually => no need to compare
                    unset($this->lessonValues[$lessonID]);
                    continue;
                }

                // The initial summary deputat
                $this->setSummaryDeputat($teacherID, $lessonValues, $subjectIndex);

                foreach ($this->lessonValues as $comparisonID => $compTeacherIDs) {
                    // The lesson should not be compared to itself
                    if ($lessonID == $comparisonID) {
                        continue;
                    }

                    $teacherTeaches = array_key_exists($teacherID, $compTeacherIDs);
                    $plausible      = $teacherTeaches ?
                        $this->isAggregationPlausible($lessonValues, $compTeacherIDs[$teacherID])
                        : false;
                    if ($plausible) {
                        $this->aggregate($teacherID, $subjectIndex, $compTeacherIDs[$teacherID]);

                        // Aggregated lessons should not be reiterated
                        $skipValues[] = $comparisonID;
                        continue;
                    }
                }
            }

            // Reduces nested iteration
            unset($this->lessonValues[$lessonID]);
        }

        /**
         * Compares the string values of two array indexes
         *
         * @param array $one the first array
         * @param array $two the second array
         *
         * @return int  see return value for strcmp
         */
        function cmp($one, $two)
        {
            return strcmp($one['name'], $two['name']);
        }

        usort($this->deputat, 'cmp');
    }

    /**
     * Gets all schedules in the database
     *
     * @return array An array with the schedules
     */
    public function getDepartmentSchedules()
    {
        return [];

        /**
         * TODO: get the departments for which the user has scheduling access, and their schedules.
         * TODO: get the names from the schedules from the department resource name and the planning period name
         * $canManageSchedules = $user->authorise('organizer.schedule', "com_thm_organizer.$resource.$resourceID");
         */

    }

    /**
     * Retrieves the unique pool ids associated
     *
     * @param object &$schedule  the schedule being processed
     * @param string  $lessonID  the id of the lesson
     * @param string  $teacherID the id of the teacher
     *
     * @return array the associated pool ids
     */
    private function getPools(&$schedule, $lessonID, $teacherID)
    {
        $previousPoolIDs = empty($this->lessonValues[$lessonID][$teacherID]['pools']) ?
            [] : $this->lessonValues[$lessonID][$teacherID]['pools'];

        $newPools = (array)$schedule->lessons->$lessonID->pools;
        foreach ($newPools as $pool => $delta) {
            if ($delta == 'removed') {
                unset($newPools[$pool]);
            }

            foreach ($this->irrelevant['pools'] as $irrelevant) {
                if (strpos($pool, $irrelevant) === 0) {
                    unset($newPools[$pool]);
                }
            }
        }

        $newPoolIDs = array_keys($newPools);
        asort($newPoolIDs);

        return array_unique(array_merge($previousPoolIDs, $newPoolIDs));
    }

    /**
     * Gets the rate at which lessons are converted to scholastic weekly hours
     *
     * @param string $subjectName the 'subject' name
     *
     * @return float|int  the conversion rate
     */
    private function getRate($subjectName)
    {
        $params = OrganizerHelper::getParams();
        if ($subjectName == 'Betreuung von Bachelorarbeiten') {
            return floatval('0.' . $params->get('bachelor_value', 25));
        }

        if ($subjectName == 'Betreuung von Diplomarbeiten') {
            return floatval('0.' . $params->get('master_value', 50));
        }

        if ($subjectName == 'Betreuung von Masterarbeiten') {
            return floatval('0.' . $params->get('master_value', 50));
        }

        return 1;
    }

    /**
     * Creates a concatenated subject name from the relevant subject names for the lesson
     *
     * @param object &$schedule the schedule being processed
     * @param string  $lessonID the id of the lesson
     *
     * @return string  the concatenated name of the subject(s)
     */
    private function getSubjectName(&$schedule, $lessonID)
    {
        $subjects = (array)$schedule->lessons->$lessonID->subjects;
        foreach ($subjects as $subject => $delta) {
            if ($delta == 'removed') {
                unset($subjects[$subject]);
                continue;
            }

            if (strpos($subject, 'KOL.B') !== false) {
                return 'Betreuung von Bachelorarbeiten';
            }

            if (strpos($subject, 'KOL.D') !== false) {
                return 'Betreuung von Diplomarbeiten';
            }

            if (strpos($subject, 'KOL.M') !== false) {
                return 'Betreuung von Masterarbeiten';
            }

            $subjects[$subject] = $schedule->subjects->$subject->name;
        }

        return implode('/', $subjects);
    }

    /**
     * Checks whether the lesson type is relevant
     *
     * @param object &$schedule the schedule being processed
     * @param string  $lessonID the id of the lesson
     *
     * @return mixed  string type if relevant, otherwise false
     */
    private function getType(&$schedule, $lessonID)
    {
        $lessonType = empty($schedule->lessons->$lessonID->method) ?
            '' : $schedule->lessons->$lessonID->method;

        if (empty($lessonType)) {
            return '';
        }

        if (in_array($lessonType, $this->irrelevant['methods'])) {
            return false;
        }

        return $lessonType;
    }

    /**
     * Checks whether the subject is relevant
     *
     * @param object &$schedule the schedule being processed
     * @param string  $lessonID the id of the lesson
     *
     * @return bool  true if relevant, otherwise false
     */
    private function isSubjectRelevant(&$schedule, $lessonID)
    {
        $subjects = (array)$schedule->lessons->$lessonID->subjects;
        foreach ($subjects as $subject => $delta) {
            if ($delta == 'removed') {
                continue;
            }

            foreach ($this->irrelevant['subjects'] as $prefix) {
                if (strpos($subject, $prefix) !== false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks whether the lesson should be tallied instead of summarized. (Oral exams or colloquia)
     *
     * @param object &$schedule the schedule being processed
     * @param string  $lessonID the id of the lesson
     *
     * @return bool  true if the lesson should be tallied instead of summarized, otherwise false
     */
    private function isTallied(&$schedule, $lessonID)
    {
        $subjects = $schedule->lessons->$lessonID->subjects;
        foreach ($subjects as $subjectID => $delta) {
            if ($delta != 'removed' and strpos($subjectID, 'KOL.') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets consumption by instance (block + lesson)
     *
     * @param object &$schedule the schedule being processed
     * @param string  $day      the day being iterated
     * @param object &$blocks   the blocks of the date being iterated
     * @param array  &$teachers teachers to compare against if the schedule is not the original
     *
     * @return void
     */
    private function resolveTime(&$schedule, $day, &$blocks, &$teachers = null)
    {
        $seconds = 2700;
        foreach ($blocks as $blockNumber => $blockLessons) {
            foreach ($blockLessons as $lessonID => $lessonValues) {
                // The lesson is no longer relevant
                if (isset($lessonValues->delta) and $lessonValues->delta == 'removed') {
                    continue;
                }

                // Calculate the scholastic hours (45 minutes)
                $gridBlock = $schedule->periods->{$schedule->lessons->$lessonID->grid}->$blockNumber;
                $startTime = $gridBlock->startTime;
                $startDT   = strtotime(substr($startTime, 0, 2) . ':' . substr($startTime, 2, 2) . ':00');
                $endTime   = $gridBlock->endTime;
                $endDT     = strtotime(substr($endTime, 0, 2) . ':' . substr($endTime, 2, 2) . ':00');
                $hours     = ($endDT - $startDT) / $seconds;

                $this->setDeputatByInstance($schedule, $day, $blockNumber, $lessonID, $hours, $teachers);
            }
        }
    }

    /**
     * Sets object properties
     *
     * @return void
     */
    private function setObjectProperties()
    {
        $this->params = OrganizerHelper::getParams();;
        $departmentID = $this->params->get('departmentID', 0);
        if (!empty($departmentID)) {
            $this->setDepartmentName($departmentID);
        }

        $input                        = OrganizerHelper::getInput();
        $this->reset                  = $input->getBool('reset', false);
        $this->selected               = [];
        $this->teachers               = [];
        $this->irrelevant['methods']  = ['KLA', 'SIT', 'PRÜ', 'SHU', 'VER', 'IVR', 'VRT', 'VSM', 'TAG'];
        $this->irrelevant['teachers'] = ['NN.', 'DIV.', 'FS.', 'TUTOR.', 'SW'];
        $this->irrelevant['pools']    = ['TERMINE.'];
        $this->irrelevant['subjects'] = ['NN.'];
        $this->setSchedule();
    }

    /**
     * Resolves the department id to its name
     *
     * @param int $departmentID the id of the department
     *
     * @return void  sets the object variable $departmentName on success
     */
    private function setDepartmentName($departmentID)
    {
        $shortTag = Languages::getShortTag();

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("short_name_$shortTag")->from('#__thm_organizer_departments')->where("id = '$departmentID'");
        $dbo->setQuery($query);

        $departmentName = OrganizerHelper::executeQuery('loadResult');

        if (empty($departmentName)) {
            return;
        }

        $this->departmentName = Languages::_('THM_ORGANIZER_DEPARTMENT') . ' ' . $departmentName;
    }

    /**
     * Sets the pertinent deputat information
     *
     * @param object &$schedule    the schedule being processed
     * @param string  $day         the day being iterated
     * @param int     $blockNumber the block number being iterated
     * @param string  $lessonID    the lesson being iterated
     * @param string  $teacherID   the teacher being iterated
     * @param int     $hours       the number of school hours for the lesson
     *
     * @return void  sets object values
     */
    private function setDeputat(&$schedule, $day, $blockNumber, $lessonID, $teacherID, $hours = 0)
    {
        $subjectIsRelevant = $this->isSubjectRelevant($schedule, $lessonID);
        $lessonType        = $this->getType($schedule, $lessonID);

        $invalidLesson = (!$subjectIsRelevant or $lessonType === false);
        if ($invalidLesson) {
            return;
        }

        if (empty($this->lessonValues[$lessonID])) {
            $this->lessonValues[$lessonID] = [];
        }

        $this->setLessonTeacher($schedule, $lessonID, $teacherID);

        // Tallied items have flat payment values and are correspondingly not tracked as accurately.
        $isTallied = $this->isTallied($schedule, $lessonID);
        if ($isTallied) {
            $this->lessonValues[$lessonID][$teacherID]['type'] = 'tally';

            return;
        }

        $pools = $this->getPools($schedule, $lessonID, $teacherID);
        if (empty($pools)) {
            unset($this->lessonValues[$lessonID]);

            return;
        }

        $this->lessonValues[$lessonID][$teacherID]['type']       = 'summary';
        $this->lessonValues[$lessonID][$teacherID]['lessonType'] = $lessonType;
        $this->lessonValues[$lessonID][$teacherID]['pools']      = $pools;
        if (!isset($this->lessonValues[$lessonID][$teacherID]['periods'])) {
            $this->lessonValues[$lessonID][$teacherID]['periods'] = [];
        }

        if (!isset($this->lessonValues[$lessonID][$teacherID]['startDate'])) {
            $this->lessonValues[$lessonID][$teacherID]['startDate'] = Dates::formatDate($day);
        }

        $DOWConstant  = strtoupper(date('l', strtotime($day)));
        $weekday      = Languages::_($DOWConstant);
        $plannedBlock = "$weekday-$blockNumber";
        if (!array_key_exists($plannedBlock, $this->lessonValues[$lessonID][$teacherID]['periods'])) {
            $this->lessonValues[$lessonID][$teacherID]['periods'][$plannedBlock] = [];
        }

        if (!array_key_exists($day, $this->lessonValues[$lessonID][$teacherID]['periods'][$plannedBlock])) {
            $this->lessonValues[$lessonID][$teacherID]['periods'][$plannedBlock][$day] = $hours;
        }

        $this->lessonValues[$lessonID][$teacherID]['endDate'] = Dates::formatDate($day);

        return;
    }

    /**
     * Iterates the lesson associated pools for the purpose of teacher consumption
     *
     * @param object &$schedule    the schedule being processed
     * @param string  $day         the day being iterated
     * @param int     $blockNumber the block number being iterated
     * @param string  $lessonID    the lesson ID
     * @param int     $hours       the number of school hours for the lesson
     * @param array  &$teachers    teachers to compare against if the schedule is not the original
     *
     * @return void
     */
    private function setDeputatByInstance(&$schedule, $day, $blockNumber, $lessonID, $hours, &$teachers = null)
    {
        $scheduleTeachers = $schedule->lessons->$lessonID->teachers;
        foreach ($scheduleTeachers as $teacherID => $teacherDelta) {
            if ($teacherDelta == 'removed') {
                continue;
            }

            /**
             * The function was called during the iteration of the schedule of another department. Only the teachers
             * from the original are relevant.
             */
            if (!empty($teachers) and !in_array($teacherID, $teachers)) {
                continue;
            }

            $irrelevant = false;
            foreach ($this->irrelevant['teachers'] as $prefix) {
                if (strpos($teacherID, $prefix) === 0) {
                    $irrelevant = true;
                    break;
                }
            }

            if (!$irrelevant) {
                $this->setDeputat($schedule, $day, $blockNumber, $lessonID, $teacherID, $hours);
            }
        }
    }

    /**
     * Associates a teacher with a given lesson
     *
     * @param object &$schedule  the schedule being processed
     * @param string  $lessonID  the id of the lesson
     * @param string  $teacherID the id of the teacher
     *
     * @return void  sets object variables
     */
    private function setLessonTeacher(&$schedule, $lessonID, $teacherID)
    {
        // Check for existing association
        if (empty($this->lessonValues[$lessonID][$teacherID])) {
            $this->lessonValues[$lessonID][$teacherID] = [];
            $this->lessonValues[$lessonID][$teacherID]['teacherName']
                                                       = Teachers::getLNFName($schedule->teachers->$teacherID);

            $this->lessonValues[$lessonID][$teacherID]['subjectName']
                = $this->getSubjectName($schedule, $lessonID);
        }
    }

    /**
     * Method to set a schedule by its id from the database
     *
     * @return void sets the instance's schedule variable
     */
    public function setSchedule()
    {
        $this->scheduleID = OrganizerHelper::getInput()->getInt('scheduleID', 0);
        $query            = $this->_db->getQuery(true);
        $query->select('schedule');
        $query->from('#__thm_organizer_schedules');
        $query->where("id = '$this->scheduleID'");
        $this->_db->setQuery($query);

        $result = OrganizerHelper::executeQuery('loadResult');
        if (empty($result)) {
            $this->schedule = null;
        } else {
            $this->schedule = json_decode($result);
        }
    }

    /**
     * Sets the values for tallied lessons
     *
     * @param string  $teacherID    the teacher's id
     * @param array  &$lessonValues the values for the lesson being iterated
     *
     * @return void  sets values in the object variable $deputat
     */
    private function setTallyDeputat($teacherID, &$lessonValues)
    {
        if (empty($this->deputat[$teacherID]['tally'])) {
            $this->deputat[$teacherID]['tally'] = [];
        }

        $subjectName = $lessonValues['subjectName'];
        if (empty($this->deputat[$teacherID]['tally'][$subjectName])) {
            $this->deputat[$teacherID]['tally'][$subjectName] = [];
        }

        $this->deputat[$teacherID]['tally'][$subjectName]['rate'] = $this->getRate($subjectName);
        if (empty($this->deputat[$teacherID]['tally'][$subjectName]['count'])) {
            $this->deputat[$teacherID]['tally'][$subjectName]['count'] = 1;

            return;
        }

        $this->deputat[$teacherID]['tally'][$subjectName]['count']++;

        return;
    }

    /**
     * Sets the values for summarized lessons
     *
     * @param string  $teacherID    the teacher's id
     * @param array  &$lessonValues the values for the lesson being iterated
     * @param string  $index        the index to be used for the lesson
     *
     * @return void  sets values in the object variable $deputat
     */
    private function setSummaryDeputat($teacherID, &$lessonValues, $index)
    {
        if (empty($this->deputat[$teacherID]['summary'])) {
            $this->deputat[$teacherID]['summary'] = [];
        }

        $this->deputat[$teacherID]['summary'][$index]              = [];
        $this->deputat[$teacherID]['summary'][$index]['name']      = $lessonValues['subjectName'];
        $this->deputat[$teacherID]['summary'][$index]['type']      = $lessonValues['lessonType'];
        $this->deputat[$teacherID]['summary'][$index]['pools']     = $lessonValues['pools'];
        $this->deputat[$teacherID]['summary'][$index]['periods']   = $lessonValues['periods'];
        $this->deputat[$teacherID]['summary'][$index]['hours']     = $this->getSummaryHours($lessonValues['periods']);
        $this->deputat[$teacherID]['summary'][$index]['startDate'] = $lessonValues['startDate'];
        $this->deputat[$teacherID]['summary'][$index]['endDate']   = $lessonValues['endDate'];
        uksort($this->deputat[$teacherID]['summary'][$index]['periods'], 'self::periodSort');
        ksort($this->deputat[$teacherID]['summary']);

        return;
    }

    /**
     * Sorts two period keys. (Callable)
     *
     * @param string $keyOne the first key
     * @param string $keyTwo the second key
     *
     * @return int
     *
     * @SuppressWarnings(PMD.UnusedPrivateMethod)
     */
    private static function periodSort($keyOne, $keyTwo)
    {
        list($dayOne, $blockOne) = explode('-', $keyOne);
        $dayOne = self::getDayNumber($dayOne);
        list($dayTwo, $blockTwo) = explode('-', $keyTwo);
        $dayTwo = self::getDayNumber($dayTwo);

        if ($dayOne < $dayTwo) {
            return -1;
        }

        if ($dayOne > $dayTwo) {
            return 1;
        }

        if ($blockOne < $blockTwo) {
            return -1;
        }

        if ($blockOne > $blockTwo) {
            return 1;
        }

        return 0;
    }

    /**
     * Converts day names to the their order number
     *
     * @param string $dayName the name of the day
     *
     * @return int  the number of the day
     */
    private static function getDayNumber($dayName)
    {
        switch ($dayName) {
            case 'Monday':
            case 'Montag':
                return 1;
            case 'Tuesday':
            case 'Dienstag':
                return 2;
            case 'Wednesday':
            case 'Mittwoch':
                return 3;
            case 'Thursday':
            case 'Donnerstag':
                return 4;
            case 'Friday':
            case 'Freitag':
                return 5;
            case 'Saturday':
            case 'Samstag':
                return 6;
            case 'Sunday':
            case 'Sonntag':
                return 7;
        }

        // Should never occur
        return 1;
    }

    /**
     * Gets the total hours from an array with the structure period > date > hours
     *
     * @param array $periods the periods for the lesson
     *
     * @return int  the sum of the lesson's hours
     */
    private function getSummaryHours($periods)
    {
        $sum = 0;
        foreach ($periods as $period) {
            $sum += array_sum($period);
        }

        return $sum;
    }

    /**
     * Checks lesson values to determine the plausibility of aggregation
     *
     * @param array $lessonValues     the values for the lesson being iterated in the outer loop
     * @param array $comparisonValues the values for the lesson being iterated in the inner loop
     *
     * @return bool  true if the lessons are a plausible match, otherwise false
     */
    private function isAggregationPlausible($lessonValues, $comparisonValues)
    {
        // Tallied and block lessons are handled differently
        if ($comparisonValues['type'] == 'tally' or count($comparisonValues['periods']) > 20) {
            return false;
        }

        $subjectsPlausible = $lessonValues['subjectName'] == $comparisonValues['subjectName'];
        $typesPlausible    = $lessonValues['lessonType'] == $comparisonValues['lessonType'];

        return ($subjectsPlausible and $typesPlausible);
    }

    /**
     * Aggregates similar lessons to a single output
     *
     * @param string $teacherID    the id of the teacher
     * @param string $subjectIndex the index of this group of lessons in the array
     * @param array  $aggValues    the values to be aggregated
     *
     * @return void  alters object variables
     */
    private function aggregate($teacherID, $subjectIndex, $aggValues)
    {
        $aggregatedPools = array_unique(array_merge(
            $this->deputat[$teacherID]['summary'][$subjectIndex]['pools'],
            $aggValues['pools']
        ));

        $this->deputat[$teacherID]['summary'][$subjectIndex]['pools'] = $aggregatedPools;

        $aggregatedPeriods = array_merge_recursive(
            $this->deputat[$teacherID]['summary'][$subjectIndex]['periods'],
            $aggValues['periods']
        );

        uksort($aggregatedPeriods, 'self::periodSort');
        $this->deputat[$teacherID]['summary'][$subjectIndex]['periods'] = $aggregatedPeriods;
        $this->deputat[$teacherID]['summary'][$subjectIndex]['hours']   = $this->getSummaryHours($aggregatedPeriods);
    }

    /**
     * Gets a list of teacher names
     *
     * @return array  a list of resource names
     */
    public function getTeacherNames()
    {
        $teachers = [];
        foreach ($this->deputat as $teacherID => $deputat) {
            $displaySummary = !empty($deputat['summary']);
            $displayTally   = !empty($deputat['tally']);
            $display        = ($displaySummary or $displayTally);
            if (!$display) {
                unset($this->deputat[$teacherID]);
                continue;
            }

            $teachers[$teacherID] = $deputat['name'];
        }

        asort($teachers);

        return $teachers;
    }

    /**
     * Gets the list of selected teachers
     *
     * @return void
     */
    private function setSelected()
    {
        $default  = ['*'];
        $selected = OrganizerHelper::getInput()->get('teachers', $default, 'array');

        // Returns a hard false if value is not in array
        $allSelected = array_search('*', $selected);

        // Normal indexes and the default (all) were selected
        $unsetDefault = (count($selected) > 1 and $allSelected !== false);
        if ($unsetDefault) {
            unset($selected[$allSelected]);
        }

        $this->selected = $selected;
    }

    /**
     * Restricts the displayed deputat to the selected teachers
     *
     * @return void  unsets deputat indexes
     */
    private function restrictDeputat()
    {
        // Returns a hard false if value is not in array
        $allSelected = array_search('*', $this->selected);
        if ($allSelected !== false) {
            return;
        }

        $indexes = array_keys($this->deputat);
        foreach ($indexes as $index) {
            if (!in_array($index, $this->selected)) {
                unset($this->deputat[$index]);
            }
        }
    }

    /**
     * Checks the database for plausible schedules
     *
     * @param string $startDate the start date of the original schedule
     * @param string $endDate   the end date of the original schedule
     *
     * @return mixed  array on success, otherwise null
     */
    private function getPlausibleScheduleIDs($startDate, $endDate)
    {
        $query = $this->_db->getQuery(true);
        $query->select('id');
        $query->from('#__thm_organizer_schedules');
        $query->where("startDate = '$startDate'");
        $query->where("endDate = '$endDate'");
        $query->where("active = '1'");
        $this->_db->setQuery($query);

        return OrganizerHelper::executeQuery('loadColumn', []);
    }

    /**
     * Checks the database for plausible schedules
     *
     * @param int $scheduleID the id of the schedule to be iterated
     *
     * @return mixed  array on success, otherwise null
     */
    private function getSchedule($scheduleID)
    {
        $query = $this->_db->getQuery(true);
        $query->select('schedule');
        $query->from('#__thm_organizer_schedules');
        $query->where("id = '$scheduleID'");
        $this->_db->setQuery($query);

        $result = OrganizerHelper::executeQuery('loadResult');

        return empty($result) ? null : json_decode($result);
    }
}
