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

class thm_organizerModelevents extends JModel
{

    /**
    * Function to save events and content
    *
    * @return mixed int eventID on success, false on failure
    */
    function save()
    {
        $dbo = JFactory::getDBO();
        $eventID = JRequest::getInt('eventID');
        $categoryID = JRequest::getInt('category');
        $jform = JRequest::getVar('jform');

        $startdate  = $jform['startdate'];
        $startdate = trim($startdate);
        $startdate = explode(".", $startdate);
        $startdate = "{$startdate[2]}-{$startdate[1]}-{$startdate[0]}";
        //ATTENTION: Quick Hack Joomla expects puplish up to be at least 2 hours in the past to actually publish
        $publish_up = date("Y-m-d H:i:s", strtotime("-2 hours"));

        $enddate  = $jform['enddate'];
        if(!empty($enddate))
        {
            $enddate = trim($enddate);
            $enddate = explode(".", $enddate);
            $enddate = "{$enddate[2]}-{$enddate[1]}-{$enddate[0]}";
            if($enddate < $startdate) $enddate = $startdate;
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
            $conditions = "title = '$title', ";
            $conditions .= "alias = '$alias', ";
            $conditions .= "introtext = '$description', ";
            $conditions .= "state = '1', ";
            $conditions .= "catid = '$contentCatID', ";
            $conditions .= "modified = '".date('Y-m-d H:i:s')."', ";
            $conditions .= "modified_by = '$userID', ";
            $conditions .= "publish_up = '$publish_up', ";
            $conditions .= "publish_down = '$publish_down' ";
            $query->set($conditions);
            $query->where("id = '$eventID'");
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;

            $query = $dbo->getQuery(true);
            $query->update("#__thm_organizer_events");
            $conditions = "categoryID = '$categoryID', ";
            $conditions .= "startdate = '$startdate', ";
            $conditions .= "enddate = '$enddate', ";
            $conditions .= "starttime = '$starttime', ";
            $conditions .= "endtime = '$endtime', ";
            $conditions .= "recurrence_type = '$rec_type' ";
            $query->set($conditions);
            $query->where("id = '$eventID'");
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_event_groups");
            $query->where("eventID = '$eventID'");
            $dbo->setQuery((string)$query);
            $dbo->query();

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_event_rooms");
            $query->where("eventID = '$eventID'");
            $dbo->setQuery((string)$query);
            $dbo->query();

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_event_teachers");
            $query->where("eventID = '$eventID'");
            $dbo->setQuery((string)$query);
            $dbo->query();
        }
        else
        {
            $query = $dbo->getQuery(true);
            $statement = "#__content ";
            $statement .= "( title, alias, introtext, state, catid, created, access, ";
            $statement .= "created_by, publish_up, publish_down ) ";
            $statement .= "VALUES ";
            $statement .= "( '$title', '$alias', '$description', '1', '$contentCatID', ";
            $statement .= "'".date('Y-m-d H:i:s')."', '1', '$userID', '$publish_up', '$publish_down' ) ";
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

            $query = $dbo->getQuery(true);
            $query->select("id");
            $query->from("#__assets");
            $query->where("name = 'com_content.category.$contentCatID'");
            $dbo->setQuery((string)$query);
            $parentID = $dbo->loadResult();
            if($dbo->getErrorNum())return false;

            $assetsTable = JTable::getInstance('asset');
            $assetValues = array();
            $assetValues['parent_id'] = $parentID;
            $assetValues['name'] = "com_content.article.$eventID";
            $assetValues['title'] = $title;
            $assetValues['rules'] = "{}";
            $bound = $assetsTable->bind($assetValues);
            if(!$bound)return false;
            else $success = $assetsTable->store();
            if(!$success)return false;

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
     * function delete
     *
     * deletes events
     *
     * @return boolean true on success, false on db error
     */
    function delete()
    {
        $eventID = JRequest::getInt('eventID');
        $eventIDs = JRequest::getVar('eventIDs');

        if(isset($eventID) and $eventID > 0)
        {
            $success = $this->deleteIndividualEvent($eventID);
            return $success;
        }
        else if(isset($eventIDs) and count($eventIDs))
        {
            foreach($eventIDs as $eventID)
            {
                if($eventID == 0)continue;
                $success = $this->deleteIndividualEvent($eventID);
                if(!$success) return $success;
            }
            return $success;
        }
    }

    /**
     * function deleteIndividualEvent
     *
     * deletes entries in assets, content, events, event_teachers,
     * event_rooms, and event_groups associated with a particular event
     *
     * @access private
     * @param int $eventID id of the event and associated content to be deleted
     * @return boolean true on success, false on db error
     */
    private function deleteIndividualEvent($eventID)
    {
        $dbo = JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__assets");
        $query->where("name = 'com_content.article.$eventID'");
        $dbo->setQuery((string)$query);
        $assetID = $dbo->loadResult();
        if($dbo->getErrorNum())return false;

        $assetsTable = JTable::getInstance('asset');
        $assetsTable->delete($assetID);

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__content");
        $query->where("id = '$eventID'");
        $dbo->setQuery((string)$query );
        $dbo->query();
        if($dbo->getErrorNum())return false;

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_events");
        $query->where("id = '$eventID'");
        $dbo->setQuery((string)$query );
        $dbo->query();
        if($dbo->getErrorNum())return false;

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_teachers");
        $query->where("eventID = '$eventID'");
        $dbo->setQuery((string)$query );
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_rooms");
        $query->where("eventID = '$eventID'");
        $dbo->setQuery((string)$query );
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_groups");
        $query->where("eventID = '$eventID'");
        $dbo->setQuery((string)$query );
        $dbo->query();

        return true;
    }
}
?>
