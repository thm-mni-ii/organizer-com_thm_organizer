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
 * Provides general functions for subject access checks, data retrieval and display.
 */
class Subjects implements Selectable
{
    /**
     * Check if user is registered as a subject's coordinator.
     *
     * @param int $subjectID id of the subject
     *
     * @return boolean true if the user registered as a coordinator, otherwise false
     */
    public static function coordinates($subjectID)
    {
        $user = Factory::getUser();

        // Teacher coordinator responsibility association from the documentation system
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('COUNT(*)')
            ->from('#__thm_organizer_subject_teachers AS st')
            ->innerJoin('#__thm_organizer_teachers AS t ON t.id = st.teacherID')
            ->where("t.username = '{$user->username}'")
            ->where("st.subjectID = '$subjectID'")
            ->where("teacherResp = '1'");

        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Retrieves the left and right boundaries of the nested program or pool
     *
     * @return array
     */
    private static function getBoundaries()
    {
        $programBoundaries = Mappings::getBoundaries('program', Input::getInt('programID'));

        if (empty($programBoundaries)) {
            return [];
        }

        $poolBoundaries = Mappings::getBoundaries('pool', Input::getInt('poolID'));

        $validBoundaries = (!empty($poolBoundaries) and self::poolInProgram($poolBoundaries, $programBoundaries));
        if ($validBoundaries) {
            return $poolBoundaries;
        }

        return $programBoundaries;
    }

    /**
     * Retrieves the (plan) subject name
     *
     * @param int     $subjectID the table id for the subject
     * @param string  $type      the type of the id (real or plan)
     * @param boolean $withNumber
     *
     * @return string the (plan) subject name
     */
    public static function getName($subjectID, $withNumber = false)
    {
        $dbo = Factory::getDbo();
        $tag = Languages::getTag();

        $query = $dbo->getQuery(true);
        $query->select("co.name as courseName, s.name_$tag as name")
            ->select("s.short_name_$tag as shortName, s.abbreviation_$tag as abbreviation")
            ->select('co.subjectNo as courseSubjectNo, s.externalID as subjectNo')
            ->from('#__thm_organizer_subjects AS s')
            ->leftJoin('#__thm_organizer_subject_mappings AS sm ON s.id = sm.subjectID')
            ->leftJoin('#__thm_organizer_courses AS co ON co.id = sm.courseID')
            ->where("s.id = '$subjectID'");

        $dbo->setQuery($query);

        $names = OrganizerHelper::executeQuery('loadAssoc', []);
        if (empty($names)) {
            return '';
        }

        $suffix = '';

        if ($withNumber) {
            if (!empty($names['subjectNo'])) {
                $suffix .= " ({$names['subjectNo']})";
            } elseif (!empty($names['courseSubjectNo'])) {
                $suffix .= " ({$names['courseSubjectNo']})";
            }
        }

        if (!empty($names['name'])) {
            return $names['name'] . $suffix;
        }

        if (!empty($names['shortName'])) {
            return $names['shortName'] . $suffix;
        }

        return empty($names['courseName']) ? $names['abbreviation'] . $suffix : $names['courseName'] . $suffix;
    }

    /**
     * Retrieves the selectable options for the resource.
     *
     * @return array the available options
     */
    public static function getOptions()
    {
        $options = [];
        foreach (self::getResources() as $subject) {
            $options[] = HTML::_('select.option', $subject['id'], $subject['name']);
        }

        return $options;
    }

    /**
     * Looks up the names of the programs associated with the subject
     *
     * @param int $subjectID the id of the (plan) subject
     *
     * @return array the associated program names
     */
    public static function getPrograms($subjectID)
    {
        $dbo   = Factory::getDbo();
        $names = [];
        $tag   = Languages::getTag();

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$tag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
        $query->select('cat.name AS categoryName, ' . $query->concatenate($nameParts, "") . ' AS name')
            ->select('p.id')
            ->from('#__thm_organizer_programs AS p')
            ->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id')
            ->innerJoin('#__thm_organizer_mappings AS m1 ON m1.programID = p.id')
            ->innerJoin('#__thm_organizer_mappings AS m2 ON m1.lft < m2.lft AND m1.rgt > m2.rgt')
            ->leftJoin('#__thm_organizer_categories AS cat ON cat.programID = p.id')
            ->where("m2.subjectID = '$subjectID'");

        $dbo->setQuery($query);

        $results = OrganizerHelper::executeQuery('loadAssocList', []);
        if (empty($results)) {
            return $results;
        }

        foreach ($results as $result) {
            $names[$result['id']] = empty($result['name']) ? $result['categoryName'] : $result['name'];
        }

        return $names;
    }

    /**
     * Retrieves the resource items.
     *
     * @return array the available resources
     */
    public static function getResources()
    {
        $programID = Input::getInt('programID', -1);
        $teacherID = Input::getInt('teacherID', -1);
        if ($programID === -1 and $teacherID === -1) {
            return [];
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $tag = Languages::getTag();
        $query->select("DISTINCT s.id, s.name_$tag AS name, s.externalID, s.creditpoints")
            ->select('t.surname, t.forename, t.title, t.username')
            ->from('#__thm_organizer_subjects AS s')
            ->order('name')
            ->group('s.id');

        $boundarySet = self::getBoundaries();
        if (!empty($boundarySet)) {
            $query->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = s.id');
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

        if ($teacherID !== -1) {
            $query->innerJoin('#__thm_organizer_subject_teachers AS st ON st.subjectID = s.id');
            $query->innerJoin('#__thm_organizer_teachers AS t ON st.teacherID = t.id');
            $query->where("st.teacherID = '$teacherID'");
        } else {
            $query->leftJoin('#__thm_organizer_subject_teachers AS st ON st.subjectID = s.id');
            $query->innerJoin('#__thm_organizer_teachers AS t ON st.teacherID = t.id');
            $query->where("st.teacherResp = '1'");
        }

        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

    /**
     * Retrieves the teachers associated with a given subject and their respective responsibilities for it.
     *
     * @param int  $subjectID the id of the subject with which the teachers must be associated
     *
     * @param null $responsibility
     *
     * @return array the teachers associated with the subject, empty if none were found.
     */
    public static function getTeachers($subjectID, $responsibility = null)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('t.id, t.surname, t.forename, t.fieldID, t.title, st.teacherResp')
            ->from('#__thm_organizer_teachers AS t')
            ->innerJoin('#__thm_organizer_subject_teachers AS st ON st.teacherID = t.id')
            ->where("st.subjectID = '$subjectID'");

        if (!empty($responsibility) and is_numeric($responsibility)) {
            $query->where("st.teacherResp = $responsibility");
        }
        $dbo->setQuery($query);

        $results = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($results)) {
            return [];
        }

        $teachers = [];
        foreach ($results as $teacher) {
            $forename = empty($teacher['forename']) ? '' : $teacher['forename'];
            $fullName = $teacher['surname'];
            $fullName .= empty($forename) ? '' : ", {$teacher['forename']}";
            if (empty($teachers[$teacher['id']])) {
                $teacher['forename']    = $forename;
                $teacher['title']       = empty($teacher['title']) ? '' : $teacher['title'];
                $teacher['teacherResp'] = [$teacher['teacherResp'] => $teacher['teacherResp']];
                $teachers[$fullName]    = $teacher;
                continue;
            }

            $teachers[$teacher['id']]['teacherResp'] = [$teacher['teacherResp'] => $teacher['teacherResp']];
        }

        Teachers::nameSort($teachers);
        Teachers::respSort($teachers);

        return $teachers;
    }

    /**
     * Checks whether the pool is subordinate to the selected program
     *
     * @param array $poolBoundaries    the pool's left and right values
     * @param array $programBoundaries the program's left and right values
     *
     * @return boolean  true if the pool is subordinate to the program,
     *                   otherwise false
     */
    private static function poolInProgram($poolBoundaries, $programBoundaries)
    {
        $first = $poolBoundaries[0];
        $last  = end($poolBoundaries);

        $leftValid  = $first['lft'] > $programBoundaries[0]['lft'];
        $rightValid = $last['rgt'] < $programBoundaries[0]['rgt'];
        if ($leftValid and $rightValid) {
            return true;
        }

        return false;
    }
}
