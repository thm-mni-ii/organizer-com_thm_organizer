<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        create/edit appointment/event model
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
 
class GiessenSchedulerModelEvent extends JModel
{
    public $event = null;
    public $rooms = null;
    public $eventRooms = null;
    public $teachers = null;
    public $eventTeachers = null;
    public $categories = null;

    public function __construct()
    {
        parent::__construct();
        $this->loadEvent();
        if($this->id)$this->loadEventResources();
        $this->loadResources();
        $this->loadCategories();
    }

    public function loadEvent()
    {
        $eventid = JRequest::getInt('eventID')? JRequest::getInt('eventID'): 0;
        $dbo = JFactory::getDBO();
        $user = JFactory::getUser();
        
        $query = $dbo->getQuery(true);
        $query->select("*");
        $query->from("#__thm_organizer_events");
        $query->where("id = '$eventid'");
        $dbo->setQuery((string)$query);
        $event = $dbo->loadAssoc();

        if(count($event))
        {
            //clean event data
            $event['starttime'] = substr($event['starttime'], 0, 5);
            $event['endtime'] = substr($event['endtime'], 0, 5);
            $event['startdate'] = strrev(str_replace("-", ".", $event['startdate']));
            $event['enddate'] = strrev(str_replace("-", ".", $event['enddate']));
        }
        else
        {
            $event = array();
            $event['id'] = 0;
            $event['title'] = '';
            $event['alias'] = '';
            $event['description'] = '';
            $event['categoryID'] = 0;
            $event['contentID'] = 0;
            $event['startdate'] = '';
            $event['enddate'] = '';
            $event['starttime'] = '';
            $event['endtime'] = '';
            $event['created_by'] = 0;
            $event['created'] = '';
            $event['modified_by'] = 0;
            $event['modified'] = '';
            $event['recurrence_number'] = 0;
            $event['recurrence_type'] = 0;
            $event['recurrence_counter'] = 0;
            $event['image'] = '';
            $event['register'] = 0;
            $event['unregister'] = 0;
        }
        $this->event = $event;
    }

    private function loadEventResources()
    {
        $this->loadEventRooms();
        $this->loadEventTeachers();
        $this->loadEventGroups();
    }

    private function loadEventRooms()
    {
        return;
    }

    private function loadEventTeachers()
    {
        return;
    }

    private function loadEventGroups()
    {
        return;
    }
}
