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
require_once 'language.php';


/**
 * Provides general functions for teacher access checks, data retrieval and display.
 */
class THM_OrganizerHelperTeachers
{
    /**
     * Checks for multiple teacher entries (responsibilities) for a subject and removes the lesser
     *
     * @param array &$list the list of teachers responsilbe for a subject
     *
     * @return void  removes duplicate list entries dependent on responsibility
     */
    private static function ensureUnique(&$list)
    {
        $keysToIds = [];
        foreach ($list as $key => $item) {
            $keysToIds[$key] = $item['id'];
        }

        $valueCount = array_count_values($keysToIds);
        foreach ($list as $key => $item) {
            $unset = ($valueCount[$item['id']] > 1 and $item['teacherResp'] > 1);
            if ($unset) {
                unset($list[$key]);
            }
        }
    }

    /**
     * Retrieves the teacher responsible for the subject's development
     *
     * @param int  $subjectID      the subject's id
     * @param int  $responsibility represents the teacher's level of
     *                             responsibility for the subject
     * @param bool $multiple       whether or not multiple results are desired
     * @param bool $unique         whether or not unique results are desired
     *
     * @return array  an array of teacher data
     */
    public static function getDataBySubject($subjectID, $responsibility = null, $multiple = false, $unique = true)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('t.id, t.surname, t.forename, t.title, t.username, u.id AS userID, teacherResp, gpuntisID');
        $query->from('#__thm_organizer_teachers AS t');
        $query->innerJoin('#__thm_organizer_subject_teachers AS st ON t.id = st.teacherID ');
        $query->leftJoin('#__users AS u ON t.username = u.username');
        $query->where("st.subjectID = '$subjectID' ");

        if (!empty($responsibility)) {
            $query->where("st.teacherResp = '$responsibility'");
        }

        $query->order('surname ASC');
        $dbo->setQuery($query);

        if ($multiple) {

            $teacherList = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
            if (empty($teacherList)) {
                return [];
            }

            if ($unique) {
                self::ensureUnique($teacherList);
            }

            return $teacherList;
        }

        return THM_OrganizerHelperComponent::executeQuery('loadAssoc', []);
    }

    /**
     * Generates a default teacher text based upon organizer's internal data
     *
     * @param int $teacherID the teacher's id
     *
     * @return string  the default name of the teacher
     */
    public static function getDefaultName($teacherID)
    {
        $teacher = JTable::getInstance('teachers', 'thm_organizerTable');
        $teacher->load($teacherID);

        $return = '';
        if (!empty($teacher->id)) {
            $title    = empty($teacher->title) ? '' : "{$teacher->title} ";
            $forename = empty($teacher->forename) ? '' : "{$teacher->forename} ";
            $surname  = $teacher->surname;
            $return   .= $title . $forename . $surname;
        }

        return $return;
    }

    /**
     * Gets the ids of departments with which the teacher is associated
     *
     * @param int $teacherID the teacher's id
     *
     * @return array the ids of departments with which the teacher is associated
     */
    public static function getDepartmentIDs($teacherID)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('departmentID')
            ->from('#__thm_organizer_department_resources')
            ->where("teacherID = $teacherID");
        $dbo->setQuery($query);
        $departmentIDs = THM_OrganizerHelperComponent::executeQuery('loadColumn', []);

        return empty($departmentIDs) ? [] : $departmentIDs;
    }

    /**
     * Gets the departments with which the teacher is associated
     *
     * @param int $teacherID the teacher's id
     *
     * @return array the departments with which the teacher is associated id => name
     */
    public static function getDepartmentNames($teacherID)
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("d.short_name_$shortTag AS name")
            ->from('#__thm_organizer_departments AS d')
            ->innerJoin('#__thm_organizer_department_resources AS dr ON dr.departmentID = d.id')
            ->where("teacherID = $teacherID");
        $dbo->setQuery($query);
        $departments = THM_OrganizerHelperComponent::executeQuery('loadColumn', []);

        return empty($departments) ? [] : $departments;
    }

    /**
     * Generates a preformatted teacher text based upon organizer's internal data
     *
     * @param int  $teacherID the teacher's id
     * @param bool $short     Whether or not the teacher's forename should be abbrevieated
     *
     * @return string  the default name of the teacher
     */
    public static function getLNFName($teacherID, $short = false)
    {
        $teacher = JTable::getInstance('teachers', 'thm_organizerTable');
        $teacher->load($teacherID);

        $return = '';
        if (!empty($teacher->id)) {
            if (!empty($teacher->forename)) {
                // Getting the first letter by other means can cause encoding problems with 'interesting' first names.
                $forename = $short ? mb_substr($teacher->forename, 0, 1) . '.' : $teacher->forename;
            }
            $return = $teacher->surname;
            $return .= empty($forename) ? '' : ", $forename";
        }

        return $return;
    }

    /**
     * Checks for the teacher entry in the database, creating it as necessary. Adds the id to the teacher entry in the
     * schedule.
     *
     * @param string $gpuntisID the teacher's gpuntis ID
     * @param object $data      the teacher data
     *
     * @return int the id of the teacher on success, otherwise 0
     */
    public static function getIDFromScheduleData($gpuntisID, $data)
    {
        $teacherTable   = JTable::getInstance('teachers', 'thm_organizerTable');
        $loadCriteria   = [];
        $loadCriteria[] = ['gpuntisID' => $gpuntisID];

        if (!empty($data->username)) {
            $loadCriteria[] = ['username' => $data->username];
        }
        if (!empty($data->forename)) {
            $loadCriteria[] = ['surname' => $data->surname, 'forename' => $data->forename];
        }

        foreach ($loadCriteria as $criteria) {
            try {
                $success = $teacherTable->load($criteria);
            } catch (Exception $exc) {
                THM_OrganizerHelperComponent::message($exc->getMessage(), 'error');

                return 0;
            }

            if ($success) {
                return $teacherTable->id;
            }
        }

        // Entry not found
        $success = $teacherTable->save($data);

        return $success ? $teacherTable->id : 0;
    }

    /**
     * Checks whether the user is a registered teacher returning their internal teacher id if existent.
     *
     * @param int $userID the user id if empty the current user is used
     *
     * @return int the teacher id if the user is a teacher, otherwise 0
     */
    public static function getIDFromUserData($userID = null)
    {
        $user = JFactory::getUser();
        if (empty($user->id)) {
            return false;
        }

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')
            ->from('#__thm_organizer_teachers')
            ->where("username = '{$user->username}'");
        $dbo->setQuery($query);

        return (int)THM_OrganizerHelperComponent::executeQuery('loadResult');
    }

    /**
     * Getter method for teachers in database. Only retrieving the IDs here allows for formatting the names according to
     * the needs of the calling views.
     *
     * @return array  the scheduled teachers which the user has access to
     */
    public static function getPlanTeachers()
    {
        $user = JFactory::getUser();
        if (empty($user->id)) {
            return [];
        }

        $input         = THM_OrganizerHelperComponent::getInput();
        $departmentIDs = explode(',', $input->getString('departmentIDs'));
        $isTeacher     = self::getIDFromUserData();
        if (empty($departmentIDs) and empty($isTeacher)) {
            return [];
        }

        $departmentIDs = Joomla\Utilities\ArrayHelper::toInteger($departmentIDs);

        foreach ($departmentIDs as $key => $departmentID) {
            $departmentAccess = THM_OrganizerHelperAccess::allowSchedulingAccess(0, $departmentID);
            if (!$departmentAccess) {
                unset($departmentIDs[$key]);
            }
        }

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('DISTINCT lt.teacherID')
            ->from('#__thm_organizer_lesson_teachers AS lt')
            ->innerJoin('#__thm_organizer_teachers AS t ON t.id = lt.teacherID');

        $wherray = [];
        if ($isTeacher) {
            $wherray[] = "t.username = '{$user->username}'";
        }

        if (!empty($departmentIDs)) {
            $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.teacherID = lt.teacherID');

            $where = 'dr.departmentID IN (' . implode(',', $departmentIDs) . ')';

            $selectedPrograms = $input->getString('programIDs');

            if (!empty($selectedPrograms)) {
                $programIDs = "'" . str_replace(',', "', '", $selectedPrograms) . "'";
                $query->innerJoin('#__thm_organizer_lesson_subjects AS ls ON lt.subjectID = ls.id');
                $query->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.subjectID = ls.id');
                $query->innerJoin('#__thm_organizer_plan_pools AS ppo ON lp.poolID = ppo.id');

                $where .= " AND ppo.programID in ($programIDs)";
                $where = "($where)";
            }

            $wherray[] = $where;
        }

        $query->where(implode(' OR ', $wherray));
        $dbo->setQuery($query);
        $teacherIDs = THM_OrganizerHelperComponent::executeQuery('loadColumn', []);

        if (empty($teacherIDs)) {
            return [];
        }

        $teachers = [];
        foreach ($teacherIDs as $teacherID) {
            $teachers[THM_OrganizerHelperTeachers::getLNFName($teacherID)] = $teacherID;
        }

        ksort($teachers);

        return $teachers;
    }

    /**
     * Checks whether the teacher is associated with lessons
     *
     * @param string $table     the dynamic part of the table name
     * @param int    $teacherID the id of the teacher being checked
     *
     * @return bool true if the teacher is assigned to a lesson
     */
    public static function teaches($table, $teacherID)
    {
        if (empty($table)) {
            return false;
        }

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('COUNT(*)')->from("#__thm_organizer_{$table}_teachers")->where("teacherID = '$teacherID'");
        $dbo->setQuery($query);

        return (bool)THM_OrganizerHelperComponent::executeQuery('loadResult');
    }
}
