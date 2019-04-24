<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/departments.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/subjects.php';

/**
 * Provides validation methods for xml subject objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperXMLSubjects
{
    /**
     * Checks whether subject nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the xml object being validated
     *
     * @return void
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->subjects)) {
            $scheduleModel->scheduleErrors[] = \JText::_('COM_THM_ORGANIZER_ERROR_SUBJECTS_MISSING');

            return;
        }

        $scheduleModel->schedule->subjects = new \stdClass;

        foreach ($xmlObject->subjects->children() as $subjectNode) {
            self::validateIndividual($scheduleModel, $subjectNode);
        }

        if (!empty($scheduleModel->scheduleWarnings['SUBJECT-NO'])) {
            $warningCount = $scheduleModel->scheduleWarnings['SUBJECT-NO'];
            unset($scheduleModel->scheduleWarnings['SUBJECT-NO']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(\JText::_('COM_THM_ORGANIZER_WARNING_SUBJECTNO_MISSING'), $warningCount);
        }

        if (!empty($scheduleModel->scheduleWarnings['SUBJECT-FIELD'])) {
            $warningCount = $scheduleModel->scheduleWarnings['SUBJECT-FIELD'];
            unset($scheduleModel->scheduleWarnings['SUBJECT-FIELD']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(\JText::_('COM_THM_ORGANIZER_WARNING_SUBJECT_FIELD_MISSING'), $warningCount);
        }
    }

    /**
     * Checks whether subject nodes have the expected structure and required
     * information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$subjectNode   the subject node to be validated
     *
     * @return void
     */
    private static function validateIndividual(&$scheduleModel, &$subjectNode)
    {
        $subjectID = trim((string)$subjectNode[0]['id']);
        if (empty($subjectID)) {
            if (!in_array(\JText::_('COM_THM_ORGANIZER_ERROR_SUBJECT_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = \JText::_('COM_THM_ORGANIZER_ERROR_SUBJECT_ID_MISSING');
            }

            return;
        }

        $longName = trim((string)$subjectNode->longname);
        if (empty($longName)) {
            $scheduleModel->scheduleErrors[]
                = sprintf(\JText::_('COM_THM_ORGANIZER_ERROR_SUBJECT_LONGNAME_MISSING'), $subjectID);

            return;
        }

        $subjectID    = str_replace('SU_', '', $subjectID);
        $subjectIndex = $scheduleModel->schedule->departmentname . '_' . $subjectID;

        $subject            = new \stdClass;
        $subject->gpuntisID = $subjectID;
        $subject->name      = $subjectID;
        $subject->longname  = $longName;

        $subjectNo = trim((string)$subjectNode->text);

        if (empty($subjectNo)) {
            $scheduleModel->scheduleWarnings['SUBJECT-NO'] = empty($scheduleModel->scheduleWarnings['SUBJECT-NO']) ?
                1 : $scheduleModel->scheduleWarnings['SUBJECT-NO'] + 1;

            $subjectNo = '';
        }

        $subject->subjectNo = $subjectNo;

        $fieldID      = str_replace('DS_', '', trim($subjectNode->subject_description[0]['id']));
        $invalidField = (empty($fieldID) or empty($scheduleModel->schedule->fields->$fieldID));

        if ($invalidField) {
            $subject->description = '';
            $subject->fieldID     = null;
        } else {
            $subject->description = $fieldID;
            $subject->fieldID     = $scheduleModel->schedule->fields->$fieldID->id;
        }

        // This requires the field, gpuntisID, longname and subjectNo have been set (mostly) => order important.
        $subject->id = THM_OrganizerHelperSubjects::getPlanResourceID($subjectIndex, $subject);

        $scheduleModel->schedule->subjects->$subjectIndex = $subject;
    }
}
