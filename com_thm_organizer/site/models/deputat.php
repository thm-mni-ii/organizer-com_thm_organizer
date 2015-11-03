<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelDeputat
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
require_once JPATH_COMPONENT . '/helpers/teacher.php';

/**
 * Class THM_OrganizerModelConsumption for component com_thm_organizer
 * Class provides methods to get the neccessary data to display a schedule consumption
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelDeputat extends JModelLegacy
{
    public $scheduleID = null;

    public $schedule = null;

    public $reset = false;

    public $lessonValues = null;

    public $deputat = null;

    public $selected = array();

    public $teachers = array();

    public $irrelevant = array();

    public $departmentName = '';


    /**
     * Sets construction model properties
     *
     * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->setObjectProperties();

        if (!empty($this->schedule))
        {
            $this->calculateDeputat();
            $this->teachers = $this->getTeacherNames();
            $this->setSelected('teachers');
            $this->restrictDeputat();
        }
    }

    /**
     * Sets object properties
     *
     * @return  void
     */
    private function setObjectProperties()
    {
        $this->params = JFactory::getApplication()->getParams();
        $departmentID = $this->params->get('departmentID', 0);
        if (!empty($departmentID))
        {
            $this->setDepartmentName($departmentID);
        }
        $input = JFactory::getApplication()->input;
        $this->reset = $input->getBool('reset', false);
        $this->selected = array();
        $this->teachers = array();
        $this->irrelevant['methods'] = array('K', 'SIT', 'PRÜ');
        $this->irrelevant['teachers'] = array('NN.', 'DIV.', 'FS.', 'FS.', 'TUTOR.', 'SW');
        $this->irrelevant['pools'] = array('TERMINE.');
        $this->irrelevant['subjects'] = array('NN.');
        $this->setSchedule();
    }

    /**
     * Resolves the department id to its name
     *
     * @param   int  $departmentID  the id of the department
     *
     * @return  void  sets the object variable $departmentName on success
     */
    private function setDepartmentName($departmentID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('short_name')->from('#__thm_organizer_departments')->where("id = '$departmentID'");
        $dbo->setQuery((string) $query);
        try
        {
            $departmentName = JText::_('COM_THM_ORGANIZER_DEPARTMENT');
            $this->departmentName = $departmentName. ' ' . $dbo->loadResult();
            return;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
            return;
        }
    }

    /**
     * Gets all schedules in the database
     *
     * @return array An array with the schedules
     */
    public function getDepartmentSchedules()
    {
        $query = $this->_db->getQuery(true);
        $columns = array('departmentname', 'semestername');
        $select = 'id, ' . $query->concatenate($columns, ' - ') . ' AS name';
        $query->select($select);
        $query->from("#__thm_organizer_schedules");
        $query->where("active = '1'");
        $departmentID = $this->params->get('departmentID', 0);
        if (!empty($departmentID))
        {
            $query->where("departmentID = '$departmentID'");
        }
        $query->order('name');

        $this->_db->setQuery((string) $query);
        try 
        {
            $results = $this->_db->loadAssocList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return array();
        }

        if (empty($results))
        {
            return array();
        }

        foreach ($results as $key => $value)
        {
            $canManage = THM_OrganizerHelperComponent::allowResourceManage('schedule', $value['id']);
            if (!$canManage)
            {
                unset($results[$key]);
            }
        }
        return $results;
    }
    
    /**
     * Method to set a schedule by its id from the database
     *
     * @return  object  an schedule object on success, otherwise an empty object
     */
    public function setSchedule()
    {        
        $this->scheduleID = JFactory::getApplication()->input->getInt('scheduleID', 0);
        $query = $this->_db->getQuery(true);
        $query->select('schedule');
        $query->from("#__thm_organizer_schedules");
        $query->where("id = '$this->scheduleID'");
        $this->_db->setQuery((string) $query);
        try
        {
            $result = $this->_db->loadResult();
            $this->schedule = json_decode($result);
        }
        catch (Exception $exception)
        {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');
            $this->schedule = null;
        }
    }
    
    /**
     * Calculates resource consumption from a schedule
     *
     * @return  object  an object modeling resource consumption
     */
    public function calculateDeputat()
    {
        $this->lessonValues = array();

        $startdate =  (!empty($this->schedule->termStartDate))? $this->schedule->termStartDate : $this->schedule->startdate;
        $enddate =  (!empty($this->schedule->termEndDate))? $this->schedule->termEndDate : $this->schedule->enddate;

        foreach ($this->schedule->calendar as $day => $blocks)
        {
            if ($day < $startdate OR $day > $enddate)
            {
                continue;
            }
            $this->resolveTime($this->schedule, $day, $blocks);
        }
        $teacherIDs = array_keys((array)$this->schedule->teachers);
        $this->checkOtherSchedules($teacherIDs, $startdate, $enddate);
        $this->convertLessonValues();
    }

    /**
     * Sets consumption by instance (block + lesson)
     *
     * @param   object  &$schedule  the schedule being processed
     * @param   string  $day        the day being iterated
     * @param   object  &$blocks    the blocks of the date being iterated
     * @param   array   &$teachers  teachers to compare against if the schedule is not the original
     * 
     * @return  void
     */
    private function resolveTime(&$schedule, $day, &$blocks, &$teachers = null)
    {
        $seconds = 2700;
        foreach ($blocks as $blockNumber => $blockLessons)
        {
            foreach ($blockLessons as $lessonID => $lessonValues)
            {
                // The lesson is no longer relevant
                if (isset($lessonValues->delta) AND $lessonValues->delta == 'removed')
                {
                    continue;
                }

                // Calculate the scholastic hours (45 minutes)
                $gridBlock =$schedule->periods->{$schedule->lessons->$lessonID->grid}->$blockNumber;
                $startTime = $gridBlock->starttime;
                $startDT = strtotime(substr($startTime, 0, 2) . ':' . substr($startTime, 2, 2) . ':00');
                $endTime = $gridBlock->endtime;
                $endDT = strtotime(substr($endTime, 0, 2) . ':' . substr($endTime, 2, 2) . ':00');
                $hours = ($endDT - $startDT) / $seconds;

                $this->setDeputatByInstance($schedule, $day, $blockNumber, $lessonID, $hours, $teachers);
            }
        }
    }

    /**
     * Iterates the lesson associated pools for the purpose of teacher consumption
     *
     * @param   object  &$schedule    the schedule being processed
     * @param   string  $day          the day being iterated
     * @param   int     $blockNumber  the block number being iterated
     * @param   string  $lessonID     the lesson ID
     * @param   int     $hours        the number of school hours for the lesson
     * @param   array   &$teachers    teachers to compare against if the schedule is not the original
     *
     * @return  void
     */
    private function setDeputatByInstance(&$schedule, $day, $blockNumber, $lessonID, $hours, &$teachers = null)
    {
        $scheduleTeachers = $schedule->lessons->$lessonID->teachers;
        foreach ($scheduleTeachers as $teacherID => $teacherDelta)
        {
            if ($teacherDelta == 'removed')
            {
                continue;
            }

            /**
             * The function was called during the iteration of the schedule of another department. Only the teachers
             * from the original are relevant.
             */
            if (!empty($teachers) AND !in_array($teacherID, $teachers))
            {
                continue;
            }

            $irrelevant = false;
            foreach ($this->irrelevant['teachers'] AS $prefix)
            {
                if (strpos($teacherID, $prefix) === 0)
                {
                    $irrelevant = true;
                    break;
                }
            }

            if (!$irrelevant)
            {
                $this->setDeputat($schedule, $day, $blockNumber, $lessonID, $teacherID, $hours);
            }
        }
    }

    /**
     * Sets the pertinent deputat information
     *
     * @param   object  &$schedule    the schedule being processed
     * @param   string  $day          the day being iterated
     * @param   int     $blockNumber  the block number being iterated
     * @param   string  $lessonID     the lesson being iterated
     * @param   string  $teacherID    the teacher being iterated
     * @param   int     $hours        the number of school hours for the lesson
     *
     * @return  void  sets object values
     */
    private function setDeputat(&$schedule, $day, $blockNumber, $lessonID, $teacherID, $hours = 0)
    {
        $subjectIsRelevant = $this->isRelevantSubject($schedule, $lessonID);
        if (!$subjectIsRelevant)
        {
            return;
        }

        if (empty($this->lessonValues[$lessonID]))
        {
            $this->lessonValues[$lessonID] = array();
            $this->lessonValues[$lessonID]['teacherID'] = $teacherID;
            $this->lessonValues[$lessonID]['teacherName'] = THM_OrganizerHelperTeacher::getLNFName($schedule->teachers->$teacherID);
            $this->lessonValues[$lessonID]['subjectName'] = $this->getSubjectName($schedule, $lessonID);
        }

        // Tallied items have flat payment values and are correspondingly not tracked as accurately
        $isTallied = $this->isTallied($schedule, $lessonID);
        if ($isTallied)
        {
            $this->lessonValues[$lessonID]['type'] = 'tally';
            return;
        }

        $lessonType = $schedule->lessons->$lessonID->description;

        // Some 'lesson' types are irrelevant for deputat calculation;
        if (in_array($lessonType, $this->irrelevant['methods']))
        {
            unset($this->lessonValues[$lessonID]);
            return;
        }

        $DOWConstant = strtoupper(date('l', strtotime($day)));
        $weekday = JText::_($DOWConstant);

        $previousPools = empty($this->lessonValues[$lessonID]['pools'])?
            array() : $this->lessonValues[$lessonID]['pools'];
        $mergedPools = array_merge($previousPools, $this->getPools($schedule, $lessonID));
        $pools = array_unique($mergedPools);
        $this->lessonValues[$lessonID]['type'] = 'summary';
        $this->lessonValues[$lessonID]['lessonType'] = $lessonType;
        $this->lessonValues[$lessonID]['pools'] = $pools;
        if (!isset($this->lessonValues[$lessonID]['periods']))
        {
            $this->lessonValues[$lessonID]['periods'] = array();
        }
        if (!isset($this->lessonValues[$lessonID]['startdate']))
        {
            $this->lessonValues[$lessonID]['startdate'] = THM_OrganizerHelperComponent::formatDate($day);
        }

        $plannedBlock = "$weekday-$blockNumber";
        if (!array_key_exists($plannedBlock, $this->lessonValues[$lessonID]['periods']))
        {
            $this->lessonValues[$lessonID]['periods'][$plannedBlock] = array();
        }
        if (!array_key_exists($day, $this->lessonValues[$lessonID]['periods'][$plannedBlock]))
        {
            $this->lessonValues[$lessonID]['periods'][$plannedBlock][$day] = $hours;
        }

        $this->lessonValues[$lessonID]['enddate'] = THM_OrganizerHelperComponent::formatDate($day);

        return;
    }

    /**
     * Checks whether the subject is relevant
     *
     * @param   object  &$schedule    the schedule being processed
     * @param   string  $lessonID  the id of the lesson
     *
     * @return  bool  true if relevant, otherwise false
     */
    private function isRelevantSubject(&$schedule, $lessonID)
    {
        $subjects = (array) $schedule->lessons->$lessonID->subjects;
        foreach ($subjects AS $subject => $delta)
        {
            if ($delta == 'removed')
            {
                continue;
            }
            foreach ($this->irrelevant['subjects'] AS $prefix)
            {
                if (strpos($subject, $prefix) !== false)
                {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Creates a concatenated subject name from the relevant subject names for the lesson
     *
     * @param   object  &$schedule  the schedule being processed
     * @param   string  $lessonID   the id of the lesson
     *
     * @return  string  the concatenated name of the subject(s)
     */
    private function getSubjectName(&$schedule, $lessonID)
    {
        $subjects = (array) $schedule->lessons->$lessonID->subjects;
        foreach ($subjects AS $subject => $delta)
        {
            if ($delta == 'removed')
            {
                unset($subjects[$subject]);
                continue;
            }
            if (strpos($subject, 'KOL.B') !== false)
            {
                return 'Betreuung von Bachelorarbeiten';
            }
            if (strpos($subject, 'KOL.D') !== false)
            {
                return 'Betreuung von Diplomarbeiten';
            }
            if (strpos($subject, 'KOL.M') !== false)
            {
                return 'Betreuung von Masterarbeiten';
            }
            $subjects[$subject] = $schedule->subjects->{$subject}->longname;
        }
        return implode('/', $subjects);
    }

    /**
     * Creates a concatenated pool name from the relevant pool keys for the lesson
     *
     * @param   object  &$schedule  the schedule being processed
     * @param   string  $lessonID  the id of the lesson
     *
     * @return  string  the concatenated name of the subject(s)
     */
    private function getPools(&$schedule, $lessonID)
    {
        $pools = (array) $schedule->lessons->$lessonID->pools;
        foreach ($pools AS $pool => $delta)
        {
            if ($delta == 'removed')
            {
                unset($pools[$pool]);
            }
        }
        $poolIDs = array_keys($pools);
        asort($poolIDs);
        return $poolIDs;
    }

    /**
     * Checks whether the lesson should be tallied instead of summarized. (Oral exams or colloquia)
     *
     * @param   object  &$schedule  the schedule being processed
     * @param   string  $lessonID  the id of the lesson
     *
     * @return  bool  true if the lesson should be tallied instead of summarized, otherwise false
     */
    private function isTallied(&$schedule, $lessonID)
    {
        $subjects = $schedule->lessons->$lessonID->subjects;
        foreach ($subjects as $subjectID => $delta)
        {
            if ($delta != 'removed' AND strpos($subjectID, 'KOL.') !== false)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Converts the individual lessons into the actual deputat
     *
     * @return  void  sets the deputat object variable
     */
    private function convertLessonValues()
    {
        $this->deputat = array();

        // Ensures unique ids for block lessons
        $blockCounter = 1;
        $skipValues = array();

        foreach ($this->lessonValues as $lessonID => $lessonValues)
        {
            if (in_array($lessonID, $skipValues))
            {
                continue;
            }

            $teacherID = $lessonValues['teacherID'];
            if (empty($this->deputat[$teacherID]))
            {
                $this->deputat[$teacherID] = array();
                $this->deputat[$teacherID]['name'] = $lessonValues['teacherName'];
            }

            if ($lessonValues['type'] == 'tally')
            {
                $this->setTallyDeputat($lessonValues);
                unset($this->lessonValues[$lessonID]);
                continue;
            }

            $subjectIndex = "{$lessonValues['subjectName']}-{$lessonValues['lessonType']}";

            // Current threshhold for a block lesson is 20 scholastic hours per week (10 periods)
            if (count($lessonValues['periods']) > 20)
            {
                $blockIndex = "$subjectIndex-$blockCounter";
                $this->setSummaryDeputat($lessonValues, $blockIndex);

                // Block lessons are listed individually => no need to compare
                unset($this->lessonValues[$lessonID]);
                continue;
            }

            // The initial summary deputat
            $this->setSummaryDeputat($lessonValues, $subjectIndex);

            foreach ($this->lessonValues as $comparisonID => $comparisonValues)
            {
                // The lesson should not be compared to itself
                if ($lessonID == $comparisonID)
                {
                    continue;
                }

                if ($this->isAggregationPlausible($lessonValues, $comparisonValues))
                {
                    $this->aggregate($teacherID, $subjectIndex, $comparisonValues);

                    // Aggregated lessons should not be reiterated
                    $skipValues[] = $comparisonID;
                    continue;
                }
            }

            // Reduces nested iteration
            unset($this->lessonValues[$lessonID]);
        }

        function cmp($a, $b)
        {
            return strcmp($a["name"], $b["name"]);
        }

        usort($this->deputat, "cmp");
    }

    /**
     * Sets the values for tallied lessons
     *
     * @param   array  &$lessonValues  the values for the lesson being iterated
     *
     * @return  void  sets values in the object variable $deputat
     */
    private function setTallyDeputat(&$lessonValues)
    {
        if (empty($this->deputat[$lessonValues['teacherID']]['tally']))
        {
            $this->deputat[$lessonValues['teacherID']]['tally'] = array();
        }
        $subjectName = $lessonValues['subjectName'];
        if (empty($this->deputat[$lessonValues['teacherID']]['tally'][$subjectName]))
        {
            $this->deputat[$lessonValues['teacherID']]['tally'][$subjectName] = array();
        }
        $this->deputat[$lessonValues['teacherID']]['tally'][$subjectName]['rate'] = $this->getRate($subjectName);
        if (empty($this->deputat[$lessonValues['teacherID']]['tally'][$subjectName]['count']))
        {
            $this->deputat[$lessonValues['teacherID']]['tally'][$subjectName]['count'] = 1;
            return;
        }
        $this->deputat[$lessonValues['teacherID']]['tally'][$subjectName]['count']++;
        return;
    }

    /**
     * Gets the rate at which lessons are converted to scholastic weekly hours
     *
     * @param   string  $subjectName  the 'subject' name
     *
     * @return  float|int  the conversion rate
     */
    private function getRate($subjectName)
    {
        $params = JFactory::getApplication()->getParams();
        if ($subjectName == 'Betreuung von Bachelorarbeiten')
        {
            return floatval('0.' . $params->get('bachelor_value', 25));
        }
        if ($subjectName == 'Betreuung von Diplomarbeiten')
        {
            return floatval('0.' . $params->get('master_value', 50));
        }
        if ($subjectName == 'Betreuung von Masterarbeiten')
        {
            return floatval('0.' . $params->get('master_value', 50));
        }
        return 1;
    }

    /**
     * Sets the values for summarized lessons
     *
     * @param   array   &$lessonValues  the values for the lesson being iterated
     * @param   string  $index          the index to be used for the lesson
     *
     * @return  void  sets values in the object variable $deputat
     */
    private function setSummaryDeputat(&$lessonValues, $index)
    {
        $teacherID = $lessonValues['teacherID'];
        if (empty($this->deputat[$lessonValues['teacherID']]['summary']))
        {
            $this->deputat[$teacherID]['summary'] = array();
        }
        $this->deputat[$teacherID]['summary'][$index] = array();
        $this->deputat[$teacherID]['summary'][$index]['name'] = $lessonValues['subjectName'];
        $this->deputat[$teacherID]['summary'][$index]['type'] = $lessonValues['lessonType'];
        $this->deputat[$teacherID]['summary'][$index]['pools'] = $lessonValues['pools'];
        $this->deputat[$teacherID]['summary'][$index]['periods'] = $lessonValues['periods'];
        $this->deputat[$teacherID]['summary'][$index]['hours'] = $this->getSummaryHours($lessonValues['periods']);
        $this->deputat[$teacherID]['summary'][$index]['startdate'] = $lessonValues['startdate'];
        $this->deputat[$teacherID]['summary'][$index]['enddate'] = $lessonValues['enddate'];
        uksort($this->deputat[$teacherID]['summary'][$index]['periods'], 'self::periodSort');
        ksort($this->deputat[$teacherID]['summary']);
        return;
    }

    /**
     * Sorts two period keys. (Callable)
     *
     * @param   string  $keyOne  the first key
     * @param   string  $keyTwo  the second key
     *
     * @return int
     */
    private static function periodSort($keyOne, $keyTwo)
    {
        list($dayOne, $blockOne) = explode('-', $keyOne);
        $dayOne = self::getDayNumber($dayOne);
        list($dayTwo, $blockTwo) = explode('-', $keyTwo);
        $dayTwo = self::getDayNumber($dayTwo);

        if ($dayOne < $dayTwo)
        {
            return -1;
        }

        if ($dayOne > $dayTwo)
        {
            return 1;
        }

        if ($blockOne < $blockTwo)
        {
            return -1;
        }

        if ($blockOne > $blockTwo)
        {
            return 1;
        }

        return 0;
    }

    /**
     * Converts day names to the their order number
     *
     * @param   string  $dayName  the name of the day
     *
     * @return  int  the number of the day
     */
    private static function getDayNumber($dayName)
    {
        switch($dayName)
        {
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
    }

    /**
     * Gets the total hours from an array with the structure period > date > hours
     *
     * @param   array  $periods  the periods for the lesson
     *
     * @return  int  the sum of the lesson's hours
     */
    private function getSummaryHours($periods)
    {
        $sum = 0;
        foreach ($periods AS $period)
        {
            $sum += array_sum($period);
        }
        return $sum;
    }

    /**
     * Checks lesson values to determine the plausibility of aggregation
     *
     * @param   array  $lessonValues      the values for the lesson being iterated in the outer loop
     * @param   array  $comparisonValues  the values for the lesson being iterated in the inner loop
     *
     * @return  bool  true if the lessons are a plausible match, otherwise false
     */
    private function isAggregationPlausible($lessonValues, $comparisonValues)
    {
        // Tallied and block lessons are handled differently
        if ($comparisonValues['type'] == 'tally' OR count($comparisonValues['periods']) > 20)
        {
            return false;
        }

        $teacherPlausible = $lessonValues['teacherID'] == $comparisonValues['teacherID'];
        $subjectsPlausible = $lessonValues['subjectName'] == $comparisonValues['subjectName'];
        $typesPlausible = $lessonValues['lessonType'] == $comparisonValues['lessonType'];
        return ($teacherPlausible AND $subjectsPlausible AND $typesPlausible);
    }

    /**
     * Aggregates similar lessons to a single output
     *
     * @param   string  $teacherID     the id of the teacher
     * @param   string  $subjectIndex  the index of this group of lessons in the array
     * @param   array   $aggValues     the values to be aggregated
     */
    private function aggregate($teacherID, $subjectIndex, $aggValues)
    {
        $aggregatedPools = array_merge($this->deputat[$teacherID]['summary'][$subjectIndex]['pools'], $aggValues['pools']);
        array_unique($aggregatedPools);
        $this->deputat[$teacherID]['summary'][$subjectIndex]['pools'] = $aggregatedPools;
        $aggregatedPeriods = array_merge_recursive($this->deputat[$teacherID]['summary'][$subjectIndex]['periods'], $aggValues['periods']);
        uksort($aggregatedPeriods, 'self::periodSort');
        $this->deputat[$teacherID]['summary'][$subjectIndex]['periods'] = $aggregatedPeriods;
        $this->deputat[$teacherID]['summary'][$subjectIndex]['hours'] = $this->getSummaryHours($aggregatedPeriods);
    }

    /**
     * Gets a list of teacher names
     *
     * @return  array  a list of resource names
     */
    public function getTeacherNames()
    {
        $teachers = array();
        foreach ($this->deputat AS $teacherID => $deputat)
        {
            $displaySummary = !empty($deputat['summary']);
            $displayTally = !empty($deputat['tally']);
            $display = ($displaySummary OR $displayTally);
            if (!$display)
            {
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
     * @return  void
     */
    private function setSelected()
    {
        $default = array('*');
        $selected = JFactory::getApplication()->input->get('teachers', $default, 'array');

        // Returns a hard false if value is not in array
        $allSelected = array_search('*', $selected);

        // Normal indexes and the default (all) were selected
        $unsetDefault = (count($selected) > 1 AND $allSelected !== false);
        if ($unsetDefault)
        {
            unset($selected[$allSelected]);
        }

        $this->selected = $selected;
    }

    /**
     * Restricts the displayed deputat to the selected teachers
     *
     * @return  void  unsets deputat indexes
     */
    private function restrictDeputat()
    {
        // Returns a hard false if value is not in array
        $allSelected = array_search('*', $this->selected);
        if ($allSelected !== false)
        {
            return;
        }

        $indexes = array_keys($this->deputat);
        foreach ($indexes AS $index)
        {
            if (!in_array($index, $this->selected))
            {
                unset($this->deputat[$index]);
            }
        }
    }

    /**
     * Checks for the cross department deputat of teachers belonging to the department
     *
     * @param   object  &$teachers  the teachers listed in the original schedule
     * @param   string  $startdate  the startdate of the original schedule
     * @param   string  $enddate    the enddate of the original schedule
     *
     * @return  void  adds deputat to the lesson values array
     */
    private function checkOtherSchedules($teachers, $startdate, $enddate)
    {
        $schedulesIDs = $this->getPlausibleScheduleIDs($startdate, $enddate);
        if (empty($schedulesIDs))
        {
            return;
        }

        foreach ($schedulesIDs AS $scheduleID)
        {
            $schedule = $this->getSchedule($scheduleID);
            foreach ($schedule->calendar as $day => $blocks)
            {
                if ($day < $startdate OR $day > $enddate)
                {
                    continue;
                }
                $this->resolveTime($schedule, $day, $blocks, $teachers);
            }
            unset($schedule);
        }
    }

    /**
     * Checks the database for plausible schedules
     *
     * @param   string  $startdate  the startdate of the original schedule
     * @param   string  $enddate    the enddate of the original schedule
     *
     * @return  mixed  array on success, otherwise null
     */
    private function getPlausibleScheduleIDs($startdate, $enddate)
    {
        $query = $this->_db->getQuery(true);
        $query->select('id');
        $query->from("#__thm_organizer_schedules");
        $query->where("term_startdate = '$startdate'");
        $query->where("term_enddate = '$enddate'");
        $query->where("active = '1'");
        $this->_db->setQuery((string) $query);
        try
        {
            return $this->_db->loadColumn();
        }
        catch (Exception $exception)
        {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Checks the database for plausible schedules
     *
     * @param   int  $scheduleID  the id of the schedule to be iterated
     *
     * @return  mixed  array on success, otherwise null
     */
    private function getSchedule($scheduleID)
    {
        $query = $this->_db->getQuery(true);
        $query->select('schedule');
        $query->from("#__thm_organizer_schedules");
        $query->where("id = '$scheduleID'");
        $this->_db->setQuery((string) $query);
        try
        {
            $result = $this->_db->loadResult();
            return json_decode($result);
        }
        catch (Exception $exception)
        {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');
            return null;
        }
    }
}