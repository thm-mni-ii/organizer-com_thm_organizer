<?php
/**
 * 
 * Edit Event Model for Giessen Scheduler Component
 *
 * extensive use of objects are used in order to use functions provided by the joomla framework
 * not because of any personal preference for large memory usage
 * 
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
/**
 * Room Model
 *
 */
class GiessenSchedulerModelEditEvent extends JModel
{
    var $dbo = null;
    var $event = null;
	
    /**
     * Constructor
     *
     * @since 1.5
     */
    function __construct()
    {
        parent::__construct();
        $this->dbo = & JFactory::getDBO();
        $eventid = JRequest::getVar('eventid');
        $this->loadEvent($eventid);
    }
		
    /**
    * Load object variable $event with the event from the db tables
    *
    * @param int $eventid the id of the event
    */
    function loadEvent($eventid)
    {
        global $mainframe;
        $dbo =& $this->dbo;
        $user =& JFactory::getUser();
        $gid = $user->gid;

        $query = "SELECT contentid FROM #__thm_organizer_events WHERE eid = '$eventid'";
        $dbo->setQuery( $query );
        $savedcid = $dbo->loadResult();
        //check whether associated content was changed external to giessen scheduler
        if(isset($savedcid))
        {
            $query = "SELECT title FROM #__content WHERE id = '$savedcid' AND state != '-2'";
            $dbo->setQuery($query);
            $confirmtitle = $dbo->loadResult();
        }
        if(isset($confirmtitle))
        {
            $query = "SELECT gse.eid AS eventid, c.id AS contentid, gse.title, edescription AS description, 
                        gse.created_by AS author, ecatid, startdate, enddate, ec.access, recurrence_type,
                        SUBSTR(starttime, 1, 5) AS starttime, SUBSTR(endtime, 1, 5) AS endtime,
                        SUBSTR(publish_up, 1, 11) AS publish_up, SUBSTR(publish_down, 1, 11) AS publish_down,
                        sectionid, catid AS ccatid
                     FROM #__thm_organizer_events AS gse
                     INNER JOIN #__content AS c ON id = contentid
                     INNER JOIN #__thm_organizer_categories AS ec ON ecatid = ecid
                     WHERE eid='$eventid'";
            $dbo->setQuery( $query );
            $fetchedevent = $dbo->loadAssoc();
        }
        else
        {	
            $query = "INSERT INTO #__thm_organizer_events (contentid) VALUES ('0') WHERE eid = '$eventid'";
            $dbo->setQuery($query);
            $dbo->query();	  
            $query = "SELECT gse.eid AS eventid, gse.title, edescription AS description,
                        gse.created_by AS author, ecatid, startdate, enddate, ec.access, recurrence_type,
                        SUBSTR(starttime, 1, 5) AS starttime, SUBSTR(endtime, 1, 5) AS endtime
                      FROM #__thm_organizer_events AS gse
                      INNER JOIN #__thm_organizer_categories AS ec ON ecatid = ecid
                      WHERE eid='$eventid'";
            $dbo->setQuery( $query );
            $fetchedevent = $dbo->loadAssoc();
        }
        if(isset($fetchedevent))
        {
            if(isset($fetchedevent['access']) && $fetchedevent['access']  > $gid) $mainframe->redirect();
            if(isset($fetchedevent['startdate']) && $fetchedevent['startdate'] == '0000-00-00')
                unset($fetchedevent['startdate']);
            if(isset($fetchedevent['enddate']) && $fetchedevent['enddate'] == '0000-00-00')
                unset($fetchedevent['enddate']);
            if(isset($fetchedevent['starttime']) && $fetchedevent['starttime'] == '00:00')
                unset($fetchedevent['starttime']);
            if(isset($fetchedevent['endtime']) && $fetchedevent['endtime'] == '00:00')
                unset($fetchedevent['endtime']);
            $query = "SELECT oid
                      FROM #__thm_organizer_objects
                      INNER JOIN #__thm_organizer_eventobjects ON objectid = oid
                      WHERE eventid = '$eventid'";
            $dbo->setQuery( $query );
            $savedSchedObjects = $dbo->loadResultArray();
            $query = "SELECT id AS oid, name AS oname
                      FROM #__giessen_staff_groups
                      INNER JOIN #__thm_organizer_eventobjects ON objectid = id
                      WHERE eventid = '$eventid'";
            $dbo->setQuery( $query );
            $savedStaffObjects = $dbo->loadResultArray();
            $fetchedevent['savedObjects'] = array_merge($savedSchedObjects, $savedStaffObjects);
        }
        
        //load information for select boxes
        $query = "SELECT oid, oname FROM #__thm_organizer_objects WHERE otype = 'teacher' ORDER BY oname";
        $dbo->setQuery( $query );
        $fetchedevent['teachers'] = $dbo->loadObjectList();
        $query = "SELECT oid, oname FROM #__thm_organizer_objects WHERE otype = 'room' ORDER BY oname";
        $dbo->setQuery( $query );
        $fetchedevent['rooms'] = $dbo->loadObjectList();
        $query = "SELECT oid, oname FROM #__thm_organizer_objects WHERE otype = 'class' ORDER BY oname";
        $dbo->setQuery( $query );
        $fetchedevent['semesters'] = $dbo->loadObjectList();
        $query = "SELECT id AS oid, name AS oname FROM #__giessen_staff_groups ORDER BY name";
        $dbo->setQuery( $query );
        $fetchedevent['groups'] = $dbo->loadObjectList();
        $query = "SELECT id, title FROM #__sections";
        $dbo->setQuery( $query );
        $fetchedevent['sections'] = $dbo->loadAssocList();
        $query = "SELECT id, section, title FROM #__categories WHERE section NOT LIKE 'com%'";
        $dbo->setQuery( $query );
        $fetchedevent['ccategories'] = $dbo->loadAssocList();
        $query = "SELECT ecid, ecname FROM #__thm_organizer_categories WHERE access <= '$gid'";
        $dbo->setQuery( $query );
        $fetchedevent['ecategories'] = $dbo->loadAssocList();



        $this->event = $fetchedevent;
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
	