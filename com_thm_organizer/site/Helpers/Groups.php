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

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use stdClass;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class Groups implements XMLValidator
{
    /**
     * Checks whether the given group is associated with an allowed department
     *
     * @param array $groupIDs the ids of the groups being checked
     *
     * @return bool  true if the group is associated with an allowed department, otherwise false
     */
    public static function allowEdit($groupIDs)
    {
        if (empty(Factory::getUser()->id)) {
            return false;
        }

        if (Access::isAdmin()) {
            return true;
        }

        if (empty($groupIDs)) {
            return false;
        }

        $groupIDs           = "'" . implode("', '", $groupIDs) . "'";
        $allowedDepartments = Access::getAccessibleDepartments('schedule');

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT dr.id')
            ->from('#__thm_organizer_department_resources as dr')
            ->innerJoin('#__thm_organizer_groups as gr on gr.categoryID = dr.categoryID')
            ->where("gr.id IN ( $groupIDs )")
            ->where("departmentID IN ('" . implode("', '", $allowedDepartments) . "')");

        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadColumn', []);
    }

    /**
     * Retrieves a list of lessons associated with a group
     *
     * @return array the lessons associated with the group
     */
    public static function getLessons()
    {
        $input = OrganizerHelper::getInput();

        $groupIDs = ArrayHelper::toInteger(explode(',', $input->getString('groupIDs', '')));
        if (empty($groupIDs[0])) {
            return [];
        }
        $groupIDs = implode(',', $groupIDs);

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
        $query->select("DISTINCT l.id, l.comment, lc.courseID, m.abbreviation_$languageTag AS method")
            ->from('#__thm_organizer_lessons AS l')
            ->innerJoin('#__thm_organizer_methods AS m on m.id = l.methodID')
            ->innerJoin('#__thm_organizer_lesson_courses AS lc on lc.lessonID = l.id')
            ->innerJoin('#__thm_organizer_lesson_groups AS lg on lg.lessonCourseID = lc.id')
            ->where("lg.groupID IN ($groupIDs)")
            ->where("l.delta != 'removed'")
            ->where("lg.delta != 'removed'")
            ->where("lc.delta != 'removed'");

        $dateTime = strtotime($date);
        switch ($interval) {
            case 'semester':
                $query->innerJoin('#__thm_organizer_terms AS term ON term.id = l.termID')
                    ->where("'$date' BETWEEN term.startDate AND term.endDate");
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

            $lesson['subjectName'] = Courses::getName($lesson['subjectID'], true);

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
        $selectedCategories = OrganizerHelper::getInput()->getString('categoryIDs');
        $short              = count($selectedCategories) === 1;
        $groups             = self::getGroups();

        $results = [];
        foreach ($groups as $group) {
            $name           = $short ? $group['name'] : $group['full_name'];
            $results[$name] = $group['id'];
        }

        ksort($results);

        return empty($results) ? [] : $results;
    }

    /**
     * Gets the plan programs with corresponding documented program titles if associated.
     *
     * @return mixed
     */
    public static function getGroups()
    {
        $dbo = Factory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select('gr.id, gr.name, gr.full_name');
        $query->from('#__thm_organizer_groups AS gr');

        $input               = OrganizerHelper::getInput();
        $selectedDepartments = $input->getString('departmentIDs');
        $selectedCategories  = $input->getString('categoryIDs');

        if (!empty($selectedDepartments)) {
            $query->innerJoin('#__thm_organizer_department_resources AS dr ON gr.categoryID = dr.categoryID');
            $departmentIDs = "'" . str_replace(',', "', '", $selectedDepartments) . "'";
            $query->where("dr.departmentID IN ($departmentIDs)");
        }

        if (!empty($selectedCategories)) {
            $categoryIDs = "'" . str_replace(',', "', '", $selectedCategories) . "'";
            $query->where("gr.categoryID in ($categoryIDs)");
        }

        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

    /**
     * Retrieves a list of subjects associated with a group
     *
     * @return array the subjects associated with the group
     */
    public static function getSubjects()
    {
        $input = OrganizerHelper::getInput();

        $groupIDs = ArrayHelper::toInteger(explode(',', $input->getString('groupIDs', '')));
        if (empty($groupIDs[0])) {
            return [];
        }
        $groupIDs = implode(',', $groupIDs);

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
        $query->select('DISTINCT lc.courseID')
            ->from('#__thm_organizer_lesson_courses AS lc')
            ->innerJoin('#__thm_organizer_lessons AS l on l.id = lc.lessonID')
            ->innerJoin('#__thm_organizer_lesson_groups AS lg on lg.lessonCourseID = lc.id')
            ->where("lg.groupID IN ($groupIDs)")
            ->where("l.delta != 'removed'")
            ->where("lg.delta != 'removed'")
            ->where("lc.delta != 'removed'");

        $dateTime = strtotime($date);
        switch ($interval) {
            case 'semester':
                $query->innerJoin('#__thm_organizer_terms AS term ON term.id = l.termID')
                    ->where("'$date' BETWEEN term.startDate AND term.endDate");
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
        $courseIDs = OrganizerHelper::executeQuery('loadColumn', []);

        if (empty($courseIDs)) {
            return [];
        }

        $subjects = [];
        foreach ($courseIDs as $courseID) {
            $name            = Courses::getName($courseID, true);
            $subjects[$name] = $courseID;
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
        $group = $scheduleModel->schedule->groups->$untisID;

        $table  = OrganizerHelper::getTable('Groups');
        $data   = ['untisID' => $group->untisID];
        $exists = $table->load($data);

        if ($exists) {
            $altered = false;
            foreach ($group as $key => $value) {
                if (property_exists($table, $key) and empty($table->$key) and !empty($value)) {
                    $table->set($key, $value);
                    $altered = true;
                }
            }

            if ($altered) {
                $table->store();
            }

            $scheduleModel->schedule->groups->$untisID->id = $table->id;

            return;
        }
        $table->save($data);
        $scheduleModel->schedule->groups->$untisID->id = $table->id;

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
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_GROUPS_MISSING');

            return;
        }

        $scheduleModel->schedule->groups = new stdClass;

        foreach ($xmlObject->classes->children() as $groupNode) {
            self::validateIndividual($scheduleModel, $groupNode);
        }
    }

    /**
     * Checks whether XML node has the expected structure and required
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
            if (!in_array(Languages::_('THM_ORGANIZER_GROUP_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_GROUP_ID_MISSING');
            }

            return;
        }

        $internalID = str_replace('CL_', '', $internalID);
        $externalID = trim((string)$node->external_name);
        $untisID    = empty($externalID) ? $internalID : str_replace('CL_', '', $externalID);

        $full_name = trim((string)$node->longname);
        if (empty($full_name)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_GROUP_LONGNAME_MISSING'),
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
                Languages::_('THM_ORGANIZER_GROUP_MISSING_CATEGORY'), $full_name, $internalID
            );

            return;
        } elseif (empty($scheduleModel->schedule->degrees->$degreeID)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_GROUP_CATEGORY_LACKING'), $full_name, $internalID, $degreeID
            );

            return;
        }

        $grid = (string)$node->timegrid;
        if (empty($grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_GROUP_MISSING_GRID'), $full_name, $internalID
            );

            return;
        } elseif (empty($scheduleModel->schedule->periods->$grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_GROUP_GRID_LACKING'), $full_name, $internalID, $grid
            );

            return;
        }

        $group            = new stdClass;
        $group->degree    = $degreeID;
        $group->untisID   = $untisID;
        $group->full_name = $full_name;
        $group->name      = $name;
        $group->grid      = $grid;
        $group->gridID    = Grids::getID($grid);

        $scheduleModel->schedule->groups->$internalID = $group;
        self::setID($scheduleModel, $internalID);
    }
}
