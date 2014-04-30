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
require_once JPATH_COMPONENT . '/helper/event.php';

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
        $data = $this->cleanRequestData();
        THM_OrganizerHelperEvent::buildtext($data);
        $eventSaved = ($data['id'] > 0)? $this->saveExistingEvent($data) : $this->saveNewEvent($data);
        $teachersSaved = $this->saveResources("#__thm_organizer_event_teachers", "teachers", "teacherID", $data['id']);
        $roomsSaved = $this->saveResources("#__thm_organizer_event_rooms", "rooms", "roomID", $data['id']);
        $groupsSaved = $this->saveResources("#__thm_organizer_event_groups", "groups", "groupID", $data['id']);
        if ($eventSaved AND $teachersSaved AND $roomsSaved AND $groupsSaved)
        {
            $groups = JFactory::getApplication()->input->get('groups');
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
     * cleanRequestData
     *
     * filters the data from the request
     *
     * @return mixed $data request data
     */
    public function cleanRequestData()
    {
        $data = JRequest::getVar('jform', null, null, null, 4);
        $data['categoryID'] = JFactory::getApplication()->input->getInt('category');
        $data['userID'] = JFactory::getUser()->id;
        $data['title'] = addslashes($data['title']);
        $data['alias'] = JApplication::stringURLSafe($data['title']);
        $data['fulltext'] = $this->getDbo()->escape($data['description']);
        return $data;
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
        $dbo->execute();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->select("id, level");
        $query->from("#__assets");
        $query->where("name = 'com_content.category.{$data['contentCatID']}'");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $parentID = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_CONTENT_CATEGORIES"), 500);
        }
        
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
            $this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $dbo->stderr(true)));
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
        $dbo->execute();
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
        $dbo->execute();
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
        
        try 
        {
            $data['id'] = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_CONTENT"), 500);
        }
        
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__assets");
        $query->where("name = 'com_content.category.{$data['contentCatID']}'");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $parentID = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_ASSETS"), 500);
        }
        
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
        
        try 
        {
            $assetID = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_ASSETS"), 500);
        }
        
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->update("#__content");
        $query->set("asset_id = '$assetID'");
        $query->where("id = '{$data['id']}'");
        $dbo->setQuery((string) $query);
        $dbo->execute();
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
        $dbo->execute();
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
        
        try 
        {
            $dbo->execute();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_RESOURCE_SAVE"), 500);
        }

        // Add new ones (if requested)
        $resources = JFactory::getApplication()->input->get($requestName, array());
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
            $dbo->execute();
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
        
        try 
        {
            $assetID = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_ASSETS"), 500);
        }
        
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
        $dbo->execute();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_events");
        $query->where("id = '$eventID'");
        $dbo->setQuery((string) $query);
        $dbo->execute();
        if ($dbo->getErrorNum())
        {
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_teachers");
        $query->where("eventID = '$eventID'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $dbo->execute();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_EVENT_TEACHERS_DELETE"), 500);
        }

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_rooms");
        $query->where("eventID = '$eventID'");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $dbo->execute();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_EVENT_ROOMS_DELETE"), 500);
        }

        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_event_groups");
        $query->where("eventID = '$eventID'");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $dbo->execute();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_EVENT_GROUPS_DELETE"), 500);
        }

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
            return true;
        }
        $mailer->setSubject(stripslashes($data['title']));
        $mailer->setBody(strip_tags($data['introtext']));
        return $mailer->Send();
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
        $groups = JFactory::getApplication()->input->get('groups');
        foreach ($groups as $group)
        {
            $query->clear('where');
            $query->where("map.group_id = $group");
            $dbo->setQuery((string) $query);
            
            try
            {
                $groupEMails = $dbo->loadColumn();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_GROUP_EMAILS"), 500);
            }
            
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
