<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        events model
 * @description encapsulation of functions used by all event views
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.model');

class thm_organizerModelevent_edit extends JModel
{

    /**
    * Function to save events and content
    *
    * @return mixed int eventID on success, false on failure
    */
    function save()
    {
        return "notsaved!";

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
?>
