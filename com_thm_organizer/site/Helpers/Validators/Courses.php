<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers\Validators;

use Organizer\Helpers\Languages;
use Organizer\Helpers\ResourceHelper;
use stdClass;

/**
 * Provides general functions for course access checks, data retrieval and display.
 */
class Courses extends ResourceHelper implements UntisXMLValidator
{
    /**
     * Retrieves the resource id using the Untis ID. Creates the resource id if unavailable.
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $index         the id of the resource in Untis
     *
     * @return void modifies the scheduleModel, setting the id property of the resource
     */
    public static function setID(&$scheduleModel, $index)
    {
        $subject = $scheduleModel->schedule->courses->$index;

        $table        = self::getTable();
        $loadCriteria = ['subjectIndex' => $index];
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

        $scheduleModel->schedule->courses->$index->id = $table->id;

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

        $scheduleModel->schedule->courses = new stdClass;

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
        $subject->untisID      = $untisID;
        $subject->name         = $name;
        $subject->subjectIndex = $subjectIndex;
        $subject->subjectNo    = $subjectNo;

        $scheduleModel->schedule->courses->$subjectIndex = $subject;
        self::setID($scheduleModel, $subjectIndex);
    }
}
