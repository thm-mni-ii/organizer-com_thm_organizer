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

namespace Organizer\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Class provides generalized functions useful for several component files.
 */
class Access
{
    /**
     * Checks whether the user has access to documentation resources and their respective views.
     *
     * @param string $resource   the resource type being checked
     * @param int    $resourceID the resource id being checked
     * @param int    $userID     the user id if empty the current user is used
     *
     * @return bool true if the user is authorized for facility management functions and views.
     */
    public static function allowDocumentAccess($resource = '', $resourceID = 0, $userID = null)
    {
        if (self::isAdmin($userID)) {
            return true;
        }

        $user = Factory::getUser($userID);
        if (empty($resource) or empty($resourceID)) {
            $allowedDepartments = self::getAccessibleDepartments('document', $userID);
            $canManage          = false;
            foreach ($allowedDepartments as $departmentID) {
                $departmentManager = $user->authorise('organizer.document',
                    "com_thm_organizer.department.$departmentID");
                $canManage         = ($canManage or $departmentManager);
            }

            return $canManage;
        }

        return $user->authorise('organizer.document', "com_thm_organizer.$resource.$resourceID");
    }

    /**
     * Checks whether the user has access to course management functions
     *
     * @param int $courseID id of the course resource
     *
     * @return bool true if the user is authorized to manage courses, otherwise false
     */
    public static function allowCourseAccess($courseID = 0)
    {
        $user = Factory::getUser();

        if (empty($user->id)) {
            return false;
        }

        if (Access::isAdmin()) {
            return true;
        }

        return Courses::teaches($courseID);
        // TODO: add subsequent checks for granular access
    }

    /**
     * Checks whether the user has access to facility management resources and their respective views.
     *
     * @return bool true if the user is authorized for facility management functions and views.
     */
    public static function allowFMAccess()
    {
        return (self::isAdmin() or Factory::getUser()->authorise('organizer.fm', 'com_thm_organizer'));
    }

    /**
     * Checks whether the user has access to human resources as such and their respective views.
     *
     * @return bool true if the user is authorized for facility management functions and views.
     */
    public static function allowHRAccess()
    {
        return (self::isAdmin() or Factory::getUser()->authorise('organizer.hr', 'com_thm_organizer'));
    }

    /**
     * Checks whether the user has access to advanced front end management views
     *
     * @param int $departmentID the id against which to perform access checks
     * @param int $userID       the user id
     *
     * @return bool true if the user is authorized for advanced front end management views.
     */
    public static function allowManagementAccess($departmentID = 0, $userID = null)
    {
        if (self::isAdmin($userID)) {
            return true;
        }

        if (empty($departmentID)) {
            return false;
        }

        if (empty($departmentID)) {
            return count(self::getAccessibleDepartments('manage', $userID)) > 0;
        }

        $assetIndex = "com_thm_organizer.department.$departmentID";

        return Factory::getUser($userID)->authorise('organizer.manage', $assetIndex);
    }

    /**
     * Checks whether the user has access to scheduling resources and their respective views.
     *
     * @param int $scheduleID   the id of the schedule for whom access rights are being checked
     * @param int $departmentID the id against which to perform access checks
     * @param int $userID       the user id
     *
     * @return bool true if the user is authorized for scheduling functions and views.
     */
    public static function allowSchedulingAccess($scheduleID = 0, $departmentID = 0, $userID = null)
    {
        if (self::isAdmin($userID)) {
            return true;
        }

        $user = Factory::getUser();
        if (empty($scheduleID)) {
            if (empty($departmentID)) {
                return count(self::getAccessibleDepartments('schedule', $userID)) > 0;
            }

            $assetIndex = "com_thm_organizer.department.$departmentID";

            return $user->authorise('organizer.schedule', $assetIndex);
        }

        return $user->authorise('organizer.schedule', "com_thm_organizer.schedule.$scheduleID");
    }

    /**
     * Checks whether the user has authorization to edit a specific subject's documentation.
     *
     * @param $subjectID
     *
     * @return bool
     */
    public static function allowSubjectAccess($subjectID)
    {

        $user = Factory::getUser();

        if (empty($user->id)) {
            return false;
        }

        if (Access::isAdmin()) {
            return true;
        }

        if (empty($subjectID) or !Access::checkAssetInitialization('subject', $subjectID)) {
            return Access::allowDocumentAccess();
        }

        if (Access::allowDocumentAccess('subject', $subjectID)) {
            return true;
        }

        return Subjects::coordinates($subjectID);
    }

    /**
     * Checks whether the user has privileged access to front end views
     *
     * @param int $departmentID the id against which to perform access checks
     * @param int $userID       the user id
     *
     * @return bool true if the user is authorized for advanced front end management views.
     */
    public static function allowViewAccess($departmentID = 0, $userID = null)
    {
        if (self::isAdmin($userID)) {
            return true;
        }

        if (self::allowManagementAccess($departmentID, $userID)) {
            return true;
        }

        if (empty($departmentID)) {
            return count(self::getAccessibleDepartments('view', $userID)) > 0;
        }

        $assetIndex = "com_thm_organizer.department.$departmentID";

        return Factory::getUser()->authorise('organizer.view', $assetIndex);
    }

    /**
     * Checks for resources which have not yet been saved as an asset allowing transitional edit access
     *
     * @param string $resourceName the name of the resource type
     * @param int    $itemID       the id of the item being checked
     *
     * @return bool  true if the resource has an associated asset, otherwise false
     */
    public static function checkAssetInitialization($resourceName, $itemID)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('asset_id')->from("#__thm_organizer_{$resourceName}s")->where("id = '$itemID'");
        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Gets the ids of for which the user is authorized access
     *
     * @param string $action the action for authorization
     * @param int    $userID the user id
     *
     * @return array  the department ids, empty if user has no access
     */
    public static function getAccessibleDepartments($action = null, $userID = null)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')->from('#__thm_organizer_departments');
        $dbo->setQuery($query);
        $departmentIDs = OrganizerHelper::executeQuery('loadColumn', []);

        // Don't bother checking departments if the user is an administrator
        if (self::isAdmin($userID)) {
            return $departmentIDs;
        }

        if (!in_array($action, ['document', 'manage', 'schedule', 'view'])) {
            return [];
        }

        $allowed              = false;
        $allowedDepartmentIDs = [];

        foreach ($departmentIDs as $departmentID) {
            switch ($action) {
                case 'document':
                    $allowed = self::allowDocumentAccess('department', $departmentID, $userID);
                    break;
                case 'manage':
                    $allowed = self::allowManagementAccess($departmentID, $userID);
                    break;
                case 'schedule':
                    $allowed = self::allowSchedulingAccess(null, $departmentID, $userID);
                    break;
                case 'view':
                    $allowed = self::allowViewAccess($departmentID, $userID);
                    break;
            }

            if ($allowed) {
                $allowedDepartmentIDs[] = $departmentID;
            }
            $allowed = false;
        }

        return $allowedDepartmentIDs;
    }

    /**
     * Checks whether the user is an authorized administrator
     *
     * @param int $userID the id of the user
     *
     * @return bool true if the user is an administrator, otherwise false
     */
    public static function isAdmin($userID = null)
    {
        $user = Factory::getUser($userID);

        return ($user->authorise('core.admin') or $user->authorise('core.admin', 'com_thm_organizer'));
    }
}
