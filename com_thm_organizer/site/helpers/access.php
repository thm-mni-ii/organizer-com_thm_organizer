<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerHelperAccess
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class provides methods for access checks
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THM_OrganizerHelperAccess
{
    /**
     * Checks for user creation access to the category with which the event is to be associated
     *
     * @param   int  $categoryID  the id of the content category
     *
     * @return  bool  true if the user can create content for the category, otherwise false
     */
    public static function canCreateEvents()
    {
        $user = JFactory::getUser();
        $canCreateContent = $user->authorise('core.create', 'com_content');
        $canCreateCategories = (count($user->getAuthorisedCategories('com_content', 'core.create')));
        return ($canCreateContent OR $canCreateCategories);
    }

    /**
     * Checks for user creation access to the category with which the event is to be associated
     *
     * @param   int  $categoryID  the id of the content category
     *
     * @return  bool  true if the user can create content for the category, otherwise false
     */
    public static function canCreateEvent($categoryID)
    {
        $user = JFactory::getUser();
        return $user->authorise('core.create', "com_content.category.$categoryID");
    }

    /**
     * Checks for user creation access to the category with which the event is to be associated
     *
     * @param   int  $eventID     the id of the associated content
     * @param   int  $created_by  the user id of the original author
     *
     * @return  bool  true if the user can edit the associated content, otherwise false
     */
    public static function canEditEvent($eventID, $created_by = 0)
    {
        $user = JFactory::getUser();
        $asset = "com_content.article.$eventID";
        $canEdit = $user->authorise('core.edit', $asset);
        if ($canEdit)
        {
            return true;
        }

        $userID	= $user->get('id');
        if (empty($userID))
        {
            return false;
        }
        $isOwn = empty($created_by)? false : $userID == $created_by;
        if (empty($isOwn))
        {
            return false;
        }
        return $user->authorise('core.edit.own', $asset);
    }

    /**
     * Checks for user deletion access to the content
     *
     * @param   int  $eventID  the id of the associated content
     *
     * @return  bool  true if the user can delete the associated content, otherwise false
     */
    public static function canDeleteEvent($eventID)
    {
        $user = JFactory::getUser();
        return $user->authorise('core.create', "com_content.article.$eventID");
    }

}
