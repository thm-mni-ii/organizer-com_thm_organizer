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

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/teachers.php';

/**
 * Provides functions for XML teacher validation and modeling.
 */
class THM_OrganizerHelperXMLTeachers
{
    /**
     * Checks whether teacher nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the xml object being validated
     *
     * @return void
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->teachers)) {
            $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_TEACHERS_MISSING");

            return;
        }

        $scheduleModel->schedule->teachers = new stdClass;

        foreach ($xmlObject->teachers->children() as $teacherNode) {
            self::validateIndividual($scheduleModel, $teacherNode);
        }

        if (!empty($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID'])) {
            $warningCount = $scheduleModel->scheduleWarnings['TEACHER-EXTERNALID'];
            unset($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']);
            $scheduleModel->scheduleWarnings[] = sprintf(JText::_('COM_THM_ORGANIZER_WARNING_TEACHER_EXTID_MISSING'),
                $warningCount);
        }

        if (!empty($scheduleModel->scheduleWarnings['TEACHER-FORENAME'])) {
            $warningCount = $scheduleModel->scheduleWarnings['TEACHER-FORENAME'];
            unset($scheduleModel->scheduleWarnings['TEACHER-FORENAME']);
            $scheduleModel->scheduleWarnings[] = sprintf(JText::_('COM_THM_ORGANIZER_WARNING_FORENAME_MISSING'),
                $warningCount);
        }
    }

    /**
     * Checks whether teacher nodes have the expected structure and required
     * information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$teacherNode   the teacher node to be validated
     *
     * @return void
     */
    private static function validateIndividual(&$scheduleModel, &$teacherNode)
    {
        $internalID = trim((string)$teacherNode[0]['id']);
        if (empty($internalID)) {
            if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_TEACHER_ID_MISSING"), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_TEACHER_ID_MISSING");
            }

            return;
        }

        $internalID = str_replace('TR_', '', $internalID);

        $surname = trim((string)$teacherNode->surname);
        if (empty($surname)) {
            $scheduleModel->scheduleErrors[] = sprintf(JText::_('COM_THM_ORGANIZER_ERROR_TEACHER_SURNAME_MISSING'),
                $internalID);

            return false;
        }

        $externalID = trim((string)$teacherNode->external_name);

        if (empty($externalID)) {
            $scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']
                = empty($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']) ?
                1 : $scheduleModel->scheduleWarnings['TEACHER-EXTERNALID'] + 1;
        } else {
            $externalID = str_replace('TR_', '', $externalID);
        }

        $teacher = new stdClass;
        if (empty($externalID)) {
            $teacherID          = $internalID;
            $teacher->gpuntisID = $internalID;
        } else {
            $teacherID          = $externalID;
            $teacher->gpuntisID = $externalID;
        }

        $teacher->localUntisID = $internalID;
        $teacher->surname      = $surname;

        $fieldID        = str_replace('DS_', '', trim($teacherNode->teacher_description[0]['id']));
        $invalidFieldID = (empty($fieldID) or empty($scheduleModel->schedule->fields->{$fieldID}));
        if ($invalidFieldID) {
            $teacher->description = '';
            $teacher->fieldID     = null;
        } else {
            $teacher->description = $fieldID;
            $teacher->fieldID     = $scheduleModel->schedule->fields->{$fieldID}->id;
        }

        $forename = trim((string)$teacherNode->forename);
        if (empty($forename)) {
            $scheduleModel->scheduleWarnings['TEACHER-FORENAME']
                = empty($scheduleModel->scheduleWarnings['TEACHER-FORENAME']) ?
                1 : $scheduleModel->scheduleWarnings['TEACHER-FORENAME'] + 1;
        }

        $teacher->forename = empty($forename) ? '' : $forename;

        $title          = trim((string)$teacherNode->title);
        $teacher->title = empty($title) ? '' : $title;

        $userName          = trim((string)$teacherNode->payrollnumber);
        $teacher->username = empty($userName) ? '' : $userName;


        $teacher->id = THM_OrganizerHelperTeachers::getID($teacherID, $teacher);
        THM_OrganizerHelperDepartments::setDepartmentResource($teacher->id, 'teacherID');

        $scheduleModel->schedule->teachers->$teacherID = $teacher;
    }
}
