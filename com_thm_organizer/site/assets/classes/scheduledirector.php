<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        StundenplanDirektor
 * @description StundenplanDirektor file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
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
	 * @param   THMAbstractBuilder $builder The builder to use
	 */
	public function __construct(THMAbstractBuilder $builder)
	{
		$this->_builder = $builder;
	}

	/**
	 * Method to create a schedule
	 *
	 * @param   object $scheduleData The event object
	 * @param   string $username     The current logged in username
	 * @param   string $title        The schedule title
	 *
	 * @return array An array with information about the status of the creation
	 */
	public function createSchedule($scheduleData, $username, $title)
	{
		return $this->_builder->createSchedule($scheduleData, $username, $title);
	}
}
