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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use stdClass;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class Plan_Pools implements XMLValidator
{
    /**
     * Checks whether the given plan pool is associated with an allowed department
     *
     * @param array $ppIDs the ids of the plan pools being checked
     *
     * @return bool  true if the plan pool is associated with an allowed department, otherwise false
     */
    public static function allowEdit($ppIDs)
    {
        if (empty(Factory::getUser()->id)) {
            return false;
        }

        if (Access::isAdmin()) {
            return true;
        }

        if (empty($ppIDs)) {
            return false;
        }

        $ppIDs              = "'" . implode("', '", $ppIDs) . "'";
        $allowedDepartments = Access::getAccessibleDepartments('schedule');

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT dr.id')
            ->from('#__thm_organizer_department_resources as dr')
            ->innerJoin('#__thm_organizer_plan_pools as ppl on ppl.programID = dr.programID')
            ->where("ppl.id IN ( $ppIDs )")
            ->where("departmentID IN ('" . implode("', '", $allowedDepartments) . "')");

        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadColumn', []);
    }

    /**
     * Retrieves a list of lessons associated with a pool
     *
     * @return array the lessons associated with the pool
     */
    public static function getLessons()
    {
        $input = OrganizerHelper::getInput();

        $poolIDs = ArrayHelper::toInteger(explode(',', $input->getString('poolIDs', '')));
        if (empty($poolIDs[0])) {
            return [];
        }
        $poolIDs = implode(',', $poolIDs);

        $date = $input->getString('date');
        if (!Dates::isStandardized($date)) {
            $date = date('Y-m-d');
        }

        $interval = $input->getString('dateRestriction');
        if (!in_array($interval, ['day', 'week', 'month', 'semester'])) {
            $interval = 'semester';
        }

        $languageTag = Languages::getShortTag();

        $dbo = Factory::getDbo();

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

        $results = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($results)) {
            return [];
        }

        $lessons = [];
        foreach ($results as $lesson) {
            $index = '';

            $lesson['subjectName'] = Subjects::getName($lesson['subjectID'], 'plan', true);

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
     * Retrieves a list of resources in the form of name => id.
     *
     * @return array the resources, or empty
     */
    public static function getOptions()
    {

        $selectedPrograms = OrganizerHelper::getInput()->getString('programIDs');
        $short            = count($selectedPrograms) === 1;
        $planPools        = self::getPlanPools();

        $results = [];
        foreach ($planPools as $planPool) {
            $name           = $short ? $planPool['name'] : $planPool['full_name'];
            $results[$name] = $planPool['id'];
        }

        ksort($results);

        return empty($results) ? [] : $results;
    }

    /**
     * Gets the plan programs with corresponding documented program titles if associated.
     *
     * @return mixed
     */
    public static function getPlanPools()
    {

        $dbo = Factory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select('ppl.id, ppl.name, ppl.full_name');
        $query->from('#__thm_organizer_plan_pools AS ppl');

        $input               = OrganizerHelper::getInput();
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

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

    /**
     * Retrieves a list of subjects associated with a pool
     *
     * @return array the subjects associated with the pool
     */
    public static function getSubjects()
    {
        $input = OrganizerHelper::getInput();

        $poolIDs = ArrayHelper::toInteger(explode(',', $input->getString('poolIDs', '')));
        if (empty($poolIDs[0])) {
            return [];
        }
        $poolIDs = implode(',', $poolIDs);

        $date = $input->getString('date');
        if (!Dates::isStandardized($date)) {
            $date = date('Y-m-d');
        }

        $interval = $input->getString('dateRestriction');
        if (!in_array($interval, ['day', 'week', 'month', 'semester'])) {
            $interval = 'semester';
        }

        $dbo = Factory::getDbo();

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
        $subjectIDs = OrganizerHelper::executeQuery('loadColumn', []);

        if (empty($subjectIDs)) {
            return [];
        }

        $subjects = [];
        foreach ($subjectIDs as $subjectID) {
            $name            = Subjects::getName($subjectID, 'plan', true);
            $subjects[$name] = $subjectID;
        }

        ksort($subjects);

        return $subjects;
    }

    /**
     * Retrieves the resource id using the Untis ID. Creates the resource id if unavailable.
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $untisID       the id of the resource in Untis
     *
     * @return void modifies the scheduleModel, setting the id property of the resource
     */
    public static function setID(&$scheduleModel, $untisID)
    {
        $pool = $scheduleModel->schedule->pools->$untisID;

        $table  = OrganizerHelper::getTable('Plan_Pools');
        $data   = ['gpuntisID' => $pool->gpuntisID];
        $exists = $table->load($data);

        if ($exists) {
            $altered = false;
            foreach ($pool as $key => $value) {
                if (property_exists($table, $key) and empty($table->$key) and !empty($value)) {
                    $table->set($key, $value);
                    $altered = true;
                }
            }

            if ($altered) {
                $table->store();
            }

            $scheduleModel->schedule->degrees->$untisID->id = $table->id;

            return;
        }
        $table->save($data);
        $scheduleModel->schedule->degrees->$untisID->id = $table->id;

        return;

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
        if (empty($xmlObject->classes)) {
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_POOLS_MISSING');

            return;
        }

        $scheduleModel->schedule->pools = new stdClass;

        foreach ($xmlObject->classes->children() as $poolNode) {
            self::validateIndividual($scheduleModel, $poolNode);
        }
    }

    /**
     * Checks whether subject nodes have the expected structure and required
     * information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$node          the node to be validated
     *
     * @return void
     */
    public static function validateIndividual(&$scheduleModel, &$node)
    {
        $internalID = trim((string)$node[0]['id']);
        if (empty($internalID)) {
            if (!in_array(Languages::_('THM_ORGANIZER_POOL_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_POOL_ID_MISSING');
            }

            return;
        }

        $internalID = str_replace('CL_', '', $internalID);
        $externalID = trim((string)$node->external_name);
        $untisID    = empty($externalID) ? $internalID : str_replace('CL_', '', $externalID);

        $full_name  = trim((string)$node->longname);
        if (empty($full_name)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_POOL_LONGNAME_MISSING'),
                $internalID
            );

            return;
        }

        $name = trim((string)$node->classlevel);
        if (empty($name)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_ERROR_NODE_NAME'),
                $full_name,
                $internalID
            );

            return;
        }

        $degreeID = str_replace('DP_', '', trim((string)$node->class_department[0]['id']));
        if (empty($degreeID)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_POOL_PROGRAM_MISSING'), $full_name, $internalID
            );

            return;
        } elseif (empty($scheduleModel->schedule->degrees->$degreeID)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_POOL_PROGRAM_LACKING'), $full_name, $internalID, $degreeID
            );

            return;
        }

        $grid = (string)$node->timegrid;
        if (empty($grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_POOL_GRID_MISSING'), $full_name, $internalID
            );

            return;
        } elseif (empty($scheduleModel->schedule->periods->$grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_POOL_GRID_LACKING'), $full_name, $internalID, $grid
            );

            return;
        }

        $pool            = new stdClass;
        $pool->degree    = $degreeID;
        $pool->gpuntisID = $untisID;
        $pool->full_name = $full_name;
        $pool->name      = $name;
        $pool->grid      = $grid;
        $pool->gridID    = Grids::getID($grid);

        $scheduleModel->schedule->pools->$internalID = $pool;
        self::setID($scheduleModel, $internalID);
    }
}
