<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Organizer\Helpers\Rooms;

/**
 * Class retrieves dynamic room options.
 */
class Room_Ajax extends BaseModel
{
    /**
     * Gets the pool options as a string
     *
     * @param bool $short whether or not the options should use abbreviated names
     *
     * @return string the concatenated room options
     */
    public function getOptions($short = false)
    {
        $rooms = Rooms::getPlannedRooms($short);

        foreach ($rooms as $roomName => $roomData) {
            $rooms[$roomName] = $roomData['id'];
        }

        return json_encode($rooms);
    }
}
