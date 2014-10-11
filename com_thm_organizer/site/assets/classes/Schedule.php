<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        Schedule
 * @description Schedule file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
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
     * @var    Array
     */
    private $_arr = null;

    /**
     * Username
     *
     * @var    String
     */
    private $_username = null;

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
     * Joomla data abstraction
     *
     * @var    DataAbstraction
     */
    private $_JDA = null;

    /**
     * Config
     *
     * @var    Object
     */
    private $_cfg = null;

    /**
     * Constructor with the joomla data abstraction object and configuration object
     *
     * @param   DataAbstraction  $JDA  An object to abstract the joomla methods
     * @param   MySchedConfig    $CFG  An object which has configurations including
     */
    public function __construct($JDA, $CFG)
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
            $this->_builder = new THMPDFBuilder($this->_JDA, $this->_cfg, $options);
        }
        elseif ($this->_what == "ics")
        {
            require_once dirname(__FILE__) . "/ics.php";
            $this->_builder = new THMICSBuilder($this->_JDA, $this->_cfg, $options);
        }
        elseif ($this->_what == "ical")
        {
            require_once dirname(__FILE__) . "/ical.php";
            $this->_builder = new THMICALBuilder($this->_JDA, $this->_cfg, $options);
        }

        $direktor = new THMScheduleDirector($this->_builder);
        return $direktor->createSchedule($this->_arr, $this->_username, $this->_title);
    }

    /**
     * Method to get the active schedules
     *
     * @return   mixed  The active schedules or false
     */
    public function getActiveSchedules()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('departmentname, semestername, id, creationdate, startdate, enddate');
        $query->from('#__thm_organizer_schedules');
        $query->where('active = 1');
        $dbo->setQuery((string) $query);
        $result = $dbo->loadAssocList();

        $error = $dbo->getErrorMsg();

        if (!empty($error))
        {
            return $error;
        }

        if ($result === null)
        {
            return false;
        }

        return array("success" => true, "data" => $result);
    }
}
