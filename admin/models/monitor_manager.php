<?php
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.model' );
 
/**
 * Room IP List Model
 *
 * @package    Giessen Scheduler
 * @subpackage Components
 */
class thm_organizersModelmonitor_manager extends JModel
{
    public $monitors = null;

    public function __construct()
    {
        parent::__construct();
        $this->loadMonitors();
    }

    /**
     * loadMonitors
     *
     * retrieves saved monitors from the database and creates a link to the
     * monitor_edit view
     */
    private function loadMonitors()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("m.monitorID, m.roomID, m.ip, r.name AS room");
        $query->from("#__thm_organizer_monitors AS m");
        $query->leftJoin("#__thm_organizer_rooms AS r ON r.id = m.roomID");
        $dbo->setQuery((string)$query);
        $monitors = $dbo->loadAssocList();

        if(count($monitors))
        {
            $link = "index.php?option=com_thm_organizer&view=monitor_edit&monitorID=";
            foreach($monitors as $k => $monitor)
            {
                if(empty($monitor['room'])) $this->initializeRoom($monitors[$k]);
                $monitors[$k]['link'] = $link.$monitor['monitorID'];
            }
        }
        else $monitors = array();
        $this->monitors = $monitors;
    }

    /**
     * initializeRoom
     *
     * the rooms are initially installed with names instead of ids since they
     * dont exist at the time of installation. this makes sure the room id is
     * properly set should resources be available.
     *
     * @param array $monitor the index for which had no associated room resource
     */
    private function initializeRoom(&$monitor)
    {
        //set display name to default
        $monitor['room'] = $monitor['roomID'];

        //check if resource is available
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__thm_organizer_rooms");
        $query->where("name = '{$monitor['roomID']}'");
        $dbo->setQuery((string)$query);
        $roomID = $dbo->loadResult();

        //update monitor entry with resource id if found
        if(isset($roomID))
        {
            $monitor['roomID'] = $roomID;
            $query = $dbo->getQuery(true);
            $query->update("#__thm_organizer_monitors");
            $query->where("monitorID = '{$monitor['monitorID']}'");
            $query->set("roomID = '$roomID'");
            $dbo->setQuery((string)$query);
            $dbo->query();
        }
    }
}