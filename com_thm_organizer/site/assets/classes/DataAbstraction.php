<?php
/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name    	DataAbstraction
 * @description DataAbstraction file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

/**
 * Class DataAbstraction for component com_thm_organizer
 *
 * Class provides methods to abstract the joomla methods
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class DataAbstraction
{
	/**
	 * Database
	 *
	 * @var    object
	 * @since  1.0
	 */
	private $_dbo;

	/**
	 * User
	 *
	 * @var    object
	 * @since  1.0
	 */
	private $_user;

	/**
	 * Joomla mainframe
	 *
	 * @var    object
	 * @since  1.0
	 */
	private $_mainframe;

	/**
	 * Constructor with initial tasks
	 *
	 * @since  1.5
	 *
	 */
	public function __construct()
	{
		$this->mainframe = JFactory::getApplication();
		$this->mainframe->initialise();
		$this->dbo = JFactory::getDBO();
		$this->user = JFactory::getUser();
	}

	/**
	 * Method to get the username
	 *
	 * @return String The username
	 */
	public function getUserName()
	{
		return $this->user->username;
	}

	/**
	 * Method to get the user session id
	 *
	 * @return String The session id
	 */
	public function getUserSessionID()
	{
		return session_id();
	}

	/**
	 * Method to get the user roles
	 *
	 * @return The user roles
	 */
	public function getUserRoles()
	{
		return $this->user->groups;
	}

	/**
	 * Method to get the user id
	 *
	 * @return The user id
	 */
	public function getUserID()
	{
		return $this->user->id;
	}

	/**
	 * Method to get the joomla temp folder path
	 *
	 * @return String The temp folder path
	 */
	public function getDownloadFolder()
	{
		$confObject = JFactory::getApplication();
		$tmpPath = $confObject->getCfg('tmp_path') . DS;
		return $tmpPath;
	}

	/**
	 * Method to get the joomla temp folder path
	 *
	 * @param   String   $sqlstatement  The SQL statement
	 * @param   Boolean  $arr 		    A flag which indicates whether that the result should be a array or object
	 *
	 * @return String The temp folder path
	 */
	public function query( $sqlstatement, $arr = false )
	{
		$this->dbo->setQuery($sqlstatement);
		if (strpos(strtolower($sqlstatement), "select") === 0)
		{
			if ($arr == false)
			{
				$data = $this->dbo->loadObjectList();
			}
			else
			{
				$data = $this->dbo->loadResultArray();
			}
		}
		else
		{
			$this->dbo->query();
			$data = true;
		}
		if ($this->dbo->getErrorNum())
		{
			$data = false;
		}

		return $data;
	}

	/**
	 * Method to get database object
	 *
	 * @return Object The database object
	 */
	public function getDBO( )
	{
		return $this->dbo;
	}

	/**
	 * Method to get a selected request item
	 *
	 * @param   String  $var  The item to select
	 *
	 * @return The item which is selected via $var
	 */
	public function getRequest( $var )
	{
		return JREQUEST::getVar($var);
	}

	/**
	 * Method to get the component settings
	 *
	 * @return The setting object
	 */
	public function getSettings()
	{
		return (object) array(
				"eStudyPath" => "", "eStudywsapiPath" => "","eStudyCreateCoursePath" => "",
				"eStudySoapSchema" => "", "downFolder" => "","vacationcat" => ""
		);
		$settings = $this->query("SELECT * FROM #__thm_organizer_settings WHERE id=1");
		if ($settings)
		{
			$settings = $settings[ 0 ];
		}
		else
		{
			return (object) array(
						"eStudyPath" => "", "eStudywsapiPath" => "","eStudyCreateCoursePath" => "",
					    "eStudySoapSchema" => "", "downFolder" => "","vacationcat" => ""
					);
		}
		return $settings;
	}

	/**
	 * Method to check if the component is available
	 *
	 * @param   String  $com  The component name
	 *
	 * @return Boolean True if the component is available unless false
	 */
	public function isComponentavailable( $com )
	{
		$query	= $this->dbo->getQuery(true);
		$query->select('extension_id AS "id", element AS "option", params, enabled');
		$query->from('#__extensions');
		$query->where('`type` = ' . $this->dbo->quote('component'));
		$query->where('`element` = ' . $this->dbo->quote($com));
		$this->dbo->setQuery($query);
		if ($error = $this->dbo->getErrorMsg())
		{
			return false;
		}

		$result = $this->dbo->loadObject();

		if ($result === null)
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to get the semester id
	 *
	 * @return The semester id
	 */
	public function getSemID()
	{
		$semesterID = JRequest::getString('semesterID');
		return $semesterID;
	}

	/**
	 * Method to get the document object
	 *
	 * @return The document object
	 */
	public function getDoc()
	{
		$doc =& JFactory::getDocument();
		return $doc;
	}
}
