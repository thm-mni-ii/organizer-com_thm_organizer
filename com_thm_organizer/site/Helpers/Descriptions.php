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
use stdClass;

/**
 * Provides functions for XML description validation and modeling.
 */
class Descriptions implements XMLValidator
{
    /**
     * Checks whether the resource already exists in the database
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $tableName     the name of the table to check
     * @param string  $gpuntisID     the gpuntis description id
     * @param string  $constant      the text constant for message output
     *
     * @return bool  true if the entry already exists, otherwise false
     */
    private static function exists(&$scheduleModel, $tableName, $gpuntisID, $constant)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')->from("#__thm_organizer_$tableName")->where("gpuntisID = '$gpuntisID'");
        $dbo->setQuery($query);

        $resourceID = OrganizerHelper::executeQuery('loadResult');

        if (empty($resourceID)) {
            $scheduleModel->scheduleErrors[]
                = sprintf(Languages::_("THM_ORGANIZER_ERROR_INVALID_$constant"), $gpuntisID);

            return false;
        }

        return $resourceID;
    }

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

        $scheduleModel->schedule->fields     = new stdClass;
        $scheduleModel->schedule->methods    = new stdClass;
        $scheduleModel->schedule->room_types = new stdClass;

        foreach ($xmlObject->descriptions->children() as $node) {
            self::validateIndividual($scheduleModel, $node);
        }
    }

    /**
     * Checks whether subject nodes have the expected structure and required
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
                Languages::_('THM_ORGANIZER_DESCRIPTION_NAME_MISSING'), $untisID
            );

            return;
        }

        $typeFlag   = strtolower(trim((string)$node->flags));
        $validFlags = ['f' => 'Fields', 'r' => 'Room_Types', 'u' => 'Methods'];

        if (empty($typeFlag)) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_DESCRIPTION_TYPE_MISSING'), $name, $untisID
            );

            return;
        } elseif (!isset($validFlags[$typeFlag])) {
            $scheduleModel->scheduleErrors[] = sprintf(
                Languages::_('THM_ORGANIZER_DESCRIPTION_TYPE_INVALID'), $name, $untisID
            );

            return;
        }

        $helper = $validFlags[$typeFlag];
        $helper::setID($scheduleModel, $untisID);
    }
}
