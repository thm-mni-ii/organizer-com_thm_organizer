<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelTeacher_Ajax
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/rooms.php';

/**
 * Class provides methods for building a model of the curriculum in JSON format
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelRoom_Ajax extends JModelLegacy
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
