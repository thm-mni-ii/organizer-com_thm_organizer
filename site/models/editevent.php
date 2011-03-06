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

class thm_organizerModeleditevent extends JModel
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
        $query->from('#__thm_organizer_event_rooms');
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
                    $access = JAccess::check($userID, 'core.create', $asset);
                else if($this->event['id'] > 0)
                {
                    if($isAuthor) $canEditOwn = JAccess::check($userID, 'core.edit.own', $asset);
                    else $canEditOwn = false;
                    $canEdit = JAccess::check($userID, 'core.edit', $asset);
                    $access = $canEdit or $canEditOwn;
                }
                if(!$access)unset($results[$k]);
            }
            if(count($results))
            {
                $categories = array();
                $initialID = $results[0]['id'];
                foreach($results as $k => $v)
                    $categories[$v['id']] = $v;
                if(!$this->event['categoryID'])
                    $this->event['categoryID'] = $initialID;
                $this->categories = $categories;
            }
            else $this->categories = array();
        }
        else $this->categories = array();
    }


    /**
    * Function to save events and content
    *
    *@return true if no db errors occured; false otherwise
    */
    function save()
    {
        global $mainframe;
        $dbo = & JFactory::getDBO();

        //¿allows special characters to retain coding?
        $description = JRequest::getVar( 'description', '', 'post','string', JREQUEST_ALLOWRAW );

        //clean event date/time
        $startdate = trim($_POST['startdate']);
        $newdate = strtotime ( '+1 day' , strtotime ( $startdate ) ) ;
        $newdate = date ( 'Y-m-j' , $newdate );
        $enddate = trim($_POST['enddate']);
        if(strlen($enddate) != 10 || ($enddate <= $startdate) ) $enddate = '';

        $content = $_POST['content'];
        $contentid = $_POST['contentid'];
        $eventid = $_POST['eventid'];
        $mysched = $_POST['mysched'];//event creation interface is used by  mysched
        $itemid = JRequest::getVar('Itemid');//keep the style of the calling menu by using its id

        if($content == 'on')//content is wanted
        {
            //clean content date/time
            $publish_up = trim($_POST['publish_up']);
            if($publish_up == "") $publish_up = date("Y-m-d H:i:s");//no publish date for article
            $publish_up = strtotime( $publish_up );
            $publish_down = trim($_POST['publish_down']);
            if($publish_down != "") $publish_down = strtotime( $publish_down );//no unpublish date
            else if($enddate)//no unpublish date => use end date of event
            {
                $publish_down = strtotime($enddate);
                $publish_down = strtotime ( '+1 day' , $publish_down );//unpublish date is the day after event expiry
            }
            else $publish_down = strtotime ( '+1 day' , $publish_up );//default unpublish date is the day after start date
            $publish_up = date("Y-m-d H:i:s", $publish_up);
            $publish_down = date("Y-m-d H:i:s", $publish_down);
            $initial = true;

            if($contentid != 0)//existing content
            {
                $query = "UPDATE #__content
                          SET title = '".$_POST['title']."',
                              alias = '".strtolower($_POST['title'])."',
                              modified = '".date('Y-m-d H:i:s')."',
                              modified_by = '".$_POST['author']."',
                              introtext = '".$description."',
                              sectionid = '".$_POST['sectionid']."',
                              catid = '".$_POST['ccatid']."',
                              publish_up = '".$publish_up."',
                              publish_down = '".$publish_down."'
                         WHERE id = '".$contentid."';";
                $dbo->setQuery( $query );
                $dbo->query();
                if($dbo->getErrorNum())return 0;
            }
            else//new content
            {
                $query = "INSERT INTO #__content
                          (
                              title, alias, created_by,
                              created, introtext, sectionid,
                              catid, publish_up, publish_down, state
                          )
                          VALUES
                          (
                              '".$_POST['title']."','".strtolower($_POST['title'])."','".$_POST['author']."',
                              '".date('Y-m-d H:i:s')."','".$description."','".$_POST['sectionid']."',
                              '".$_POST['ccatid']."','".$publish_up."','".$publish_down."', '1'
                          )";
                $dbo->setQuery( $query );
                $dbo->query();
                if($dbo->getErrorNum())return 0;

                //get content id of new content
                $query = "SELECT MAX(id) FROM #__content
                          WHERE title = '".$_POST['title']."' AND introtext = '".$description."';";
                $dbo->setQuery( $query );
                $contentid = $dbo->loadResult();
                if($dbo->getErrorNum())return 0;
            }
        }
        else if($contentid != 0)//content not wanted, but already present
        {
            $query = "DELETE FROM #__content WHERE id = '".$_POST['contentid']."';";
            $dbo->setQuery( $query );
            $dbo->query();
            if($dbo->getErrorNum())return 0;
            $contentid = 0;
        }

        if($eventid != 0)//existing event
        {
            $query = "UPDATE #__thm_organizer_events
                      SET contentid = '$contentid',
                          title = '".$_POST['title']."',
                          ealias = '".strtolower($_POST['title'])."',
                          modified = '".date('Y-m-d H:i:s')."',
                          modified_by = '".$_POST['author']."',
                          edescription = '".$description."',
                          ecatid = '".$_POST['ecatid']."',
                          startdate = '$startdate',
                          enddate = '$enddate',
                          starttime = '".$_POST['starttime']."',
                          endtime = '".$_POST['endtime']."',
                          recurrence_type = '".$_POST['rec_type']."'
                      WHERE eid = '".$_POST['eventid']."';";
            $dbo->setQuery( $query );
            $tempquery = $query;
            $dbo->query();
            if($dbo->getErrorNum())return 0;

            //remove all existing relations to resources
            $query = "DELETE FROM #__thm_organizer_eventobjects WHERE eventid = '".$_POST['eventid']."';";
            $dbo->setQuery( $query );
            $dbo->query();
            if($dbo->getErrorNum())return 0;

            $objectstring = "";
            $initial = true;

            if(isset($_POST['teachers']))
                foreach($_POST['teachers'] as $teacher)
                {
                    if($teacher != "-1")
                    {
                        if(!$initial) $objectstring = $objectstring.",";
                        else $initial = false;
                        $objectstring = $objectstring."('".$eventid."','$teacher')";
                    }
                }
            if(isset($_POST['semesters']))
                foreach($_POST['semesters'] as $semester)
                {
                    if($fs != "-1")
                    {
                        if(!$initial) $objectstring = $objectstring.",";
                        else $initial = false;
                        $objectstring = $objectstring."('".$eventid."','$semester')";
                    }
                }
            if(isset($_POST['rooms']))
                foreach($_POST['rooms'] as $room)
                {
                    if($room != "-1")
                    {
                        if(!$initial) $objectstring = $objectstring.",";
                        else $initial = false;
                        $objectstring = $objectstring."('".$eventid."','$room')";
                    }
                }
            if(isset($_POST['groups']))
                foreach($_POST['groups'] as $group)
                {
                    if($group != "-1")
                    {
                        if(!$initial) $objectstring = $objectstring.",";
                        else $initial = false;
                        $objectstring = $objectstring."('".$eventid."','$group')";
                    }
                }

                if($objectstring != "")
                {
                    $query = "INSERT IGNORE INTO #__thm_organizer_eventobjects (eventid, objectid) VALUES $objectstring;";
                    $dbo->setQuery( $query );
                    $dbo->query();
                    if($dbo->getErrorNum())return 0;
                }
        }
        else//create new event
        {
            $query = "INSERT INTO #__thm_organizer_events
                          (
                              contentid, title, ealias, created_by,
                              created, edescription, ecatid, startdate,
                              enddate, starttime, endtime, recurrence_type
                          )
                          VALUES
                          (
                              '$contentid','".$_POST['title']."','".strtolower($_POST['title'])."','".$_POST['author']."',
                              '".date('Y-m-d H:i:s')."','".$description."','".$_POST['ecatid']."','".$startdate."',
                              '".$enddate."','".$_POST['starttime']."','".$_POST['endtime']."', '".$_POST['rec_type']."'
                          )";
            $dbo->setQuery( $query );
            $tempquery = $query;
            $dbo->query();
            if($dbo->getErrorNum())return 0;

            //event is newly created the relation to objects must be created
            //the biggest id with attributes the same as the request variables will always be the correct
            $query = $query = "SELECT MAX(eid)
                      FROM #__thm_organizer_events
                      WHERE title = '".$_POST['title']."'
                            AND edescription = '".$description."';";
            $dbo->setQuery($query);
            $eventid = $dbo->loadResult();

            $objectstring = "";
            $initial = true;

            if(isset($_POST['teachers']))
                foreach($_POST['teachers'] as $teacher)
                {
                    if($teacher != "-1")
                    {
                        if(!$initial) $objectstring = $objectstring.",";
                        else $initial = false;
                        $objectstring = $objectstring."('".$eventid."','$teacher')";
                    }
                }
            if(isset($_POST['semesters']))
                foreach($_POST['semesters'] as $semester)
                {
                    if($fs != "-1")
                    {
                        if(!$initial) $objectstring = $objectstring.",";
                        else $initial = false;
                        $objectstring = $objectstring."('".$eventid."','$semester')";
                    }
                }
            if(isset($_POST['rooms']))
                foreach($_POST['rooms'] as $room)
                {
                    if($room != "-1")
                    {
                        if(!$initial) $objectstring = $objectstring.",";
                        else $initial = false;
                        $objectstring = $objectstring."('".$eventid."','$room')";
                    }
                }
            if(isset($_POST['groups']))
                foreach($_POST['groups'] as $group)
                {
                    if($group != "-1")
                    {
                        if(!$initial) $objectstring = $objectstring.",";
                        else $initial = false;
                        $objectstring = $objectstring."('".$eventid."','$group')";
                    }
                }

            if($objectstring != "")
            {
                $query = "INSERT INTO #__thm_organizer_eventobjects	(eventid, objectid) VALUES $objectstring";
                $dbo->setQuery( $query );
                $dbo->query();
                if($dbo->getErrorNum())return 0;
            }
        }
        return $eventid;
    }


    /**
     * Deletes entries in events, eventobjects, and content
     * associated with a particular event
     *
     * @param $eventid: The id of the event to be deleted
     * @return true if no db errors occured, otherwise false
     */
    function delete($eventid)
    {
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
        return true;
    }
}
