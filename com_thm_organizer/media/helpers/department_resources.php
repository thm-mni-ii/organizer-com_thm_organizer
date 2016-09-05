<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperDepartment_Resources
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Provides validation methods for department resources
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperDepartment_Resources
{
	/**
	 * Checks whether the plan resource is already associated with a department, creating an entry if none already exists.
	 *
	 * @param   int    $planResourceID the db id for the plan resource
	 * @param   string $column         the column in which the resource information is stored
	 *
	 * @throws Exception
	 */
	public static function setDepartmentResource($planResourceID, $column, $departmentID = null)
	{
		if (empty($departmentID))
		{
			$formData             = JFactory::getApplication()->input->get('jform', array(), 'array');
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
}
