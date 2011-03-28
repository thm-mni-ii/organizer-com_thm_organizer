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
 
class thm_organizerModelevent extends JModel
{
    public $id = 0;
    public $event = null;

    public function __construct()
    {
        parent::__construct();
        $this->loadEvent();
        if($this->event['id'] != 0)
        {
            $this->loadEventResources();
            $this->setAccess();
        }
    }

    public function loadEvent()
    {
        $eventID = JRequest::getInt('eventID')? JRequest::getInt('eventID'): 0;
        $dbo = JFactory::getDBO();
        $user = JFactory::getUser();
        
        $query = $dbo->getQuery(true);
        $query->select($this->getSelect());
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->innerJoin("#__users AS u ON c.created_by = u.id");
        $query->innerJoin("#__thm_organizer_categories AS ecat ON e.categoryID = ecat.id");
        $query->innerJoin("#__categories AS ccat ON ecat.contentCatID = ccat.id");
        $query->where("e.id = '$eventID'");
        $dbo->setQuery((string)$query);
        $event = $dbo->loadAssoc();

        if(isset($event))
        {
            $this->id = $event['id'];
            if(!empty($event['description']))$event['description'] = trim($event['description']);
            if($event['globaldisplay'] and $event['reservesobjects'])
                $event['displaybehavior'] = JText::_ ('COM_THM_ORGANIZER_E_GLOBALRESERVES_EXPLANATION');
            else if($event['globaldisplay'])
                $event['displaybehavior'] = JText::_ ('COM_THM_ORGANIZER_E_GLOBAL_EXPLANATION');
            else if($event['reservesobjects'])
                $event['displaybehavior'] = JText::_ ('COM_THM_ORGANIZER_E_RESERVES_EXPLANATION');
            else
                $event['displaybehavior'] = JText::_ ('COM_THM_ORGANIZER_E_NOGLOBALRESERVES_EXPLANATION');
            if($event['starttime'] == "00:00")unset($event['starttime']);
            if($event['endtime'] == "00:00")unset($event['endtime']);
            if($event['enddate'] == "00.00.0000")unset($event['enddate']);
        }
        else
        {
            $this->id = 0;
            $event = array();
            $event['id'] = 0;
            $event['title'] = JText::_('COM_THM_ORGANIZER_E_EMPTY');
            $event['alias'] = '';
            $event['description'] = JText::_('COM_THM_ORGANIZER_E_EMPTY_DESC');
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
        $event['teachers'] = array();
        $event['groups'] = array();
        $event['rooms'] = array();
        $this->event = $event;
    }

    private function getSelect()
    {
        $select = "e.id AS id, ";
        $select .= "e.categoryID AS eventCategoryID, ";
        $select .= "DATE_FORMAT(e.startdate, '%d.%m.%Y') AS startdate, ";
        $select .= "DATE_FORMAT(e.enddate, '%d.%m.%Y') AS enddate, ";
        $select .= "SUBSTR(e.starttime, 1, 5) AS starttime, ";
        $select .= "SUBSTR(e.endtime, 1, 5) AS endtime, ";
        $select .= "e.recurrence_type AS rec_type, ";
        $select .= "ecat.title AS eventCategory, ";
        $select .= "ecat.description AS eventCategoryDesc, ";
        $select .= "ecat.contentCatID AS contentCategoryID, ";
        $select .= "ecat.globaldisplay, ";
        $select .= "ecat.reservesobjects, ";
        $select .= "c.title AS title, ";
        $select .= "c.introtext AS description, ";
        $select .= "DATE_FORMAT(c.publish_up, '%d.%m.%Y') AS publish_up, ";
        $select .= "DATE_FORMAT(c.publish_down, '%d.%m.%Y') AS publish_down, ";
        $select .= "c.access AS contentAccess, ";
        $select .= "ccat.title AS contentCategory, ";
        $select .= "ccat.description AS contentCategoryDesc, ";
        $select .= "ccat.access AS contentCategoryAccess, ";
        $select .= "u.name AS author, ";
        $select .= "u.id AS authorID ";
        return $select;
    }

    private function loadEventResources()
    {
        $this->loadEventRooms();
        $this->loadEventTeachers();
        $this->loadEventGroups();
    }

    private function loadEventRooms()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("name");
        $query->from("#__thm_organizer_event_rooms AS er");
        $query->innerJoin("#__thm_organizer_rooms AS r ON er.roomID = r.id");
        $query->where("er.eventID = '$this->id'");
        $dbo->setQuery((string)$query);
        $this->event['rooms'] = $dbo->loadResultArray();
    }

    private function loadEventTeachers()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("name");
        $query->from("#__thm_organizer_event_teachers AS et");
        $query->innerJoin("#__thm_organizer_teachers AS t ON et.teacherID = t.id");
        $query->where("et.eventID = '$this->id'");
        $dbo->setQuery((string)$query);
        $this->event['teachers'] = $dbo->loadResultArray();
    }

    private function loadEventGroups()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("title AS name");
        $query->from("#__thm_organizer_event_groups AS eg");
        $query->innerJoin("#__usergroups AS ug ON eg.groupID = ug.id");
        $query->where("eg.eventID = '$this->id'");
        $dbo->setQuery((string)$query);
        $this->event['groups'] = $dbo->loadResultArray();
    }

    private function setAccess()
    {
        $access = false;
        $user = JFactory::getUser();
        $isAuthor = ($user->id == $this->event['authorID'])? true : false;
        $assetname = "com_content.article.{$this->event['id']}";
        if($isAuthor) $canEditOwn = $user->authorise('edit.own', $assetname);
        $canEdit = $user->authorise('edit', $assetname);
        $access = $canEdit or $canEditOwn;
        $this->event['access'] = $access;
    }
}
