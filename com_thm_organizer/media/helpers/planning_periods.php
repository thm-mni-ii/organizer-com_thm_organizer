<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerModelPlanning_Period
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'departments.php';

/**
 * Provides validation methods for xml room objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperPlanning_Periods
{
	/**
	 * Gets the id of the planning period whose dates encompass the current date
	 *
	 * @return int the id of the planning period for the dates used on success, otherwise 0
	 */
	public static function getCurrentID()
	{
		$date  = date('Y-m-d');
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('id')
			->from('#__thm_organizer_planning_periods')
			->where("'$date' BETWEEN startDate and endDate");
		$dbo->setQuery($query);

		try
		{
			$result = $dbo->loadResult();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return 0;
		}

		return empty($result) ? 0 : $result;
	}

	/**
	 * Checks for the planning period entry in the database, creating it as necessary.
	 *
	 * @param array $data the planning period's data
	 *
	 * @return  mixed  int the id if the room could be resolved/added, otherwise null
	 */
	public static function getID($data)
	{
		$ppTable      = JTable::getInstance('planning_periods', 'thm_organizerTable');
		$loadCriteria = ['startDate' => $data['startDate'], 'endDate' => $data['endDate']];

		try
		{
			$success = $ppTable->load($loadCriteria);
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return null;
		}

		if ($success)
		{
			return $ppTable->id;
		}
		elseif (empty($data))
		{
			return null;
		}

		// Entry not found
		$success = $ppTable->save($data);

		return $success ? $ppTable->id : null;
	}

	/**
	 * Checks for the planning period name for a given planning period id
	 *
	 * @param string $ppID the planning period's id
	 *
	 * @return  mixed  string the name if the planning period could be resolved, otherwise null
	 */
	public static function getName($ppID)
	{
		$ppTable = JTable::getInstance('planning_periods', 'thm_organizerTable');

		try
		{
			$success = $ppTable->load($ppID);
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return null;
		}

		return $success ? $ppTable->name : null;
	}

	/**
	 * Getter method for rooms in database. Only retrieving the IDs here allows for formatting the names according to
	 * the needs of the calling views.
	 *
	 * @return string  all pools in JSON format
	 *
	 * @throws RuntimeException
	 */
	public static function getPlanningPeriods()
	{
		$dbo                 = JFactory::getDbo();
		$default             = [];
		$input               = JFactory::getApplication()->input;
		$selectedDepartments = $input->getString('departmentIDs');
		$selectedPrograms    = $input->getString('programIDs');

		$query = $dbo->getQuery(true);
		$query->select('DISTINCT pp.id, pp.name, pp.startDate, pp.endDate')
			->from('#__thm_organizer_planning_periods AS pp');

		if (!empty($selectedDepartments) OR !empty($selectedPrograms))
		{
			$query->innerJoin('#__thm_organizer_lessons AS l on l.planningPeriodID = pp.id');

			if (!empty($selectedDepartments))
			{
				$query->innerJoin("#__thm_organizer_departments AS dpt ON l.departmentID = dpt.id");
				$departmentIDs = "'" . str_replace(',', "', '", $selectedDepartments) . "'";
				$query->where("l.departmentID IN ($departmentIDs)");
			}

			if (!empty($selectedPrograms))
			{
				$query->innerJoin('#__thm_organizer_lesson_subjects AS ls on ls.lessonID = l.id');
				$query->innerJoin('#__thm_organizer_lesson_pools AS lp on lp.subjectID = ls.id');
				$query->innerJoin('#__thm_organizer_plan_pools AS ppo ON lp.poolID = ppo.id');
				$programIDs = "'" . str_replace(',', "', '", $selectedPrograms) . "'";
				$query->where("ppo.programID in ($programIDs)");
			}
		}

		$query->order('startDate');
		$dbo->setQuery($query);

		try
		{
			$results = $dbo->loadAssocList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return $default;
		}

		return empty($results) ? $default : $results;
	}
}
