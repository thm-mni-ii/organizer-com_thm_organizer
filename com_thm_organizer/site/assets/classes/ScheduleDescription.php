<?php
/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		ScheduleDescription
 * @description ScheduleDescription file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

/**
 * Class ScheduleDescription for component com_thm_organizer
 *
 * Class provides methods to load the schedule description
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class ScheduleDescription
{
	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  1.0
	 */
	private $_JDA = null;

	/**
	 * Semester id
	 *
	 * @var    Integer
	 * @since  1.0
	 */
	private $_semID = null;

	/**
	 * Config
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_cfg = null;

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
	 * @param   MySchedConfig	 $CFG  A object which has configurations including
	 *
	 * @since  1.5
	 *
	 */
	public function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->cfg = $CFG->getCFG();
		$this->semID = $JDA->getSemID();
	}

	/**
	 * Method to load the schedule description
	 *
	 * @return Array An array with information about the schedule
	 */
	public function load()
	{
		$query = "SELECT description, startdate, enddate, creationdate " .
				"FROM #__thm_organizer_schedules WHERE active != 'null' && sid = " . $this->semID;

		$obj = $this->JDA->query($query);
		if (count($obj) == 0 || $obj == false)
		{
			return array("success" => false, "data" => "");
		}
		else
		{
			$startdate = explode("-", $obj[0]->startdate);
			$startdate = $startdate[2] . "." . $startdate[1] . "." . $startdate[0];

			$enddate = explode("-", $obj[0]->enddate);
			$enddate = $enddate[2] . "." . $enddate[1] . "." . $enddate[0];

			$creationdate = explode("-", $obj[0]->creationdate);
			$creationdate = $creationdate[2] . "." . $creationdate[1] . "." . $creationdate[0];

			return array("success" => true, "data" => array(
					$obj[0]->description,
					$startdate,
					$enddate,
					$creationdate
			));
		}
	}
}
