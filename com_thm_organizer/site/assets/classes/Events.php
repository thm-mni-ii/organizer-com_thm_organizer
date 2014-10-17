<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        Events
 * @description Events file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class Events for component com_thm_organizer
 *
 * Class provides methods for sporadic events
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THMEvents
{
    /**
     * Joomla data abstraction
     *
     * @var    DataAbstraction
     */
    private $_JDA = null;

    /**
     * Config object
     *
     * @var    MySchedConfig
     */
    private $_CFG = null;

    /**
     * Configuration
     *
     * @var    Array
     */
    private $_cfg = null;

    /**
     * Joomla session id
     *
     * @var    String
     */
    private $_jsid = null;

    /**
     * Constructor with the joomla data abstraction object and configuration object
     *
     * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
     * @param   MySchedConfig    $CFG  A object which has configurations including
     */
    public function __construct($JDA, $CFG)
    {
        // Require_once JPATH_COMPONENT . "/views/scheduler/tmpl/wsapi/class.mySchedImport.php";
        $this->_JDA = $JDA;
        $this->_CFG = $CFG;
        $this->_cfg = $CFG->getCFG();
        $this->_jsid = $JDA->getUserSessionID();
    }

    /**
     * Method to load the sporadic events
     *
     * @return Array An array which includes the loaded events
     */
    public function load()
    {
        $eventmodel = JModelLegacy::getInstance('event_manager', 'thm_organizerModel', array('ignore_request' => false, 'display_type' => 4));

        $events = $eventmodel->events;

        $eventList = array( );

        if (is_array($events))
        {
            for ( $i = 0; $i < count($events); $i++ )
            {
                $temp = $events[$i];

                if (!isset($eventList[$temp["id"]]))
                {
                    $eventList[ $temp["id"] ] = array( );
                }
                $eventList[ $temp["id"] ][ "id" ]       = $temp["id"];
                $eventList[ $temp["id"] ][ "title" ]     = $temp["title"];
                $eventList[ $temp["id"] ][ "startdate" ] = $temp["startdate"];
                if ($temp["enddate"] == "0000-00-00" || $temp["enddate"] == null || $temp["enddate"] == "")
                {
                    $eventList[ $temp["id"] ][ "enddate" ] = $temp["startdate"];
                }
                else
                {
                    $eventList[ $temp["id"] ][ "enddate" ] = $temp["enddate"];
                }

                $eventList[ $temp["id"] ][ "starttime" ]    = $temp["starttime"];
                $eventList[ $temp["id"] ][ "endtime" ]      = $temp["endtime"];
                $eventList[ $temp["id"] ][ "description" ] = $temp["description"];
                $eventList[ $temp["id"] ][ "facultative" ]  = "";
                $eventList[ $temp["id"] ][ "category" ]  = $temp["eventCategory"];
                $eventList[ $temp["id"] ][ "source" ]       = "joomla";
                $eventList[ $temp["id"] ][ "recurrence_type" ] = $temp["rec_type"];
                $eventList[ $temp["id"] ][ "reserve" ] = $eventmodel->checkReserves($temp["eventCategoryID"]);
                $eventList[ $temp["id"] ][ "global" ] = $eventmodel->checkGlobal($temp["eventCategoryID"]);
                $eventList[ $temp["id"] ][ "objects" ] = $temp["resourceArray"];
            }
        }

        return array("success" => true,"data" => $eventList);
    }
}
