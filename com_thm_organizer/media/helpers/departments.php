<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperDepartments
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'language.php';

/**
 * Provides validation methods for department resources
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperDepartments
{
	/**
	 * Retrieves the department name from the database
	 *
	 * @param int $departmentID the
	 *
	 * @return string  the name of the department in the active language
	 */
	public static function getName($departmentID)
	{
		$languageTag = THM_OrganizerHelperLanguage::getShortTag();
		$dbo         = JFactory::getDbo();

		$query = $dbo->getQuery(true);
		$query->select("name_$languageTag as name")->from('#__thm_organizer_departments')
			->where("id = '$departmentID'");

		$dbo->setQuery($query);

		try
		{
			$name = $dbo->loadResult();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '';
		}

		return empty($name) ? '' : $name;
	}

	/**
	 * Checks whether the plan resource is already associated with a department, creating an entry if none already exists.
	 *
	 * @param int    $planResourceID the db id for the plan resource
	 * @param string $column         the column in which the resource information is stored
	 *
	 * @throws Exception
	 */
	public static function setDepartmentResource($planResourceID, $column, $departmentID = null)
	{
		if (empty($departmentID))
		{
			$formData             = JFactory::getApplication()->input->get('jform', [], 'array');
			$data['departmentID'] = $formData['departmentID'];
		}
		else
		{
			$data['departmentID'] = $departmentID;
		}

		$data[$column] = $planResourceID;

		$deptResourceTable = JTable::getInstance('department_resources', 'thm_organizerTable');
		$exists            = $deptResourceTable->load($data);
		if ($exists)
		{
			return;
		}

		try
		{
			$deptResourceTable->save($data);
		}
		catch (Exception $exc)
		{
			die;
		}

		return;
	}

	/**
	 * Getter method for teachers in database. Only retrieving the IDs here allows for formatting the names according to
	 * the needs of the calling views.
	 *
	 * @param bool $short whether or not abbreviated names should be returned
	 *
	 * @return array
	 *
	 * @throws RuntimeException
	 */
	public static function getPlanDepartments($short = true)
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$tag   = THM_OrganizerHelperLanguage::getShortTag();

		$query->select("DISTINCT d.id, d.short_name_$tag AS shortName, d.name_$tag AS name");
		$query->from('#__thm_organizer_departments AS d');
		$query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.departmentID = d.id');

		$dbo->setQuery($query);

		$default = [];
		try
		{
			$results = $dbo->loadAssocList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return $default;
		}

		if (empty($results))
		{
			return $default;
		}

		$departments = [];
		foreach ($results as $department)
		{
			$departments[$department['id']] = $short ? $department['shortName'] : $department['name'];
		}

		asort($departments);

		return $departments;
	}
}
