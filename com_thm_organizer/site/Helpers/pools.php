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

require_once 'departments.php';
require_once 'date.php';
require_once 'languages.php';
require_once 'programs.php';

use THM_OrganizerHelperLanguages as Languages;

/**
 * Provides general functions for (subject) pool access checks, data retrieval and display.
 */
class THM_OrganizerHelperPools
{
    /**
     * Creates a text for the required pool credit points
     *
     * @param object $pool the pool
     *
     * @return string  the required amount of credit points
     */
    public static function getCrPText($pool)
    {
        $minCrPExists = !empty($pool->minCrP);
        $maxCrPExists = !empty($pool->maxCrP);
        if ($maxCrPExists) {
            if ($minCrPExists) {
                if ($pool->minCrP == $pool->maxCrP) {
                    return "$pool->maxCrP CrP";
                }

                return "$pool->minCrP - $pool->maxCrP CrP";
            }

            return "max. $pool->maxCrP CrP";
        }

        if ($minCrPExists) {
            return "min. $pool->minCrP CrP";
        }

        return '';
    }

    /**
     * Retrieves the table id if existent.
     *
     * @param string $gpuntisID the pool name in untis
     *
     * @return int id on success, otherwise 0
     */
    public static function getID($gpuntisID)
    {
        $table  = \JTable::getInstance('plan_pools', 'thm_organizerTable');
        $data   = ['gpuntisID' => $gpuntisID];
        $exists = $table->load($data);

        return $exists ? $table->id : 0;
    }

    /**
     * Retrieves the pool's full name if existent.
     *
     * @param int $poolID the table's pool id
     *
     * @return string the full name, otherwise an empty string
     */
    public static function getFullName($poolID)
    {
        $table  = \JTable::getInstance('plan_pools', 'thm_organizerTable');
        $exists = $table->load($poolID);

        return $exists ? $table->full_name : '';
    }

    /**
     * Retrieves the pool's full name if existent.
     *
     * @param int    $poolID the table's pool id
     * @param string $type   the pool's type (real|plan)
     *
     * @return string the full name, otherwise an empty string
     */
    public static function getName($poolID, $type = 'plan')
    {
        if ($type == 'plan') {
            $table  = \JTable::getInstance('plan_pools', 'thm_organizerTable');
            $exists = $table->load($poolID);

            return $exists ? $table->name : '';
        }

        $table  = \JTable::getInstance('pools', 'thm_organizerTable');
        $exists = $table->load($poolID);

        if (!$exists) {
            return '';
        }

        $languageTag = Languages::getShortTag();

        if (!empty($table->{'name_' . $languageTag})) {
            return $table->{'name_' . $languageTag};
        } elseif (!empty($table->{'short_name_' . $languageTag})) {
            return $table->{'short_name_' . $languageTag};
        }

        return !empty($table->{'abbreviation_' . $languageTag}) ? $table->{'abbreviation_' . $languageTag} : '';

    }

    /**
     * Getter method for pools in database e.g. for selecting a schedule
     *
     * @param bool $short whether or not abbreviated names should be returned
     *
     * @return array  the plan pools
     */
    public static function getPlanPools($short = true)
    {
        $dbo = \JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select('ppl.id, ppl.name, ppl.full_name');
        $query->from('#__thm_organizer_plan_pools AS ppl');

        $input               = THM_OrganizerHelperComponent::getInput();
        $selectedDepartments = $input->getString('departmentIDs');
        $selectedPrograms    = $input->getString('programIDs');

        if (!empty($selectedDepartments)) {
            $query->innerJoin('#__thm_organizer_department_resources AS dr ON ppl.programID = dr.programID');
            $departmentIDs = "'" . str_replace(',', "', '", $selectedDepartments) . "'";
            $query->where("dr.departmentID IN ($departmentIDs)");
        }

        if (!empty($selectedPrograms)) {
            $programIDs = "'" . str_replace(',', "', '", $selectedPrograms) . "'";
            $query->where("ppl.programID in ($programIDs)");
        }

        $dbo->setQuery($query);

        $results = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
        if (empty($results)) {
            return [];
        }

        $pools = [];
        foreach ($results as $pool) {
            $name         = $short ? $pool['name'] : $pool['full_name'];
            $pools[$name] = $pool['id'];
        }

        ksort($pools);

        return $pools;
    }

    /**
     * Retrieves a list of lessons associated with a pool
     *
     * @return array the lessons associated with the pool
     */
    public static function getPoolLessons()
    {
        $input = THM_OrganizerHelperComponent::getInput();

        $poolIDs = Joomla\Utilities\ArrayHelper::toInteger(explode(',', $input->getString('poolIDs', '')));
        if (empty($poolIDs[0])) {
            return [];
        }
        $poolIDs = implode(',', $poolIDs);

        $date = $input->getString('date');
        if (!THM_OrganizerHelperDate::isStandardized($date)) {
            $date = date('Y-m-d');
        }

        $interval = $input->getString('dateRestriction');
        if (!in_array($interval, ['day', 'week', 'month', 'semester'])) {
            $interval = 'semester';
        }

        $languageTag = Languages::getShortTag();

        $dbo = \JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select("DISTINCT l.id, l.comment, ls.subjectID, m.abbreviation_$languageTag AS method")
            ->from('#__thm_organizer_lessons AS l')
            ->innerJoin('#__thm_organizer_methods AS m on m.id = l.methodID')
            ->innerJoin('#__thm_organizer_lesson_subjects AS ls on ls.lessonID = l.id')
            ->innerJoin('#__thm_organizer_lesson_pools AS lp on lp.subjectID = ls.id')
            ->where("lp.poolID IN ($poolIDs)")
            ->where("l.delta != 'removed'")
            ->where("lp.delta != 'removed'")
            ->where("ls.delta != 'removed'");

        $dateTime = strtotime($date);
        switch ($interval) {
            case 'semester':
                $query->innerJoin('#__thm_organizer_planning_periods AS pp ON pp.id = l.planningPeriodID')
                    ->where("'$date' BETWEEN pp.startDate AND pp.endDate");
                break;
            case 'month':
                $monthStart = date('Y-m-d', strtotime('first day of this month', $dateTime));
                $startDate  = date('Y-m-d', strtotime('Monday this week', strtotime($monthStart)));
                $monthEnd   = date('Y-m-d', strtotime('last day of this month', $dateTime));
                $endDate    = date('Y-m-d', strtotime('Sunday this week', strtotime($monthEnd)));
                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
                    ->where("c.schedule_date BETWEEN '$startDate' AND '$endDate'");
                break;
            case 'week':
                $startDate = date('Y-m-d', strtotime('Monday this week', $dateTime));
                $endDate   = date('Y-m-d', strtotime('Sunday this week', $dateTime));
                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
                    ->where("c.schedule_date BETWEEN '$startDate' AND '$endDate'");
                break;
            case 'day':
                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
                    ->where("c.schedule_date = '$date'");
                break;
        }

        $dbo->setQuery($query);

        $results = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
        if (empty($results)) {
            return [];
        }

        $lessons = [];
        foreach ($results as $lesson) {
            $index = '';

            $lesson['subjectName'] = THM_OrganizerHelperSubjects::getName($lesson['subjectID'], 'plan', true);

            $index .= $lesson['subjectName'];

            if (!empty($lesson['method'])) {
                $index .= " - {$lesson['method']}";
            }
            $index           .= " - {$lesson['id']}";
            $lessons[$index] = $lesson;
        }

        ksort($lessons);

        return $lessons;
    }

    /**
     * Retrieves a list of subjects associated with a pool
     *
     * @return array the subjects associated with the pool
     */
    public static function getPoolSubjects()
    {
        $input = THM_OrganizerHelperComponent::getInput();

        $poolIDs = Joomla\Utilities\ArrayHelper::toInteger(explode(',', $input->getString('poolIDs', '')));
        if (empty($poolIDs[0])) {
            return [];
        }
        $poolIDs = implode(',', $poolIDs);

        $date = $input->getString('date');
        if (!THM_OrganizerHelperDate::isStandardized($date)) {
            $date = date('Y-m-d');
        }

        $interval = $input->getString('dateRestriction');
        if (!in_array($interval, ['day', 'week', 'month', 'semester'])) {
            $interval = 'semester';
        }

        $dbo = \JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select('DISTINCT ls.subjectID')
            ->from('#__thm_organizer_lesson_subjects AS ls')
            ->innerJoin('#__thm_organizer_lessons AS l on l.id = ls.lessonID')
            ->innerJoin('#__thm_organizer_lesson_pools AS lp on lp.subjectID = ls.id')
            ->where("lp.poolID IN ($poolIDs)")
            ->where("l.delta != 'removed'")
            ->where("lp.delta != 'removed'")
            ->where("ls.delta != 'removed'");

        $dateTime = strtotime($date);
        switch ($interval) {
            case 'semester':
                $query->innerJoin('#__thm_organizer_planning_periods AS pp ON pp.id = l.planningPeriodID')
                    ->where("'$date' BETWEEN pp.startDate AND pp.endDate");
                break;
            case 'month':
                $monthStart = date('Y-m-d', strtotime('first day of this month', $dateTime));
                $startDate  = date('Y-m-d', strtotime('Monday this week', strtotime($monthStart)));
                $monthEnd   = date('Y-m-d', strtotime('last day of this month', $dateTime));
                $endDate    = date('Y-m-d', strtotime('Sunday this week', strtotime($monthEnd)));
                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
                    ->where("c.schedule_date BETWEEN '$startDate' AND '$endDate'");
                break;
            case 'week':
                $startDate = date('Y-m-d', strtotime('Monday this week', $dateTime));
                $endDate   = date('Y-m-d', strtotime('Sunday this week', $dateTime));
                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
                    ->where("c.schedule_date BETWEEN '$startDate' AND '$endDate'");
                break;
            case 'day':
                $query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
                    ->where("c.schedule_date = '$date'");
                break;
        }

        $dbo->setQuery($query);
        $subjectIDs = THM_OrganizerHelperComponent::executeQuery('loadColumn', []);

        if (empty($subjectIDs)) {
            return [];
        }

        $subjects = [];
        foreach ($subjectIDs as $subjectID) {
            $name            = THM_OrganizerHelperSubjects::getName($subjectID, 'plan', true);
            $subjects[$name] = $subjectID;
        }

        ksort($subjects);

        return $subjects;
    }

    /**
     * Attempts to get the plan pool's id, creating it if non-existent.
     *
     * @param string $gpuntisID the untis id for the given pool
     * @param object $pool      the pool object
     *
     * @return mixed int on success, otherwise null
     */
    public static function getPlanResourceID($gpuntisID, $pool)
    {
        $poolID = self::getID($gpuntisID);
        if (!empty($poolID)) {
            return $poolID;
        }

        $data              = [];
        $data['gpuntisID'] = $gpuntisID;

        $programID = THM_OrganizerHelperPrograms::getID($pool->degree);
        if (!empty($programID)) {
            $data['programID'] = $programID;
        }

        $data['name']      = $pool->restriction;
        $data['full_name'] = $pool->longname;
        $data['gridID']    = $pool->gridID;

        $table   = \JTable::getInstance('plan_pools', 'thm_organizerTable');
        $success = $table->save($data);

        return $success ? $table->id : null;

    }
}
