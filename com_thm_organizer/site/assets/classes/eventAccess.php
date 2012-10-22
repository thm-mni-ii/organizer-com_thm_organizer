<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        event access
 *@author      James Antrim jamesDOTantrimATyahooDOTcom
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
/**
 * Contains static functions to determine user access to events
 * 
 * @package  Joomla.Site
 * 
 * @since    1.5
 */
class eventAccess
{
    /**
     * Checks if the current user is the author of a given event
     * 
     * @param   int  $eventID  the id of the event to be checked
     *
     * @return bool true if user is the autor of the event, otherwise false
     */
    public static function isAuthor($eventID)
    {
        $dbo = JFactory::getDbo();
        $user = JFactory::getUser();
        $query = $dbo->getQuery(true);
        $query->select("created_by AS author");
        $query->from("#__content");
        $query->where("id = '$eventID'");
        $dbo->setQuery((string) $query);
        $author = $dbo->loadResult();
        $isAuthor = ($user->id == $author)? true : false;
        return $isAuthor;
    }

    /**
     * canCreate
     *
     * checks if the current user can create content (events)
     *
     * @return boolean true if the user can create content, otherwise false
     */
    public static function canCreate()
    {
        $canCreate = false;
        $dbo = JFactory::getDbo();
        $user = JFactory::getUser();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT c.id");
        $query->from("#__categories AS c");
        $query->innerJoin("#__thm_organizer_categories AS ec ON ec.contentCatID = c.id");
        $dbo->setQuery((string) $query);
        $categoryIDs = $dbo->loadResultArray();

        if (isset($categoryIDs) and count($categoryIDs))
        {
            foreach ($categoryIDs as $categoryID)
            {
                $assetname = "com_content.category.$categoryID";
                $canCreate = $user->authorise('core.create', $assetname);
                if ($canCreate)
                {
                    break;
                }
            }
        }
        return $canCreate;
    }

    /**
     * Checks if the current user can edit a given event
     *
     * @param   int  $eventID  the id of the event
     * 
     * @return  boolean true if the user can edit the event, otherwise false
     */
    public static function canEdit($eventID)
    {
        $user = JFactory::getUser();
        $eventID = JRequest::getInt('eventID');
        $assetname = "com_content.article.$eventID";
        $canEdit = $canEditOwn = false;
        $canEdit = $user->authorise('core.edit', $assetname);
        $canEdit = (isset($canEdit) and $canEdit);
        $canEditOwn = (self::isAuthor($eventID))? self::canEditOwn($eventID) : false;
        if ($canEdit or $canEditOwn)
        {
            $canEdit = true;
        }
        return $canEdit;
    }

    /**
     * Checks if the user can edit en event which he has authored
     *
     * @param   int  $eventID  the id of the event to be edited
     * 
     * @return  boolean true if the user can edit own events, otherwise false
     */
    public static function canEditOwn($eventID)
    {
        $user = JFactory::getUser();
        $assetname = "com_content.article.$eventID";
        $canEditOwn = $user->authorise('core.edit.own', $assetname);
        if (!isset($canEditOwn))
        {
            $canEditOwn = false;
        }
        return $canEditOwn;
    }

    /**
     * Checks if the current user is allowed to delete an event
     * 
     * @param   int  $eventID  the id of the event to be checked
     *
     * @return  boolean true if the user can delete a given event, otherwise false
     */
    public static function canDelete($eventID)
    {
        $user = JFactory::getUser();
        $assetname = "com_content.article.$eventID";
        $canDelete = $user->authorise('core.delete', $assetname);
        $canDelete = (isset($canDelete))? $canDelete : false;
        return $canDelete;
    }

    /**
     * Issues a generic warning when unauthorized function calls are performed
     * 
     * @return void
     */
    public static function noAccess()
    {
        JError::raiseError(777, JText::_('COM_THM_ORGANIZER_ERROR_NOAUTH'));
    }
}
