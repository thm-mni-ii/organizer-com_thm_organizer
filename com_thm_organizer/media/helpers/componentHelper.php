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
	 * Configure the Linkbar.
	 *
	 * @param   object &$view the view context calling the function
	 *
	 * @return void
	 */
	public static function addSubmenu(&$view)
	{
		$viewName = $view->get('name');

		// No submenu creation while editing a resource
		if (strpos($viewName, 'edit'))
		{
			return;
		}

		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_MAIN_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=thm_organizer',
			$viewName == 'thm_organizer'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_DEGREE_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=degree_manager',
			$viewName == 'degree_manager'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_MONITOR_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=monitor_manager',
			$viewName == 'monitor_manager'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_TEACHER_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=teacher_manager',
			$viewName == 'teacher_manager'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=department_manager',
			$viewName == 'department_manager'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_SUBJECT_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=subject_manager',
			$viewName == 'subject_manager'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_FIELD_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=field_manager',
			$viewName == 'field_manager'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_COLOR_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=color_manager',
			$viewName == 'color_manager'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_METHOD_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=method_manager',
			$viewName == 'method_manager'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_POOL_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=pool_manager',
			$viewName == 'pool_manager'
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
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=program_manager',
			$viewName == 'program_manager'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_SCHEDULE_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=schedule_manager',
			$viewName == 'schedule_manager'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_THM_ORGANIZER_GRID_MANAGER_TITLE'),
			'index.php?option=com_thm_organizer&amp;view=grid_manager',
			$viewName == 'grid_manager'
		);

		$view->sidebar = JHtmlSidebar::render();
	}

	/**
	 * Set variables for user actions.
	 *
	 * @param   object &$object the object calling the function (manager model or edit view)
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

		$object->actions = $result;
	}

	/**
	 * Checks access for edit views
	 *
	 * @param   object &$model the model checking permissions
	 * @param   int    $itemID the id if the resource to be edited (empty for new entries)
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

		// Views accessible with component create/edit access
		$resourceEditViews = array('color_edit', 'degree_edit', 'field_edit', 'room_edit', 'teacher_edit');
		if (in_array($name, $resourceEditViews))
		{
			if ((int) $itemID > 0)
			{
				return $model->actions->{'core.edit'};
			}

			return $model->actions->{'core.create'};
		}

		// Merge views always deal with existing resources and implicitly delete one or more entries in doing so
		$resourceMergeViews = array('room_merge', 'teacher_merge');
		if (in_array($name, $resourceMergeViews))
		{
			return ($model->actions->{'core.edit'} AND $model->actions->{'core.delete'});
		}

		$departmentEditViews = array('pool_edit', 'program_edit', 'schedule_edit', 'subject_edit');
		if (in_array($name, $departmentEditViews))
		{
			if (!empty($itemID))
			{
				$resourceName = str_replace('_edit', '', $name);
				$initialized  = self::checkAssetInitialization($resourceName, $itemID);
				if (!$initialized)
				{
					return self::allowDeptResourceCreate();
				}

				return self::allowResourceManage($resourceName, $itemID);
			}

			return self::allowDeptResourceCreate();
		}

		return false;
	}

	/**
	 * Checks for resources which have not yet been saved as an asset allowing transitional edit access
	 *
	 * @param   string $resourceName the name of the resource type
	 * @param   int    $itemID       the id of the item being checked
	 *
	 * @return  bool  true if the resource has an associated asset, otherwise false
	 */
	public static function checkAssetInitialization($resourceName, $itemID)
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('asset_id')->from("#__thm_organizer_{$resourceName}s")->where("id = '$itemID'");
		$dbo->setQuery((string) $query);

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
	 * Checks whether the user has access to a department
	 *
	 * @param   string $resourceName the name of the resource type
	 * @param   int    $itemID       the id of the resource
	 *
	 * @return  bool  true if the user has access to at least one department, otherwise false
	 */
	public static function allowResourceManage($resourceName, $itemID)
	{
		$user = JFactory::getUser();

		// Core admin sets this implicitly
		$isAdmin = $user->authorise('core.admin', "com_thm_organizer");
		$canEdit = $user->authorise('core.manage', "com_thm_organizer.$resourceName.$itemID");
		if ($isAdmin OR $canEdit)
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks whether the user has access to a department
	 *
	 * @return  bool  true if the user has access to at least one department, otherwise false
	 */
	public static function allowDeptResourceCreate()
	{
		$allowedDepartments = self::getAccessibleDepartments();

		return count($allowedDepartments) ? true : false;
	}

	/**
	 * Gets the ids of for which the user is authorized access
	 *
	 * @return  array  the department ids, empty if user has no access
	 */
	public static function getAccessibleDepartments()
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('id')->from('#__thm_organizer_departments');
		$dbo->setQuery((string) $query);

		try
		{
			$departmentIDs = $dbo->loadColumn();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return array();
		}

		// Don't bother checking departments if the user is an administrator
		$user = JFactory::getUser();
		if ($user->authorise('core.admin'))
		{
			return $departmentIDs;
		}

		$allowedDepartmentIDs = array();
		foreach ($departmentIDs as $departmentID)
		{
			$isComponentAdmin    = $user->authorise('core.admin', "com_thm_organizer.department.$departmentID");
			$canManageDepartment = $user->authorise('organizer.manage', "com_thm_organizer.department.$departmentID");
			if ($isComponentAdmin OR $canManageDepartment)
			{
				$allowedDepartmentIDs[] = $departmentID;
			}
		}

		return $allowedDepartmentIDs;
	}

	/**
	 * Gets a div with a given background color and text with a dynamically calculated text color
	 *
	 * @param   string $text    the text to be displayed
	 * @param   string $bgColor hexadecimal color code
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
	 * @param   string $bgColor the background color associated with the field
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
	 * Formats the date stored in the database according to the format in the component parameters
	 *
	 * @param   string $date the date to be formatted
	 *
	 * @return  string|bool  a formatted date string otherwise false
	 */
	public static function formatDate($date)
	{
		$params     = JComponentHelper::getParams('com_thm_organizer');
		$dateFormat = $params->get('dateFormat', 'd.m.Y');

		return date($dateFormat, strtotime($date));
	}

	/**
	 * Formats the date stored in the database according to the format in the component parameters
	 *
	 * @param   string $date the date to be formatted
	 *
	 * @return  string|bool  a formatted date string otherwise false
	 */
	public static function formatDateShort($date,$withText = false)
	{
		$params     = JComponentHelper::getParams('com_thm_organizer');
		$dateFormat = $params->get('dateFormatShort', 'd.m');
		$formattedDate = date($dateFormat, strtotime($date));

		if ($withText)
		{
			$shortDOW = date('D', strtotime($date));
			$text = JText::_(strtoupper($shortDOW));
			$formattedDate = "$text $formattedDate";
		}

		return $formattedDate;
	}

	/**
	 * Formats the date stored in the database according to the format in the component parameters
	 *
	 * @param   string $time the date to be formatted
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
	 * Converts a date string from the format in the component settings into the format used by the database
	 *
	 * @param   string $date the date string
	 *
	 * @return  string  date sting in format Y-m-d
	 */
	public static function standardizeDate($date)
	{
		if (empty($date))
		{
			return '';
		}

		$params     = JComponentHelper::getParams('com_thm_organizer');
		$dateFormat = $params->get('dateFormat', 'd.m.Y');

		return date_format(date_create_from_format($dateFormat, $date), 'Y-m-d');
	}

	/**
	 * Creates a select box
	 *
	 * @param   mixed  $entries        a set of keys and values
	 * @param   string $name           the name of the element
	 * @param   mixed  $attributes     optional attributes: object, array, or string in the form key => value(,)+
	 * @param   mixed  $selected       optional selected items
	 * @param   array  $defaultOptions default options key => value
	 *
	 * @return  string  the html output for the select box
	 */
	public static function selectBox($entries, $name, $attributes = null, $selected = null, $defaultOptions = null)
	{
		$options = array();

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
			$attributes = array();
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
				$attributes    = array();
				array_walk($singleAttribs, 'walk', $attributes);

				function walk($val, $key, &$attributes)
				{
					list($attibKey, $attribValue) = explode(' => ', $val);
					$attributes[$attibKey] = $attribValue;
				}
			}
			else
			{
				$attributes = array();
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
}
