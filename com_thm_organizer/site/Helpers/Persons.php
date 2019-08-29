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
 * Provides general functions for person access checks, data retrieval and display.
 */
class Persons extends ResourceHelper implements DepartmentAssociated, Selectable
{
    const COORDINATES = 1;

    const NO = 0;

    const TEACHER = 2;

    const YES = 1;

    /**
     * Retrieves person entries from the database
     *
     * @return string  the persons who hold courses for the selected program and pool
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

        $boundarySet = Mappings::getMappings($resourceType, $resourceID);

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT t.id, t.forename, t.surname')->from('#__thm_organizer_persons AS t');
        $query->innerJoin('#__thm_organizer_subject_persons AS st ON st.personID = t.id');
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

        $persons = OrganizerHelper::executeQuery('loadObjectList');
        if (empty($persons)) {
            return '[]';
        }

        foreach ($persons as $key => $value) {
            $persons[$key]->name = empty($value->forename) ?
                $value->surname : $value->surname . ', ' . $value->forename;
        }

        return json_encode($persons);
    }

    /**
     * Checks for multiple person entries (roles) for a subject and removes the lesser
     *
     * @param array &$list the list of persons with a role for the subject
     *
     * @return void  removes duplicate list entries dependent on role
     */
    private static function ensureUnique(&$list)
    {
        $keysToIds = [];
        foreach ($list as $key => $item) {
            $keysToIds[$key] = $item['id'];
        }

        $valueCount = array_count_values($keysToIds);
        foreach ($list as $key => $item) {
            $unset = ($valueCount[$item['id']] > 1 and $item['role'] > 1);
            if ($unset) {
                unset($list[$key]);
            }
        }
    }

    /**
     * Retrieves the persons associated with a given subject, optionally filtered by role.
     *
     * @param int  $subjectID the subject's id
     * @param int  $role      represents the person's role for the subject
     * @param bool $multiple  whether or not multiple results are desired
     * @param bool $unique    whether or not unique results are desired
     *
     * @return array  an array of person data
     */
    public static function getDataBySubject($subjectID, $role = null, $multiple = false, $unique = true)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('t.id, t.surname, t.forename, t.title, t.username, u.id AS userID, role, untisID');
        $query->from('#__thm_organizer_persons AS t');
        $query->innerJoin('#__thm_organizer_subject_persons AS st ON t.id = st.personID ');
        $query->leftJoin('#__users AS u ON t.username = u.username');
        $query->where("st.subjectID = '$subjectID' ");

        if (!empty($role)) {
            $query->where("st.role = '$role'");
        }

        $query->order('surname ASC');
        $dbo->setQuery($query);

        if ($multiple) {
            $personList = OrganizerHelper::executeQuery('loadAssocList');
            if (empty($personList)) {
                return [];
            }

            if ($unique) {
                self::ensureUnique($personList);
            }

            return $personList;
        }

        return OrganizerHelper::executeQuery('loadAssoc', []);
    }

    /**
     * Generates a default person text based upon organizer's internal data
     *
     * @param int $personID the person's id
     *
     * @return string  the default name of the person
     */
    public static function getDefaultName($personID)
    {
        $person = self::getTable();
        $person->load($personID);

        $return = '';
        if (!empty($person->id)) {
            $title    = empty($person->title) ? '' : "{$person->title} ";
            $forename = empty($person->forename) ? '' : "{$person->forename} ";
            $surname  = $person->surname;
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
            ->where("personID = $resourceID");
        $dbo->setQuery($query);
        $departmentIDs = OrganizerHelper::executeQuery('loadColumn', []);

        return empty($departmentIDs) ? [] : $departmentIDs;
    }

    /**
     * Gets the departments with which the person is associated
     *
     * @param int $personID the person's id
     *
     * @return array the departments with which the person is associated id => name
     */
    public static function getDepartmentNames($personID)
    {
        $dbo   = Factory::getDbo();
        $tag   = Languages::getTag();
        $query = $dbo->getQuery(true);

        $query->select("d.shortName_$tag AS name")
            ->from('#__thm_organizer_departments AS d')
            ->innerJoin('#__thm_organizer_department_resources AS dr ON dr.departmentID = d.id')
            ->where("personID = $personID");
        $dbo->setQuery($query);
        $departments = OrganizerHelper::executeQuery('loadColumn', []);

        return empty($departments) ? [] : $departments;
    }

    /**
     * Generates a preformatted person text based upon organizer's internal data
     *
     * @param int  $personID the person's id
     * @param bool $short    Whether or not the person's forename should be abbrevieated
     *
     * @return string  the default name of the person
     */
    public static function getLNFName($personID, $short = false)
    {
        $person = self::getTable();
        $person->load($personID);

        $return = '';
        if (!empty($person->id)) {
            if (!empty($person->forename)) {
                // Getting the first letter by other means can cause encoding problems with 'interesting' first names.
                $forename = $short ? mb_substr($person->forename, 0, 1) . '.' : $person->forename;
            }
            $return = $person->surname;
            $return .= empty($forename) ? '' : ", $forename";
        }

        return $return;
    }

    /**
     * Checks whether the user is a registered person returning their internal person id if existent.
     *
     * @param int $userID the user id if empty the current user is used
     *
     * @return int the person id if the user is a person, otherwise 0
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
            ->from('#__thm_organizer_persons')
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
        foreach (self::getResources() as $person) {
            $name      = self::getLNFName($person['id']);
            $options[] = HTML::_('select.option', $person['id'], $name);
        }

        return $options;
    }

    /**
     * Getter method for persons in database. Only retrieving the IDs here allows for formatting the names according to
     * the needs of the calling views.
     *
     * @return array  the scheduled persons which the user has access to
     */
    public static function getResources()
    {
        $user = Factory::getUser();
        if (empty($user->id)) {
            return [];
        }

        $departmentIDs = Input::getFilterIDs('department');
        $thisPersonID  = self::getIDByUserID();
        if (empty($departmentIDs) and empty($thisPersonID)) {
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

        $query->select('DISTINCT p.*')
            ->from('#__thm_organizer_persons AS p')
            ->innerJoin('#__thm_organizer_instance_persons AS ip ON ip.personID = p.id')
            ->where('p.active = 1')
            ->order('p.surname, p.forename');

        $wherray = [];
        if ($thisPersonID) {
            $wherray[] = "p.username = '{$user->username}'";
        }

        if (count($departmentIDs)) {
            $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.personID = p.id');

            $where = 'dr.departmentID IN (' . implode(',', $departmentIDs) . ')';

            $selectedCategories = Input::getFilterIDs('category');

            if (!empty($selectedPrograms)) {
                $categoryIDs = "'" . str_replace(',', "', '", $selectedCategories) . "'";
                $query->innerJoin('#__thm_organizer_person_groups AS pg ON pg.personID = ip.id')
                    ->innerJoin('#__thm_organizer_groups AS g ON g.id = pg.groupID');

                $where .= " AND g.categoryID in ($categoryIDs)";
                $where = "($where)";
            }

            $wherray[] = $where;
        }

        $query->where(implode(' OR ', $wherray));
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

    /**
     * Function to sort persons by their surnames and forenames.
     *
     * @param array &$persons the persons array to sort.
     */
    public static function nameSort(&$persons)
    {
        uasort($persons, function ($personOne, $personTwo) {
            if ($personOne['surname'] == $personTwo['surname']) {
                return $personOne['forename'] > $personTwo['forename'];
            }

            return $personOne['surname'] > $personTwo['surname'];
        });
    }

    /**
     * Function to sort persons by their roles.
     *
     * @param array &$persons the persons array to sort.
     */
    public static function roleSort(&$persons)
    {
        uasort($persons, function ($personOne, $personTwo) {
            $roleOne = isset($personOne['role'][self::COORDINATES]);
            $roleTwo = isset($personTwo['role'][self::COORDINATES]);
            if ($roleOne or !$roleTwo) {
                return 1;
            }

            return -1;
        });
    }

    /**
     * Checks whether the person is associated with lessons
     *
     * @param string $table    the dynamic part of the table name
     * @param int    $personID the id of the person being checked
     *
     * @return bool true if the person is assigned to a lesson
     */
    public static function teaches($table, $personID)
    {
        if (empty($table)) {
            return false;
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('COUNT(*)')->from("#__thm_organizer_{$table}_persons")->where("personID = '$personID'");
        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadResult');
    }
}
