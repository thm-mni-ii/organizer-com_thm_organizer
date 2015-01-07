<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelEvent
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT_SITE . '/helpers/event.php';

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
     * Saves the event, associated content, and asset to the database
     *
     * @param   array  $event  the event information from the request
     *
     * @return  mixed  int id on success, boolean false on failure
     */
    public function save(&$event)
    {
        $event['title'] = $this->_db->escape($event['title']);
        $event['alias'] = JApplicationHelper::stringURLSafe($event['title']);
        THM_OrganizerHelperEvent::processTimes($event);
        THM_OrganizerHelperEvent::createIntroText($event);
        $event['introtext'] = $this->_db->escape($event['introtext']);
        $event['fulltext'] = $this->_db->escape($event['description']);

        $this->_db->transactionStart();

        $eventSaved = empty($event['id'])? $this->insertEvent($event) : $this->updateEvent($event);
        $teachersSaved = $this->saveResources($event['id'], $event['teachers'], 'teacherID', '#__thm_organizer_event_teachers');
        $roomsSaved = $this->saveResources($event['id'], $event['rooms'], 'roomID', '#__thm_organizer_event_rooms');
        $groupsSaved = $this->saveResources($event['id'], $event['groups'], 'groupID', '#__thm_organizer_event_groups');

        if ($eventSaved AND $teachersSaved AND $roomsSaved AND $groupsSaved)
        {
            $groups = JFactory::getApplication()->input->get('groups', array(), 'array');
            if (isset($event['emailNotification']) AND count($groups))
            {
                $success = $this->notify($event);
                if ($success)
                {
                    $this->_db->transactionCommit();
                    return $event['id'];
                }
                $this->_db->transactionRollback();
                return false;
            }
            $this->_db->transactionCommit();
            return $event['id'];
        }
        $this->_db->transactionRollback();
        return false;
    }

    /**
     * Saves an existing event updating appropriate entries in the content and event tables
     *
     * @param   array  &$event  the event data
     *
     * @return  boolean true on success, otherwise false
     */
    private function updateEvent(&$event)
    {
        $content = JTable::getInstance('Content');
        try
        {
            $contentLoaded = $content->load($event['id']);
            if (!$contentLoaded)
            {
                return false;
            }
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }


        $this->setContentAttributes($content, $event);
        $content->modified = date('Y-m-d H:i:s');
        $content->modified_by = JFactory::getUser()->id;

        try
        {
            $contentStored = $content->store();
            if (!$contentStored)
            {
                return false;
            }
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
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
        $conditions .= "recurrence_type = '{$event['recurrence_type']}' ";
        $conditions .= "global = '{$event['global']}' ";
        $conditions .= "reserves = '{$event['reserves']}' ";
        $query->set($conditions);
        $query->where("id = '{$event['id']}'");
        $this->_db->setQuery((string) $query);
        try
        {
            $eventSaved = $this->_db->execute();
            return empty($eventSaved)? false : true;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Saves a new event creating appropriate entries in the content and event tables
     *
     * @param   array  &$event  the event data
     *
     * @return  boolean true on success, otherwise false
     */
    private function insertEvent(&$event)
    {
        $content = JTable::getInstance('Content');

        $this->setContentAttributes($content, $event);
        $content->created = date('Y-m-d H:i:s');
        $content->created_by = JFactory::getUser()->id;

        try
        {
            $contentStored = $content->store();
            if(!$contentStored OR empty($content->id))
            {
                return false;
            }
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        $event['id'] = $content->id;

        $query = $this->_db->getQuery(true);
        $statement = "#__thm_organizer_events";
        $statement .= "( id, categoryID, startdate, enddate, starttime, endtime, recurrence_type, start, end, global, reserves ) ";
        $statement .= "VALUES ";
        $statement .= "( '{$event['id']}', '{$event['categoryID']}', '{$event['startdate']}', '{$event['enddate']}', ";
        $statement .= "'{$event['starttime']}', '{$event['endtime']}', '{$event['recurrence_type']}', '{$event['start']}', ";
        $statement .= "'{$event['end']}', '{$event['global']}', '{$event['reserves']}') ";
        $query->insert($statement);
        $this->_db->setQuery((string) $query);
        try
        {
            $eventSaved = $this->_db->execute();
            return empty($eventSaved)? false : true;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Sets content attributes used for both insert and update
     *
     * @param   object  &$content  the object representing the content table
     * @param   array   &$event    the array holding event data
     *
     * @return  void  sets common object variables
     */
    private function setContentAttributes(&$content, &$event)
    {
        $content->title = $event['title'];
        $content->alias = $event['alias'];
        $content->introtext = $event['introtext'];
        $content->fulltext = $event['fulltext'];
        $content->state = 1;
        $content->catid = $event['categoryID'];
        $content->access = 1;
        $content->publish_up = $event['publish_up'];
        $content->publish_down = $event['publish_down'];
    }

    /**
     * Saves associations of events and event resources
     *
     * @param   int     $eventID         the id of the event
     * @param   array   &$resources      the event resources
     * @param   string  $columnName  the name of the resource id column
     * @param   string  $tableName       the name of the resource association table
     *
     * @return  boolean true on success false on failure
     */
    private function saveResources($eventID, &$resources, $columnName, $tableName)
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

        if (!empty($resources))
        {
            $query = $this->_db->getQuery(true);
            $statement = "$tableName ";
            $statement .= "( eventID, $columnName ) ";
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
