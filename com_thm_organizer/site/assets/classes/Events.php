<?php
/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		Events
 * @description Events file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class Events for component com_thm_organizer
 *
 * Class provides methods for sporadic events
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class Events
{
    /**
     * Joomla data abstraction
     *
     * @var    DataAbstraction
     * @since  1.0
     */
    private $_JDA = null;

    /**
     * Config object
     *
     * @var    MySchedConfig
     * @since  1.0
     */
    private $_CFG = null;

    /**
     * Configuration
     *
     * @var    Array
     * @since  1.0
     */
    private $_cfg = null;

    /**
     * Joomla session id
     *
     * @var    String
     * @since  1.0
     */
    private $_jsid = null;

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
        // Require_once JPATH_COMPONENT . "/views/scheduler/tmpl/wsapi/class.mySchedImport.php";
        $this->JDA = $JDA;
        $this->CFG = $CFG;
        $this->cfg = $CFG->getCFG();
        $this->jsid = $JDA->getUserSessionID();
    }

    /**
     * Method to load the sporadic events
     *
     * @return Array An array which includes the loaded events
     */
    public function load()
    {
        $eventmodel = JModel::getInstance('event_list', 'thm_organizerModel', array('ignore_request' => false, 'display_type' => 4));

        $events = $eventmodel->events;

        $arr = array( );

        if (is_array($events))
        {
            for ( $i = 0; $i < count($events); $i++ )
            {
                $temp = $events[$i];

                if (!isset($arr[$temp["id"]]))
                {
                    $arr[ $temp["id"] ] = array( );
                }
                $arr[ $temp["id"] ][ "eid" ]       = $temp["id"];
                $arr[ $temp["id"] ][ "title" ]     = $temp["title"];
                $arr[ $temp["id"] ][ "startdate" ] = $temp["startdate"];
                if ($temp["enddate"] == "0000-00-00" || $temp["enddate"] == null || $temp["enddate"] == "")
                {
                    $arr[ $temp["id"] ][ "enddate" ] = $temp["startdate"];
                }
                else
                {
                    $arr[ $temp["id"] ][ "enddate" ] = $temp["enddate"];
                }

                $arr[ $temp["id"] ][ "starttime" ]    = $temp["starttime"];
                $arr[ $temp["id"] ][ "endtime" ]      = $temp["endtime"];
                $arr[ $temp["id"] ][ "edescription" ] = $temp["description"];
                $arr[ $temp["id"] ][ "facultative" ]  = "";
                $arr[ $temp["id"] ][ "category" ]  = $temp["eventCategory"];
                $arr[ $temp["id"] ][ "source" ]       = "joomla";
                $arr[ $temp["id"] ][ "recurrence_type" ] = $temp["rec_type"];
                $arr[ $temp["id"] ][ "reserve" ] = $eventmodel->checkReserves($temp["eventCategoryID"]);
                $arr[ $temp["id"] ][ "global" ] = $eventmodel->checkGlobal($temp["eventCategoryID"]);
                $arr[ $temp["id"] ][ "objects" ] = $temp["resourceArray"];
            }
        }

        $username = $this->JDA->getUserName();

        $pregres = preg_match("/[^[:alnum:]]/", $this->jsid);

        // && false weil es erstmal rausgenommen wurde
        if ($pregres == 0 && strlen($this->jsid) > 0 && $username != "" && false)
        {
            try
            {
                $SI           = new mySchedImport($username, $this->jsid, $this->CFG);
                $estudycalres = $SI->getCalendar();

                if ($estudycalres != null)
                {
                    $temp = array();
                    if (is_array($estudycalres))
                    {
                        foreach ($estudycalres as $v)
                        {
                            $temp[ "eid" ]          = "";
                            $temp[ "title" ]        = $v->summary;
                            $temp[ "startdate" ]    = date("Y-m-d", strtotime($v->start));
                            $temp[ "enddate" ]      = date("Y-m-d", strtotime($v->end));
                            $temp[ "starttime" ]    = date("H:i:s", strtotime($v->start));
                            $temp[ "endtime" ]      = date("H:i:s", strtotime($v->end));
                            $temp[ "edescription" ] = $v->description;
                            $temp[ "source" ]       = "estudy";
                            $temp[ "recurrence_type" ] = 0;
                            $temp[ "facultative" ]  = $v->isFacultative;
                            $temp[ "objects" ]      = array();
                            array_push($arr, $temp);
                            $temp = array();
                        }
                    }
                }
                return array("success" => true,"data" => $arr);
            }
            catch (Exception $e)
            {
                return array("success" => true,"data" => $arr);
            }
        }
        else
        {
            return array("success" => true, "data" => $arr);
        }
    }
}
