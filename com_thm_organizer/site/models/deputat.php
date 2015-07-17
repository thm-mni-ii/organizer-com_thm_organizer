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

    public $deputat = null;

    public $selected = array();

    public $teachers = array();

    public $irrelevantTypes = array();

    public $irrelevantTeacherPrefixes = array();


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
        }
    }

    /**
     * Sets object properties
     *
     * @return  void
     */
    private function setObjectProperties()
    {
        $input = JFactory::getApplication()->input;
        $this->reset = $input->getBool('reset', false);
        $this->selected = array();
        $this->teachers = array();
        $this->irrelevantTypes = array('K', 'SIT', 'PRÜ');
        $this->irrelevantTeacherPrefixes = array('NN.', 'DIV.', 'FS.', 'FS.', 'TUTOR.', 'SW');
        $this->setSchedule();
    }

    /**
     * Gets all schedules in the database
     *
     * @return array An array with the schedules
     */
    public function getActiveSchedules()
    {
        $query = $this->_db->getQuery(true);
        $columns = array('departmentname', 'semestername');
        $select = 'id, ' . $query->concatenate($columns, ' - ') . ' AS name';
        $query->select($select);
        $query->from("#__thm_organizer_schedules");
        $query->where("active = '1'");
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
        $this->deputat = array();

        $startdate =  (!empty($this->schedule->termStartDate))? $this->schedule->termStartDate : $this->schedule->startdate;
        $enddate =  (!empty($this->schedule->termEndDate))? $this->schedule->termEndDate : $this->schedule->enddate;

        foreach ($this->schedule->calendar as $day => $blocks)
        {
            if ($day < $startdate OR $day > $enddate)
            {
                continue;
            }
            $this->resolveTime($day, $blocks);
        }
    }

    /**
     * Sets consumption by instance (block + lesson)
     *
     * @param   string  $day      the day being iterated
     * @param   object  &$blocks  the blocks of the date being iterated
     * 
     * @return  void
     */
    private function resolveTime($day, &$blocks)
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
                $gridBlock =$this->schedule->periods->{$this->schedule->lessons->$lessonID->grid}->$blockNumber;
                $startTime = $gridBlock->starttime;
                $startDT = strtotime(substr($startTime, 0, 2) . ':' . substr($startTime, 2, 2) . ':00');
                $endTime = $gridBlock->endtime;
                $endDT = strtotime(substr($endTime, 0, 2) . ':' . substr($endTime, 2, 2) . ':00');
                $hours = ($endDT - $startDT) / $seconds;

                $this->setDeputatByInstance($day, $blockNumber, $lessonID, $hours);
            }
        }
    }

    /**
     * Iterates the lesson associated pools for the purpose of teacher consumption
     *
     * @param   string  $day          the day being iterated
     * @param   int     $blockNumber  the block number being iterated
     * @param   string  $lessonID     the lesson ID
     * @param   int     $hours        the number of school hours for the lesson
     *
     * @return  void
     */
    private function setDeputatByInstance($day, $blockNumber, $lessonID, $hours)
    {
        $teachers = $this->schedule->lessons->$lessonID->teachers;
        foreach ($teachers as $teacherID => $teacherDelta)
        {
            if ($teacherDelta == 'removed')
            {
                continue;
            }

            $irrelevant = false;
            foreach ($this->irrelevantTeacherPrefixes AS $prefix)
            {
                if (strpos($teacherID, $prefix) === 0)
                {
                    $irrelevant = true;
                    break;
                }
            }

            if (!$irrelevant)
            {
                $this->setDeputat($day, $blockNumber, $lessonID, $teacherID, $hours);
            }
        }
        asort($this->deputat);
    }

    /**
     * Sets the pertinent deputat information
     *
     * @param   string  $day          the day being iterated
     * @param   int     $blockNumber  the block number being iterated
     * @param   string  $lessonID     the lesson being iterated
     * @param   string  $teacherID    the teacher being iterated
     * @param   int     $hours        the number of school hours for the lesson
     */
    private function setDeputat($day, $blockNumber, $lessonID, $teacherID, $hours = 0)
    {
        if (empty($this->deputat[$teacherID]))
        {
            $this->deputat[$teacherID] = array();
            $this->deputat[$teacherID]['name'] = THM_OrganizerHelperTeacher::getLNFName($this->schedule->teachers->$teacherID);
            $this->deputat[$teacherID]['summary'] = array();
            $this->deputat[$teacherID]['tally'] = array();
        }

        $subjectName = $this->getSubjectName($lessonID);
        $rate = $this->getRate($subjectName);
        $poolName = $this->getPoolName($lessonID);
        $lessonType = $this->schedule->lessons->$lessonID->description;

        // Tallied items have flat payment values and are correspondingly not tracked as accurately
        $isTallied = $this->isTallied($lessonID);
        if ($isTallied)
        {

            if (empty($this->deputat[$teacherID]['tally'][$subjectName]))
            {
                $this->deputat[$teacherID]['tally'][$subjectName] = array();
                $this->deputat[$teacherID]['tally'][$subjectName]['rate'] = $rate;
                $this->deputat[$teacherID]['tally'][$subjectName]['count'] = 1;
                return;
            }
            $this->deputat[$teacherID]['tally'][$subjectName]['count']++;
            return;
        }

        $lessonType = $this->schedule->lessons->$lessonID->description;

        // Some 'lesson' types are irrelevant for deputat calculation;
        if (in_array($lessonType, $this->irrelevantTypes))
        {
            return;
        }

        $DOWConstant = strtoupper(date('l', strtotime($day)));
        $weekday = JText::_($DOWConstant);

        $subjectIndex = "$subjectName-$lessonType";
        $plannedBlock = "$weekday-$blockNumber";
        if (empty($this->deputat[$teacherID]['summary'][$subjectIndex]))
        {
            $this->deputat[$teacherID]['summary'][$subjectIndex] = array();
            $this->deputat[$teacherID]['summary'][$subjectIndex]['subjects'] = $subjectName;
            $this->deputat[$teacherID]['summary'][$subjectIndex]['type'] = $lessonType;
            $this->deputat[$teacherID]['summary'][$subjectIndex]['pools'] = $poolName;
            $this->deputat[$teacherID]['summary'][$subjectIndex]['periods'] = array();
            $this->deputat[$teacherID]['summary'][$subjectIndex]['startdate'] = THM_OrganizerHelperComponent::formatDate($day);
        }

        if (empty($this->deputat[$teacherID]['summary'][$subjectIndex]['hours']))
        {
            $this->deputat[$teacherID]['summary'][$subjectIndex]['hours'] = $hours;
        }
        else
        {
            $this->deputat[$teacherID]['summary'][$subjectIndex]['hours'] += $hours;
        }

        if (!in_array($plannedBlock, $this->deputat[$teacherID]['summary'][$subjectIndex]['periods']))
        {
            $this->deputat[$teacherID]['summary'][$subjectIndex]['periods'][$plannedBlock] = $hours;
        }

        $this->deputat[$teacherID]['summary'][$subjectIndex]['sws']
            = array_sum($this->deputat[$teacherID]['summary'][$subjectIndex]['periods']);
        $this->deputat[$teacherID]['summary'][$subjectIndex]['enddate'] = THM_OrganizerHelperComponent::formatDate($day);

        return;
    }

    /**
     * Creates a concatenated subject name from the relevant subject names for the lesson
     *
     * @param   string  $lessonID  the id of the lesson
     *
     * @return  string  the concatenated name of the subject(s)
     */
    private function getSubjectName($lessonID)
    {
        $subjects = (array) $this->schedule->lessons->$lessonID->subjects;
        foreach ($subjects AS $subject => $delta)
        {
            if ($delta == 'removed')
            {
                unset($subjects[$subject]);
                continue;
            }
            if ($subject == 'KOL.B')
            {
                return 'Betreuung von Bachelorarbeiten';
            }
            if ($subject == 'KOL.M')
            {
                return 'Betreuung von Masterarbeiten';
            }
            $subjects[$subject] = $this->schedule->subjects->{$subject}->longname;
        }
        return implode('/', $subjects);
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
        if ($subjectName == 'Betreuung von Bachelorarbeiten')
        {
            return 0.3;
        }
        if ($subjectName == 'Betreuung von Masterarbeiten')
        {
            return 0.6;
        }
        return 1;
    }

    /**
     * Creates a concatenated subject name from the relevant subject names for the lesson
     *
     * @param   string  $lessonID  the id of the lesson
     *
     * @return  string  the concatenated name of the subject(s)
     */
    private function getPoolName($lessonID)
    {
        $pools = (array) $this->schedule->lessons->$lessonID->pools;
        foreach ($pools AS $pool => $delta)
        {
            if ($delta == 'removed')
            {
                unset($pools[$pool]);
            }
        }
        $poolIDs = array_keys($pools);
        asort($poolIDs);
        return implode(', ', $poolIDs);
    }

    /**
     * Checks whether the lesson should be tallied instead of summarized. (Oral exams or colloquia)
     *
     * @param   string  $lessonID  the id of the lesson
     *
     * @return  bool  true if the lesson should be tallied instead of summarized, otherwise false
     */
    private function isTallied($lessonID)
    {
        $subjects = $this->schedule->lessons->$lessonID->subjects;
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
     * Function to get a table displaying resource consumption for a schedule
     * 
     * @return  string  a HTML string for a consumption table
     */
    public function getDeputatTable()
    {
        $table = "<table id='thm_organizer-deputat-table' ";
        $table .= "class='deputat-table'>";

        foreach ($this->deputat as $deputat)
        {
            $displaySummary = count($deputat['summary']);
            $displayTally = count($deputat['tally']);
            $display = ($displaySummary OR $displayTally);
            if ($display)
            {
                $table .= '<tr class="teacher-header"><th colspan="6">' . $deputat['name'] . '</th></tr>';
                if ($displaySummary)
                {
                    $table .= '<tr class="group-header">';
                    $table .= '<th>Lehrveranstaltung</th>';
                    $table .= '<th>Art<br/>(Kürzel)</th>';
                    $table .= '<th>Studiengang Semester</th>';
                    $table .= '<th>Wochentag u. Stunde<br/>(bei Blockveranstalt. Datum)</th>';
                    $table .= '<th>SWS</th>';
                    $table .= '<th>Gemeldetes Deputat (Summe)</th>';
                    $table .= '</tr>';
                    $table .= $this->getSummaryRows($deputat);
                }
                if ($displayTally)
                {
                    $table .= '<tr class="group-header">';
                    $table .= '<th colspan="3">Art der Abschlussarbeit<br/>(nur bei Betreuung als Referent/in)</th>';
                    $table .= '<th>Umfang der Anrechnung in SWS je Arbeit<br />(insgesamt max. 2 SWS)</th>';
                    $table .= '<th>Anzahl der Arbeiten</th>';
                    $table .= '<th>Gemeldetes Deputat (SWS)</th>';
                    $table .= '</tr>';
                    $table .= $this->getTallyRows($deputat);
                }
            }
        }

        $table .= '</table>';
        return $table;
    }

    /**
     * Retrieves a rows containing information about
     *
     * @param   array   &$deputat  the table columns
     *
     * @return  string  HTML String for the summary row
     */
    private function getSummaryRows(&$deputat)
    {
        $style = 'style ="vnd.ms-excel.numberformat:@;"';

        $rows = array();
        $swsSum = 0;
        $realSum = 0;
        foreach ($deputat['summary'] as $summary)
        {
            $periodsText = (count($summary['periods']) > 10)?
                "{$summary['startdate']} bis {$summary['enddate']}" : implode(', ', array_keys($summary['periods']));
            $row = '<tr>';
            $row .= '<td>' . $summary['subjects'] . '</td>';
            $row .= '<td>' . $summary['type'] . '</td>';
            $row .= '<td>' . $summary['pools'] . '</td>';
            $row .= '<td>' . $periodsText . '</td>';
            $row .= '<td ' . $style . '>' . $summary['sws'] . '</td>';
            $swsSum += $summary['sws'];
            $row .= '<td ' . $style . '>' . $summary['hours'] . '</td>';
            $realSum += $summary['hours'];
            $row .= '</tr>';
            $rows[] = $row;
        }
        $sumRow = '<tr>';
        $sumRow .= '<td></td>';
        $sumRow .= '<td></td>';
        $sumRow .= '<td></td>';
        $sumRow .= '<td>Summe</td>';
        $sumRow .= '<td ' . $style . '>' . $swsSum . '</td>';
        $sumRow .= '<td ' . $style . '>' . $realSum . '</td>';
        $sumRow .= '</tr>';
        $rows[] = $sumRow;

        return implode('', $rows);
    }
    /**
     * Retrieves a row containing a summary of the column values in all the other rows. In the process it removes
     * columns without values.
     *
     * @param   array   &$deputat  the table columns
     *
     * @return  string  HTML String for the summary row
     */
    private function getTallyRows(&$deputat)
    {
        $style = 'style ="vnd.ms-excel.numberformat:@;"';
        $rows = array();
        foreach ($deputat['tally'] as $name => $data)
        {
            $row = '<tr>';
            $row .= '<td colspan="3">' . $name . '</td>';
            $row .= '<td ' . $style . '>' . $data['rate'] . '</td>';
            $row .= '<td ' . $style . '>' . $data['count'] . '</td>';
            $row .= '<td ' . $style . '>' . ($data['rate'] * $data['count']) . '</td>';
            $row .= '</tr>';
            $rows[] = $row;
        }

        return implode('', $rows);
    }

    /**
     * Gets a list of teacher names
     *
     * @return  array  a list of resource names
     */
    public function getTeacherNames()
    {
        $names = array();
        foreach ($this->schedule->teachers as $key => $teacher)
        {
            $names[$key] = THM_OrganizerHelperTeacher::getDefaultName($teacher);
        }
        asort($names);
        return $names;
    }

    /**
     * Gets the list of selected resources
     *
     * @param   string  $type  the resource type (rooms|roomtypes|teachers|fields)
     *
     * @return  void
     */
    private function setSelected($type)
    {
        $default = array();
        $default[] = '*';
        $selected = JFactory::getApplication()->input->get($type, $default, 'array');
        $useDefault = ($this->reset OR (count($selected) > 1 AND in_array('*', $selected)));
        if ($useDefault)
        {
            $this->selected[$type] = $default;
            return;
        }
        $this->selected[$type] = $selected;
    }
}
