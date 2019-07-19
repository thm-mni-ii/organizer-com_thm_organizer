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

/**
 * Provides general functions for teacher access checks, data retrieval and display.
 */
class Teachers extends ResourceHelper implements DepartmentAssociated, Selectable
{
    const COORDINATES = 1;

    const NO = 0;

    const TEACHER = 2;

    const YES = 1;

    /**
     * Retrieves teacher entries from the database
     *
     * @return string  the teachers who hold courses for the selected program and pool
     */
    public static function byProgramOrPool()
    {
        $programID = Input::getInt('programID', -1);
        $poolID    = Input::getInt('poolID', -1);

        if ($poolID > 0) {
            $resourceType = 'pool';
            $resourceID   = $poolID;
        } else {
            $resourceType = 'program';
            $resourceID   = $programID;
        }

        $boundarySet = Mappings::getBoundaries($resourceType, $resourceID);

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT t.id, t.forename, t.surname')->from('#__thm_organizer_teachers AS t');
        $query->innerJoin('#__thm_organizer_subject_teachers AS st ON st.teacherID = t.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = st.subjectID');
        if (!empty($boundarySet)) {
            $where   = '';
            $initial = true;
            foreach ($boundarySet as $boundaries) {
                $where   .= $initial ?
                    "((m.lft >= '{$boundaries['lft']}' AND m.rgt <= '{$boundaries['rgt']}')"
                    : " OR (m.lft >= '{$boundaries['lft']}' AND m.rgt <= '{$boundaries['rgt']}')";
                $initial = false;
            }

            $query->where($where . ')');
        }

        $query->order('t.surname');
        $dbo->setQuery($query);

        $teachers = OrganizerHelper::executeQuery('loadObjectList');
        if (empty($teachers)) {
            return '[]';
        }

        foreach ($teachers as $key => $value) {
            $teachers[$key]->name = empty($value->forename) ?
                $value->surname : $value->surname . ', ' . $value->forename;
        }

        return json_encode($teachers);
    }

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
     * @param int  $responsibility represents the teacher's level of responsibility for the subject
     * @param bool $multiple       whether or not multiple results are desired
     * @param bool $unique         whether or not unique results are desired
     *
     * @return array  an array of teacher data
     */
    public static function getDataBySubject($subjectID, $responsibility = null, $multiple = false, $unique = true)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('t.id, t.surname, t.forename, t.title, t.username, u.id AS userID, teacherResp, untisID');
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
            $teacherList = OrganizerHelper::executeQuery('loadAssocList');
            if (empty($teacherList)) {
                return [];
            }

            if ($unique) {
                self::ensureUnique($teacherList);
            }

            return $teacherList;
        }

        return OrganizerHelper::executeQuery('loadAssoc', []);
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
        $teacher = self::getTable();
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
     * Retrieves the ids of departments associated with the resource
     *
     * @param int $resourceID the id of the resource for which the associated departments are requested
     *
     * @return array the ids of departments associated with the resource
     */
    public static function getDepartmentIDs($resourceID)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('departmentID')
            ->from('#__thm_organizer_department_resources')
            ->where("teacherID = $resourceID");
        $dbo->setQuery($query);
        $departmentIDs = OrganizerHelper::executeQuery('loadColumn', []);

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
        $dbo   = Factory::getDbo();
        $tag   = Languages::getTag();
        $query = $dbo->getQuery(true);

        $query->select("d.short_name_$tag AS name")
            ->from('#__thm_organizer_departments AS d')
            ->innerJoin('#__thm_organizer_department_resources AS dr ON dr.departmentID = d.id')
            ->where("teacherID = $teacherID");
        $dbo->setQuery($query);
        $departments = OrganizerHelper::executeQuery('loadColumn', []);

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
        $teacher = self::getTable();
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
     * Checks whether the user is a registered teacher returning their internal teacher id if existent.
     *
     * @param int $userID the user id if empty the current user is used
     *
     * @return int the teacher id if the user is a teacher, otherwise 0
     */
    public static function getIDByUserID($userID = null)
    {
        $user = Factory::getUser($userID);
        if (empty($user->id)) {
            return false;
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')
            ->from('#__thm_organizer_teachers')
            ->where("username = '{$user->username}'");
        $dbo->setQuery($query);

        return (int)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Retrieves a list of resources in the form of name => id.
     *
     * @return array the resources, or empty
     */
    public static function getOptions()
    {
        $options = [];
        foreach (self::getResources() as $teacher) {
            $name      = self::getLNFName($teacher['id']);
            $options[] = HTML::_('select.option', $teacher['id'], $name);
        }

        return $options;
    }

    /**
     * Getter method for teachers in database. Only retrieving the IDs here allows for formatting the names according to
     * the needs of the calling views.
     *
     * @return array  the scheduled teachers which the user has access to
     */
    public static function getResources()
    {
        $user = Factory::getUser();
        if (empty($user->id)) {
            return [];
        }

        $departmentIDs = Input::getFilterIDs('department');
        $isTeacher     = self::getIDByUserID();
        if (empty($departmentIDs) and empty($isTeacher)) {
            return [];
        }

        foreach ($departmentIDs as $key => $departmentID) {
            $departmentAccess = Access::allowViewAccess($departmentID);
            if (!$departmentAccess) {
                unset($departmentIDs[$key]);
            }
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('DISTINCT t.*')
            ->from('#__thm_organizer_teachers AS t')
            ->innerJoin('#__thm_organizer_lesson_teachers AS lt ON lt.teacherID = t.id')
            ->order('t.surname, t.forename');

        $wherray = [];
        if ($isTeacher) {
            $wherray[] = "t.username = '{$user->username}'";
        }

        if (count($departmentIDs)) {
            $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.teacherID = lt.teacherID');

            $where = 'dr.departmentID IN (' . implode(',', $departmentIDs) . ')';

            $selectedPrograms = Input::getFilterIDs('program');

            if (!empty($selectedPrograms)) {
                $programIDs = "'" . str_replace(',', "', '", $selectedPrograms) . "'";
                $query->innerJoin('#__thm_organizer_lesson_courses AS lcrs ON lcrs.id = lt.lessonCourseID')
                    ->innerJoin('#__thm_organizer_lesson_groups AS lg ON lg.lessonCourseID = lcrs.id')
                    ->innerJoin('#__thm_organizer_groups AS gr ON gr.id = lg.groupID');

                $where .= " AND gr.programID in ($programIDs)";
                $where = "($where)";
            }

            $wherray[] = $where;
        }

        $query->where(implode(' OR ', $wherray));
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

    /**
     * Function to sort teachers by their surnames and forenames.
     *
     * @param array &$teachers the teachers array to sort.
     */
    public static function nameSort(&$teachers)
    {
        uasort($teachers, function ($teacherOne, $teacherTwo) {
            $oneResp = isset($teacherOne['teacherResp'][self::COORDINATES]);
            $twoResp = isset($teacherTwo['teacherResp'][self::COORDINATES]);
            if ($oneResp or !$twoResp) {
                return 1;
            }

            return -1;
        });
    }

    /**
     * Function to sort teachers by their surnames and forenames.
     *
     * @param array &$teachers the teachers array to sort.
     */
    public static function respSort(&$teachers)
    {
        uasort($teachers, function ($teacherOne, $teacherTwo) {
            if ($teacherOne['surname'] == $teacherTwo['surname']) {
                return $teacherOne['forename'] > $teacherTwo['forename'];
            }

            return $teacherOne['surname'] > $teacherTwo['surname'];
        });
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

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('COUNT(*)')->from("#__thm_organizer_{$table}_teachers")->where("teacherID = '$teacherID'");
        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadResult');
    }
}
