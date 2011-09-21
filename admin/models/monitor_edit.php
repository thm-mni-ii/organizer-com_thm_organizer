<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        monitor editor model
 * @description database abstraction file for monitors
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
class thm_organizersModelmonitor_edit extends JModel
{
    public $monitorID = 0;
    public $roomID = 0;
    public $ip = '';
    public $rooms = null;

    public function __construct()
    {
        parent::__construct();
        $this->getData();
        $this->getRooms();
    }

    /**
     * getData
     *
     * fills object variables with data if existant
     */
    private function getData()
    {
        $monitorIDs = JRequest::getVar('cid',  null, '', 'array');
        $monitorID = (empty($monitorIDs))? JRequest::getVar('monitorID') : $monitorIDs[0];
        if($monitorID)
        {
            $dbo = $this->getDbo();
            $query = $dbo->getQuery(true);
            $query->select("*");
            $query->from("#__thm_organizer_monitors");
            $query->where("monitorID = $monitorID");
            $dbo->setQuery((string)$query);
            $monitorData = $dbo->loadAssoc();
            if(isset($monitorData))
                foreach($monitorData as $variable => $value)
                    $this->$variable = $value;
        }
    }

    /**
     * getRooms
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
        $rooms = $dbo->loadObjectList();
        $this->rooms = (count($rooms))? $rooms : array();
    }	
}
