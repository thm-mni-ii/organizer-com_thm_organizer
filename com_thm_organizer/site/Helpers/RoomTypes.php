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

/**
 * Provides general functions for room type access checks, data retrieval and display.
 */
class RoomTypes extends ResourceHelper implements Selectable
{
    use Filtered;

    const NO = 0;

    const YES = 1;

    /**
     * Retrieves a list of resources in the form of name => id.
     *
     * @return array the resources, or empty
     */
    public static function getOptions()
    {
        $options = [];
        foreach (self::getResources() as $type) {
            $options[] = HTML::_('select.option', $type['id'], $type['name']);
        }

        return $options;
    }

    /**
     * Retrieves the resource items.
     *
     * @param bool $associated whether the type needs to be associated with a room
     * @param bool $public
     *
     * @return array the available resources
     */
    public static function getResources($associated = self::YES, $public = self::YES)
    {
        $dbo = Factory::getDbo();
        $tag = Languages::getTag();

        $query = $dbo->getQuery(true);
        $query->select("DISTINCT t.*, t.id AS id, t.name_$tag AS name")->from('#__thm_organizer_room_types AS t');

        if ($public !== null) {
            $query->where('t.public = ' . $public);
        }

        if ($associated === self::YES) {
            $query->innerJoin('#__thm_organizer_rooms AS r ON r.typeID = t.id');
        } elseif ($associated === self::NO) {
            $query->leftJoin('#__thm_organizer_rooms AS r ON r.typeID = t.id');
            $query->where('r.typeID IS NULL');
        }

        self::addResourceFilter($query, 'building', 'b1', 'r');

        // This join is used specifically to filter campuses independent of buildings.
        $query->leftJoin('#__thm_organizer_buildings AS b2 ON b2.id = r.buildingID');
        self::addCampusFilter($query, 'b2');

        $query->order('name');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }
}
