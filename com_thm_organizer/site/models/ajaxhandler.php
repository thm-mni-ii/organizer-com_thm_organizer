<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        reservation ajax response model
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

include_once JPATH_COMPONENT . "/assets/classes/DataAbstraction.php";
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
     * Joomla data abstraction
     *
     * @var    DataAbstraction
     */
    private $_JDA = null;

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
        $this->_JDA = new THM_OrganizerDataAbstraction;
        $this->_CFG = new mySchedConfig($this->_JDA);
    }

    /**
     * Method to execute tasks
     *
     * @param   String  $task     The task to execute
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

        $taskArray = explode(".", $task);
        try
        {
            if ($taskArray[0] == 'TreeView' AND $taskArray[1] == 'load')
            {
                $schedNavModel = JModel::getInstance('Schedule_Navigation', 'THM_OrganizerModel', $options);
                return $schedNavModel->load($options);
            }
            $className = $taskArray[0];
            require_once JPATH_COMPONENT . "/assets/classes/{$className}.php";
            $className = "THM" . $className;

            if (count($options) == 0)
            {
                $class = new $className($this->_JDA, $this->_CFG);
            }
            else
            {
                $class = new $className($this->_JDA, $this->_CFG, $options);
            }
            return $class->$taskArray[1]();
        }
        catch (Exception $e)
        {
            return array("success" => false, "data" => "Error while perfoming the task.");
        }
    }
}
