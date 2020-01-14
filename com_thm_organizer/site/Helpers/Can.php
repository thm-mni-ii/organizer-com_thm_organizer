<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;

/**
 * Class provides generalized functions useful for several component files.
 */
class Can
{
	/**
	 * Checks whether the user is an authorized administrator.
	 *
	 * @return bool true if the user is an administrator, otherwise false
	 */
	public static function administrate()
	{
		$user = Users::getUser();
		if (!$user->id)
		{
			return false;
		}


		return ($user->authorise('core.admin') or $user->authorise('core.admin', 'com_thm_organizer'));
	}

	/**
	 * Performs ubiquitous authorization checks.
	 *
	 * @return bool|null true if the user has administrative authorization, false if the user is a guest, otherwise null
	 */
	private static function basic()
	{
		if (!Users::getID())
		{
			return false;
		}

		if (self::administrate())
		{
			return true;
		}

		return null;
	}

	/**
	 * Checks for resources which have not yet been saved as an asset allowing transitional edit access
	 *
	 * @param   string  $resourceName  the name of the resource type
	 * @param   int     $itemID        the id of the item being checked
	 *
	 * @return bool  true if the resource has an associated asset, otherwise false
	 */
	private static function isInitialized($resourceName, $itemID)
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('asset_id')->from("#__thm_organizer_{$resourceName}s")->where("id = '$itemID'");
		$dbo->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('loadResult');
	}

	/**
	 * Checks whether the user has access to documentation resources and their respective views.
	 *
	 * @param   string     $resourceType  the resource type being checked
	 * @param   array|int  $resource      the resource id being checked or an array if resource ids to check
	 *
	 * @return bool true if the user is authorized for facility management functions and views.
	 */
	public static function document($resourceType, $resource = null)
	{
		if (is_bool($authorized = self::basic()))
		{
			return $authorized;
		}

		$user = Users::getUser();

		if ($resourceType and is_int($resource) and self::isInitialized($resourceType, $resource))
		{
			if ($user->authorise('organizer.document', "com_thm_organizer.$resourceType.$resource"))
			{
				return true;
			}

			if ($resourceType === 'subject' and Subjects::coordinates($resource))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets the ids of departments for which the user is authorized documentation access
	 *
	 * @return array  the department ids, empty if user has no access
	 */
	public static function documentTheseDepartments()
	{
		return self::getAuthorizedDepartments('document');
	}

	/**
	 * Checks whether the user has access to the participant information
	 *
	 * @param   string     $resourceType  the resource type being checked
	 * @param   array|int  $resource      the resource id being checked or an array if resource ids to check
	 *
	 * @return bool true if the user is authorized to manage courses, otherwise false
	 */
	public static function edit($resourceType, $resource = null)
	{
		if (is_bool($authorized = self::basic()))
		{
			return $authorized;
		}

		$user = Users::getUser();

		switch ($resourceType)
		{
			case 'categories':
			case 'category':

				return self::editScheduleResource('Categories', $resource);

			case 'event':
			case 'events':

				return self::editScheduleResource('Events', $resource);

			case 'group':
			case 'groups':

				return self::editScheduleResource('Groups', $resource);

			case 'participant':

				if (!is_int($resource))
				{
					return false;
				}

				if ($user->id === $resource)
				{
					return true;
				}

				return self::manage($resourceType, $resource);

			case 'person':
			case 'persons':

				if (self::manage('persons'))
				{
					return true;
				}

				return self::editScheduleResource('Persons', $resource);
		}

		return false;
	}

	/**
	 * Returns whether the user is authorized to edit the schedule resource.
	 *
	 * @param   string     $helperClass  the name of the helper class
	 * @param   array|int  $resource     the resource id being checked or an array if resource ids to check
	 *
	 * @return bool true if the user is authorized to manage courses, otherwise false
	 */
	private static function editScheduleResource($helperClass, $resource)
	{
		if (empty($resource))
		{
			return false;
		}

		$authorizedDepartments = Can::scheduleTheseDepartments();
		$helper                = "Organizer\\Helpers\\$helperClass";

		if (is_int($resource))
		{
			$resourceDepartments = $helper::getDepartmentIDs($resource);

			return (bool) array_intersect($resourceDepartments, $authorizedDepartments);
		}
		elseif (is_array($resource))
		{
			foreach ($resource as $resourceID)
			{
				$resourceDepartments = $helper::getDepartmentIDs($resourceID);
				if (!array_intersect($resourceDepartments, $authorizedDepartments))
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Gets the department ids of for which the user is authorized access
	 *
	 * @param   string  $function  the action for authorization
	 *
	 * @return array  the department ids, empty if user has no access
	 */
	private static function getAuthorizedDepartments($function)
	{
		if (!Users::getID())
		{
			return [];
		}

		$departmentIDs = Departments::getIDs();

		if (self::administrate())
		{
			return $departmentIDs;
		}

		if (!function_exists($function))
		{
			return [];
		}

		$allowedDepartmentIDs = [];

		foreach ($departmentIDs as $departmentID)
		{
			if (self::$function('department', $departmentID))
			{
				$allowedDepartmentIDs[] = $departmentID;
			}
		}

		return $allowedDepartmentIDs;
	}

	/**
	 * Checks whether the user can manage the given resource.
	 *
	 * @param   string     $resourceType  the resource type being checked
	 * @param   array|int  $resource      the resource id being checked or an array if resource ids to check
	 *
	 * @return bool true if the user is authorized for scheduling functions and views.
	 */
	public static function manage($resourceType, $resource = null)
	{
		if (is_bool($authorized = self::basic()))
		{
			return $authorized;
		}

		$user = Users::getUser();

		if ($resourceType === 'courses' or $resourceType === 'course')
		{
			return (Courses::coordinates($resource) or Courses::hasResponsibility($resource));
		}

		if ($resourceType === 'department' and is_int($resource))
		{
			return $user->authorise('organizer.manage', "com_thm_organizer.department.$resource");
		}

		if ($resourceType === 'facilities')
		{
			return $user->authorise('organizer.fm', 'com_thm_organizer');
		}

		if ($resourceType === 'participant' and is_int($resource))
		{
			$participantCourses = Participants::getCourses($resource);

			foreach ($participantCourses as $courseID)
			{
				if (Courses::coordinates($courseID))
				{
					return true;
				}
			}

			return false;
		}

		if ($resourceType === 'persons')
		{
			return $user->authorise('organizer.hr', 'com_thm_organizer');
		}

		return false;
	}

	/**
	 * Gets the ids of departments for which the user is authorized managing access
	 *
	 * @return array  the department ids, empty if user has no access
	 */
	public static function manageTheseDepartments()
	{
		return self::getAuthorizedDepartments('manage');
	}

	/**
	 * Checks whether the user has access to scheduling resources and their respective views.
	 *
	 * @param   string     $resourceType  the resource type being checked
	 * @param   array|int  $resource      the resource id being checked or an array if resource ids to check
	 *
	 * @return bool true if the user is authorized for scheduling functions and views.
	 */
	public static function schedule($resourceType, $resource = null)
	{
		if (is_bool($authorized = self::basic()))
		{
			return $authorized;
		}

		if (!$resource)
		{
			return false;
		}

		$user = Users::getUser();

		if ($resourceType === 'schedule')
		{
			return $user->authorise('organizer.schedule', "com_thm_organizer.schedule.$resource");
		}

		if ($resourceType === 'department')
		{
			return $user->authorise('organizer.schedule', "com_thm_organizer.department.$resource");
		}

		return false;
	}

	/**
	 * Gets the ids of departments for which the user is authorized scheduling access
	 *
	 * @return array  the department ids, empty if user has no access
	 */
	public static function scheduleTheseDepartments()
	{
		return self::getAuthorizedDepartments('schedule');
	}

	/**
	 * Checks whether the user has privileged access to resource associated views.
	 *
	 * @param   string     $resourceType  the resource type being checked
	 * @param   array|int  $resource      the resource id being checked or an array if resource ids to check
	 *
	 * @return bool true if the user is authorized for scheduling functions and views.
	 */
	public static function view($resourceType, $resource = null)
	{
		if (is_bool($authorized = self::basic()))
		{
			return $authorized;
		}

		$user = Users::getUser();

		if ($resourceType === 'department' and is_int($resource))
		{
			if ($user->authorise('organizer.view', "com_thm_organizer.department.$resource"))
			{
				return true;
			}

			return self::manage($resourceType, $resource);
		}

		return false;
	}

	/**
	 * Gets the ids of departments for which the user is authorized privileged view access
	 *
	 * @return array  the department ids, empty if user has no access
	 */
	public static function viewTheseDepartments()
	{
		return self::getAuthorizedDepartments('view');
	}
}
