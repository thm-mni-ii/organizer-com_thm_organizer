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

require_once 'departments.php';

/**
 * Provides general functions for room type access checks, data retrieval and display.
 */
class THM_OrganizerHelperRoomTypes
{
    /**
     * Checks for the room type name for a given room type id
     *
     * @param string $typeID the room type's id
     *
     * @return string the name if the room type could be resolved, otherwise empty
     * @throws Exception
     */
    public static function getName($typeID)
    {
        $roomTypesTable = JTable::getInstance('room_types', 'thm_organizerTable');

        try {
            $success = $roomTypesTable->load($typeID);
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return '';
        }

        $languageTag = THM_OrganizerHelperLanguage::getShortTag();
        $attribute   = "name_$languageTag";

        return $success ? $roomTypesTable->$attribute : '';
    }
}
