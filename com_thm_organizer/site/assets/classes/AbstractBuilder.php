<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        AbstractBuilder
 * @description AbstractBuilder file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Abstract class AbstractBuilder for component com_thm_organizer
 *
 * Class provides abstract methods for the builder pattern
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
abstract class THMAbstractBuilder
{
    /**
     * Saves the file to $dest with $filename in picturtype $type
     *
     * @param   Array   $arr       Array with the schedule data
     * @param   String  $username  The username of the logged in user
     * @param   String  $title     The title of the schedule
     *
     * @return void
     */
    abstract public function createSchedule($arr, $username, $title);
}
