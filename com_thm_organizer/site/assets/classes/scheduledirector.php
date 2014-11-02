<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        StundenplanDirektor
 * @description StundenplanDirektor file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class StundenplanDirektor for component com_thm_organizer
 *
 * Class provides methods for the builder pattern
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THMScheduleDirector
{
    /**
     * Builder
     *
     * @var    Object
     */
    private $_builder = null;

    /**
     * Constructor to set the builder
     *
     * @param   THMAbstractBuilder  $builder  The builder to use
     */
    public function __construct( THMAbstractBuilder $builder )
    {
        $this->_builder = $builder;
    }

    /**
     * Method to create a schedule
     *
     * @param   Object  $arr       The event object
     * @param   String  $username  The current logged in username
     * @param   String  $title     The schedule title
     *
     * @return Array An array with information about the status of the creation
     */
    public function createSchedule( $arr, $username, $title )
    {
        return $this->_builder->createSchedule($arr, $username, $title);
    }
}
