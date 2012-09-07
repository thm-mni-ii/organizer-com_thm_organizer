<?php
/**
 * @version     v0.0.1
 * @category	Joomla component
 * @package     THM_Oganizer
 * @subpackage  com_thm_organizer.site
 * @name        reservation ajax response model
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

include_once JPATH_COMPONENT . "/assets/classes/DataAbstraction.php";
include_once JPATH_COMPONENT . "/assets/classes/config.php";

/**
 * Class THM_organizerModelAjaxhandler for component com_thm_organizer
 *
 * Class provides methods to deal with AJAX requests
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class THM_OrganizerModelAjaxhandler extends JModel
{
	/**
	 * Semester id
	 *
	 * @var    int
	 * @since  v0.0.1
	 */
	private $_semID = null;
	
	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  v0.0.1
	 */
	private $_JDA = null;
	
	/**
	 * Configuration
	 *
	 * @var    object
	 * @since  1.0
	 */
	private $_CFG = null;
	
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		parent::__construct();
		$this->JDA = new DataAbstraction;
		$this->CFG = new mySchedConfig($this->JDA);
	}

	/**
	 * Method to execute tasks
	 *
	 * @param   String  $task 	  The task to execute
	 * @param   Array   $options  An array with options to forward to the class that handle the task (Default: Array)
	 *
	 * @return  Array
	 */
	public function executeTask($task, $options = array())
	{
		if (is_string($task) === true)
		{
			if (preg_match("/^[A-Za-z]+\.[A-Za-z]+$/", $task) === 0)
			{
				return array("success" => false, "data" => "Unknown task!");
			}
		}
		else
		{
			return array("success" => false, "data" => "Unknown task!");
		}

		$taskarr = explode(".", $task);
		try
		{
			require_once JPATH_COMPONENT . "/assets/classes/" . $taskarr[0] . ".php";
			$classname = $taskarr[0];
			if (count($options) == 0)
			{
				$class = new $classname($this->JDA, $this->CFG);
			}
			else
			{
				$class = new $classname($this->JDA, $this->CFG, $options);
			}
			return $class->$taskarr[1]();
		}
		catch (Exception $e)
		{
			return array("success" => false, "data" => "Unknown task!");
		}
	}
}