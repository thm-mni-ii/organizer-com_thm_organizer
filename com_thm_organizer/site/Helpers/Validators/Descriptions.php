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

use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use stdClass;

/**
 * Provides functions for XML description validation and modeling.
 */
class Descriptions implements UntisXMLValidator
{
    /**
     * Retrieves the resource id using the Untis ID. Creates the resource id if unavailable.
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $untisID       the id of the resource in Untis
     * @param string  $typeFlag      the flag identifying the categorization resource
     *
     * @return void modifies the scheduleModel, setting the id property of the resource
     */
    public static function setID(&$scheduleModel, $untisID, $typeFlag = '')
    {
        $resource = '';
        switch ($typeFlag) {
            case 'f':
                $resource = 'Fields';

                break;
            case 'r':
                $resource = 'RoomTypes';

                break;
            case 'u':
                $resource = 'Methods';

                break;
        }

        $table  = OrganizerHelper::getTable($resource);
        $data   = ['untisID' => $untisID];
        $exists = $table->load($data);

        if ($exists) {
            $property                                         = strtolower($resource);
            $scheduleModel->schedule->$property->$untisID     = new stdClass;
            $scheduleModel->schedule->$property->$untisID->id = $table->id;
        }

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
        if (empty($xmlObject->descriptions)) {
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_DESCRIPTIONS_MISSING');

            return;
        }

        $scheduleModel->schedule->fields    = new stdClass;
        $scheduleModel->schedule->methods   = new stdClass;
        $scheduleModel->schedule->roomtypes = new stdClass;

        foreach ($xmlObject->descriptions->children() as $node) {
            self::validateIndividual($scheduleModel, $node);
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
            $missingText = Languages::_('THM_ORGANIZER_DESCRIPTION_ID_MISSING');
            if (!in_array($missingText, $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = $missingText;
            }

            return;
        }

        $untisID = str_replace('DS_', '', $untisID);
        $name    = trim((string)$node->longname);

        if (empty($name)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_DESCRIPTION_NAME_MISSING'),
                $untisID
            );

            return;
        }

        $typeFlag   = strtolower(trim((string)$node->flags));
        $validFlags = ['f', 'r', 'u'];

        if (empty($typeFlag)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_DESCRIPTION_TYPE_MISSING'),
                $name,
                $untisID
            );

            return;
        } elseif (!in_array($typeFlag, $validFlags)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_DESCRIPTION_TYPE_INVALID'),
                $name,
                $untisID
            );

            return;
        }

        self::setID($scheduleModel, $untisID, $typeFlag);
    }
}
