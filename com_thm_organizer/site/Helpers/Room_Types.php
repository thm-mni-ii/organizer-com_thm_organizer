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

use Joomla\CMS\Factory;
use stdClass;

/**
 * Provides general functions for room type access checks, data retrieval and display.
 */
class Room_Types implements ResourceCategory
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
        $roomTypesTable = OrganizerHelper::getTable('Room_Types');

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
     * Retrieves a list of resources in the form of name => id.
     *
     * @return array the resources, or empty
     */
    public static function getOptions()
    {
        $types   = self::getPlanRoomTypes();
        $default = [Languages::_('THM_ORGANIZER_ALL_ROOM_TYPES') => '0'];

        return array_merge($default, $types);
    }

    /**
     * Returns room types which are used by rooms.
     * Optionally filterable by DepartmentIDs.
     *
     * @return array
     */
    public static function getPlanRoomTypes()
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

    /**
     * Sets indexes for previously defined resource category types. Does not create them.
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $untisID       the id of the resource in Untis
     *
     * @return void modifies the scheduleModel, setting the id property of the resource
     */
    public static function setID(&$scheduleModel, $untisID)
    {
        $table  = OrganizerHelper::getTable('Room_Types');
        $data   = ['untisID' => $untisID];
        $exists = $table->load($data);

        if ($exists) {
            $scheduleModel->schedule->room_types->$untisID     = new stdClass;
            $scheduleModel->schedule->room_types->$untisID->id = $table->id;
        }
    }
}
