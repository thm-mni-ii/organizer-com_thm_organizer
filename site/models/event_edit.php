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
jimport('joomla.application.component.modelform');

class thm_organizerModelevent_edit extends JModelForm
{
    public $event = null;
    public $rooms = null;
    public $teachers = null;
    public $groups = null;
    public $categories = null;

    public function __construct()
    {
        parent::__construct();
        $this->loadEvent();
        if($this->event['id'])$this->loadEventResources();
        $this->loadResources();
        $this->loadCategories();
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

        if(isset($event))
        {
            $form = $this->getForm();
            $form->bind($event);
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
        $select .= "c.introtext AS description, ";
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
                    $access = $user->authorise ('core.create', $asset);
                else if($this->event['id'] > 0)
                {
                    if($isAuthor) $canEditOwn = $user->authorise ('core.edit.own', $asset);
                    else $canEditOwn = false;
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
    * Function to save events and content
    *
    * @return mixed int eventID on success, false on failure
    */
    function save()
    { 
        $dbo = & JFactory::getDBO();

        $eventID = JRequest::getInt('eventID');
        $categoryID = JRequest::getInt('category');
        $jform = JRequest::getVar('jform');

        $startdate  = $jform['startdate'];
        $startdate = trim($startdate);
        $startdate = explode(".", $startdate);
        $startdate = "{$startdate[2]}-{$startdate[1]}-{$startdate[0]}";
        $publish_up = date("Y-m-d H:i:s");

        $enddate  = $jform['enddate'];
        if(!empty($enddate))
        {
            $enddate = trim($enddate);
            $enddate = explode(".", $enddate);
            $enddate = "{$enddate[2]}-{$enddate[1]}-{$enddate[0]}";
            $publish_down = date("Y-m-d H:i:s", strtotime('+1 day', strtotime($enddate)));
        }
        else
        {
            $enddate = "";
            $publish_down = date("Y-m-d H:i:s", strtotime('+1 day', strtotime($startdate)));
        }

        $starttime  = (strlen($jform['starttime']) == 4)? "0{$jform['starttime']}" : $jform['starttime'];
        $endtime  = (strlen($jform['endtime']) == 4)? "0{$jform['endtime']}" : $jform['endtime'];

        $query = $dbo->getQuery(true);
        $query->select('contentCatID');
        $query->from('#__thm_organizer_categories');
        $query->where("id = '$categoryID'");
        $dbo->setQuery((string)$query);
        $contentCatID = $dbo->loadResult();

        $title = addslashes($jform['title']);
        $alias = JApplication::stringURLSafe($jform['title']);
        $description = addslashes($jform['description']);
        $userID = JFactory::getUser()->id;
        $rec_type = JRequest::getInt('rec_type');
        $schedulerCall = JRequest::getVar('schedulerCall');

        if($eventID > 0)
        {
            $query = $dbo->getQuery(true);
            $query->update('#__content');
            $conditions = "title = '$title'";
            $conditions .= "alias = '$alias'";
            $conditions .= "introtext = '$description'";
            $conditions .= "state = '1'";
            $conditions .= "catid = '$contentCatID'";
            $conditions .= "modified = '".date('Y-m-d H:i:s')."'";
            $conditions .= "modified_by = '$userID'";
            $conditions .= "publish_up = '$publish_up'";
            $conditions .= "publish_down = '$publish_down'";
            $query->set($conditions);
            $query->where("id = '$eventID'");
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;

            $query = $dbo->getQuery(true);
            $query->update("#__thm_organizer_events");
            $conditions = "categoryID = '$categoryID'";
            $conditions .= "startdate = '$startdate'";
            $conditions .= "enddate = '$enddate'";
            $conditions .= "starttime = '$starttime'";
            $conditions .= "endtime = '$endtime'";
            $conditions .= "recurrence_type = '$rec_type'";
            $query->set($conditions);
            $query->where("id = '$eventID'");
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_event_groups");
            $query->from("#__thm_organizer_event_rooms");
            $query->from("#__thm_organizer_event_teachers");
            $query->where("eventID = '$eventID'");
            $dbo->setQuery((string)$query);
            $dbo->query();
        }
        else
        {
            $query = $dbo->getQuery(true);
            $statement = "#__content ";
            $statement .= "( title, alias, introtext, state, catid, created, ";
            $statement .= "created_by, publish_up, publish_down ) ";
            $statement .= "VALUES ";
            $statement .= "( '$title', '$alias', '$description', '1', '$contentCatID', ";
            $statement .= "'".date('Y-m-d H:i:s')."', '$userID', '$publish_up', '$publish_down' ) ";
            $query->insert($statement);
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;

            $query = $dbo->getQuery(true);
            $query->select('MAX(id)');
            $query->from('#__content');
            $query->where("title = '$title'");
            $query->where("introtext = '$description'");
            $query->where("catid = '$contentCatID'");
            $dbo->setQuery((string)$query);
            $eventID = $dbo->loadResult();
            if($dbo->getErrorNum())return false;

            /*
             * joomla assets table is nested and everything is interdependant for the
             * rgt and lft values therefore in orde to create an entry in this table
             * space must be made in these values, for articles this amount of space
             * is 2 units.
             */

            $query = $dbo->getQuery(true);
            $query->select("id, lft, rgt");
            $query->from("#__assets");
            $query->where("name = 'com_content.category.$contentCatID'");
            $dbo->setQuery((string)$query);
            $assetParentValues = $dbo->loadAssoc();
            if($dbo->getErrorNum())return false;

            $query = $dbo->getQuery(true);
            $query->select("lft, MAX(rgt) AS rgt");
            $query->from("#__assets");
            $query->where("parent_id = '{$assetParentValues['id']}'");
            $dbo->setQuery((string)$query);
            $assetRightSiblingValues = $dbo->loadAssoc();
            if($dbo->getErrorNum())return false;
            if($assetRightSiblingValues['lft'] == null)//parent without children
            {
                $assetRightSiblingValues['lft'] = $assetParentValues['lft'];
                $assetRightSiblingValues['rgt'] = $assetParentValues['lft'];
            }

            // Create space in the tree at the new location for the new node in right ids.
            $query = $dbo->getQuery(true);
            $query->update("#__assets");
            $query->set('rgt = rgt + 2');
            $query->where("rgt >= {$assetParentValues['rgt']}");
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;

            // Create space in the tree at the new location for the new node in left ids.
            $query = $dbo->getQuery(true);
            $query->update("#__assets");
            $query->set("lft = lft + 2");
            $query->where("lft > {$assetRightSiblingValues['lft']}");
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;

            $assetLFT = $assetRightSiblingValues['rgt'] + 1;
            $assetRGT = $assetLFT + 1;
            $rules = '{"core.delete":[],"core.edit":[],"core.edit.state":[]}';

            $query = $dbo->getQuery(true);
            $statement = "#__assets ";
            $statement .= "( parent_id, level, lft, rgt, name, title, rules ) ";
            $statement .= "VALUES ";
            $statement .= "( '{$assetParentValues['id']}', '3', '$assetLFT', '$assetRGT', ";
            $statement .= "'com_content.article.$eventID', '$title', '$rules' ) ";
            $query->insert($statement);
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;

            $query = $dbo->getQuery(true);
            $query->select('id');
            $query->from('#__assets');
            $query->where("name = 'com_content.article.$eventID'");
            $dbo->setQuery((string)$query);
            $assetID = $dbo->loadResult();
            if($dbo->getErrorNum())return false;

            $query = $dbo->getQuery(true);
            $query->update("#__content");
            $query->set("asset_id = '$assetID'");
            $query->where("id = '$eventID'");
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;

            $query = $dbo->getQuery(true);
            $statement = "#__thm_organizer_events";
            $statement .= "( id, categoryID, startdate, enddate, ";
            $statement .= "starttime, endtime, recurrence_type ) ";
            $statement .= "VALUES ";
            $statement .= "( '$eventID', '$categoryID', '$startdate', '$enddate', ";
            $statement .= "'$starttime', '$endtime', '$rec_type' ) ";
            $query->insert($statement);
            $dbo->setQuery( $query );
            $dbo->query();
            if($dbo->getErrorNum())return false;
        }

        $teachers = (isset($_REQUEST['teachers']))? JRequest::getVar('teachers') : array();
        $noTeacherIndex = array_search('-1', $teachers);
        if($noTeacherIndex)unset($teachers[$noTeacherIndex]);
        if(count($teachers))
        {
            $query = $dbo->getQuery(true);
            $statement = "#__thm_organizer_event_teachers ";
            $statement .= "( eventID, teacherID ) ";
            $statement .= "VALUES ";
            $statement .= "( '$eventID', '".  implode("' ), ( '$eventID', '", $teachers)."' ) ";
            $query->insert($statement);
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;
        }

        $rooms = (isset($_REQUEST['rooms']))? JRequest::getVar('rooms') : array();
        $noRoomIndex = array_search('-1', $rooms);
        if($noRoomIndex)unset($rooms[$noRoomIndex]);
        if(count($rooms))
        {
            $query = $dbo->getQuery(true);
            $statement = "#__thm_organizer_event_rooms ";
            $statement .= "( eventID, roomID ) ";
            $statement .= "VALUES ";
            $statement .= "( '$eventID', '".  implode("' ), ( '$eventID', '", $rooms)."' ) ";
            $query->insert($statement);
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;
        }

        $groups = (isset($_REQUEST['groups']))? JRequest::getVar('groups') : array();
        $noGroupIndex = array_search('-1', $groups);
        if($noGroupIndex)unset($groups[$noGroupIndex]);
        if(count($groups))
        {
            $query = $dbo->getQuery(true);
            $statement = "#__thm_organizer_event_groups ";
            $statement .= "( eventID, groupID ) ";
            $statement .= "VALUES ";
            $statement .= "( '$eventID', '".  implode("' ), ( '$eventID', '", $groups)."' ) ";
            $query->insert($statement);
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;
        }
        return $eventID;
    }


    /**
     * Deletes entries in events, eventobjects, and content
     * associated with a particular event
     *
     * @param $eventid: The id of the event to be deleted
     * @return boolean true on success, false on failure
     */
    function delete($eventid)
    {/*
        //establish db object
        $dbo = & JFactory::getDBO();
        $query = "SELECT contentid FROM #__thm_organizer_events WHERE eid = '$eventid'";
        $dbo->setQuery($query);
        $contentid = $dbo->loadResult();
        if(isset($contentid) && $contentid != 0)
        {
            $query = "DELETE FROM #__content WHERE id = '$contentid'";
            $dbo->setQuery($query);
            $dbo->query();
            if ($dbo->getErrorNum())return false;
        }
        $query = "DELETE FROM #__thm_organizer_events WHERE eid = '$eventid'";
        $dbo->setQuery($query);
        $dbo->query();
        if ($dbo->getErrorNum())return false;
        $query = "DELETE FROM #__thm_organizer_eventobjects WHERE eventid = '$eventid'";
        $dbo->setQuery($query);
        $dbo->query();
        if ($dbo->getErrorNum())return false;
        return true;*/
    }
}
