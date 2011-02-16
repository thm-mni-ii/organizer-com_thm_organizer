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
        $this->monitors = array();
        $this->loadMonitors();
        if(count($this->monitors) > 0) $this->setMonitorEditLinks();
    }

    private function loadMonitors()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_monitors AS m');
        $query->leftJoin('#__thm_organizer_semesters AS s ON m.semesterID = s.id');
        $query->leftJoin('#__thm_organizer_rooms AS r ON r.id = m.roomID');
        $dbo->setQuery((string)$query);
        $monitors = $dbo->loadAssocList();
        foreach($monitors as $k => $v)
            if(empty($v['name']))$monitors[$k]['name'] = $monitors[$k]['roomID'];
        $this->monitors = $monitors;
    }

    //todo change monitorID to monitor in usages
    private function setMonitorEditLinks()
    {
        foreach($this->monitors as $mKey => $mValue)
        {
            $this->monitors[$mKey]['link'] = 'index.php?option=com_thm_organizer&view=monitor_edit&monitorID='.$mValue['monitorID'];
        }
    }
}