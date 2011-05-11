<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        monitor editor model
 * @description database abstraction and persistance file for monitors
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelmonitor_edit extends JModel
{
    public $monitorID;
    public $roomID;
    public $ip;
    public $rooms;

    public function __construct()
    {
        parent::__construct();
        $this->getData();
        $this->getRooms();
    }

    /**
     * private function getData
     *
     * fills the monitor relevant object variables
     */
    private function getData()
    {
        $monitorIDs = JRequest::getVar('cid',  null, '', 'array');
        if(!empty($monitorIDs)) $monitorID = $monitorIDs[0];
        if(!isset($monitorID)) $monitorID = JRequest::getVar('monitorID');
        if(is_numeric($monitorID) and $monitorID != 0)
        {
            $dbo = JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $query->select("*");
            $query->from("#__thm_organizer_monitors");
            $query->where("monitorID = $monitorID");
            $dbo->setQuery((string)$query);
            $monitorData = $dbo->loadAssoc();
            unset($query);
            if(!empty($monitorData))
                foreach($monitorData as $k => $v)$this->$k = $v;
        }
        else
        {
            $this->monitorID = 0;
            $this->semesterID = 0;
            $this->roomID = 0;
            $this->ip = '';
        }
    }

    /**
     * private function getRooms
     *
     * gets the IDs and names of the available rooms
     */
    private function getRooms()
    {
        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("id, name");
        $query->from("#__thm_organizer_rooms");
        $dbo->setQuery((string)$query );
        $this->rooms = $dbo->loadObjectList();
    }

    public function store()
    {
        $monitorID = JRequest::getVar('monitorID');
        $roomID = JRequest::getVar('room', '');
        $ip = JRequest::getVar('ip', '');

        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        if(empty($monitorID))
        {
            $statement = "#__thm_organizer_monitors ";
            $statement .= "(roomID, ip) ";
            $statement .= "VALUES ";
            $statement .= "( '$roomID', '$ip' ) ";
            $query->insert($statement);
        }
        else
        {
            $query->update("#__thm_organizer_monitors");
            $query->set("roomID = '$roomID', ip = '$ip'");
            $query->where("monitorID = '$monitorID'");
        }
        $dbo->setQuery((string)$query );
        $dbo->query();
        return ($dbo->getErrorNum())? false : true;
    }
	
    public function delete()
    {
        $monitorIDs = JRequest::getVar( 'cid', array(0), 'post', 'array' );
        if(count($monitorIDs) > 0)
        {
            $dbo = & JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $query->delete("#__thm_organizer_monitors");
            $monitorIDs = "'".implode("', '", $monitorIDs)."'";
            $query->where("monitorID IN ( $monitorIDs )");
            $dbo->setQuery((string)$query);
            $result = $dbo->query();
            if ($dbo->getErrorNum()) return false;
            else return true;
        }
        return true;
    }
	
}
