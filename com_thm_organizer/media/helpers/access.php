<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class provides generalized functions useful for several component files.
 */
class THM_OrganizerHelperAccess
{
    /**
     * Checks whether the user has access to documenation resources and their respective views.
     *
     * @param string $resource
     * @param int    $resourceID
     *
     * @return bool true if the user is authorized for facility management functions and views.
     * @throws Exception
     */
    public static function allowDocumentAccess($resource = '', $resourceID = 0)
    {
        if (self::isAdmin()) {
            return true;
        }

        $user = JFactory::getUser();
        if (empty($resource) or empty($resourceID)) {
            $allowedDepartments = self::getAccessibleDepartments('manage');
            $canManage          = false;
            foreach ($allowedDepartments as $departmentID) {
                $departmentManager = $user->authorise('organizer.manage', "com_thm_organizer.department.$departmentID");
                $canManage         = ($canManage or $departmentManager);
            }

            return $canManage;
        }

        return $user->authorise('organizer.manage', "com_thm_organizer.$resource.$resourceID");
    }

    /**
     * Checks whether the user has access to facility management resources and their respective views.
     *
     * @return bool true if the user is authorized for facility management functions and views.
     */
    public static function allowFMAccess()
    {
        return (self::isAdmin() or JFactory::getUser()->authorise('organizer.fm', 'com_thm_organizer'));
    }

    /**
     * Checks whether the user has access to human resources as such and their respective views.
     *
     * @return bool true if the user is authorized for facility management functions and views.
     */
    public static function allowHRAccess()
    {
        return (self::isAdmin() or JFactory::getUser()->authorise('organizer.hr', 'com_thm_organizer'));
    }

    /**
     * Checks whether the user has access to scheduling resources and their respective views.
     *
     * @param int $scheduleID   the id of the schedule for whom access rights are being checked
     * @param int $departmentID the id against which to perform access checks
     *
     * @return bool true if the user is authorized for facility management functions and views.
     * @throws Exception
     */
    public static function allowSchedulingAccess($scheduleID = 0, $departmentID = 0)
    {
        if (self::isAdmin()) {
            return true;
        }

        $user = JFactory::getUser();
        if (empty($scheduleID)) {
            if (empty($departmentID)) {
                return count(self::getAccessibleDepartments('schedule')) > 0;
            }

            $assetIndex = "com_thm_organizer.department.$departmentID";

            return $user->authorise('organizer.schedule', $assetIndex);
        }

        return $user->authorise('organizer.schedule', "com_thm_organizer.schedule.$scheduleID");
    }

    /**
     * Checks for resources which have not yet been saved as an asset allowing transitional edit access
     *
     * @param string $resourceName the name of the resource type
     * @param int    $itemID       the id of the item being checked
     *
     * @return bool  true if the resource has an associated asset, otherwise false
     * @throws Exception
     */
    public static function checkAssetInitialization($resourceName, $itemID)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('asset_id')->from("#__thm_organizer_{$resourceName}s")->where("id = '$itemID'");
        $dbo->setQuery($query);

        try {
            $assetID = $dbo->loadResult();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return false;
        }

        return empty($assetID) ? false : true;
    }

    /**
     * Gets the ids of for which the user is authorized access
     *
     * @param string $action the action for authorization
     *
     * @return array  the department ids, empty if user has no access
     * @throws Exception
     */
    public static function getAccessibleDepartments($action = null)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')->from('#__thm_organizer_departments');
        $dbo->setQuery($query);

        try {
            $departmentIDs = $dbo->loadColumn();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return [];
        }

        // Don't bother checking departments if the user is an administrator
        if (self::isAdmin()) {
            return $departmentIDs;
        }

        if (!in_array($action, ['manage', 'schedule'])) {
            return [];
        }

        $allowedDepartmentIDs = [];

        foreach ($departmentIDs as $departmentID) {
            $allowed = $action == 'manage' ?
                self::allowDocumentAccess('department', $departmentID) :
                self::allowSchedulingAccess(null, $departmentID);

            if ($allowed) {
                $allowedDepartmentIDs[] = $departmentID;
            }
        }

        return $allowedDepartmentIDs;
    }

    /**
     * Checks whether the user is an authorized administrator
     *
     * @return bool true if the user is an administrator, otherwise false
     */
    public static function isAdmin()
    {
        $user = JFactory::getUser();

        return ($user->authorise('core.admin') or $user->authorise('core.admin', 'com_thm_organizer'));
    }
}
