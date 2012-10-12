<?php
/**
 * @version     v0.0.1
 * @category	Joomla component
 * @package     THM_Oganizer
 * @subpackage  com_thm_organizer.site
 * @name        scheduler model
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Class THM_OrganizerModelScheduler for component com_thm_organizer
 *
 * Class provides methods to get the neccessary data to display a schedule
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class THM_OrganizerModelScheduler extends JModel
{
	/**
	 * Semester id
	 *
	 * @var    int
	 * @since  v0.0.1
	 */
	public $semesterID = null;

	/**
	 * Message
	 *
	 * @var    String
	 * @since  v0.0.1
	 */
	protected $msg;

	/**
	 * Constructor
	 *
	 * @since  v0.0.1
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Method to get the session id
	 *
	 * @return  String  The current session id or empty string if the username is null
	 */
	public function getSessionID()
	{
		$user = JFactory::getUser();
		if ($user->username == null)
		{
			return "";
		}

		$dbo = JFactory::getDBO();
		$dbo->setQuery("SELECT DISTINCT #__session.session_id, #__session.username, #__session.usertype, #__users.email FROM #__session
				LEFT OUTER JOIN #__users ON #__session.username = #__users.username
				WHERE #__session.username = '" . $user->get('username') . "' AND #__session.guest = 0");
		$rows = $dbo->loadObjectList();
		return $rows['0']->session_id;
	}

	/**
	 * Method to get the semester author by the given semester id
	 *
	 * @param   Integer  $semesterID  Semester id
	 *
	 * @return  String   The semester author
	 */
	public function getSemesterAuthor($semesterID)
	{
		$dbo = JFactory::getDBO();
		$dbo->setQuery("SELECT DISTINCT username as author FROM #__thm_organizer_semesters
				INNER JOIN #__users ON manager = #__users.id WHERE #__thm_organizer_semesters.id = " . $semesterID
		);
		$rows = $dbo->loadObjectList();
		if ($rows == null)
		{
			return "";
		}
		return $rows['0']->author;
	}
	
	/**
	 * Method to check if the component is available
	 *
	 * @param   String  $com  Component name
	 *
	 * @return  Boolean true if the component is available, false otherwise
	 */
	public function getComStatus($com)
	{
		$dbo = JFactory::getDBO();
		$query	= $dbo->getQuery(true);
		$query->select('extension_id AS "id", element AS "option", params, enabled');
		$query->from('#__extensions');
		$query->where('`type` = ' . $dbo->quote('component'));
		$query->where('`element` = ' . $dbo->quote($com));
		$dbo->setQuery($query);
		if ($error = $dbo->getErrorMsg())
		{
			return false;
		}

		$result = $dbo->loadObject();

		if ($result === null)
		{
			return false;
		}
		return true;
	}
	
	public function getActiveSchedule($departmentSemesterSelection)
	{
		$departmentSemester = explode(";", $departmentSemesterSelection);
		if(count($departmentSemester) == 2)
		{
			$department = $departmentSemester[0];
			$semester = $departmentSemester[1];
		}
		else
		{
			return false;
		}
		
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);
		$query->select('*');
		$query->from('#__thm_organizer_schedules');
		$query->where('departmentname = '.$dbo->quote($department));
		$query->where('semestername = '.$dbo->quote($semester));
		$query->where('active = 1');
		$dbo->setQuery($query);
		
		if ($error = $dbo->getErrorMsg())
		{
			return false;
		}

		$result = $dbo->loadObject();
		
		if ($result === null)
		{
			return false;
		}
		return $result;
	}
}