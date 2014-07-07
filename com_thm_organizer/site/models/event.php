<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelEvent
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT_SITE . '/helper/event.php';

/**
 * Handles event perssistence
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelEvent extends JModelLegacy
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
        $data = $this->processRequestData();
        if (empty($data))
        {
            return 0;
        }

        THM_OrganizerHelperEvent::buildText($data);
        $eventSaved = ($data['id'] > 0)? $this->updateEvent($data) : $this->insertEvent($data);
        $teachersSaved = $this->saveResources('#__thm_organizer_event_teachers', 'teachers', 'teacherID', $data['id']);
        $roomsSaved = $this->saveResources('#__thm_organizer_event_rooms', 'rooms', 'roomID', $data['id']);
        $groupsSaved = $this->saveResources('#__thm_organizer_event_groups', 'groups', 'groupID', $data['id']);
        if ($eventSaved AND $teachersSaved AND $roomsSaved AND $groupsSaved)
        {
            $groups = JFactory::getApplication()->input->get('groups', array(), 'array');
            if (isset($data['emailNotification']) AND count($groups))
            {
                $success = $this->notify($data);
                if ($success)
                {
                    $dbo->transactionCommit();
                    return $data['id'];
                }
                $dbo->transactionRollback();
                return 0;
            }
            $dbo->transactionCommit();
            return $data['id'];
        }
        $dbo->transactionRollback();
        return 0;
    }

    /**
     * Processes the request data, reformatting, and consolidating it-
     *
     * @return  array  $data  array of request data, empty if the form object could not be found
     */
    public function processRequestData()
    {
        $input = JFactory::getApplication()->input;
        $data = $input->get('jform', null, 'array');
        if (empty($data))
        {
            return array();
        }

        $data['title'] = addslashes($data['title']);
        $data['alias'] = JApplication::stringURLSafe($data['title']);
        $data['fulltext'] = JFactory::getDbo()->escape($data['description']);
        $data['categoryID'] = $input->getInt('category', 0);
        $data['rec_type'] = $input->getInt('rec_type', 0);
        $data['userID'] = JFactory::getUser()->id;
        return $data;
    }

    /**
     * Performs the update query to the appropriate tables
     *
     * @param   mixed  &$event  the event data
     *
     * @return  boolean true on success, otherwise false
     */
    private function updateEvent(&$event)
    {
        $contentUpdated = $this->updateContent($event);
        if (!$contentUpdated)
        {
            return false;
        }

        $assetUpdated = $this->updateAsset($event);
        if (!$assetUpdated)
        {
            return false;
        }

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_events');
        $conditions = "categoryID = '{$event['categoryID']}', ";
        $conditions .= "startdate = '{$event['startdate']}', ";
        $conditions .= "enddate = '{$event['enddate']}', ";
        $conditions .= "starttime = '{$event['starttime']}', ";
        $conditions .= "endtime = '{$event['endtime']}', ";
        $conditions .= "start = '{$event['start']}', ";
        $conditions .= "end = '{$event['end']}', ";
        $conditions .= "recurrence_type = '{$event['rec_type']}' ";
        $query->set($conditions);
        $query->where("id = '{$event['id']}'");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
            return true;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Updates the content entry values
     *
     * @param   array  &$event  holds data from the request
     *
     * @return  bool  true on success, otherwise false
     */
    private function updateContent(&$event)
    {
        $query = $this->_db->getQuery(true);
        $query->update('#__content');
        $conditions = "title = '{$event['title']}', ";
        $conditions .= "alias = '{$event['alias']}', ";
        $conditions .= "introtext = '{$event['introtext']}', ";
        $conditions .= "#__content.fulltext = '{$event['fulltext']}', ";
        $conditions .= "state = '1', ";
        $conditions .= "catid = '{$event['contentCatID']}', ";
        $conditions .= "modified = '" . date('Y-m-d H:i:s') . "', ";
        $conditions .= "modified_by = '{$event['userID']}', ";
        $conditions .= "publish_up = '{$event['publish_up']}', ";
        $conditions .= "publish_down = '{$event['publish_down']}' ";
        $query->set($conditions);
        $query->where("id = '{$event['id']}'");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
            return true;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Updates the asset entry values
     *
     * @param   array  &$event  holds data from the request
     *
     * @return  bool  true on success, otherwise false
     */
    private function updateAsset(&$event)
    {
        // Gets the parent id (it may have changed)
        $query = $this->_db->getQuery(true);
        $query->select("id, level");
        $query->from("#__assets");
        $query->where("name = 'com_content.category.{$event['contentCatID']}'");
        $this->_db->setQuery((string) $query);
        try
        {
            $parentID = $this->_db->loadResult();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
        if (empty($parentID))
        {
            return false;
        }

        $asset = JTable::getInstance('Asset');
        $asset->loadByName("com_content.article.{$event['id']}");
        $asset->parent_id = $parentID;
        $asset->title = $event['title'];
        $asset->setLocation($parentID, 'last-child');
        try
        {
            $asset->store();
            return true;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Saves a new event creating appropriate entries in the content, assets,
     * and event tables
     *
     * @param   array  &$event  holds data from the request
     *
     * @return  boolean true on success, otherwise false
     */
    private function insertEvent(&$event)
    {
        $contentSaved = $this->insertContent($event);
        if (!$contentSaved)
        {
            return false;
        }

        $assetSaved = $this->insertAsset($event);
        if (!$assetSaved)
        {
            return false;
        }

        $query = $this->_db->getQuery(true);
        $statement = "#__thm_organizer_events";
        $statement .= "( id, categoryID, startdate, enddate, ";
        $statement .= "starttime, endtime, recurrence_type, start, end ) ";
        $statement .= "VALUES ";
        $statement .= "( '{$event['id']}', '{$event['categoryID']}', '{$event['startdate']}', '{$event['enddate']}', ";
        $statement .= "'{$event['starttime']}', '{$event['endtime']}', '{$event['rec_type']}', '{$event['start']}', '{$event['end']}' ) ";
        $query->insert($statement);
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
            return true;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Saves a new event's content
     *
     * @param   array  &$event  the event information
     *
     * @return  bool  true if no exception has been
     */
    private function insertContent(&$event)
    {
        $query = $this->_db->getQuery(true);
        $statement = "#__content";
        $statement .= "( title, alias, ";
        $statement .= "introtext, #__content.fulltext, ";
        $statement .= "state, catid, ";
        $statement .= "created, access, ";
        $statement .= "created_by, publish_up, ";
        $statement .= "publish_down ) ";
        $statement .= "VALUES ";
        $statement .= "( '{$event['title']}', '{$event['alias']}', ";
        $statement .= "'{$event['introtext']}', '{$event['fulltext']}', ";
        $statement .= "'1', '{$event['contentCatID']}', ";
        $statement .= "'" . date('Y-m-d H:i:s') . "', '1', ";
        $statement .= "'{$event['userID']}', '{$event['publish_up']}', ";
        $statement .= "'{$event['publish_down']}' ) ";
        $query->insert($statement);
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        $query = $this->_db->getQuery(true);
        $query->select('MAX(id)');
        $query->from('#__content');
        $query->where("title = '{$event['title']}'");
        $query->where("introtext = '{$event['introtext']}'");
        $query->where("catid = '{$event['contentCatID']}'");
        $this->_db->setQuery((string) $query);
        try
        {
            $event['id'] = $this->_db->loadResult();
            return empty($event['id'])? false : true;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Saves a new entry in the asset table and sets the corresponding content value
     *
     * @param   array  &$event  the event information
     *
     * @return  bool  true on success, otherwise false
     */
    private function insertAsset(&$event)
    {
        // Get the content category asset id
        $query = $this->_db->getQuery(true);
        $query->select("id");
        $query->from("#__assets");
        $query->where("name = 'com_content.category.{$event['contentCatID']}'");
        $this->_db->setQuery((string) $query);
        try
        {
            $categoryAssetID = $this->_db->loadResult();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
        if (empty($categoryAssetID))
        {
            return false;
        }

        // Create the new asset
        $asset = JTable::getInstance('Asset');
        $asset->name = "com_content.article.{$event['id']}";
        $asset->parent_id = $categoryAssetID;
        $asset->rules = '{}';
        $asset->title = $event['title'];
        $asset->setLocation($categoryAssetID, 'last-child');
        try
        {
            $asset->store();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        // Get the id of the new asset
        $query = $this->_db->getQuery(true);
        $query->select('id');
        $query->from('#__assets');
        $query->where("name = 'com_content.article.{$event['id']}'");
        $this->_db->setQuery((string) $query);
        try
        {
            $assetID = $this->_db->loadResult();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
        if (empty($assetID))
        {
            return false;
        }

        // Update the asset id value for the previously created content
        $query = $this->_db->getQuery(true);
        $query->update("#__content");
        $query->set("asset_id = '$assetID'");
        $query->where("id = '{$event['id']}'");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        // Confirm the content was updated
        $query = $this->_db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from('#__assets');
        $query->where("asset_id = '$assetID'");
        $query->where("id = '{$event['id']}'");
        $this->_db->setQuery((string) $query);
        try
        {
            $count = $this->_db->loadResult();
            return $count == 1;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Saves associations of events and event resources
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
        // Remove old associations
        $query = $this->_db->getQuery(true);
        $query->delete();
        $query->from($tableName);
        $query->where("eventID = '$eventID'");
        $this->_db->setQuery((string) $query);
        try 
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        // Add new ones (if requested)
        $resources = JFactory::getApplication()->input->get($requestName, array(), 'array');
        $noResourceIndex = array_search('-1', $resources);
        if ($noResourceIndex)
        {
            unset($resources[$noResourceIndex]);
        }
        if (!empty($resources))
        {
            $query = $this->_db->getQuery(true);
            $statement = "$tableName ";
            $statement .= "( eventID, $resourceColumn ) ";
            $statement .= "VALUES ";
            $statement .= "( '$eventID', '" . implode("' ), ( '$eventID', '", $resources) . "' ) ";
            $query->insert($statement);
            $this->_db->setQuery((string) $query);
            try
            {
                $this->_db->execute();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
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
        // Get the asset id
        $query = $this->_db->getQuery(true);
        $query->select("id");
        $query->from("#__assets");
        $query->where("name = 'com_content.article.$eventID'");
        $this->_db->setQuery((string) $query);
        try 
        {
            $assetID = $this->_db->loadResult();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
        if (empty($assetID))
        {
            return false;
        }

        $this->_db->transactionStart();

        $assetsTable = JTable::getInstance('asset');
        try
        {
            $assetsTable->delete($assetID);
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->_db->transactionRollback();
            return false;
        }

        $query = $this->_db->getQuery(true);
        $query->delete();
        $query->from("#__content");
        $query->where("id = '$eventID'");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->_db->transactionRollback();
            return false;
        }

        $query = $this->_db->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_events");
        $query->where("id = '$eventID'");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->_db->transactionRollback();
            return false;
        }

        $query = $this->_db->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_teachers");
        $query->where("eventID = '$eventID'");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->_db->transactionRollback();
            return false;
        }

        $query = $this->_db->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_rooms");
        $query->where("eventID = '$eventID'");
        $this->_db->setQuery((string) $query);
        try 
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->_db->transactionRollback();
            return false;
        }

        $query = $this->_db->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_groups");
        $query->where("eventID = '$eventID'");
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->_db->transactionRollback();
            return false;
        }

        $this->_db->transactionCommit();
        return true;
    }

    /**
     * Sends an email with the appointment title as subject and the introtext
     * for the appointment as body on the members of the affected groups
     *
     * @param   mixed  &$event  the event information
     *
     * @return  boolean  true on success, otherwise
     */
    private function notify(&$event)
    {
        $user = JFactory::getUser();
        $mailer = JFactory::getMailer();
        $sender = array($user->email, $user->name);
        $mailer->setSender($sender);
        $recipients = $this->getRecipients();
        if (!empty($recipients))
        {
            $mailer->addRecipient($recipients);
        }
        else
        {
            return true;
        }
        $mailer->setSubject(stripslashes($event['title']));
        $mailer->setBody(strip_tags($event['introtext']));
        $success = $mailer->Send();
        return ($success === true)? true : false;
    }

    /**
     * Retrieves the users in the affected groups
     *
     * @return mixed array of email addresses
     */
    private function getRecipients()
    {
        $recipients = array();
        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT email, name');
        $query->from('#__users AS user');
        $query->innerJoin('#__user_usergroup_map AS map ON user.id = map.user_id');
        $groups = JFactory::getApplication()->input->get('groups', array(), 'array');
        foreach ($groups as $group)
        {
            $query->clear('where');
            $query->where("map.group_id = $group");
            $this->_db->setQuery((string) $query);
            
            try
            {
                $groupEMails = $this->_db->loadColumn();
                if (empty($groupEMails))
                {
                    continue;
                }
                foreach ($groupEMails as $email)
                {
                    if (!in_array($email, $recipients))
                    {
                        $recipients[] = $email;
                    }
                }
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return $recipients;
            }
        }
        return $recipients;
    }
}
