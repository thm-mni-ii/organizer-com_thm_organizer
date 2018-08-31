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

/**
 * Provides functions for XML description validation and modeling.
 */
class THM_OrganizerHelperXMLDescriptions
{
    /**
     * Checks whether the resource already exists in the database
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string $tableName      the name of the table to check
     * @param string $gpuntisID      the gpuntis description id
     * @param string $constant       the text constant for message output
     *
     * @return bool  true if the entry already exists, otherwise false
     * @throws Exception
     */
    private static function exists(&$scheduleModel, $tableName, $gpuntisID, $constant)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')->from("#__thm_organizer_$tableName")->where("gpuntisID = '$gpuntisID'");
        $dbo->setQuery($query);

        try {
            $resourceID = $dbo->loadResult();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return false;
        }

        if (empty($resourceID)) {
            $scheduleModel->scheduleErrors[] = sprintf(JText::_("COM_THM_ORGANIZER_ERROR_INVALID_$constant"),
                $gpuntisID);

            return false;
        }

        return $resourceID;
    }

    /**
     * Checks whether description nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the xml object being validated
     *
     * @return void
     * @throws Exception
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->descriptions)) {
            $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_DESCRIPTIONS_MISSING");

            return;
        }

        $scheduleModel->schedule->fields     = new stdClass;
        $scheduleModel->schedule->methods    = new stdClass;
        $scheduleModel->schedule->room_types = new stdClass;

        foreach ($xmlObject->descriptions->children() as $descriptionNode) {
            $gpuntisID = trim((string)$descriptionNode[0]['id']);

            if (empty($gpuntisID)) {
                if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_DESCRIPTION_ID_MISSING"),
                    $scheduleModel->scheduleErrors)) {
                    $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_DESCRIPTION_ID_MISSING");
                }

                return;
            }

            $descriptionID = str_replace('DS_', '', $gpuntisID);
            $longName      = trim((string)$descriptionNode->longname);

            if (empty($longName)) {
                $scheduleModel->scheduleErrors[] = sprintf(JText::_('COM_THM_ORGANIZER_ERROR_DESCRIPTION_NAME_MISSING'),
                    $descriptionID);

                return;
            }

            $typeFlag = trim((string)$descriptionNode->flags);

            if (empty($typeFlag)) {
                $scheduleModel->scheduleErrors[] = sprintf(JText::_('COM_THM_ORGANIZER_ERROR_DESCRIPTION_TYPE_MISSING'),
                    $longName, $descriptionID);

                return;
            }

            $type = $typeID = '';
            switch ($typeFlag) {
                case 'f':
                case 'F':
                    $type   = 'fields';
                    $typeID = self::exists($scheduleModel, $type, $descriptionID, 'FIELD');
                    break;
                case 'r':
                case 'R':
                    $type   = 'room_types';
                    $typeID = self::exists($scheduleModel, $type, $descriptionID, 'ROOM_TYPE');
                    break;
                case 'u':
                case 'U':
                    $type   = 'methods';
                    $typeID = self::exists($scheduleModel, $type, $descriptionID, 'METHOD');
                    break;
            }

            $validType = (!empty($type) and !empty($typeID));
            if ($validType) {
                $scheduleModel->schedule->$type->$descriptionID            = new stdClass;
                $scheduleModel->schedule->$type->$descriptionID->gpuntisID = $gpuntisID;
                $scheduleModel->schedule->$type->$descriptionID->name      = $longName;
                $scheduleModel->schedule->$type->$descriptionID->id        = $typeID;
            }
        }
    }
}
