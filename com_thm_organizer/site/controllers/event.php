<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerControllerEvent
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') OR die;
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT . "/assets/classes/eventAccess.php";
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper' . DS . 'event.php';

/**
 * Performs access checks and user actions for events and associated resources
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class THM_OrganizerControllerEvent extends JController
{
    /**
     * edit
     *
     * performs access checks for the current user against the id of the event
     * to be edited, or content (event) creation access if id is missing or 0
     *
     * @return void
     */
    public function edit()
    {
        $eventID = JRequest::getInt('eventID', null);
        $eventIDs = JRequest::getVar('eventIDs', null);
        $menuID = JRequest::getInt('Itemid');
        $access = false;
        if (!isset($eventID) and isset($eventIDs))
        {
            $eventID = 0;
            foreach ($eventIDs as $selectedID)
            {
                if ($selectedID)
                {
                    $eventID = $selectedID;
                    break;
                }
            }
        }
        if (isset($eventID) and $eventID > 0)
        {
            $access = THMEventAccess::canEdit($eventID) or THMEventAccess::canEditOwn($eventID);
        }
        else
        {
            $access = THMEventAccess::canCreate();
        }
        if ($access)
        {
            $url = "index.php?option=com_thm_organizer&view=event_edit";
            $url .= ($eventID)? "&eventID=$eventID" : "";
            $url .= (isset($menuID))? "&Itemid=$menuID" : "";
            $this->setRedirect(JRoute::_($url, false));
        }
        else
        {
            THMEventAccess::noAccess();
        }
    }

    /**
     * save
     *
     * performs access checks and calls the save function of the events model
     * reroutes to the single event view of the created event upon success
     *
     * @return void
     */
    public function save()
    {
        $eventID = JRequest::getInt('eventID', 0);
        $menuID = JRequest::getVar('Itemid');

        if (THMEventAccess::canCreate() OR THMEventAccess::canEdit($eventID))
        {
            $model = $this->getModel('event');
            $eventID = $model->save();

            if ($eventID)
            {
                $msg = JText::_('COM_THM_ORGANIZER_EVENT_SAVED');
                    $link = JRoute::_("index.php?option=com_thm_organizer&view=event_details&eventID=$eventID&Itemid=$menuID", false);
                $this->setRedirect($link, $msg);
            }
            else
            {
                $msg = JText::_('COM_THM_ORGANIZER_EVENT_SAVE_FAILED');
                    $link = JRoute::_("index.php?option=com_thm_organizer&view=event_edit&Itemid=$menuID", false);
                $this->setRedirect($link, $msg, 'error');
            }
        }
        else
        {
            THMEventAccess::noAccess();
        }
    }

    /**
     * save2new
     *
     * performs access checks and calls the save function of the events model
     * reroutes to the event editing view for the creation of a new event upon
     * success
     *
     * @return void
     */
    public function save2new()
    {
        $eventID = JRequest::getInt('id', 0);
        $menuID = JRequest::getVar('Itemid');

        if ($eventID == 0)
        {
            $canSave = THMEventAccess::canCreate();
        }
        else
        {
            $isAuthor = THMEventAccess::isAuthor($eventID);
            $canEditOwn = ($isAuthor)? THMEventAccess::canEditOwn($eventID) : false;
            $canSave = THMEventAccess::canEdit($eventID) or $canEditOwn;
        }

        if ($canSave)
        {
            $model = $this->getModel('event');
            $eventID = $model->save();

            if ($eventID)
            {
                $msg = JText::_('COM_THM_ORGANIZER_EVENT_SAVED');
                $link = JRoute::_("index.php?option=com_thm_organizer&view=event_edit&eventID=0&Itemid=$menuID", false);
                $this->setRedirect($link, $msg);
            }
            else
            {
                $msg = JText::_('COM_THM_ORGANIZER_EVENT_SAVE_FAILED');
                $link = JRoute::_("index.php?option=com_thm_organizer&view=event_edit&eventID=0&Itemid=$menuID", false);
                $this->setRedirect($link, $msg, 'error');
            }
        }
        else
        {
            THMEventAccess::noAccess();
        }
    }

    /**
     * delete
     *
     * performs access checks calls the delete function of the event model for
     * one or multiple eventsted items
     *
     * @return void
     */
    public function delete()
    {
        $eventID = JRequest::getInt('eventID');
        $eventIDs = JRequest::getVar('eventIDs');
        $menuID = JRequest::getVar('Itemid');
        $success = false;
        $model = $this->getModel('event');
        if (isset($eventID) && $eventID != 0)
        {
            (THMEventAccess::canDelete($eventID))?
                $success = $model->delete($eventID) : THMEventAccess::noAccess();
        }
        elseif (isset($eventIDs) and count($eventIDs))
        {
            foreach ($eventIDs as $id)
            {
                if (THMEventAccess::canDelete($id))
                {
                    $success = $model->delete($id);
                }
                else
                {
                    THMEventAccess::noAccess();
                }
            }
        }
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_EVENT_DELETED');
            $link = JRoute::_("index.php?option=com_thm_organizer&view=event_manager&Itemid=$menuID", false);
            $this->setRedirect($link, $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_EVENT_DELETE_FAILED');
            $link = JRoute::_("index.php?option=com_thm_organizer&view=event_manager&Itemid=$menuID", false);
            $this->setRedirect($link, $msg, 'error');
        }
    }

    /**
     * function search
     *
     * redirects to the event_manager view which reformats its sql restriction
     *
     * @return void
 
    public function search()
    {
        JComponentHelper::ge
        $menuID = JRequest::getVar('Itemid');
        $link = JRoute::_("index.php?option=com_thm_organizer&view=event_manager&Itemid=$menuID", false);
        $this->setRedirect($link);
    }
    */
}
