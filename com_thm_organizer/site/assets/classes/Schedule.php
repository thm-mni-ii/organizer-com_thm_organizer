<?php
/**
 * @version     v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        Schedule
 * @description Schedule file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die("DIE!!!");

require_once dirname(__FILE__) . "/scheduledirector.php";

/**
 * Class Schedule for component com_thm_organizer
 *
 * Class provides methods to create a schedule in different formats
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class Schedule
{
	/**
	 * Builder
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_builder = null;

	/**
	 * Lesson array
	 *
	 * @var    Array
	 * @since  1.0
	 */
	private $_arr = null;

	/**
	 * Username
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_username = null;

	/**
	 * Schedule title
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_title = null;

	/**
	 * Output type
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_what = null;

	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  1.0
	 */
	private $_JDA = null;

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
	 * @param   DataAbstraction  $JDA      An object to abstract the joomla methods
	 * @param   MySchedConfig	 $CFG      An object which has configurations including
	 * @param   object			 $options  An object which has options including	(Default: null)
	 *
	 * @since  1.5
	 *
	 */
	public function __construct($JDA, $CFG, $options = null)
	{
		$this->_arr      = json_decode(file_get_contents("php://input"));
		$this->_username = $JDA->getRequest("username");
		$this->_title    = $JDA->getRequest("title");
		$this->_what     = $JDA->getRequest("what");
		$this->startdate = $JDA->getRequest("startdate");
		$this->enddate = $JDA->getRequest("enddate");
		$this->semesterID = $JDA->getRequest("semesterID");
		$this->_cfg = $CFG->getCFG();
		$this->_JDA = $JDA;
	}

	/**
	 * Method to create the schedules in different formats
	 *
	 * @return Array An array with information about the status of the creation
	 */
	public function export()
	{
		$options = array("startdate" => $this->startdate, "enddate" => $this->enddate, "semesterID" => $this->semesterID);
		if ($this->_what == "pdf")
		{
			require_once dirname(__FILE__) . "/pdf.php";
			$this->_builder = new PDFBauer($this->_JDA, $this->_cfg, $options);
		}
		elseif ($this->_what == "ics")
		{
			require_once dirname(__FILE__) . "/ics.php";
			$this->_builder = new ICSBauer($this->_JDA, $this->_cfg, $options);
		}
		elseif ($this->_what == "ical")
		{
			require_once dirname(__FILE__) . "/ical.php";
			$this->_builder = new ICALBauer($this->_JDA, $this->_cfg, $options);
		}

		$direktor = new StundenplanDirektor($this->_builder);
		return $direktor->erstelleStundenplan($this->_arr, $this->_username, $this->_title);
	}
}
