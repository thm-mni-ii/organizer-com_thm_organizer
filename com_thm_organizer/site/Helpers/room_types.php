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
require_once 'OrganizerHelper.php';

use Joomla\CMS\Factory;

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
     */
    public static function getName($typeID)
    {
        $roomTypesTable = \JTable::getInstance('room_types', 'thm_organizerTable');

        try {
            $success = $roomTypesTable->load($typeID);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');

            return '';
        }

        $languageTag = Languages::getShortTag();
        $attribute   = "name_$languageTag";

        return $success ? $roomTypesTable->$attribute : '';
    }

    /**
     * Returns room types which are used by rooms.
     * Optionally filterable by DepartmentIDs.
     *
     * @return array
     */
    public static function getUsedRoomTypes()
    {
        $languageTag = Languages::getShortTag();
        $dbo         = Factory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select('DISTINCT t.id, t.name_' . $languageTag . ' AS name')
            ->from('#__thm_organizer_room_types AS t')
            ->innerJoin('#__thm_organizer_rooms AS r ON r.typeID = t.id');

        $query->order('name');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', [], ['name', 'id']);
    }
}
