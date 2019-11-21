<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class Groups extends ResourceHelper implements Selectable
{
	use Filtered;

	/**
	 * Retrieves the ids of departments associated with the resource
	 *
	 * @param   int  $resourceID  the id of the resource for which the associated departments are requested
	 *
	 * @return array the ids of departments associated with the resource
	 */
	public static function getDepartmentIDs($resourceID)
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('DISTINCT dr.id')
			->from('#__thm_organizer_department_resources AS dr')
			->innerJoin('#__thm_organizer_groups as gr on gr.categoryID = dr.categoryID')
			->where("gr.id  = $resourceID");
		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadColumn', []);
	}

	/**
	 * Retrieves the selectable options for the resource.
	 *
	 * @param   string  $access  any access restriction which should be performed
	 *
	 * @return array the available options
	 */
	public static function getOptions($access = '')
	{
		$categoryIDs = Input::getFilterIDs('category');
		$options     = [];
		$short       = count($categoryIDs) === 1;

		foreach (self::getResources() as $group)
		{
			$name      = $short ? $group['name'] : $group['fullName'];
			$options[] = HTML::_('select.option', $group['id'], $name);
		}

		uasort($options, function ($optionOne, $optionTwo) {
			return $optionOne->text > $optionTwo->text;
		});

		// Any out of sequence indexes cause JSON to treat this as an object
		return array_values($options);
	}

	/**
	 * Retrieves the resource items.
	 *
	 * @param   string  $access  any access restriction which should be performed
	 *
	 * @return array the available resources
	 */
	public static function getResources($access = '')
	{
		$dbo = Factory::getDbo();

		$query = $dbo->getQuery(true);
		$query->select('gr.*');
		$query->from('#__thm_organizer_groups AS gr');

		if (!empty($access))
		{
			$query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = gr.categoryID');
			self::addAccessFilter($query, 'dr', $access);
		}

		self::addDeptSelectionFilter($query, 'category', 'gr', 'categoryID');
		self::addResourceFilter($query, 'category', 'cat', 'gr');

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadAssocList', []);
	}

	/**
	 * Retrieves a list of subjects associated with a group
	 *
	 * @return array the subjects associated with the group
	 */
	public static function getSubjects()
	{
		$groupIDs = Input::getFilterIDs('group');
		if (empty($groupIDs))
		{
			return $groupIDs;
		}

		$groupIDs = implode(',', $groupIDs);

		$date = Input::getCMD('date');
		if (!Dates::isStandardized($date))
		{
			$date = date('Y-m-d');
		}

		$interval = Input::getCMD('interval');
		if (!in_array($interval, ['day', 'week', 'month', 'semester']))
		{
			$interval = 'semester';
		}

		$dbo = Factory::getDbo();

		$query = $dbo->getQuery(true);
		$query->select('DISTINCT lc.courseID')
			->from('#__thm_organizer_lesson_courses AS lc')
			->innerJoin('#__thm_organizer_lessons AS l on l.id = lc.lessonID')
			->innerJoin('#__thm_organizer_lesson_groups AS lg on lg.lessonCourseID = lc.id')
			->where("lg.groupID IN ($groupIDs)")
			->where("l.delta != 'removed'")
			->where("lg.delta != 'removed'")
			->where("lc.delta != 'removed'");

		$dateTime = strtotime($date);
		switch ($interval)
		{
			case 'semester':
				$query->innerJoin('#__thm_organizer_terms AS term ON term.id = l.termID')
					->where("'$date' BETWEEN term.startDate AND term.endDate");
				break;
			case 'month':
				$monthStart = date('Y-m-d', strtotime('first day of this month', $dateTime));
				$startDate  = date('Y-m-d', strtotime('Monday this week', strtotime($monthStart)));
				$monthEnd   = date('Y-m-d', strtotime('last day of this month', $dateTime));
				$endDate    = date('Y-m-d', strtotime('Sunday this week', strtotime($monthEnd)));
				$query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
					->where("c.schedule_date BETWEEN '$startDate' AND '$endDate'");
				break;
			case 'week':
				$startDate = date('Y-m-d', strtotime('Monday this week', $dateTime));
				$endDate   = date('Y-m-d', strtotime('Sunday this week', $dateTime));
				$query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
					->where("c.schedule_date BETWEEN '$startDate' AND '$endDate'");
				break;
			case 'day':
				$query->innerJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id')
					->where("c.schedule_date = '$date'");
				break;
		}

		$dbo->setQuery($query);
		$courseIDs = OrganizerHelper::executeQuery('loadColumn', []);

		if (empty($courseIDs))
		{
			return [];
		}

		$subjects = [];
		foreach ($courseIDs as $courseID)
		{
			$name            = Courses::getName($courseID, true);
			$subjects[$name] = $courseID;
		}

		ksort($subjects);

		return $subjects;
	}
}
