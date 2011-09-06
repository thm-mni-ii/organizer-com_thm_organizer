<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        create/edit appointment/event controller
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
require_once(JPATH_COMPONENT."/assets/classes/eventAccess.php");

class thm_organizerControllerevents extends JController
{
    /**
     * edit
     * 
     * reroutes to the event_edit view if access is allowed
     * 
     * @access public
     */
    public function edit()
    {
        $eventID = JRequest::getInt('eventID', null);
        $eventIDs = JRequest::getVar('eventIDs', null);
        $menuID = JRequest::getInt('Itemid');
        $access = false;
        if(!isset($eventID) and isset($eventIDs))
        {
            $eventID = 0;
            foreach($eventIDs as $selectedID)
            {
                if($selectedID)
                {
                    $eventID = $selectedID;
                    break;
                }
            }
        }
        if(isset($eventID) and $eventID > 0)
            $access = eventAccess::canEdit($eventID) or eventAccess::canEditOwn($eventID);
        else $access = eventAccess::canCreate();
        if($access)
        {
            $url = "index.php?option=com_thm_organizer&view=event_edit";
            $url .= ($eventID)? "&eventID=$eventID" : "";
            $url .= (isset($menuID))? "&Itemid=$menuID" : "";
            $link = JRoute::_($url, false);
            $this->setRedirect($link);
        }
        else
        {
            JError::raiseError( 777, JText::_('COM_THM_ORGANIZER_ERROR_NOAUTH') );
            return;
        }
    }

    /**
    * Saves the event
    *
    */
    function save()
    {
        $eventID = JRequest::getInt('eventID', 0);
        $menuID = JRequest::getVar('Itemid');

        if($eventID == 0) $canSave = eventAccess::canCreate();
        else $canSave = eventAccess::canEdit($eventID);
            
        if($canSave)
        {
            $schedulerCall = JRequest::getVar('schedulerCall');
            $model = $this->getModel('events');
            $eventID = $model->save();
            
            if($eventID)
            {
                $msg = JText::_( 'COM_THM_ORGANIZER_EVENT_SAVED' );
                if($schedulerCall)
                    $link = JRoute::_("index.php?option=com_thm_organizer&view=event&eventID=$eventID&tmpl=component", false);
                else $link = JRoute::_("index.php?option=com_thm_organizer&view=event&eventID=$eventID&Itemid=$menuID", false);
                $this->setRedirect($link, $msg);
            }
            else
            {
                $msg = JText::_( 'COM_THM_ORGANIZER_EVENT_SAVE_FAILED' );
                if($schedulerCall) $link = JRoute::_('index.php?option=com_thm_organizer&view=event_edit&eventID=0&tmpl=component', false);
                else $link = JRoute::_("index.php?option=com_thm_organizer&view=event_edit&Itemid=$menuID", false);
                $this->setRedirect($link, $msg, 'error');
            }
        }
        else
        {
            JError::raiseError( 777, JText::_('COM_THM_ORGANIZER_ERROR_NOAUTH') );
            return;
        }
    }

    /**
     * Saves the event
     *
     */
    function save2new()
    {
        $eventID = JRequest::getInt('eventID', 0);
        $menuID = JRequest::getVar('Itemid');

        if($eventID == 0) $canSave = eventAccess::canCreate();
        else
        {
            $isAuthor = eventAccess::isAuthor($eventID);
            $canEditOwn = ($isAuthor)? eventAccess::canEditOwn($eventID) : false;
            $canSave = eventAccess::canEdit($eventID) or $canEditOwn;
        }

        if($canSave)
        {
            $schedulerCall = JRequest::getVar('schedulerCall');
            $model = $this->getModel('events');
            $eventID = $model->save();

            if($eventID)
            {
                $msg = JText::_( 'COM_THM_ORGANIZER_EVENT_SAVED' );
                if($schedulerCall)
                    $link = JRoute::_('index.php?option=com_thm_organizer&view=event_edit&eventID=0&tmpl=component', false);
                else $link = JRoute::_("index.php?option=com_thm_organizer&view=event_edit&eventID=0&Itemid=$menuID", false);
                $this->setRedirect($link, $msg);
            }
            else
            {
                $msg = JText::_( 'COM_THM_ORGANIZER_EVENT_SAVE_FAILED' );
                if($schedulerCall) $link = JRoute::_('index.php?option=com_thm_organizer&view=event_edit&eventID=0&tmpl=component', false);
                else $link = JRoute::_("index.php?option=com_thm_organizer&view=event_edit&Itemid=$menuID", false);
                $this->setRedirect($link, $msg, 'error');
            }
        }
        else
        {
            JError::raiseError( 777, JText::_('COM_THM_ORGANIZER_ERROR_NOAUTH') );
            return;
        }
    }

    /**
     * function delete
     *
     * deletes event(s) and associated items
     *
     * @access public
     */
    public function delete()
    {
        $eventID = JRequest::getInt('eventID');
        $eventIDs = JRequest::getVar('eventIDs');
        $menuID = JRequest::getVar('Itemid');

        $success = false;
        $model = $this->getModel('events');
        if(isset($eventID))
        {
            $canDelete = eventAccess::canDelete($eventID);
            $success = $model->delete($eventID);
        }
        else if(isset($eventIDs) and count($eventIDs))
        {
            foreach($eventIDs as $eventID)
            {
                $canDelete = eventAccess::canDelete($eventID);
                $success = $model->delete($eventID);
            }
        }
        if($success)
        {
            $msg = JText::_( 'COM_THM_ORGANIZER_EVENT_DELETED' );
            $link = JRoute::_("index.php?option=com_thm_organizer&view=event_list&Itemid=$menuID", false);
            $this->setRedirect($link, $msg);
        }
        else
        {
            $msg = JText::_( 'COM_THM_ORGANIZER_EVENT_DELETE_FAILED' );
            $link = JRoute::_("index.php?option=com_thm_organizer&view=event_list&Itemid=$menuID", false);
            $this->setRedirect($link, $msg, 'error');
        }
    }

    /**
     * function search
     *
     * redirects to the event_list view which reformats its sql restriction
     *
     * @access public
     */
    public function search()
    {
        $menuID = JRequest::getVar('Itemid');
        $msg = "im search angekommen";
        $link = JRoute::_("index.php?option=com_thm_organizer&view=event_list&Itemid=$menuID", false);
        $this->setRedirect($link, $msg);
    }
}