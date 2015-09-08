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
     * Config
     *
     * @var    Object
     */
    private $_cfg = null;

    /**
     * Constructor with the configuration object
     *
     * @param   MySchedConfig    $cfg  An object which has configurations including
     */
    public function __construct($cfg)
    {
        $input = JFactory::getApplication()->input;
        $this->_arr      = json_decode(file_get_contents("php://input"));
        $this->_username = $input->getString("username");
        $this->_title    = $input->getString("title");
        $this->_what     = $input->getString("what");
        $this->startdate = $input->getString("startdate");
        $this->enddate = $input->getString("enddate");
        $this->semesterID = $input->getString("semesterID");
        $this->_cfg = $cfg;
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
            $this->_builder = new THMPDFBuilder($this->_cfg, $options);
        }
        elseif ($this->_what == "xls")
        {
            require_once dirname(__FILE__) . "/xls.php";
            $this->_builder = new THMXLSBuilder($this->_cfg, $options);
        }
        elseif ($this->_what == "ical")
        {
            require_once dirname(__FILE__) . "/ical.php";
            $this->_builder = new THMICALBuilder($this->_cfg, $options);
        }

        $director = new THMScheduleDirector($this->_builder);
        return $director->createSchedule($this->_arr, $this->_username, $this->_title);
    }
}
