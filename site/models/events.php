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
     * @todo fit the names of data items to fit with content column names so that
     *  an instance of jtable can be used to handle content, it could be relatively
     *  easy to implement, but not sure if any naming conflicts will result
     */

    /**
     * Function to save events and content
     *
     * @return mixed int eventID on success, false on failure
     */
    function save()
    {
        $data = $this->cleanRequestData();
        if($data['eventID'] > 0) $success = $this->saveExistingEvent(&$data);
        else $success = $this->saveNewEvent(&$data);
        if(!$success) return 0;
        $success = $this->saveResources("#__thm_organizer_event_teachers", "teachers", "teacherID", $data['eventID']);
        if(!$success) return 0;
        $success = $this->saveResources("#__thm_organizer_event_rooms", "rooms", "roomID", $data['eventID']);
        if(!$success) return 0;
        $success = $this->saveResources("#__thm_organizer_event_groups", "groups", "groupID", $data['eventID']);
        if(!$success) return 0;
        return $data['eventID'];
    }

    /**
     * function cleanRequestData
     *
     * filters the data from the request
     */
    private function cleanRequestData()
    {
        $data = JRequest::getVar('jform', null, null, null, 4);
        $data['eventID'] = JRequest::getInt('eventID');
        $data['categoryID'] = JRequest::getInt('category');
        $data['userID'] = JFactory::getUser()->id;
        $data['title'] = addslashes($data['title']);
        $data['alias'] = JApplication::stringURLSafe($data['title']);
        $data['fulltext'] = addslashes($data['description']);
        $this->setContentCategoryData(&$data);
        $this->handleDatesandTimes(&$data);
        $this->createIntroText(&$data);
        return $data;
    }

    /**
     * function setContentCategoryData
     *
     * retrieves the content category id and title and places them in the
     * data array
     *
     * @param array $data holds data from the request
     */
    private function setContentCategoryData(&$data)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('title, contentCatID');
        $query->from('#__thm_organizer_categories');
        $query->where("id = '{$data['categoryID']}'");
        $dbo->setQuery((string)$query);
        $category = $dbo->loadAssoc();
        $data['contentCatName'] = $category['title'];
        $data['contentCatID'] = $category['contentCatID'];
    }

    /**
     * handleDatesandTimes
     *
     * cleans and sets date and time related properties
     *
     * @param array $data holds data from the request
     */
    private function handleDatesandTimes(&$data)
    {
        $data['rec_type'] = JRequest::getInt('rec_type');
        $data['startdate'] = trim($data['startdate']);
        $data['nativestartdate'] = $data['startdate'];
        $data['startdate'] = explode(".", $data['startdate']);
        $data['startdate'] = "{$data['startdate'][2]}-{$data['startdate'][1]}-{$data['startdate'][0]}";

        if(!empty($data['enddate']))
        {
            $data['enddate'] = trim($data['enddate']);
            $data['nativeenddate'] = $data['enddate'];
            $data['enddate'] = explode(".", $data['enddate']);
            $data['enddate'] = "{$data['enddate'][2]}-{$data['enddate'][1]}-{$data['enddate'][0]}";
            if($data['enddate'] < $data['startdate']) $data['enddate'] = $data['startdate'];
            $data['publish_down'] = date("Y-m-d H:i:s", strtotime('+1 day', strtotime($data['enddate'])));
        }
        else
        {
            $data['enddate'] = $data['startdate'];
            $data['publish_down'] = date("Y-m-d H:i:s", strtotime('+1 day', strtotime($data['enddate'])));
        }
        $data['starttime'] = (strlen($data['starttime']) == 4)?
            "0{$data['starttime']}" : $data['starttime'];
        $data['endtime'] = (strlen($data['endtime'])  == 4)?
            "0{$data['endtime']}" : $data['endtime'];

        $data['publish_up'] = $this->getPublishDate();
    }

    /**
     * getPublishDate
     *
     * uses the joomla configuration timezone to adjust the publish up date to
     * UTC time
     *
     * @return date the date normalized to UTC time for content
     */
    private function getPublishDate()
    {
        date_default_timezone_set("UTC");
        $hereZone = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));
        $hereTime = new DateTime("now", $hereZone);
        $offset = $hereTime->getOffset();
        if($offset > 0) $offset = " -{$offset} ";
        else
        {
            $offset = 0 - $offset;
            $offset = " +{$offest}";
        }
        $offset .= " seconds";
        return date("Y-m-d H:i:s", strtotime($offset));
    }

    /**
     * function createIntroText
     *
     * creates a short text to describe the appointment as such
     *
     * @param array $data holds data from the request
     */
    private function createIntroText(&$data)
    {
        $introText = "<p>".JText::_('COM_THM_ORGANIZER_E_INTROTEXT_START');
        $introText .= '"'.$data['contentCatName'].'"';
        $introText .= JText::_('COM_THM_ORGANIZER_E_INTROTEXT_HAPPENS');
        $introText .= $this->getDateText($data);

        $roomNames = $this->getNames('rooms', 'name', '#__thm_organizer_rooms');
        if(count($roomNames))
        {
            if(count($roomNames) == 1)
                $introText .= JText::_('COM_THM_ORGANIZER_E_IN').$roomNames[0];
            else
            {
                $introText .= JText::_('COM_THM_ORGANIZER_E_IN_PLURAL');
                $introText .= implode(', ', $roomNames);
            }
        }
        $introText .= JText::_('COM_THM_ORGANIZER_E_INTROTEXT_END');

        $teacherNames = $this->getNames('teachers', 'name', '#__thm_organizer_teachers');
        if(count($teacherNames))
            $introText .= " ( ".implode(", ", $teacherNames)." )";

        $groupNames = $this->getNames('groups', 'title', '#__usergroups');
        if(count($groupNames))
            $introText .= " ".JText::_('COM_THM_ORGANIZER_E_AFFECTED').implode(", ", $groupNames);

        $introText .= "</p>";
        $data['introtext'] = $introText;
    }

    /**
     * getDateText
     *
     * creates an introductory text for events
     *
     * @param array $data an array of preprepared date and time entries
     * @return string $introText
     */
    private function getDateText($data)
    {
        $dateText = $timeText = "";

        //one day events and reoccuring events use the same time text
        if($data['startdate'] == $data['enddate'] or $data['rec_type'] == 1)
        {
            if($data['starttime'] != "")//" ab ".time
                $timeText .= JText::_ ('COM_THM_ORGANIZER_E_FROM').$data['starttime'];
            if($data['endtime'] != "")//" bis ".time
                $timeText .= JText::_ ('COM_THM_ORGANIZER_E_TO').$data['endtime'];
            if($data['starttime'] == "" and $data['endtime'] == "")//" während die Offnungzeiten"
                $timeText .= JText::_("COM_THM_ORGANIZER_E_ALLDAY");
        }

        //single day events use the same date text irregardless of repetition
        if($data['startdate'] == $data['enddate'])
            $dateText .= JText::_('COM_THM_ORGANIZER_E_ON').$data['nativestartdate'].$timeText;
        //repeating events which span multiple days
        else if($data['rec_type'])
        {
            $dateText .= JText::_('COM_THM_ORGANIZER_E_BETWEEN').$data['nativestartdate'];
            $dateText .= JText::_('COM_THM_ORGANIZER_E_AND').$data['nativeenddate'];
            $dateText .= $timeText;
        }
        //block events which span multiple days
        else
        {
            $dateText .= JText::_ ('COM_THM_ORGANIZER_E_FROM');
            if($data['starttime'] != "")
                $dateText .= $data['starttime'].JText::_ ('COM_THM_ORGANIZER_E_ON');
            $dateText .= $data['startdate'].JText::_ ('COM_THM_ORGANIZER_E_TO');
            if($data['endtime'] != "")
                $dateText .= $data['endtime'].JText::_ ('COM_THM_ORGANIZER_E_ON');
            $dateText .= $data['starttime'];
            if($data['starttime'] == "" and $data['endtime'] == "")
                $dateText .= JText::_("COM_THM_ORGANIZER_E_ALLDAY");
        }
        return $dateText;
    }

    /**
     * getNames
     *
     * retrieves resource names from the db
     *
     * @param string $requestName the name with which the REQUESTed resources can
     *                            be called upon
     * @param string $columnName the column name in which the names are stored
     * @param string $tableName the table which manages the resource
     * @return array $names the names of the requested resources
     */
    private function getNames($requestName, $columnName, $tableName)
    {
        $names = array();
        $$requestName = (isset($_REQUEST[$requestName]))? JRequest::getVar($requestName) : array();
        $dummyIndex = array_search('-1', $$requestName);
        if($dummyIndex)unset($$requestName[$dummyIndex]);
        if(count($$requestName))
        {
            $dbo = JFactory::getDbo();
            $query = $dbo->getQuery(true);
            $query->select("$columnName");
            $query->from("$tableName");
            $requestedIDs = "( '".implode("', '", $$requestName)."' )";
            $query->where("id IN $requestedIDs");
            $dbo->setQuery((string)$query );
            $names = $dbo->loadResultArray();
            $names = (count($names))? $names : array();
        }
        return $names;
    }

    private function saveExistingEvent(&$data)
    {
        $dbo = JFactory::getDBO();

        $query = $dbo->getQuery(true);
        $query->update('#__content');
        $conditions = "title = '{$data['title']}', ";
        $conditions .= "alias = '{$data['alias']}', ";
        $conditions .= "introtext = '{$data['introtext']}', ";
        $conditions .= "#__content.fulltext = '{$data['fulltext']}', ";
        $conditions .= "state = '1', ";
        $conditions .= "catid = '{$data['contentCatID']}', ";
        $conditions .= "modified = '".date('Y-m-d H:i:s')."', ";
        $conditions .= "modified_by = '{$data['userID']}', ";
        $conditions .= "publish_up = '{$data['publish_up']}', ";
        $conditions .= "publish_down = '{$data['publish_down']}' ";
        $query->set($conditions);
        $query->where("id = '{$data['eventID']}'");
        $dbo->setQuery((string)$query );
        $dbo->query();
        if($dbo->getErrorNum())return false;

        $query = $dbo->getQuery(true);
        $query->select("id, level");
        $query->from("#__assets");
        $query->where("name = 'com_content.category.{$data['contentCatID']}'");
        $dbo->setQuery((string)$query);
        $parentID = $dbo->loadResult();
        if($dbo->getErrorNum())return false;

        $asset = JTable::getInstance('Asset');
        $asset->loadByName("com_content.article.{$data['eventID']}");
        $asset->parent_id = $parentID;
        $asset->title = $data['title'];
        $asset->setLocation($parentID, 'last-child');
        if (!$asset->store())
        {
            $this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $db->stderr(true)));
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->update("#__thm_organizer_events");
        $conditions = "categoryID = '{$data['categoryID']}', ";
        $conditions .= "startdate = '{$data['startdate']}', ";
        $conditions .= "enddate = '{$data['enddate']}', ";
        $conditions .= "starttime = '{$data['starttime']}', ";
        $conditions .= "endtime = '{$data['endtime']}', ";
        $conditions .= "recurrence_type = '{$data['rec_type']}' ";
        $query->set($conditions);
        $query->where("id = '{$data['eventID']}'");
        $dbo->setQuery((string)$query );
        $dbo->query();
        if($dbo->getErrorNum())return false;

        return true;
    }
    
    /**
     * saveNewEvent
     * 
     * saves a new event creating appropriate entries in the content, assets,
     * and event tables
     * 
     * @param array $data holds data from the request
     * @return boolean true on success false on failure
     */
    private function saveNewEvent(&$data)
    {
        $dbo = JFactory::getDBO();
        
        $query = $dbo->getQuery(true);
        $statement = "#__content";
        $statement .= "( title, alias, ";
        $statement .= "introtext, #__content.fulltext, ";
        $statement .= "state, catid, ";
        $statement .= "created, access, ";
        $statement .= "created_by, publish_up, ";
        $statement .= "publish_down ) ";
        $statement .= "VALUES ";
        $statement .= "( '{$data['title']}', '{$data['alias']}', ";
        $statement .= "'{$data['introtext']}', '{$data['fulltext']}', ";
        $statement .= "'1', '{$data['contentCatID']}', ";
        $statement .= "'".date('Y-m-d H:i:s')."', '1', ";
        $statement .= "'{$data['userID']}', '{$data['publish_up']}', ";
        $statement .= "'{$data['publish_down']}' ) ";
        $query->insert($statement);
        $dbo->setQuery((string)$query );
        $dbo->query();
        if($dbo->getErrorNum())return false;

        $query = $dbo->getQuery(true);
        $query->select('MAX(id)');
        $query->from('#__content');
        $query->where("title = '{$data['title']}'");
        $query->where("introtext = '{$data['introtext']}'");
        $query->where("catid = '{$data['contentCatID']}'");
        $dbo->setQuery((string)$query);
        $data['eventID'] = $dbo->loadResult();
        if($dbo->getErrorNum())return false;

        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__assets");
        $query->where("name = 'com_content.category.{$data['contentCatID']}'");
        $dbo->setQuery((string)$query);
        $parentID = $dbo->loadResult();
        if($dbo->getErrorNum())return false;

        $asset = JTable::getInstance('Asset');
        $asset->name = "com_content.article.{$data['eventID']}";
        $asset->parent_id = $parentID;
        $asset->rules = '{}';
        $asset->title = $data['title'];
        $asset->setLocation($parentID, 'last-child');
        if (!$asset->store())
        {
            $this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $db->stderr(true)));
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->select('id');
        $query->from('#__assets');
        $query->where("name = 'com_content.article.{$data['eventID']}'");
        $dbo->setQuery((string)$query);
        $assetID = $dbo->loadResult();
        if($dbo->getErrorNum())return false;

        $query = $dbo->getQuery(true);
        $query->update("#__content");
        $query->set("asset_id = '$assetID'");
        $query->where("id = '{$data['eventID']}'");
        $dbo->setQuery((string)$query );
        $dbo->query();
        if($dbo->getErrorNum())return false;

        $query = $dbo->getQuery(true);
        $statement = "#__thm_organizer_events";
        $statement .= "( id, categoryID, startdate, enddate, ";
        $statement .= "starttime, endtime, recurrence_type ) ";
        $statement .= "VALUES ";
        $statement .= "( '{$data['eventID']}', '{$data['categoryID']}', '{$data['startdate']}', '{$data['enddate']}', ";
        $statement .= "'{$data['starttime']}', '{$data['endtime']}', '{$data['rec_type']}' ) ";
        $query->insert($statement);
        $dbo->setQuery( $query );
        $dbo->query();
        if($dbo->getErrorNum())return false;
        
        return true;
    }

    /**
     * saveEventResources
     *
     * saves associations of events and event resources
     *
     * @param string $tableName the name of the table where resource association
     * is saved
     * @param string $requestName the name of the request variable handling the
     * resource
     * @param string $resourceColumn the name of the column which holds the
     * resource id
     * @param int $eventID the id of the event
     * @return boolean true on success false on failure
     */
    private function saveResources($tableName, $requestName, $resourceColumn, $eventID)
    {
        $dbo = JFactory::getDBO();

        //remove old associations
        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from($tableName);
        $query->where("eventID = '$eventID'");
        $dbo->setQuery((string)$query);
        $dbo->query();

        //add new ones (if requested)
        $resources = (isset($_REQUEST[$requestName]))? JRequest::getVar($requestName) : array();
        $noResourceIndex = array_search('-1', $resources);
        if($noResourceIndex)unset($resources[$noResourceIndex]);
        if(count($resources))
        {
            $query = $dbo->getQuery(true);
            $statement = "$tableName ";
            $statement .= "( eventID, $resourceColumn ) ";
            $statement .= "VALUES ";
            $statement .= "( '$eventID', '".  implode("' ), ( '$eventID', '", $resources)."' ) ";
            $query->insert($statement);
            $dbo->setQuery((string)$query );
            $dbo->query();
            if($dbo->getErrorNum())return false;
        }

        return true;
    }

    /**
     * delete
     *
     * deletes events
     *
     * @return boolean true on success, false on db error
     */
    public function delete()
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
