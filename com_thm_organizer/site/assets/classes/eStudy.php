<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		eStudy
 * @description eStudy file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class eStudy for component com_thm_organizer
 *
 * Class provides methods to communicate with estudy
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THMeStudy
{
	/**
	 * Joomla session id
	 *
	 * @var    String
	 */
	private $_jsid = null;

	/**
	 * Semester id
	 *
	 * @var    Integer
	 */
	private $_semID = null;

	/**
	 * Module number
	 *
	 * @var    String
	 */
	private $_mnr = null;

	/**
	 * Config
	 *
	 * @var    MySchedConfig
	 */
	private $_cfg = null;

	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 */
	private $_JDA = null;

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
	 * @param   MySchedConfig    $CFG  A object which has configurations including
	 */
	public function __construct($JDA, $CFG)
	{
		require_once JPATH_COMPONENT . "/views/scheduler/tmpl/wsapi/class.mySchedImport.php";
		$this->_JDA = $JDA;
		$this->_jsid  = $JDA->getRequest("jsid");
		$this->_semID = $JDA->getSemID();
		$this->_mnr   = $JDA->getRequest("mnr");
		$this->CFG   = $CFG;
		$this->_cfg   = $CFG->getCFG();
	}

	/**
	 * Method to get the estudy course link
	 *
	 * @return Array The estudy course link
	 */
	public function getCourseLink()
	{
		if (isset($this->_jsid) && isset($this->_semID) && isset($this->_mnr))
		{
			$query = "SELECT semesterDesc ";
			$query .= "FROM #__thm_organizer_semesters ";
			$query .= "WHERE id ='$this->_semID'";
			$res  = $this->_JDA->query($query);
			if (count($res) == 1)
			{
				$data     = $res[0];
				$semester = $data->semester;
			}

			$json    = file_get_contents("php://input");
			$resdata = json_decode($json);

			$username = $this->_JDA->getUserName();

			$scheduleImport           = new mySchedImport($username, $this->_jsid, $this->CFG);
			$estudylink   = $scheduleImport->getCourseLink($resdata, strtolower($this->_mnr), $semester);
			$estudycourse = $scheduleImport->existsCourse($resdata, strtolower($this->_mnr), $semester);
			$arr[ "success" ] = true;
			$arr[ "link" ]    = $estudylink;
			$arr[ "msg" ]     = $estudycourse;

			if (!($estudycourse === false) && !($estudycourse === true))
			{
				$arr["success"] = false;
			}
			return array("data" => $arr);
		}
	}
}
