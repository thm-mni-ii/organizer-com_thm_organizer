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
require_once 'OrganizerHelper.php';

/**
 * Provides general functions for planning_period access checks, data retrieval and display.
 */
class THM_OrganizerHelperPlanning_Periods
{
    /**
     * Gets the id of the planning period whose dates encompass the current date
     *
     * @return int the id of the planning period for the dates used on success, otherwise 0
     */
    public static function getCurrentID()
    {
        $date  = date('Y-m-d');
        $dbo   = \JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')
            ->from('#__thm_organizer_planning_periods')
            ->where("'$date' BETWEEN startDate and endDate");
        $dbo->setQuery($query);

        return (int)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Checks for the planning period end date for a given planning period id
     *
     * @param string $ppID the planning period's id
     *
     * @return mixed  string the end date of the planning period could be resolved, otherwise null
     */
    public static function getEndDate($ppID)
    {
        $ppTable = \JTable::getInstance('planning_periods', 'thm_organizerTable');

        try {
            $success = $ppTable->load($ppID);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');

            return null;
        }

        return $success ? $ppTable->endDate : null;
    }

    /**
     * Checks for the planning period entry in the database, creating it as necessary.
     *
     * @param array $data the planning period's data
     *
     * @return mixed  int the id if the room could be resolved/added, otherwise null
     */
    public static function getID($data)
    {
        $ppTable      = \JTable::getInstance('planning_periods', 'thm_organizerTable');
        $loadCriteria = ['startDate' => $data['startDate'], 'endDate' => $data['endDate']];

        try {
            $success = $ppTable->load($loadCriteria);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');

            return null;
        }

        if ($success) {
            return $ppTable->id;
        } elseif (empty($data)) {
            return null;
        }

        // Entry not found
        $success = $ppTable->save($data);

        return $success ? $ppTable->id : null;
    }

    /**
     * Checks for the planning period name for a given planning period id
     *
     * @param string $ppID the planning period's id
     *
     * @return mixed  string the name if the planning period could be resolved, otherwise null
     */
    public static function getName($ppID)
    {
        $ppTable = \JTable::getInstance('planning_periods', 'thm_organizerTable');

        try {
            $success = $ppTable->load($ppID);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');

            return null;
        }

        return $success ? $ppTable->name : null;
    }

    /**
     * Retrieves the ID of the planning period occurring immediately after the reference planning period.
     *
     * @param int $currentID the id of the reference planning period
     *
     * @return int the id of the subsequent planning period if successful, otherwise 0
     */
    public static function getNextID($currentID = 0)
    {
        if (empty($currentID)) {
            $currentID = self::getCurrentID();
        }

        $currentEndDate = self::getEndDate($currentID);

        $dbo   = \JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')
            ->from('#__thm_organizer_planning_periods')
            ->where("startDate > '$currentEndDate'")
            ->order('startDate ASC');
        $dbo->setQuery($query);

        return (int)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Getter method for rooms in database. Only retrieving the IDs here allows for formatting the names according to
     * the needs of the calling views.
     *
     * @return string  all pools in JSON format
     */
    public static function getPlanningPeriods()
    {
        $dbo   = \JFactory::getDbo();
        $input = OrganizerHelper::getInput();

        $selectedDepartments = $input->getString('departmentIDs');
        $selectedPrograms    = $input->getString('programIDs');

        $query = $dbo->getQuery(true);
        $query->select('DISTINCT pp.id, pp.name, pp.startDate, pp.endDate')
            ->from('#__thm_organizer_planning_periods AS pp');

        if (!empty($selectedDepartments) or !empty($selectedPrograms)) {
            $query->innerJoin('#__thm_organizer_lessons AS l on l.planningPeriodID = pp.id');

            if (!empty($selectedDepartments)) {
                $query->innerJoin('#__thm_organizer_departments AS dpt ON l.departmentID = dpt.id');
                $departmentIDs = "'" . str_replace(',', "', '", $selectedDepartments) . "'";
                $query->where("l.departmentID IN ($departmentIDs)");
            }

            if (!empty($selectedPrograms)) {
                $query->innerJoin('#__thm_organizer_lesson_subjects AS ls on ls.lessonID = l.id');
                $query->innerJoin('#__thm_organizer_lesson_pools AS lp on lp.subjectID = ls.id');
                $query->innerJoin('#__thm_organizer_plan_pools AS ppo ON lp.poolID = ppo.id');
                $programIDs = "'" . str_replace(',', "', '", $selectedPrograms) . "'";
                $query->where("ppo.programID in ($programIDs)");
            }
        }

        $query->order('startDate');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }
}
