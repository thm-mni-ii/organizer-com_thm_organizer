<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        create/edit appointment/event controller
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');

class thm_organizerControllerevents extends JController
{

    private function isAuthor($eventID)
    {
        $dbo = JFactory::getDbo();
        $user = JFactory::getUser();
        $query = $dbo->getQuery(true);
        $query->select("created_by AS author");
        $query->from("#__content");
        $query->where("id = '$eventID'");
        $dbo->setQuery((string)$query);
        $author = $dbo->loadResult();
        $isAuthor = ($user->id == $author)? true : false;
        return $isAuthor;
    }

    private function canCreate()
    {
        $canCreate = false;
        $dbo = JFactory::getDbo();
        $user = JFactory::getUser();

        $query = $dbo->getQuery(true);
        $query->select("ccat.id");
        $query->from('#__thm_organizer_categories AS ecat');
        $query->innerJoin('#__categories AS ccat ON ecat.contentCatID = ccat.id');
        $dbo->setQuery((string)$query);
        $associatedCategoryIDs = $dbo->loadResultArray();

        if(isset($associatedCategoryIDs) and count($associatedCategoryIDs))
        {
            foreach($associatedCategoryIDs as $associatedCategoryID)
            {
                $assetname = "com_content.category.$associatedCategoryID";
                $canWrite = $user->authorise('core.create', $assetname);
                if($canWrite)
                {
                    $canCreate = true;
                    break;
                }
            }
        }

        return $canCreate;
    }

    private function canEdit($eventID)
    {
        $user = JFactory::getUser();
        $assetname = "com_content.article.$eventID";
        $canEdit = $user->authorise('core.edit', $assetname);
        if(!isset($canEdit))$canEdit = false;
        return $canEdit;
    }

    private function canEditOwn($eventID)
    {
        $user = JFactory::getUser();
        $assetname = "com_content.article.$eventID";
        if($isAuthor) $canEditOwn = $user->authorise('core.edit.own', $assetname);
        if(!isset($canEditOwn))$canEditOwn = false;
        return $canEditOwn;
    }

    private function canDelete()
    {
        $user = JFactory::getUser();
        $eventID = JRequest::getInt('eventID');
        $eventIDs = JRequest::getVar('eventIDs');
        if(isset($eventID))
        {
            $assetname = "com_content.article.$eventID";
            $canDelete = $user->authorise('core.create', $assetname);
        }
        else if(isset($eventIDs) and count($eventIDs))
        {
            foreach($eventIDs as $eventID)
            {
                $assetname = "com_content.article.$eventID";
                $canDelete = $user->authorise('core.create', $assetname);
                if(!$canDelete) break;
            }
        }
        $canDelete = (isset($canDelete))? $canDelete : false;
        return $canDelete;
    }

    /**
     * edit
     * 
     * reroutes to the event_edit view if access is allowed
     * 
     * @access public
     */
    public function edit()
    {
        $eventID = JRequest::getInt('eventID', 0);
        $menuID = JRequest::getInt('Itemid');
        $dbo = JFactory::getDbo();
        $user = JFactory::getUser();
        $access = false;

        if($eventID) $access = $this->canEdit($eventID) or $this->canEditOwn($eventID);
        else $acces = $this->canCreate();

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

        if($eventID == 0) $canSave = $this->canCreate();
        else
        {
            $isAuthor = $this->isAuthor($eventID);
            $canEditOwn = ($isAuthor)? $this->canEditOwn($eventID) : false;
            $canSave = $this->canEdit($eventID) or $canEditOwn;
        }
            
        if($canSave)
        {
            $schedulerCall = JRequest::getVar('schedulerCall');
            $model = $this->getModel('events');
            $eventID = $model->save();
            
            if($eventID)
            {
                $msg = JText::_( 'COM_THM_ORGANIZER_EE_SAVED' );
                if($schedulerCall)
                    $link = JRoute::_('index.php?option=com_thm_organizer&view=event_edit&eventID=0&tmpl=component', false);
                else $link = JRoute::_("index.php?option=com_thm_organizer&view=event&eventID=$eventID&Itemid=$menuID", false);
                $this->setRedirect($link, $msg);
            }
            else
            {
                $msg = JText::_( 'COM_THM_ORGANIZER_EE_SAVE_FAILED' );
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
        $canDelete = $this->canDelete();
        if($canDelete)
        {
            $menuID = JRequest::getVar('Itemid');
            $model = $this->getModel('events');
            $success = $model->delete();
            if($success)
            {
                $msg = JText::_( 'COM_THM_ORGANIZER_EVENTS_DELETED' );
                $link = JRoute::_("index.php?option=com_thm_organizer&view=event_list&Itemid=$menuID", false);
                $this->setRedirect($link, $msg);
            }
            else
            {
                $msg = JText::_( 'COM_THM_ORGANIZER_EVENTS_DELETE_FAILED' );
                $link = JRoute::_("index.php?option=com_thm_organizer&view=event_list&Itemid=$menuID", false);
                $this->setRedirect($link, $msg, 'error');
            }
        }
        else
        {
            JError::raiseError( 777, JText::_('COM_THM_ORGANIZER_ERROR_NOAUTH') );
            return;
        }
    }
}