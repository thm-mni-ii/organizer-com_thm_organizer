<?php
/**
 *@category    Joomla component
 *
 *@package     THM_Organizer
 *
 *@subpackage  com_thm_organizer.site
 *@name		   eStudy
 *@description eStudy file from com_thm_organizer
 *@author	   Wolf Rost, wolf.rost@mni.thm.de
 *
 *@copyright   2012 TH Mittelhessen
 *
 *@license     GNU GPL v.2
 *@link		   www.mni.thm.de
 *@version	   1.0
 */

defined('_JEXEC') or die;

/**
 * Class eStudy for component com_thm_organizer
 *
 * Class provides methods to communicate with estudy
 *
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       1.5
 */
class EStudy
{
	/**
	 * Joomla session id
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_jsid = null;

	/**
	 * Semester id
	 *
	 * @var    Integer
	 * @since  1.0
	 */
	private $_semID = null;

	/**
	 * Module number
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_mnr = null;

	/**
	 * Config
	 *
	 * @var    MySchedConfig
	 * @since  1.0
	 */
	private $_cfg = null;

	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  1.0
	 */
	private $_JDA = null;

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
		require_once JPATH_COMPONENT . "/views/scheduler/tmpl/wsapi/class.mySchedImport.php";
		$this->JDA = $JDA;
		$this->jsid  = $JDA->getRequest("jsid");
		$this->semID = $JDA->getSemID();
		$this->mnr   = $JDA->getRequest("mnr");
		$this->CFG   = $CFG;
		$this->cfg   = $CFG->getCFG();
	}

	/**
	 * Method to get the estudy course link
	 *
	 * @return Array The estudy course link
	 */
	public function getCourseLink()
	{
		if (isset($this->jsid) && isset($this->semID) && isset($this->mnr))
		{
			$username = $this->JDA->getUserName();
			$res      = $this->JDA->query("SELECT semesterDesc FROM #__thm_organizer_semesters WHERE id ='" . $this->semID . "'");
			if (count($res) == 1)
			{
				$data     = $res[0];
				$semester = $data->semester;
			}

			$json    = file_get_contents("php://input");
			$resdata = json_decode($json);

			$username = $this->JDA->getUserName();

			$SI           = new mySchedImport($username, $this->jsid, $this->CFG);
			$estudylink   = $SI->getCourseLink($resdata, strtolower($this->mnr), $semester);
			$estudycourse = $SI->existsCourse($resdata, strtolower($this->mnr), $semester);
			$arr[ "success" ] = true;
			$arr[ "link" ]    = $estudylink;
			$arr[ "msg" ]     = $estudycourse;

			if (!($estudycourse === false) && !($estudycourse === true))
			{
				$arr["success"] = false;
			}
			return array("data" => $arr);
		}
		else
		{
			die;
		}
	}
}
