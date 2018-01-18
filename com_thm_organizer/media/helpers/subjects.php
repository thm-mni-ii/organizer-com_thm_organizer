<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperSubjects
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'departments.php';

/**
 * Provides validation methods for xml subject objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperSubjects
{
    /**
     * Retrieves the table id if existent.
     *
     * @param string $subjectIndex the subject index (dept. abbreviation + gpuntis id)
     *
     * @return mixed int id on success, otherwise null
     */
    public static function getID($subjectIndex)
    {
        $table  = JTable::getInstance('plan_subjects', 'thm_organizerTable');
        $data   = ['subjectIndex' => $subjectIndex];
        $exists = $table->load($data);
        if ($exists) {
            return $exists ? $table->id : null;
        }

        return null;
    }

    /**
     * Retrieves the (plan) subject name
     *
     * @param int    $subjectID the table id for the subject
     * @param string $type      the type of the id (real or plan)
     *
     * @return string the (plan) subject name
     */
    public static function getName($subjectID, $type, $withNumber = false)
    {
        $dbo         = JFactory::getDbo();
        $languageTag = THM_OrganizerHelperLanguage::getShortTag();

        $query = $dbo->getQuery(true);
        $query->select("ps.name as psName, s.name_$languageTag as name");
        $query->select("s.short_name_$languageTag as shortName, s.abbreviation_$languageTag as abbreviation");
        $query->select("ps.subjectNo as psSubjectNo, s.externalID as subjectNo");

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

        try {
            $names = $dbo->loadAssoc();
        } catch (RuntimeException $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return '';
        }

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
     * Attempts to get the plan subject's id, creating it if non-existent.
     *
     * @param object $subject the subject object
     *
     * @return mixed int on success, otherwise null
     */
    public static function getPlanResourceID($subjectIndex, $subject)
    {
        $subjectID = self::getID($subjectIndex);

        $table = JTable::getInstance('plan_subjects', 'thm_organizerTable');

        if (!empty($subjectID)) {
            $table->load($subjectID);
        }

        $data                 = [];
        $data['subjectIndex'] = $subjectIndex;
        $data['gpuntisID']    = $subject->gpuntisID;

        if (!empty($subject->fieldID)) {
            $data['fieldID'] = $subject->fieldID;
        }

        $data['subjectNo'] = $subject->subjectNo;
        $data['name']      = $subject->longname;

        $success = $table->save($data);

        return $success ? $table->id : null;

    }

    /**
     * Looks up the names of the (plan) programs associated with the subject
     *
     * @param int    $subjectID the id of the (plan) subject
     * @param string $type      the type of the reference subject (plan|real)
     *
     * @return array the associated program names
     *
     * @since version
     */
    public static function getPrograms($subjectID, $type)
    {
        $names       = [];
        $dbo         = JFactory::getDbo();
        $languageTag = THM_OrganizerHelperLanguage::getShortTag();

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$languageTag", "' ('", "d.abbreviation", "' '", "p.version", "')'"];
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

        try {
            $results = $dbo->loadAssocList();
        } catch (RuntimeException $exception) {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

            return $names;
        }


        foreach ($results as $result) {
            $names[$result['id']] = empty($result['name']) ? $result['ppName'] : $result['name'];
        }

        return $names;
    }
}
