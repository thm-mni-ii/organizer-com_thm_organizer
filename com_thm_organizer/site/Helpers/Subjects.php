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
 * Provides general functions for subject access checks, data retrieval and display.
 */
class Subjects implements XMLValidator
{
    /**
     * Check if user is registered as a subject's teacher, optionally for a specific subject
     *
     * @param int $subjectID id of the course resource
     *
     * @return boolean true if the user is a registered teacher, otherwise false
     */
    public static function allowEdit($subjectID)
    {
        $user = Factory::getUser();

        if (empty($user->id)) {
            return false;
        }

        if (Access::isAdmin()) {
            return true;
        }

        if (empty($subjectID) or !Access::checkAssetInitialization('subject', $subjectID)) {
            return Access::allowDocumentAccess();
        }

        if (Access::allowDocumentAccess('subject', $subjectID)) {
            return true;
        }

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
     * Retrieves the (plan) subject name
     *
     * @param int     $subjectID the table id for the subject
     * @param string  $type      the type of the id (real or plan)
     * @param boolean $withNumber
     *
     * @return string the (plan) subject name
     */
    public static function getName($subjectID, $type, $withNumber = false)
    {
        $dbo         = Factory::getDbo();
        $languageTag = Languages::getShortTag();

        $query = $dbo->getQuery(true);
        $query->select("ps.name as psName, s.name_$languageTag as name");
        $query->select("s.short_name_$languageTag as shortName, s.abbreviation_$languageTag as abbreviation");
        $query->select('ps.subjectNo as psSubjectNo, s.externalID as subjectNo');

        if ($type == 'real') {
            $query->from('#__thm_organizer_subjects AS s');
            $query->leftJoin('#__thm_organizer_subject_mappings AS sm ON s.id = sm.subjectID');
            $query->leftJoin('#__thm_organizer_plan_subjects AS ps ON sm.plan_subjectID = ps.id');
            $query->where("s.id = '$subjectID'");
        } else {
            $query->from('#__thm_organizer_plan_subjects AS ps');
            $query->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id');
            $query->leftJoin('#__thm_organizer_subjects AS s ON s.id = sm.subjectID');
            $query->where("ps.id = '$subjectID'");
        }

        $dbo->setQuery($query);

        $names = OrganizerHelper::executeQuery('loadAssoc', []);
        if (empty($names)) {
            return '';
        }

        $suffix = '';

        if ($withNumber) {
            if (!empty($names['subjectNo'])) {
                $suffix .= " ({$names['subjectNo']})";
            } elseif (!empty($names['psSubjectNo'])) {
                $suffix .= " ({$names['psSubjectNo']})";
            }
        }

        if (!empty($names['name'])) {
            return $names['name'] . $suffix;
        }

        if (!empty($names['shortName'])) {
            return $names['shortName'] . $suffix;
        }

        return empty($names['psName']) ? $names['abbreviation'] . $suffix : $names['psName'] . $suffix;
    }

    /**
     * Looks up the names of the (plan) programs associated with the subject
     *
     * @param int    $subjectID the id of the (plan) subject
     * @param string $type      the type of the reference subject (plan|real)
     *
     * @return array the associated program names
     */
    public static function getPrograms($subjectID, $type)
    {
        $names       = [];
        $dbo         = Factory::getDbo();
        $languageTag = Languages::getShortTag();

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$languageTag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
        $query->select('ppr.name AS ppName, ' . $query->concatenate($nameParts, "") . ' AS name');

        if ($type == 'real') {
            $query->select('p.id');
            $query->from('#__thm_organizer_programs AS p');
            $query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
            $query->innerJoin('#__thm_organizer_mappings AS m1 ON m1.programID = p.id');
            $query->innerJoin('#__thm_organizer_mappings AS m2 ON m1.lft < m2.lft AND m1.rgt > m2.rgt');
            $query->leftJoin('#__thm_organizer_plan_programs AS ppr ON ppr.programID = p.id');
            $query->where("m2.subjectID = '$subjectID'");
        } else {
            $query->select('ppr.id');
            $query->from('#__thm_organizer_plan_programs AS ppr');
            $query->innerJoin('#__thm_organizer_plan_pools AS ppl ON ppl.programID = ppr.id');
            $query->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.poolID = ppl.id');
            $query->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.id = lp.subjectID');
            $query->leftJoin('#__thm_organizer_programs AS p ON ppr.programID = p.id');
            $query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
            $query->where("ls.subjectID = '$subjectID'");
        }

        $dbo->setQuery($query);

        $results = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($results)) {
            return [];
        }

        foreach ($results as $result) {
            $names[$result['id']] = empty($result['name']) ? $result['ppName'] : $result['name'];
        }

        return $names;
    }

    /**
     * Retrieves a list of lessons associated with a subject
     *
     * @return array the lessons associated with the subject
     */
    public static function getLessons()
    {
        $input = OrganizerHelper::getInput();

        $subjectIDs = ArrayHelper::toInteger(explode(',', $input->getString('subjectIDs', '')));
        if (empty($subjectIDs[0])) {
            return [];
        }
        $subjectIDs = implode(',', $subjectIDs);

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
            ->where("ls.subjectID IN ($subjectIDs)")
            ->where("l.delta != 'removed'")
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

            $lesson['subjectName'] = self::getName($lesson['subjectID'], 'plan', true);

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
     * Retrieves the resource id using the Untis ID. Creates the resource id if unavailable.
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $untisID       the id of the resource in Untis
     *
     * @return void modifies the scheduleModel, setting the id property of the resource
     */
    public static function setID(&$scheduleModel, $subjectIndex)
    {
        $subject = $scheduleModel->schedule->subjects->$subjectIndex;

        $table        = OrganizerHelper::getTable('Plan_Subjects');
        $loadCriteria = ['subjectIndex' => $subjectIndex];
        $exists       = $table->load($loadCriteria);

        if ($exists) {
            $altered = false;
            foreach ($subject as $key => $value) {
                if (property_exists($table, $key) and empty($table->$key) and !empty($value)) {
                    $table->set($key, $value);
                    $altered = true;
                }
            }

            if ($altered) {
                $table->store();
            }
        } else {
            $table->save($subject);
        }

        $scheduleModel->schedule->subjects->$subjectIndex->id = $table->id;

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
        if (empty($xmlObject->subjects)) {
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_SUBJECTS_MISSING');

            return;
        }

        $scheduleModel->schedule->subjects = new stdClass;

        foreach ($xmlObject->subjects->children() as $node) {
            self::validateIndividual($scheduleModel, $node);
        }

        if (!empty($scheduleModel->scheduleWarnings['SUBJECT-NO'])) {
            $warningCount = $scheduleModel->scheduleWarnings['SUBJECT-NO'];
            unset($scheduleModel->scheduleWarnings['SUBJECT-NO']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(Languages::_('THM_ORGANIZER_WARNING_SUBJECTNO_MISSING'), $warningCount);
        }

        if (!empty($scheduleModel->scheduleWarnings['SUBJECT-FIELD'])) {
            $warningCount = $scheduleModel->scheduleWarnings['SUBJECT-FIELD'];
            unset($scheduleModel->scheduleWarnings['SUBJECT-FIELD']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(Languages::_('THM_ORGANIZER_WARNING_SUBJECT_FIELD_MISSING'), $warningCount);
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
        $untisID = trim((string)$node[0]['id']);
        if (empty($untisID)) {
            if (!in_array(Languages::_('THM_ORGANIZER_ERROR_SUBJECT_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_SUBJECT_ID_MISSING');
            }

            return;
        }

        $untisID      = str_replace('SU_', '', $untisID);
        $subjectIndex = $scheduleModel->schedule->departmentname . '_' . $untisID;
        $name         = trim((string)$node->longname);

        if (empty($name)) {
            $scheduleModel->scheduleErrors[]
                = sprintf(Languages::_('THM_ORGANIZER_ERROR_SUBJECT_LONGNAME_MISSING'), $untisID);

            return;
        }


        $subjectNo = trim((string)$node->text);

        if (empty($subjectNo)) {
            $scheduleModel->scheduleWarnings['SUBJECT-NO'] = empty($scheduleModel->scheduleWarnings['SUBJECT-NO']) ?
                1 : $scheduleModel->scheduleWarnings['SUBJECT-NO']++;

            $subjectNo = '';
        }


        $fieldID      = str_replace('DS_', '', trim($node->subject_description[0]['id']));
        $invalidField = (empty($fieldID) or empty($scheduleModel->schedule->fields->$fieldID));
        $fieldID      = $invalidField ? null : $scheduleModel->schedule->fields->$fieldID->id;

        $subject               = new stdClass;
        $subject->fieldID      = $fieldID;
        $subject->gpuntisID    = $untisID;
        $subject->name         = $name;
        $subject->subjectIndex = $subjectIndex;
        $subject->subjectNo    = $subjectNo;

        $scheduleModel->schedule->subjects->$subjectIndex = $subject;
        self::setID($scheduleModel, $subjectIndex);
    }
}
