<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelAjaxhandler
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/** @noinspection PhpIncludeInspection */
include_once JPATH_COMPONENT . "/assets/classes/config.php";

/**
 * Class THM_organizerModelAjaxhandler for component com_thm_organizer
 *
 * Class provides methods to deal with AJAX requests
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelAjaxhandler extends JModelLegacy
{
	/**
	 * Configuration
	 *
	 * @var    object
	 */
	private $_CFG = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_CFG = new mySchedConfig;
	}

	/**
	 * Method to execute tasks
	 *
	 * @param   string $task    The task to execute
	 * @param   array  $options An array with options to forward to the class that handle the task (Default: array)
	 *
	 * @return  array
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

		$taskArray = explode(".", $task);
		try
		{
			if ($taskArray[0] == 'TreeView' AND $taskArray[1] == 'load')
			{
				$schedNavModel = JModelLegacy::getInstance('Schedule_Navigation', 'THM_OrganizerModel', $options);

				return $schedNavModel->load($options);
			}

			$classname = $taskArray[0];
			if (file_exists(JPATH_COMPONENT . "/assets/classes/" . $classname . ".php"))
			{
				/** @noinspection PhpIncludeInspection */
				require_once JPATH_COMPONENT . "/assets/classes/" . $classname . ".php";
			}
			else
			{
				throw new Exception("Class " . $classname . " not found");
			}

			$classname = "THM" . $classname;
			$class     = $this->getClass($classname, $options);

			return $class->$taskArray[1]();
		}
		catch (Exception $e)
		{
			return array("success" => false, "data" => "Error while perfoming the task.");
		}
	}

	/**
	 * Instantiates a new Class. Seems a little superfluous...
	 *
	 * @param   string $className the name of the class
	 * @param   array  $options   the parameters for the object
	 *
	 * @return  object  the newly instantiated class
	 */
	public function getClass($className, $options)
	{
		$class = null;

		if (count($options) == 0)
		{
			$class = new $className($this->_CFG);
		}
		else
		{
			$class = new $className($this->_CFG, $options);
		}

		return $class;
	}
}
