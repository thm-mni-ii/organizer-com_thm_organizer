<?php
/**
 *@category    joomla component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        events model
 *@author      James Antrim jamesDOTantrimATyahooDOTcom
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0 
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
/**
 * Performs data modification and business logic for events
 * 
 * @package  Joomla.Site
 * 
 * @since    2.5.4
 */
class thm_organizerModelevents extends JModel
{
    /**
     * save
     *
     * saves event and content information
     *
     * @return int id on success, 0 on failure
     */
    public function save()
    {
        $dbo = JFactory::getDbo();
        $dbo->transactionStart();
        $data = $this->cleanRequestData();
        $eventSaved = ($data['id'] > 0)? $this->saveExistingEvent($data) : $this->saveNewEvent($data);
        $teachersSaved = $this->saveResources("#__thm_organizer_event_teachers", "teachers", "teacherID", $data['id']);
        $roomsSaved = $this->saveResources("#__thm_organizer_event_rooms", "rooms", "roomID", $data['id']);
        $groupsSaved = $this->saveResources("#__thm_organizer_event_groups", "groups", "groupID", $data['id']);
        if ($eventSaved AND $teachersSaved AND $roomsSaved AND $groupsSaved)
        {
            $dbo->transactionCommit();
            if ($data['emailNotification'] AND count($_REQUEST['groups']))
            {
                $this->notify($data);
            }
            return $data['id'];
        }
        else
        {
            $dbo->transactionRollback();
            return 0;
        }
    }

    /**
     * cleanRequestData
     *
     * filters the data from the request
     *
     * @return mixed $data request data
     */
    private function cleanRequestData()
    {
        $data = JRequest::getVar('jform', null, null, null, 4);
        $data['categoryID'] = JRequest::getInt('category');
        $data['userID'] = JFactory::getUser()->id;
        $data['title'] = addslashes($data['title']);
        $data['alias'] = JApplication::stringURLSafe($data['title']);
        $data['fulltext'] = $this->getDbo()->escape($data['description']);
        $this->setContentCategoryData($data);
        $this->handleDatesandTimes($data);
        $this->createIntroText($data);
        return $data;
    }

    /**
     * Retrieves the content category id and title and places them in the
     * data array
     *
     * @param   array  &$data  holds data from the request
     * 
     * @return  void
     */
    private function setContentCategoryData(&$data)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('title, contentCatID');
        $query->from('#__thm_organizer_categories');
        $query->where("id = '{$data['categoryID']}'");
        $dbo->setQuery((string) $query);
        $category = $dbo->loadAssoc();
        $data['contentCatName'] = $category['title'];
        $data['contentCatID'] = $category['contentCatID'];
    }

    /**
     * Cleans and sets date and time related properties
     *
     * @param   array  &$data  holds data from the request
     * 
     * @return  void
     */
    private function handleDatesandTimes(&$data)
    {
        $data['rec_type'] = JRequest::getInt('rec_type');
        $data['startdate'] = trim($data['startdate']);
        $data['nativestartdate'] = $data['startdate'];
        $data['startdate'] = explode(".", $data['startdate']);
        $data['startdate'] = "{$data['startdate'][2]}-{$data['startdate'][1]}-{$data['startdate'][0]}";

        if (!empty($data['enddate']))
        {
            $data['enddate'] = trim($data['enddate']);
            $data['nativeenddate'] = $data['enddate'];
            $data['enddate'] = explode(".", $data['enddate']);
            $data['enddate'] = "{$data['enddate'][2]}-{$data['enddate'][1]}-{$data['enddate'][0]}";
            if ($data['enddate'] < $data['startdate'])
            {
                $data['enddate'] = $data['startdate'];
            }
            $data['publish_down'] = date("Y-m-d H:i:s", strtotime('+1 day', strtotime($data['enddate'])));
        }
        else
        {
            $data['enddate'] = $data['startdate'];
            $data['publish_down'] = date("Y-m-d H:i:s", strtotime('+1 day', strtotime($data['enddate'])));
        }
        $data['starttime'] = (strlen($data['starttime']) == 4)?
            "0{$data['starttime']}" : $data['starttime'];
        $data['endtime'] = (strlen($data['endtime']) == 4)?
            "0{$data['endtime']}" : $data['endtime'];

        $data['start'] = strtotime("{$data['startdate']} {$data['starttime']}");
        $data['end'] = strtotime("{$data['enddate']} {$data['endtime']}");

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
        if ($offset > 0)
        {
            $offset = " -{$offset} ";
        }
        else
        {
            $offset = 0 - $offset;
            $offset = " +{$offest}";
        }
        $offset .= " seconds";
        return date("Y-m-d H:i:s", strtotime($offset));
    }

    /**
     * Creates a short text to describe the appointment as such
     *
     * @param   array  &$data  holds data from the request
     * 
     * @return  void
     */
    private function createIntroText(&$data)
    {
        $introText = "<p>" . JText::_('COM_THM_ORGANIZER_E_INTROTEXT_START');
        $introText .= '"' . $data['contentCatName'] . '"';
        $introText .= JText::_('COM_THM_ORGANIZER_E_INTROTEXT_HAPPENS');
        $introText .= $this->getDateText($data);

        $roomNames = $this->getNames('rooms', 'name', '#__thm_organizer_rooms');
        if (count($roomNames))
        {
            if (count($roomNames) == 1)
            {
                $introText .= JText::_('COM_THM_ORGANIZER_E_IN') . $roomNames[0];
            }
            else
            {
                $introText .= JText::_('COM_THM_ORGANIZER_E_IN_PLURAL');
                $introText .= implode(', ', $roomNames);
            }
        }
        $introText .= JText::_('COM_THM_ORGANIZER_E_INTROTEXT_END');

        $teacherNames = $this->getNames('teachers', 'name', '#__thm_organizer_teachers');
        if (count($teacherNames))
        {
            $introText .= " ( " . implode(", ", $teacherNames) . " )";
        }

        $groupNames = $this->getNames('groups', 'title', '#__usergroups');
        if (count($groupNames))
        {
            $introText .= " " . JText::_('COM_THM_ORGANIZER_E_AFFECTED') . implode(", ", $groupNames);
        }

        $introText .= "</p>";
        $data['introtext'] = $introText;
    }

    /**
     * Creates an introductory text for events
     *
     * @param   array  $data  an array of preprepared date and time entries
     * 
     * @return  string $introText
     */
    private function getDateText($data)
    {
        $dateText = $timeText = "";

        // One day events and reoccuring events use the 'same' time text
        if ($data['startdate'] == $data['enddate'] or $data['rec_type'] == 1)
        {
            if ($data['starttime'] != "")
            {
                $timeText .= JText::_('COM_THM_ORGANIZER_E_FROM') . $data['starttime'];
            }
            if ($data['endtime'] != "")
            {
                $timeText .= JText::_('COM_THM_ORGANIZER_E_TO') . $data['endtime'];
            }
            if ($data['starttime'] == "" and $data['endtime'] == "")
            {
                $timeText .= JText::_("COM_THM_ORGANIZER_E_ALLDAY");
            }
        }

        // Single day events use the same date text irregardless of repetition
        if ($data['startdate'] == $data['enddate'])
        {
            $dateText .= JText::_('COM_THM_ORGANIZER_E_ON') . $data['nativestartdate'] . $timeText;
        }
        // Repeating events which span multiple days
        elseif ($data['rec_type'])
        {
            $dateText .= JText::_('COM_THM_ORGANIZER_E_BETWEEN') . $data['nativestartdate'];
            $dateText .= JText::_('COM_THM_ORGANIZER_E_AND') . $data['nativeenddate'];
            $dateText .= $timeText;
        }
        // Block events which span multiple days
        else
        {
            $dateText .= JText::_('COM_THM_ORGANIZER_E_FROM');
            if ($data['starttime'] != "")
            {
                $dateText .= $data['starttime'] . JText::_('COM_THM_ORGANIZER_E_ON');
            }
            $dateText .= $data['nativestartdate'] . JText::_('COM_THM_ORGANIZER_E_TO');
            if ($data['endtime'] != "")
            {
                $dateText .= $data['endtime'] . JText::_('COM_THM_ORGANIZER_E_ON');
            }
            $dateText .= $data['nativeenddate'];
            if ($data['starttime'] == "" and $data['endtime'] == "")
            {
                $dateText .= JText::_("COM_THM_ORGANIZER_E_ALLDAY");
            }
        }
        return $dateText;
    }

    /**
     * Retrieves resource names from the database
     *
     * @param   string  $requestName  the name with which the REQUESTed resources can
     *                                be called upon
     * @param   string  $columnName   the column name in which the names are stored
     * @param   string  $tableName    the table which manages the resource
     * 
     * @return  array   $names the names of the requested resources
     */
    private function getNames($requestName, $columnName, $tableName)
    {
        $names = array();
        $$requestName = (isset($_REQUEST[$requestName]))? JRequest::getVar($requestName) : array();
        $dummyIndex = array_search('-1', $$requestName);
        if ($dummyIndex)
        {
            unset($$requestName[$dummyIndex]);
        }
        if (count($$requestName))
        {
            $dbo = JFactory::getDbo();
            $query = $dbo->getQuery(true);
            $query->select("$columnName");
            $query->from("$tableName");
            $requestedIDs = "( '" . implode("', '", $$requestName) . "' )";
            $query->where("id IN $requestedIDs");
            $dbo->setQuery((string) $query);
            $names = $dbo->loadResultArray();
            $names = (count($names))? $names : array();
        }
        return $names;
    }

    /**
     * Performs the update query to the appropriate tables
     *
     * @param   mixed  &$data  the event data
     * 
     * @return  boolean true on success, otherwise false
     */
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
        $conditions .= "modified = '" . date('Y-m-d H:i:s') . "', ";
        $conditions .= "modified_by = '{$data['userID']}', ";
        $conditions .= "publish_up = '{$data['publish_up']}', ";
        $conditions .= "publish_down = '{$data['publish_down']}' ";
        $query->set($conditions);
        $query->where("id = '{$data['id']}'");
        $dbo->setQuery((string) $query);
        $dbo->query();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->select("id, level");
        $query->from("#__assets");
        $query->where("name = 'com_content.category.{$data['contentCatID']}'");
        $dbo->setQuery((string) $query);
        $parentID = $dbo->loadResult();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $asset = JTable::getInstance('Asset');
        $asset->loadByName("com_content.article.{$data['id']}");
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
        $conditions .= "start = '{$data['start']}', ";
        $conditions .= "end = '{$data['end']}', ";
        $conditions .= "recurrence_type = '{$data['rec_type']}' ";
        $query->set($conditions);
        $query->where("id = '{$data['id']}'");
        $dbo->setQuery((string) $query);
        $dbo->query();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        return true;
    }

    /**
     * Saves a new event creating appropriate entries in the content, assets,
     * and event tables
     * 
     * @param   array  &$data  holds data from the request
     * 
     * @return  boolean true on success, otherwise false
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
        $statement .= "'" . date('Y-m-d H:i:s') . "', '1', ";
        $statement .= "'{$data['userID']}', '{$data['publish_up']}', ";
        $statement .= "'{$data['publish_down']}' ) ";
        $query->insert($statement);
        $dbo->setQuery((string) $query);
        $dbo->query();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->select('MAX(id)');
        $query->from('#__content');
        $query->where("title = '{$data['title']}'");
        $query->where("introtext = '{$data['introtext']}'");
        $query->where("catid = '{$data['contentCatID']}'");
        $dbo->setQuery((string) $query);
        $data['id'] = $dbo->loadResult();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__assets");
        $query->where("name = 'com_content.category.{$data['contentCatID']}'");
        $dbo->setQuery((string) $query);
        $parentID = $dbo->loadResult();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $asset = JTable::getInstance('Asset');
        $asset->name = "com_content.article.{$data['id']}";
        $asset->parent_id = $parentID;
        $asset->rules = '{}';
        $asset->title = $data['title'];
        $asset->setLocation($parentID, 'last-child');
        $asset->store();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->select('id');
        $query->from('#__assets');
        $query->where("name = 'com_content.article.{$data['id']}'");
        $dbo->setQuery((string) $query);
        $assetID = $dbo->loadResult();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->update("#__content");
        $query->set("asset_id = '$assetID'");
        $query->where("id = '{$data['id']}'");
        $dbo->setQuery((string) $query);
        $dbo->query();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $statement = "#__thm_organizer_events";
        $statement .= "( id, categoryID, startdate, enddate, ";
        $statement .= "starttime, endtime, recurrence_type, start, end ) ";
        $statement .= "VALUES ";
        $statement .= "( '{$data['id']}', '{$data['categoryID']}', '{$data['startdate']}', '{$data['enddate']}', ";
        $statement .= "'{$data['starttime']}', '{$data['endtime']}', '{$data['rec_type']}', '{$data['start']}', '{$data['end']}' ) ";
        $query->insert($statement);
        $dbo->setQuery((string) $query);
        $dbo->query();
        return ($dbo->getErrorNum())? false : true;
    }

    /**
     * saves associations of events and event resources
     *
     * @param   string  $tableName       the name of the resource association table
     * @param   string  $requestName     the name of the request resource variable
     * @param   string  $resourceColumn  the name of the resource id column
     * @param   int     $eventID         the id of the event
     * 
     * @return  boolean true on success false on failure
     */
    private function saveResources($tableName, $requestName, $resourceColumn, $eventID)
    {
        $dbo = JFactory::getDBO();

        // Remove old associations
        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from($tableName);
        $query->where("eventID = '$eventID'");
        $dbo->setQuery((string) $query);
        $dbo->query();

        // Add new ones (if requested)
        $resources = (isset($_REQUEST[$requestName]))? JRequest::getVar($requestName) : array();
        $noResourceIndex = array_search('-1', $resources);
        if ($noResourceIndex)
        {
            unset($resources[$noResourceIndex]);
        }
        if (count($resources))
        {
            $query = $dbo->getQuery(true);
            $statement = "$tableName ";
            $statement .= "( eventID, $resourceColumn ) ";
            $statement .= "VALUES ";
            $statement .= "( '$eventID', '" . implode("' ), ( '$eventID', '", $resources) . "' ) ";
            $query->insert($statement);
            $dbo->setQuery((string) $query);
            $dbo->query();
            if ($dbo->getErrorNum())
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Deletes entries in assets, content, events, event_teachers,
     * event_rooms, and event_groups associated with a particular event
     *
     * @param   int  $eventID  id of the event and associated content to be deleted
     * 
     * @return  boolean true on success, otherwise false
     */
    public function delete($eventID)
    {
        $dbo = JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__assets");
        $query->where("name = 'com_content.article.$eventID'");
        $dbo->setQuery((string) $query);
        $assetID = $dbo->loadResult();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $assetsTable = JTable::getInstance('asset');
        $assetsTable->delete($assetID);

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__content");
        $query->where("id = '$eventID'");
        $dbo->setQuery((string) $query);
        $dbo->query();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_events");
        $query->where("id = '$eventID'");
        $dbo->setQuery((string) $query);
        $dbo->query();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_teachers");
        $query->where("eventID = '$eventID'");
        $dbo->setQuery((string) $query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_rooms");
        $query->where("eventID = '$eventID'");
        $dbo->setQuery((string) $query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_groups");
        $query->where("eventID = '$eventID'");
        $dbo->setQuery((string) $query);
        $dbo->query();

        return true;
    }

    /**
     * Sends an email with the appointment title as subject and the introtext
     * for the appointment as body on the members of the affected groups
     * 
     * @param   mixed  &$data  the event information
     * 
     * @return  void
     */
    private function notify(&$data)
    {
        $user = JFactory::getUser();
        $mailer = JFactory::getMailer();
        $sender = array($user->email, $user->name);
        $mailer->setSender($sender);
        $recipients = $this->getRecipients();
        if (count($recipients))
        {
            $mailer->addRecipient($recipients);
        }
        else
        {
            return;
        }
        $mailer->setSubject(stripslashes($data['title']));
        $mailer->setBody(strip_tags($data['introtext']));
        $sent = $mailer->Send();
    }

    /**
     * getRecipients
     *
     * retrieves the users in the affected groups
     *
     * @return mixed array of email addresses
     */
    private function getRecipients()
    {
        $recipients = array();
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT email, name');
        $query->from('#__users AS user');
        $query->innerJoin('#__user_usergroup_map AS map ON user.id = map.user_id');
        $groups = $_REQUEST['groups'];
        foreach ($groups as $group)
        {
            $query->clear('where');
            $query->where("map.group_id = $group");
            $dbo->setQuery((string) $query);
            $groupEMails = $dbo->loadResultArray();
            if (count($groupEMails))
            {
                foreach ($groupEMails as $email)
                {
                    if (!in_array($email, $recipients))
                    {
                        $recipients[] = $email;
                    }
                }
            }
        }
        return $recipients;
    }
}
?>
