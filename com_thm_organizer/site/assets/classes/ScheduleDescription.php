<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        ScheduleDescription
 * @description ScheduleDescription file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;

/**
 * Class ScheduleDescription for component com_thm_organizer
 * Class provides methods to load the schedule description
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THMScheduleDescription
{
    /**
     * Joomla data abstraction
     *
     * @var    DataAbstraction
     */
    private $_JDA = null;

    /**
     * Semester id
     *
     * @var    Integer
     */
    private $_semID = null;

    /**
     * Config
     *
     * @var    Object
     */
    private $_cfg = null;

    /**
     * Constructor with the joomla data abstraction object and configuration object
     *
     * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
     * @param   MySchedConfig    $CFG  A object which has configurations including
     */
    public function __construct($JDA, $CFG)
    {
        $this->_JDA = $JDA;
        $this->_cfg = $CFG->getCFG();
        $this->_semID = $JDA->getSemID();
    }

    /**
     * Method to load the schedule description
     *
     * @return Array An array with information about the schedule
     */
    public function load()
    {
        // Get a db connection.
        $dbo = JFactory::getDbo();
 
        // Create a new query object.
        $query = $dbo->getQuery(true);
 
        // Select all records from the user profile table where key begins with "custom.".
        // Order it by the ordering field.
        $query->select('description, startdate, enddate, creationdate');
        $query->from('#__thm_organizer_schedules');
        $query->where("'active != 'null' && sid = " . $this->_semID);
 
        // Reset the query using our newly populated query object.
        $dbo->setQuery($query);

        try 
        {
            $obj = $dbo->loadObject();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_SCHEDULE_INFORMATION"), 500);
        }

        if (count($obj) == 0 || $obj == false)
        {
            return array("success" => false, "data" => "");
        }
        else
        {
            $startdate = explode("-", $obj->startdate);
            $startdate = $startdate[2] . "." . $startdate[1] . "." . $startdate[0];

            $enddate = explode("-", $obj->enddate);
            $enddate = $enddate[2] . "." . $enddate[1] . "." . $enddate[0];

            $creationdate = explode("-", $obj->creationdate);
            $creationdate = $creationdate[2] . "." . $creationdate[1] . "." . $creationdate[0];

            return array("success" => true, "data" => array(
                    $obj->description,
                    $startdate,
                    $enddate,
                    $creationdate
            ));
        }
    }
}
