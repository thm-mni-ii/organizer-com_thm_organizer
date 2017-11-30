<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperComponent
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class providing functions useful to multiple component files
 *
 * @category  Joomla.Component.Media
 * @package   thm_organizer
 */
class THM_OrganizerHelperComponent
{
	/**
	 * Set variables for user actions.
	 *
	 * @param object &$object the object calling the function (manager model or edit view)
	 *
	 * @return void
	 */
	public static function addActions(&$object)
	{
		$user   = JFactory::getUser();
		$result = new JObject;

		$path    = JPATH_ADMINISTRATOR . '/components/com_thm_organizer/access.xml';
		$actions = JAccess::getActionsFromFile($path, "/access/section[@name='component']/");
		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, 'com_thm_organizer'));
		}

		$allowedDepartments = self::getAccessibleDepartments();

		if (empty($allowedDepartments))
		{
			$result->set('organizer.menu.department', false);
			$result->set('organizer.menu.manage', false);
			$result->set('organizer.menu.schedule', false);
		}
		else
		{
			$department = false;
			$manage     = false;
			$schedules  = false;

			if ($user->authorise('core.admin'))
			{
				$department = true;
				$manage     = true;
				$schedules  = true;
			}
			else
			{
				foreach ($allowedDepartments as $departmentID)
				{
					// The or allows for any odd cases of cross department responsibilities
					$department = ($department OR $user->authorise('organizer.department', "com_thm_organizer.department.$departmentID"));
					$manage     = ($manage OR $user->authorise('organizer.manage', "com_thm_organizer.department.$departmentID"));
					$schedules  = ($schedules OR $user->authorise('organizer.schedule', "com_thm_organizer.department.$departmentID"));

				}
			}

			$result->set('organizer.menu.department', $department);
			$result->set('organizer.menu.manage', $manage);
			$result->set('organizer.menu.schedule', $schedules);
		}

		$object->actions = $result;
	}

	/**
	 * Adds menu parameters to the object (id and route)
	 *
	 * @param object $object the object to add the parameters to, typically a view
	 *
	 * @return void modifies $object
	 */
	public static function addMenuParameters(&$object)
	{
		$app = JFactory::getApplication();
		$menuID = $app->input->getInt('Itemid');

		if (!empty($menuID))
		{
			$menuItem = $app->getMenu()->getItem($menuID);
			$menu = ['id' => $menuID, 'route' => JUri::base() . $menuItem->route];

			$query = explode('?', $menuItem->link)[1];
			parse_str($query, $parameters);

			if (empty($parameters['option']) OR $parameters['option'] != 'com_thm_organizer')
			{
				$menu['view'] = '';
			}
			elseif (!empty($parameters['view']))
			{
				$menu['view'] = $parameters['view'];
			}

			$object->menu = $menu;
		}
	}

	/**
	 * Configure the submenu.
	 *
	 * @param object &$view the view context calling the function
	 *
	 * @return void
	 */
	public static function addSubmenu(&$view)
	{
		$viewName = $view->get('name');
		$actions  = $view->getModel()->actions;

		// No submenu creation while editing a resource
		if (strpos($viewName, 'edit'))
		{
			return;
		}

		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER'),
			'index.php?option=com_thm_organizer&amp;view=thm_organizer',
			$viewName == 'thm_organizer'
		);

		if ($actions->{'organizer.menu.schedule'})
		{
			$spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_SCHEDULING') . '</span>';
			JHtmlSidebar::addEntry($spanText, '', false);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_SCHEDULE_UPLOAD'),
				'index.php?option=com_thm_organizer&amp;view=schedule_edit',
				$viewName == 'schedule_edit'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_SCHEDULE_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=schedule_manager',
				$viewName == 'schedule_manager'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_POOL_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=plan_pool_manager',
				$viewName == 'plan_pool_manager'
			);
			if ($actions->{'core.admin'})
			{
				JHtmlSidebar::addEntry(
					JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER_TITLE'),
					'index.php?option=com_thm_organizer&amp;view=plan_program_manager',
					$viewName == 'plan_program_manager'
				);
			}
		}

		if ($actions->{'organizer.menu.department'} OR $actions->{'organizer.menu.manage'})
		{
			$spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_MANAGEMENT_AND_DOCUMENTATION') . '</span>';
			JHtmlSidebar::addEntry($spanText, '', false);

			if ($actions->{'organizer.menu.department'})
			{
				JHtmlSidebar::addEntry(
					JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_TITLE'),
					'index.php?option=com_thm_organizer&amp;view=department_manager',
					$viewName == 'department_manager'
				);
			}
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_POOL_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=pool_manager',
				$viewName == 'pool_manager'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=program_manager',
				$viewName == 'program_manager'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_SUBJECT_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=subject_manager',
				$viewName == 'subject_manager'
			);
		}

		if ($actions->{'organizer.hr'})
		{
			$spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_HUMAN_RESOURCES') . '</span>';
			JHtmlSidebar::addEntry($spanText, '', false);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_TEACHER_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=teacher_manager',
				$viewName == 'teacher_manager'
			);
		}

		if ($actions->{'organizer.fm'})
		{
			$spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_FACILITY_MANAGEMENT') . '</span>';
			JHtmlSidebar::addEntry($spanText, '', false);
			/*JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_CAMPUS_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=campus_manager',
				$viewName == 'campus_manager'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_BUILDING_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=building_manager',
				$viewName == 'building_manager'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_EQUIPMENT_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=equipment_manager',
				$viewName == 'equipment_manager'
			);*/
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_MONITOR_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=monitor_manager',
				$viewName == 'monitor_manager'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_ROOM_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=room_manager',
				$viewName == 'room_manager'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_ROOM_TYPE_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=room_type_manager',
				$viewName == 'room_type_manager'
			);
		}

		if ($actions->{'core.admin'})
		{
			$spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_ADMINISTRATION') . '</span>';
			JHtmlSidebar::addEntry($spanText, '', false);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_COLOR_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=color_manager',
				$viewName == 'color_manager'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_DEGREE_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=degree_manager',
				$viewName == 'degree_manager'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_FIELD_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=field_manager',
				$viewName == 'field_manager'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_GRID_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=grid_manager',
				$viewName == 'grid_manager'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_THM_ORGANIZER_METHOD_MANAGER_TITLE'),
				'index.php?option=com_thm_organizer&amp;view=method_manager',
				$viewName == 'method_manager'
			);
		}

		$view->sidebar = JHtmlSidebar::render();
	}

	/**
	 * Checks whether the user has access to a department
	 *
	 * @param string $resource the resource type
	 *
	 * @return  bool  true if the user has access to at least one department, otherwise false
	 */
	public static function allowDeptResourceCreate($resource)
	{
		$area               = $resource == 'department' ? 'department' : $resource == 'schedule' ? 'schedule' : 'manage';
		$allowedDepartments = self::getAccessibleDepartments($area);

		return count($allowedDepartments) ? true : false;
	}

	/**
	 * Checks access for edit views
	 *
	 * @param object &$model the model checking permissions
	 * @param int    $itemID the id if the resource to be edited (empty for new entries)
	 *
	 * @return  bool  true if the user can access the edit view, otherwise false
	 */
	public static function allowEdit(&$model, $itemID = 0)
	{
		// Admins can edit anything. Department and monitor editing is implicitly covered here.
		$isAdmin = $model->actions->{'core.admin'};
		if ($isAdmin)
		{
			return true;
		}

		$name = $model->get('name');

		$facilityManagementViews = [
			'campus_edit',
			'building_edit',
			'equipment_edit',
			'monitor_edit',
			'room_edit',
			'room_merge',
			'room_type_edit'
		];

		if (in_array($name, $facilityManagementViews))
		{
			return $model->actions->{'organizer.fm'};
		}

		// Views accessible with component create/edit access
		$humanResourceViews = ['teacher_edit', 'teacher_merge'];

		if (in_array($name, $humanResourceViews))
		{
			return $model->actions->{'organizer.hr'};
		}

		$departmentAssetViews = [
			'department_edit',
			'pool_edit',
			'program_edit',
			'schedule_edit',
			'subject_edit'
		];

		if (in_array($name, $departmentAssetViews))
		{
			$resource = str_replace('_edit', '', $name);;

			if (!empty($itemID))
			{
				$initialized = self::checkAssetInitialization($resource, $itemID);

				if (!$initialized)
				{
					return self::allowDeptResourceCreate($resource);
				}

				$action = $resource == 'department' ? 'department' : $resource == 'schedule' ? 'schedule' : 'manage';

				return self::allowResourceManage($resource, $itemID, $action);
			}

			return self::allowDeptResourceCreate($resource);
		}

		if ($name == 'plan_pool_edit')
		{
			return self::allowPlanPoolEdit($itemID);
		}

		return false;
	}

	/**
	 * Checks whether the given plan pool is associated with an allowed department
	 *
	 * @param int $ppID the id of the plan pool being checked
	 *
	 * @return  bool  true if the plan pool is associated with an allowed department, otherwise false
	 */
	public static function allowPlanPoolEdit($ppID)
	{
		$allowedDepartments = self::getAccessibleDepartments('schedule');
		$dbo                = JFactory::getDbo();
		$query              = $dbo->getQuery(true);
		$query->select('id')
			->from('#__thm_organizer_department_resources')
			->where("poolID = '$ppID'")
			->where("departmentID IN ('" . implode("', '", $allowedDepartments) . "')");
		$dbo->setQuery($query);

		try
		{
			$entryID = $dbo->loadResult();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return false;
		}

		return empty($entryID) ? false : true;

	}

	/**
	 * Checks whether the user has access to a department
	 *
	 * @param string $resource   the name of the resource type
	 * @param int    $resourceID the id of the resource
	 * @param string $action     a specific action which must be performable on the resource
	 *
	 * @return  bool  true if the user has access to at least one department, otherwise false
	 */
	public static function allowResourceManage($resource, $resourceID, $action = '')
	{
		$user = JFactory::getUser();

		// Core admin sets this implicitly
		$isAdmin = $user->authorise('core.admin', "com_thm_organizer");

		if ($isAdmin)
		{
			return true;
		}

		$canManageDepartment    = false;
		$canManageDocumentation = false;
		$canManageSchedules     = false;

		if (!empty($action))
		{
			if ($action == 'department')
			{
				$canManageDepartment = $user->authorise('organizer.department', "com_thm_organizer.$resource.$resourceID");
			}
			elseif ($action == 'manage')
			{
				$canManageDocumentation = $user->authorise('organizer.manage', "com_thm_organizer.$resource.$resourceID");
			}
			elseif ($action == 'schedule')
			{
				$canManageSchedules = $user->authorise('organizer.schedule', "com_thm_organizer.$resource.$resourceID");
			}
		}
		else
		{
			$canManageDepartment    = $user->authorise('organizer.department', "com_thm_organizer.$resource.$resourceID");
			$canManageDocumentation = $user->authorise('organizer.manage', "com_thm_organizer.$resource.$resourceID");
			$canManageSchedules     = $user->authorise('organizer.schedule', "com_thm_organizer.$resource.$resourceID");
		}

		return ($canManageDepartment OR $canManageDocumentation OR $canManageSchedules);
	}

	/**
	 * Checks for resources which have not yet been saved as an asset allowing transitional edit access
	 *
	 * @param string $resourceName the name of the resource type
	 * @param int    $itemID       the id of the item being checked
	 *
	 * @return  bool  true if the resource has an associated asset, otherwise false
	 */
	public static function checkAssetInitialization($resourceName, $itemID)
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('asset_id')->from("#__thm_organizer_{$resourceName}s")->where("id = '$itemID'");
		$dbo->setQuery($query);

		try
		{
			$assetID = $dbo->loadResult();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return false;
		}

		return empty($assetID) ? false : true;
	}

	/**
	 * Formats the date stored in the database according to the format in the component parameters
	 *
	 * @param string $date     the date to be formatted
	 * @param bool   $withText if the day name should be part of the output
	 *
	 * @return  string|bool  a formatted date string otherwise false
	 */
	public static function formatDate($date, $withText = false)
	{
		$params        = JComponentHelper::getParams('com_thm_organizer');
		$dateFormat    = $params->get('dateFormat', 'd.m.Y');
		$formattedDate = date($dateFormat, strtotime($date));

		if ($withText)
		{
			$shortDOW      = date('l', strtotime($date));
			$text          = JText::_(strtoupper($shortDOW));
			$formattedDate = "$text $formattedDate";
		}

		return $formattedDate;
	}

	/**
	 * Formats the date stored in the database according to the format in the component parameters
	 *
	 * @param string $date     the date to be formatted
	 * @param bool   $withText if the day name should be part of the output
	 *
	 * @return  string|bool  a formatted date string otherwise false
	 */
	public static function formatDateShort($date, $withText = false)
	{
		$params        = JComponentHelper::getParams('com_thm_organizer');
		$dateFormat    = $params->get('dateFormatShort', 'd.m');
		$formattedDate = date($dateFormat, strtotime($date));

		if ($withText)
		{
			$shortDOW      = date('D', strtotime($date));
			$text          = JText::_(strtoupper($shortDOW));
			$formattedDate = "$text $formattedDate";
		}

		return $formattedDate;
	}

	/**
	 * Formats the date stored in the database according to the format in the component parameters
	 *
	 * @param string $time the date to be formatted
	 *
	 * @return  string|bool  a formatted date string otherwise false
	 */
	public static function formatTime($time)
	{
		$params     = JComponentHelper::getParams('com_thm_organizer');
		$timeFormat = $params->get('timeFormat', 'H:i');

		return date($timeFormat, strtotime($time));
	}

	/**
	 * Gets the ids of for which the user is authorized access
	 *
	 * @param string $action the specific action for access checks
	 *
	 * @return  array  the department ids, empty if user has no access
	 */
	public static function getAccessibleDepartments($action = '')
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('id')->from('#__thm_organizer_departments');
		$dbo->setQuery($query);

		try
		{
			$departmentIDs = $dbo->loadColumn();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return [];
		}

		// Don't bother checking departments if the user is an administrator
		$user = JFactory::getUser();
		if ($user->authorise('core.admin'))
		{
			return $departmentIDs;
		}

		$allowedDepartmentIDs = [];

		foreach ($departmentIDs as $departmentID)
		{
			$allowed = self::allowResourceManage('department', $departmentID, $action);

			if ($allowed)
			{
				$allowedDepartmentIDs[] = $departmentID;
			}
		}

		return $allowedDepartmentIDs;
	}

	/**
	 * Gets a div with a given background color and text with a dynamically calculated text color
	 *
	 * @param string $text    the text to be displayed
	 * @param string $bgColor hexadecimal color code
	 *
	 * @return  string  the html output string
	 */
	public static function getColorField($text, $bgColor)
	{
		$textColor = self::getTextColor($bgColor);
		$style     = 'color: ' . $textColor . '; background-color: ' . $bgColor . '; text-align:center';

		return '<div class="color-preview" style="' . $style . '">' . $text . '</div>';
	}

	/**
	 * Gets an appropriate value for text color
	 *
	 * @param string $bgColor the background color associated with the field
	 *
	 * @return  string  the hexadecimal value for an appropriate text color
	 */
	public static function getTextColor($bgColor)
	{
		$color              = substr($bgColor, 1);
		$params             = JComponentHelper::getParams('com_thm_organizer');
		$red                = hexdec(substr($color, 0, 2));
		$green              = hexdec(substr($color, 2, 2));
		$blue               = hexdec(substr($color, 4, 2));
		$relativeBrightness = ($red * 299) + ($green * 587) + ($blue * 114);
		$brightness         = $relativeBrightness / 1000;
		if ($brightness >= 128)
		{
			return $params->get('darkTextColor', '#4a5c66');
		}
		else
		{
			return $params->get('lightTextColor', '#eeeeee');
		}
	}

	/**
	 * TODO: Including this (someday) to the Joomla Core!
	 * Checks if the device is a smartphone, based on the 'Mobile Detect' library
	 *
	 * @return boolean
	 */
	public static function isSmartphone()
	{
		$mobileCheckPath = JPATH_ROOT . '/components/com_jce/editor/libraries/classes/mobile.php';

		if (file_exists($mobileCheckPath))
		{
			if (!class_exists('Wf_Mobile_Detect'))
			{
				// Load mobile detect class
				require_once $mobileCheckPath;
			}

			$checker = new Wf_Mobile_Detect;
			$isPhone = ($checker->isMobile() AND !$checker->isTablet());

			if ($isPhone)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Creates a select box
	 *
	 * @param mixed  $entries        a set of keys and values
	 * @param string $name           the name of the element
	 * @param mixed  $attributes     optional attributes: object, array, or string in the form key => value(,)+
	 * @param mixed  $selected       optional selected items
	 * @param array  $defaultOptions default options key => value
	 *
	 * @return  string  the html output for the select box
	 */
	public static function selectBox($entries, $name, $attributes = null, $selected = null, $defaultOptions = null)
	{
		$options = [];

		$defaultValid = (!empty($defaultOptions) AND is_array($defaultOptions));
		if ($defaultValid)
		{
			foreach ($defaultOptions as $key => $value)
			{
				$options[] = JHtml::_('select.option', $key, $value);
			}
		}

		$entriesValid = (is_array($entries) OR is_object($entries));
		if ($entriesValid)
		{
			foreach ($entries as $key => $value)
			{
				$textValid = (is_string($value) OR is_numeric($value));
				if (!$textValid)
				{
					continue;
				}

				$options[] = JHtml::_('select.option', $key, $value);
			}
		}

		$attribsInvalid = (empty($attributes)
			OR (!is_object($attributes) AND !is_array($attributes) AND !is_string($attributes)));
		if ($attribsInvalid)
		{
			$attributes = [];
		}
		elseif (is_object($attributes))
		{
			$attributes = (array) $attributes;
		}
		elseif (is_string($attributes))
		{
			$validString = preg_match("/^((\'[\w]+\'|\"[\w]+\") => (\'[\w]+\'|\"[\w]+\")[,]?)+$/", $attributes);
			if ($validString)
			{
				$singleAttribs = explode(',', $attributes);
				$attributes    = [];
				array_walk($singleAttribs, 'walk', $attributes);

				function walk($attribute, $key, &$attributes)
				{
					list($property, $value) = explode(' => ', $attribute);
					$attributes[$property] = $value;
				}
			}
			else
			{
				$attributes = [];
			}
		}

		if (empty($attributes['class']))
		{
			$attributes['class'] = 'organizer-select-box';
		}
		elseif (strpos('organizer-select-box', $attributes['class']) === false)
		{
			$attributes['class'] .= ' organizer-select-box';
		}

		$isMultiple = (!empty($attributes['multiple']) AND $attributes['multiple'] == 'multiple');
		$multiple   = $isMultiple ? '[]' : '';

		$name = "jform[$name]$multiple";

		return JHtml::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $selected);
	}

	/**
	 * Converts a date string from the format in the component settings into the format used by the database
	 *
	 * @param string $date the date string
	 *
	 * @return  string  date sting in format Y-m-d
	 */
	public static function standardizeDate($date)
	{
		$default = date('Y-m-d');

		if (empty($date))
		{
			return $default;
		}

		// Already standardized
		if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $date) === 1)
		{
			return $date;
		}

		$dateFormat    = JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y');
		$supportedDate = date_create_from_format($dateFormat, $date);

		return empty($supportedDate) ? $default : date_format($supportedDate, 'Y-m-d');
	}
}
