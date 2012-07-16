<?php
/**
 *@category    Joomla component
 *
 *@package     THM_Organizer
 *
 *@subpackage  com_thm_organizer.site
 *@name		   Schedule
 *@description Schedule file from com_thm_organizer
 *@author	   Wolf Rost, wolf.rost@mni.thm.de
 *
 *@copyright   2012 TH Mittelhessen
 *
 *@license     GNU GPL v.2
 *@link		   www.mni.thm.de
 *@version	   1.0
 */

defined('_JEXEC') or die;

require_once dirname(__FILE__) . "/scheduledirector.php";
require_once dirname(__FILE__) . "/pdf.php";
require_once dirname(__FILE__) . "/ics.php";
require_once dirname(__FILE__) . "/ical.php";

/**
 * Class Schedule for component com_thm_organizer
 *
 * Class provides methods to create a schedule in different formats
 *
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       1.5
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
	 * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
	 * @param   MySchedConfig	 $CFG  A object which has configurations including
	 *
	 * @since  1.5
	 *
	 */
	public function __construct($JDA, $CFG)
	{
		$this->arr      = json_decode(file_get_contents("php://input"));
		$this->username = $JDA->getRequest("username");
		$this->title    = $JDA->getRequest("title");
		$this->what     = $JDA->getRequest("what");
		$this->cfg = $CFG->getCFG();
		$this->JDA = $JDA;
	}

	/**
	 * Method to create the schedules in different formats
	 *
	 * @return Array An array with information about the status of the creation
	 */
	public function export()
	{
		if ($this->what == "pdf")
		{
			$this->builder = new PDFBauer($this->JDA, $this->cfg);
		}
		elseif ($this->what == "ics")
		{
			$this->builder = new ICSBauer($this->JDA, $this->cfg);
		}
		elseif ($this->what == "ical")
		{
			$this->builder = new ICALBauer($this->JDA, $this->cfg);
		}

		$direktor = new StundenplanDirektor($this->builder);
		return $direktor->erstelleStundenplan($this->arr, $this->username, $this->title);
	}
}
