<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewSchedule_Export
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;
require_once dirname(__FILE__) . "/scheduledirector.php";

/**
 * Class Schedule for component com_thm_organizer
 *
 * Class provides methods to create a schedule in different formats
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THMSchedule
{
	/**
	 * Builder
	 *
	 * @var    Object
	 */
	private $_builder = null;

	/**
	 * Lesson array
	 *
	 * @var    array
	 */
	private $_scheduleData = null;

	/**
	 * Username
	 *
	 * @var    String
	 */
	private $username = null;

	/**
	 * Schedule title
	 *
	 * @var    String
	 */
	private $_title = null;

	/**
	 * Output type
	 *
	 * @var    String
	 */
	private $_what = null;

	/**
	 * Config
	 *
	 * @var    Object
	 */
	private $_cfg = null;

	/**
	 * Constructor with the configuration object
	 *
	 * @param MySchedConfig $cfg An object which has configurations including
	 */
	public function __construct($cfg)
	{
		$input               = JFactory::getApplication()->input;
		$this->_scheduleData = json_decode(file_get_contents("php://input"));
		$this->username     = $input->getString("username");
		$this->_title        = $input->getString("title");
		$this->_what         = $input->getString("what");
		$this->startDate     = $input->getString("startDate");
		$this->endDate       = $input->getString("endDate");
		$this->semesterID    = $input->getString("semesterID");
		$this->_cfg          = $cfg;
	}

	/**
	 * Method to create the schedules in different formats
	 *
	 * @return array An array with information about the status of the creation
	 */
	public function export()
	{
		$options = array("startDate" => $this->startDate, "endDate" => $this->endDate, "semesterID" => $this->semesterID);
		if ($this->_what == "pdf")
		{
			require_once dirname(__FILE__) . "/pdf.php";
			$this->_builder = new THMPDFBuilder($this->_cfg, $options);
		}
		elseif ($this->_what == "ics")
		{
			require_once dirname(__FILE__) . "/ics.php";
			$this->_builder = new THMICSBuilder($this->_cfg, $options);
		}

		$director = new THMScheduleDirector($this->_builder);

		return $director->createSchedule($this->_scheduleData, $this->username, $this->_title);
	}
}
