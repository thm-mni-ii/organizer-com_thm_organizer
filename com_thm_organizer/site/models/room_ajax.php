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

jimport('joomla.application.component.model');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/rooms.php';

/**
 * Class retrieves dynamic room options.
 */
class THM_OrganizerModelRoom_Ajax extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * Gets the pool options as a string
     *
     * @param bool $short whether or not the options should use abbreviated names
     *
     * @return string the concatenated plan pool options
     */
    public function getPlanOptions($short = false)
    {
        $rooms = THM_OrganizerHelperRooms::getPlanRooms($short);

        foreach ($rooms as $roomName => $roomData) {
            $rooms[$roomName] = $roomData['id'];
        }

        return json_encode($rooms);
    }
}
