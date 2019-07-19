<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers\Validators;

use Organizer\Helpers\Departments;
use Organizer\Helpers\Languages;
use Organizer\Helpers\ResourceHelper;
use stdClass;

/**
 * Provides general functions for teacher access checks, data retrieval and display.
 */
class Teachers extends ResourceHelper implements UntisXMLValidator
{
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
        $teacher      = $scheduleModel->schedule->teachers->$untisID;
        $table        = self::getTable();
        $loadCriteria = [];

        if (!empty($teacher->username)) {
            $loadCriteria[] = ['username' => $teacher->username];
        }
        if (!empty($teacher->forename)) {
            $loadCriteria[] = ['surname' => $teacher->surname, 'forename' => $teacher->forename];
        }
        $loadCriteria[] = ['untisID' => $teacher->untisID];

        $extPattern = "/^[v]?[A-ZÀ-ÖØ-Þ][a-zß-ÿ]{1,3}([A-ZÀ-ÖØ-Þ][A-ZÀ-ÖØ-Þa-zß-ÿ]*)$/";
        foreach ($loadCriteria as $criteria) {
            $success = $table->load($criteria);

            if ($success) {
                $altered = false;
                foreach ($teacher as $key => $value) {
                    if (property_exists($table, $key) and empty($table->$key) and !empty($value)) {
                        $table->set($key, $value);
                        $altered = true;
                    }
                }

                $existingInvalid = empty(preg_match($extPattern, $table->untisID));
                $newValid        = preg_match($extPattern, $untisID);
                $overwriteUntis  = ($table->untisID != $untisID and $existingInvalid and $newValid);
                if ($overwriteUntis) {
                    $table->untisID = $untisID;
                    $altered        = true;
                }
                if ($altered) {
                    $table->store();
                }

                $scheduleModel->schedule->teachers->$untisID->id = $table->id;

                return;
            }
        }

        // Entry not found
        $table->save($teacher);
        $scheduleModel->schedule->teachers->$untisID->id = $table->id;

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
        if (empty($xmlObject->teachers)) {
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_TEACHERS_MISSING');

            return;
        }

        $scheduleModel->schedule->teachers = new stdClass;

        foreach ($xmlObject->teachers->children() as $teacherNode) {
            self::validateIndividual($scheduleModel, $teacherNode);
        }

        if (!empty($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID'])) {
            $warningCount = $scheduleModel->scheduleWarnings['TEACHER-EXTERNALID'];
            unset($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(Languages::_('THM_ORGANIZER_WARNING_TEACHER_EXTID_MISSING'), $warningCount);
        }

        if (!empty($scheduleModel->scheduleWarnings['TEACHER-FORENAME'])) {
            $warningCount = $scheduleModel->scheduleWarnings['TEACHER-FORENAME'];
            unset($scheduleModel->scheduleWarnings['TEACHER-FORENAME']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(Languages::_('THM_ORGANIZER_WARNING_FORENAME_MISSING'), $warningCount);
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
    public static function validateIndividual(&$scheduleModel, &$teacherNode)
    {
        $internalID = trim((string)$teacherNode[0]['id']);
        if (empty($internalID)) {
            if (!in_array(Languages::_('THM_ORGANIZER_ERROR_TEACHER_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_TEACHER_ID_MISSING');
            }

            return;
        }

        $internalID = str_replace('TR_', '', $internalID);
        $externalID = trim((string)$teacherNode->external_name);

        if (empty($externalID)) {
            $scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']
                = empty($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']) ?
                1 : $scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']++;
        } else {
            $externalID = str_replace('TR_', '', $externalID);
        }

        $untisID = empty($externalID) ? $internalID : $externalID;

        $surname = trim((string)$teacherNode->surname);
        if (empty($surname)) {
            $scheduleModel->scheduleErrors[]
                = sprintf(Languages::_('THM_ORGANIZER_ERROR_TEACHER_SURNAME_MISSING'), $internalID);

            return;
        }

        $forename = trim((string)$teacherNode->forename);
        if (empty($forename)) {
            $scheduleModel->scheduleWarnings['TEACHER-FORENAME']
                = empty($scheduleModel->scheduleWarnings['TEACHER-FORENAME']) ?
                1 : $scheduleModel->scheduleWarnings['TEACHER-FORENAME']++;
        }

        $fieldID        = str_replace('DS_', '', trim($teacherNode->teacher_description[0]['id']));
        $invalidFieldID = (empty($fieldID) or empty($scheduleModel->schedule->fields->$fieldID));
        $fieldID        = $invalidFieldID ? null : $scheduleModel->schedule->fields->$fieldID->id;
        $title          = trim((string)$teacherNode->title);
        $userName       = trim((string)$teacherNode->payrollnumber);

        $teacher           = new stdClass;
        $teacher->fieldID  = $fieldID;
        $teacher->forename = $forename;
        $teacher->untisID  = $untisID;
        $teacher->surname  = $surname;
        $teacher->title    = $title;
        $teacher->username = $userName;

        $scheduleModel->schedule->teachers->$internalID = $teacher;

        self::setID($scheduleModel, $internalID);
        Departments::setDepartmentResource($teacher->id, 'teacherID');
    }
}
