<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        access.php
 * @description checks user rights against events
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     0.0.1
 */

class eventAccess
{
    /**
     * isAuthor
     *
     * checks if the current user is the author of a given event
     *
     * @return bool true if user is the autor of the event, otherwise false
     */
    public function isAuthor($eventID)
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

    /**
     * canCreate
     *
     * checks if the current user can create content (events)
     *
     * @return boolean true if the user can create content, otherwise false
     */
    public function canCreate()
    {
        $canCreate = false;
        $dbo = JFactory::getDbo();
        $user = JFactory::getUser();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT c.id");
        $query->from("#__categories AS c");
        $query->innerJoin("#__thm_organizer_categories AS ec ON ec.contentCatID = c.id");
        $dbo->setQuery((string)$query);
        $categoryIDs = $dbo->loadResultArray();

        if(isset($categoryIDs) and count($categoryIDs))
        {
            foreach($categoryIDs as $categoryID)
            {
                $assetname = "com_content.category.$categoryID";
                $canCreate = $user->authorise('core.create', $assetname);
                if($canCreate) break;
            }
        }
        return $canCreate;
    }

    /**
     * canEdit
     *
     * checks if the current user can edit a given event
     *
     * @param int $eventID the id of the event
     * @return boolean true if the user can edit the event, otherwise false
     */
    public function canEdit($eventID)
    {
        $user = JFactory::getUser();
        $eventID = JRequest::getInt('eventID');
        $assetname = "com_content.article.$eventID";
        $canEdit = $canEditOwn = false;
        $canEdit = $user->authorise('core.edit', $assetname);
        $canEdit = (isset($canEdit) and $canEdit);
        $canEditOwn = (self::isAuthor($eventID))? self::canEditOwn($eventID) : false;
        if($canEdit or $canEditOwn) $canEdit = true;
        return $canEdit;
    }

    /**
     * canEditOwn
     *
     * checks if the user can edit en event which he has authored
     *
     * @param int $eventID the id of the event to be edited
     * @return boolean true if the user can edit own events, otherwise false
     */
    public function canEditOwn($eventID)
    {
        $user = JFactory::getUser();
        $assetname = "com_content.article.$eventID";
        $canEditOwn = $user->authorise('core.edit.own', $assetname);
        if(!isset($canEditOwn))$canEditOwn = false;
        return $canEditOwn;
    }

    /**
     * canDelete
     *
     * checks if the current user is allowed to delete an event
     *
     * @return boolean true if the user can delete a given event, otherwise false
     */
    public function canDelete($eventID)
    {
        $user = JFactory::getUser();
        $assetname = "com_content.article.$eventID";
        $canDelete = $user->authorise('core.delete', $assetname);
        $canDelete = (isset($canDelete))? $canDelete : false;
        return $canDelete;
    }
}