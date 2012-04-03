<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        create/edit appointment/event model
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.modelform');
class thm_organizerModelevent_edit extends JModelForm
{
    public $event = null;
    public $rooms = null;
    public $teachers = null;
    public $groups = null;
    public $categories = null;
    public $eventLink = "";
    public $listLink = "";

    public function __construct()
    {
        parent::__construct();
        $this->loadEvent();
        if($this->event['id'])$this->loadEventResources();
        $this->loadResources();
        $this->loadCategories();
        $this->setMenuLinks();
    }

    public function loadEvent()
    {
        $eventid = JRequest::getInt('eventID')? JRequest::getInt('eventID'): 0;
        $dbo = JFactory::getDBO();
        $user = JFactory::getUser();

        $query = $dbo->getQuery(true);
        $query->select($this->getSelect());
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->where("e.id = '$eventid'");
        $dbo->setQuery((string)$query);
        $event = $dbo->loadAssoc();

        if(!isset($event))
        {
            $event = array();
            $event['id'] = 0;
            $event['title'] = '';
            $event['alias'] = '';
            $event['description'] = '';
            $event['categoryID'] = 0;
            $event['contentID'] = 0;
            $event['startdate'] = (JRequest::getString('startdate'))? JRequest::getString('startdate'): '';
            $event['enddate'] = '';
            $event['starttime'] = (JRequest::getString('starttime'))? JRequest::getString('starttime'): '';
            $event['endtime'] = (JRequest::getString('endtime'))? JRequest::getString('endtime'): '';
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
        $event['enddate'] = ($event['enddate'] == '00.00.0000')? '' : $event['enddate'];
        $event['starttime'] = ($event['starttime'] == '00:00')? '' : $event['starttime'];
        $event['endtime'] = ($event['endtime'] == '00:00')? '' : $event['endtime'];
        $form = $this->getForm();
        $form->bind($event);

        $this->event = $event;
    }


    private function getSelect()
    {
        $select = "e.id AS id, ";
        $select .= "e.categoryID AS categoryID, ";
        $select .= "DATE_FORMAT(e.startdate, '%d.%m.%Y') AS startdate, ";
        $select .= "DATE_FORMAT(e.enddate, '%d.%m.%Y') AS enddate, ";
        $select .= "SUBSTR(e.starttime, 1, 5) AS starttime, ";
        $select .= "SUBSTR(e.endtime, 1, 5) AS endtime, ";
        $select .= "e.recurrence_type, ";
        $select .= "e.recurrence_number, ";
        $select .= "e.recurrence_counter, ";
        $select .= "c.title AS title, ";
        $select .= "c.fulltext AS description, ";
        $select .= "c.created_by";
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
        $query->select('roomID');
        $query->from('#__thm_organizer_event_rooms');
        $query->where("eventID = '{$this->event['id']}'");
        $dbo->setQuery((string)$query);
        $rooms = $dbo->loadResultArray();
        $this->event['rooms'] = count($rooms)? $rooms : array();
    }

    private function loadEventTeachers()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('teacherID');
        $query->from('#__thm_organizer_event_teachers');
        $query->where("eventID = '{$this->event['id']}'");
        $dbo->setQuery((string)$query);
        $teachers = $dbo->loadResultArray();
        $this->event['teachers'] = count($teachers)? $teachers : array();
    }

    private function loadEventGroups()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('groupID');
        $query->from('#__thm_organizer_event_groups');
        $query->where("eventID = '{$this->event['id']}'");
        $dbo->setQuery((string)$query);
        $groups = $dbo->loadResultArray();
        $this->event['groups'] = count($groups)? $groups : array();
    }

    private function loadResources()
    {
        $this->loadRooms();
        $this->loadTeachers();
        $this->loadGroups();
    }

    private function loadRooms()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, name');
        $query->from('#__thm_organizer_rooms');
        $dbo->setQuery((string)$query);
        $rooms = $dbo->loadAssocList();
        $this->rooms = count($rooms)? $rooms : array();
    }

    private function loadTeachers()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, name');
        $query->from('#__thm_organizer_teachers');
        $dbo->setQuery((string)$query);
        $teachers = $dbo->loadAssocList();
        $this->teachers = count($teachers)? $teachers : array();
    }

    private function loadGroups()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, title AS name');
        $query->from('#__usergroups');
        $query->where('title != "Public"');
        $query->where('title != "Super Users"');
        $dbo->setQuery((string)$query);
        $groups = $dbo->loadAssocList();
        $this->groups = count($groups)? $groups : array();
    }

    private function loadCategories()
    {
        $dbo = JFactory::getDbo();
        $user = JFactory::getUser();
        $query = $dbo->getQuery(true);
        $select = 'toc.id AS id, toc.title AS title, toc.globaldisplay AS global, ';
        $select .= 'toc.reservesobjects AS reserves, toc.description as description, ';
        $select .= 'c.id AS contentCatID, c.title AS contentCat, c.description AS contentCatDesc, ';
        $select .= 'vl.title AS access ';
        $query->select($select);
        $query->from('#__thm_organizer_categories AS toc');
        $query->innerJoin('#__categories AS c ON toc.contentCatID = c.id');
        $query->innerJoin('#__viewlevels AS vl ON c.access = vl.id');
        $query->order('toc.title ASC');
        $dbo->setQuery((string)$query);
        $results = $dbo->loadAssocList();
        if(count($results))
        {
            $userID = JFactory::getUser()->id;
            $isAuthor = ($this->event['created_by'] == $userID)? true : false;
            foreach($results as $k => $v)
            {
                $asset = "com_content".".category.".$v['contentCatID'];
                if($this->event['id'] == 0)
                    $access = $user->authorise('core.create', $asset);
                else if($this->event['id'] > 0)
                {
                    $canEditOwn = false;
                    if($isAuthor)$canEditOwn = $user->authorise ('core.edit.own', $asset);
                    $canEdit = $user->authorise ('core.edit', $asset);
                    $access = $canEdit or $canEditOwn;
                }
                if(!$access)unset($results[$k]);
            }
            if(count($results))
            {
                $categories = array();
                $initialID = $results[0]['id'];
                foreach($results as $k => $v)
                {
                    if($v['global'] and $v['reserves'])
                        $display = "<p>".JText::_('COM_THM_ORGANIZER_EE_GLOBALRESERVES_EXPLANATION')."</p>";
                    else if($v['global'])
                        $display = "<p>".JText::_('COM_THM_ORGANIZER_EE_GLOBAL_EXPLANATION')."</p>";
                    else if($v['reserves'])
                        $display = "<p>".JText::_('COM_THM_ORGANIZER_EE_RESERVES_EXPLANATION')."</p>";
                    else $display = "<p>".JText::_('COM_THM_ORGANIZER_EE_NOGLOBALRESERVES_EXPLANATION')."</p>";
                    $v['display'] = $display;

                    $contentCat = "<p>".JText::_('COM_THM_ORGANIZER_EE_CATEGORY_EXPLANATION');
                    $contentCat .= "<span class='thm_organizer_ee_highlight'>&quot;".$v['contentCat']."&quot;</span>.</p>";
                    $v['contentCat'] = $contentCat;

                    $contentCatDesc = "<p>".$v['contentCatDesc']."</p>";
                    $v['contentCatDesc'] = $contentCatDesc;

                    $access = "<p>".JText::_('COM_THM_ORGANIZER_EE_CONTENT_EXPLANATION_START');
                    $access .= $v['access'].JText::_('COM_THM_ORGANIZER_EE_CONTENT_EXPLANATION_END')."</p>";
                    $v['access'] = $access;
                    
                    $v['description'] = $dbo->escape($v['description']);
                    $v['display'] = addslashes($v['display']);
                    $v['contentCat'] = addslashes($v['contentCat']);
                    $v['contentCatDesc'] = addslashes($v['contentCatDesc']);
                    $v['access'] = addslashes($v['access']);

                    $categories[$v['id']] = $v;
                }
                if(!$this->event['categoryID'])
                    $this->event['categoryID'] = $initialID;
                $this->categories = $categories;
            }
            else $this->categories = array();
        }
        else $this->categories = array();
    }

    /**
     * Method to get the record form.
     *
     * @param array   $data Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return mixed A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_thm_organizer.event_edit', 'event_edit',
                                array('control' => 'jform', 'load_data' => $loadData));
        if(empty($form)) return false;
        return $form;
    }

    /**
     * funtion setListLink
     */
    private function setMenuLinks()
    {
        $menuID = JRequest::getInt('Itemid');
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("link");
        $query->from("#__menu AS eg");
        $query->where("id = $menuID");
        $query->where("link LIKE '%event_list%'");
        $dbo->setQuery((string)$query);
        $link = $dbo->loadResult();
        if(isset($link) and $link != "");$this->listLink = JRoute::_($link);

        if($this->event['id'] > 0)
            $this->eventLink = JRoute::_("index.php?option=com_thm_organizer&view=event&eventID=".$this->event['id']."&Itemid=$menuID");
    }
}
