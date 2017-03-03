<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperSchedule
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/mapping.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/rooms.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';

/**
 * Class offering static schedule functions
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperSchedule
{
	/**
	 * Aggregates the distinct lesson configurations to distinct instances
	 *
	 * @param mixed  $lessons the lessons which should get aggregated
	 * @param string $delta   representing date in which deltas gets accepted
	 *
	 * @return array
	 */
	private static function aggregateInstances($lessons, $delta)
	{
		$aggregatedLessons = array();
		foreach ($lessons as $lesson)
		{
			$date         = $lesson['date'];
			$lessonID     = $lesson['lessonID'];
			$subjectDelta = empty($lesson['subjectDelta']) ? '' : $lesson['subjectDelta'];
			$startTime    = substr(str_replace(':', '', $lesson['startTime']), 0, 4);
			$endTime      = substr(str_replace(':', '', $lesson['endTime']), 0, 4);
			$times        = "$startTime-$endTime";

			if (empty($aggregatedLessons[$date]))
			{
				$aggregatedLessons[$date] = array();
			}

			if (empty($aggregatedLessons[$date][$times]))
			{
				$aggregatedLessons[$date][$times] = array();
			}

			if (empty($aggregatedLessons[$date][$times][$lessonID]))
			{
				$aggregatedLessons[$date][$times][$lessonID]                  = array();
				$aggregatedLessons[$date][$times][$lessonID]['method']        = empty($lesson['method']) ? '' : $lesson['method'];
				$aggregatedLessons[$date][$times][$lessonID]['comment']       = empty($lesson['comment']) ? '' : $lesson['comment'];
				$aggregatedLessons[$date][$times][$lessonID]['startTime']     = $lesson['startTime'];
				$aggregatedLessons[$date][$times][$lessonID]['endTime']       = $lesson['endTime'];
				$aggregatedLessons[$date][$times][$lessonID]['subjects']      = array();
				$aggregatedLessons[$date][$times][$lessonID]['ccmID']         = empty($lesson['ccmID']) ? '' : $lesson['ccmID'];
				$aggregatedLessons[$date][$times][$lessonID]['calendarDelta'] = empty($lesson['calendarDelta']) ? '' : $lesson['calendarDelta'];
				$aggregatedLessons[$date][$times][$lessonID]['lessonDelta']   = empty($lesson['lessonDelta']) ? '' : $lesson['lessonDelta'];
				$aggregatedLessons[$date][$times][$lessonID]['poolDelta']     = empty($lesson['poolDelta']) ? '' : $lesson['poolDelta'];
			}

			$subjectName = self::getSubjectName($lesson);

			$configuration             = json_decode($lesson['configuration'], true);
			$configuration['modified'] = empty($lesson['configModified']) ? '' : $lesson['configModified'];
			self::resolveConfiguration($configuration, $delta);

			if (empty($aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]))
			{
				$subjectAbbr      = self::getSubjectAbbr($lesson);
				$subjectNo        = empty($lesson['subjectNo']) ? '' : $lesson['subjectNo'];
				$subjectShortName = empty($lesson['subjectShortName']) ? $subjectAbbr : $lesson['subjectShortName'];

				$aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName] = array(
					'subjectNo' => $subjectNo,
					'name'      => $subjectName,
					'shortName' => $subjectShortName,
					'abbr'      => $subjectAbbr
				);

				$aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teachers'] = $configuration['teachers'];
				$aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['rooms']    = $configuration['rooms'];
				$aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['programs'] = array();
			}
			else
			{
				$previousTeachers = $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teachers'];
				$previousRooms    = $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['rooms'];

				$aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teachers']     = $previousTeachers + $configuration['teachers'];
				$aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['rooms']        = $previousRooms + $configuration['rooms'];
				$aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['subjectDelta'] = $subjectDelta;
			}

			$aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teacherDeltas'] = $configuration['teacherDeltas'];
			$aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['roomDeltas']    = $configuration['roomDeltas'];
			$aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['pools'][$lesson['poolID']]
				= array('gpuntisID' => $lesson['poolGPUntisID'], 'name' => $lesson['poolName'], 'fullName' => $lesson['poolFullName']);

			$subjectID = $lesson['subjectID'];
			$programs  = THM_OrganizerHelperMapping::getSubjectPrograms($subjectID);
			self::addPlanPrograms($programs);

			$aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['programs'][$subjectID] = $programs;
		}

		ksort($aggregatedLessons);

		return $aggregatedLessons;
	}

	/**
	 * Adds the date clauses to the query
	 *
	 * @param array  $parameters the parameters configuring the export
	 * @param object &$query     the query object
	 *
	 * @return void modifies the query object
	 */
	private static function addDateClauses($parameters, &$query)
	{
		$dates = self::getDates($parameters);
		$query->where("c.schedule_date >= '{$dates['startDate']}'");
		$query->where("c.schedule_date <= '{$dates['endDate']}'");
	}

	/**
	 * Requested resources are not restrictive amongst themselves
	 *
	 * @param array  $parameters the request parameters
	 * @param object &$query     the query object
	 *
	 * @return void modifies the query object
	 */
	private static function addResourceClauses($parameters, &$query)
	{
		$wherray = array();

		if (!empty($parameters['poolIDs']))
		{
			$wherray[] = "pool.id IN ('" . implode("', '", $parameters['poolIDs']) . "')";
		}

		if (!empty($parameters['teacherIDs']))
		{
			foreach ($parameters['teacherIDs'] AS $teacherID)
			{
				$regexp = '[[.quotation-mark.]]teachers[[.quotation-mark.]][[.colon.]][[.{.]]' .
					'([[.quotation-mark.]][[:alnum:]]*[[.quotation-mark.]][[.colon.]]?[[.comma.]]?)*' .
					'[[.quotation-mark.]]' . $teacherID . '[[.quotation-mark.]][[.colon.]]';

				$regexp .= (empty($parameters['delta'])) ? '[[.quotation-mark.]][^removed]' : '';

				$wherray[] = "lc.configuration REGEXP '$regexp'";
			}
		}

		if (!empty($parameters['roomIDs']))
		{
			foreach ($parameters['roomIDs'] AS $roomID)
			{
				$regexp = '[[.quotation-mark.]]rooms[[.quotation-mark.]][[.colon.]][[.{.]]' .
					'([[.quotation-mark.]][[:alnum:]]*[[.quotation-mark.]][[.colon.]]?[[.comma.]]?)*' .
					'[[.quotation-mark.]]' . $roomID . '[[.quotation-mark.]][[.colon.]]';

				$regexp .= (empty($parameters['delta'])) ? '[[.quotation-mark.]][^removed]' : '';

				$wherray[] = "lc.configuration REGEXP '$regexp'";
			}
		}

		if (!empty($parameters['subjectIDs']))
		{
			$wherray[] = "ps.id IN ('" . implode("', '", $parameters['subjectIDs']) . "')";
		}

		if (!empty($parameters['lessonIDs']))
		{
			$wherray[] = "l.id IN ('" . implode("', '", $parameters['lessonIDs']) . "')";
		}

		$query->where("(" . implode(' OR ', $wherray) . ")");
	}

	/**
	 * Gets the lessons for the given pool ids.
	 *
	 * @param array $parameters array of pool ids or a single pool id
	 *
	 * @throws Exception
	 * @return string
	 */
	public static function getLessons($parameters)
	{
		$tag   = THM_OrganizerHelperLanguage::getShortTag();
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$select = "DISTINCT ccm.id AS ccmID, l.id AS lessonID, l.comment, m.abbreviation_$tag AS method, ";
		$select .= "ps.id AS psID, ps.name AS psName, ps.subjectNo, ps.gpuntisID AS psUntisID, ";
		$select .= "s.id AS subjectID, s.name_$tag AS subjectName, s.short_name_$tag AS subjectShortName, s.abbreviation_$tag AS subjectAbbr, ";
		$select .= "pool.id AS poolID, pool.gpuntisID AS poolGPUntisID, pool.name AS poolName, pool.full_name AS poolFullName, ";
		$select .= "c.schedule_date AS date, c.startTime, c.endTime, ";
		$select .= "lc.configuration, pp.id AS planProgramID";

		if (!empty($parameters['delta']))
		{
			$select .= ", lp.delta AS poolDelta, lp.modified AS poolModified";
			$select .= ", ls.delta AS subjectsDelta, ls.modified AS subjectsModified";
			$select .= ", l.delta AS lessonDelta, l.modified AS lessonModified";
			$select .= ", c.delta AS calendarDelta, c.modified AS calendarModified";
			$select .= ", lc.modified AS configModified";
			$select .= ", lt.delta AS teacherDelta, lt.modified AS teacherModified";
		}

		$query->select($select);
		self::setLessonQuery($parameters, $query);

		$query->innerJoin('#__thm_organizer_plan_programs AS pp ON pool.programID = pp.id');
		$query->innerJoin('#__thm_organizer_lesson_teachers AS lt ON lt.subjectID = ls.id');
		$query->innerJoin('#__thm_organizer_teachers AS teacher ON lt.teacherID = teacher.id');

		$query->leftJoin('#__thm_organizer_methods AS m ON l.methodID = m.id');
		$query->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id');
		$query->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');

		if (empty($parameters['delta']))
		{
			$query->where("lt.delta != 'removed'");
		}
		else
		{
			$query->where("(lt.delta != 'removed' OR (lt.delta = 'removed' AND lt.modified > '" . $parameters['delta'] . "'))");
		}

		self::addDateClauses($parameters, $query);
		$query->order('c.startTime');
		$dbo->setQuery($query);

		try
		{
			$rawLessons = $dbo->loadAssocList();
		}
		catch (Exception $exc)
		{
			return array();
		}

		if (empty($rawLessons))
		{
			return self::getNextAvailableDates($parameters);
		}

		$aggregatedLessons = self::aggregateInstances($rawLessons, $parameters['delta']);
		$dates             = self::getDates($parameters);
		$startDT           = strtotime($dates['startDate']);
		$endDT             = strtotime($dates['endDate']);

		for ($currentDT = $startDT; $currentDT <= $endDT; $currentDT = strtotime('+1 days', $currentDT))
		{
			$index = date('Y-m-d', $currentDT);
			if (!isset($aggregatedLessons[$index]))
			{
				$aggregatedLessons[$index] = array();
			}
		}

		ksort($aggregatedLessons);

		if (!empty($parameters['mySchedule']) AND !empty($parameters['userID']))
		{
			return self::getUserFilteredLessons($aggregatedLessons, $parameters['userID']);
		}

		return $aggregatedLessons;
	}

	/**
	 * Returns the best subject name of the many available
	 *
	 * @param array $lesson the lesson instance being iterated
	 *
	 * @return string  the lesson instance's display name
	 */
	private static function getSubjectAbbr($lesson)
	{
		if (!empty($lesson['subjectAbbr']))
		{
			return $lesson['subjectAbbr'];
		}

		return $lesson['psUntisID'];
	}

	/**
	 * Returns the best subject name of the many available
	 *
	 * @param array $lesson the lesson instance being iterated
	 *
	 * @return string  the lesson instance's display name
	 */
	private static function getSubjectName($lesson)
	{
		if (!empty($lesson['subjectName']))
		{
			return $lesson['subjectName'];
		}

		if (!empty($lesson['subjectShortName']))
		{
			return $lesson['subjectShortName'];
		}

		return $lesson['psName'];
	}

	/**
	 * Saves the planning period to the corresponding table if not already existent.
	 *
	 * @param string $ppName    the abbreviation for the planning period
	 * @param int    $startDate the integer value of the start date
	 * @param int    $endDate   the integer value of the end date
	 *
	 * @return int id of database entry
	 */
	public static function getPlanningPeriodID($ppName, $startDate, $endDate)
	{
		$data              = array();
		$data['startDate'] = date('Y-m-d', $startDate);
		$data['endDate']   = date('Y-m-d', $endDate);

		$table  = JTable::getInstance('planning_periods', 'thm_organizerTable');
		$exists = $table->load($data);
		if ($exists)
		{
			return $table->id;
		}

		$shortYear    = date('y', $endDate);
		$data['name'] = $ppName . $shortYear;
		$table->save($data);

		return $table->id;
	}

	/**
	 * Removes deprecated room and teacher indexes and resolves the remaining indexes to the names to be displayed
	 *
	 * @param mixed  &$configuration the lesson instance configuration
	 * @param string $delta          max date in which the delta gets accepted
	 *
	 * @return void
	 */
	private static function resolveConfiguration(&$configuration, $delta)
	{
		$deltaDate                      = empty($delta) ? date('Y-m-d H:i:s', strtotime('now')) : $delta;
		$configuration['teacherDeltas'] = array();

		foreach ($configuration['teachers'] AS $teacherID => $teacherDelta)
		{
			if ($teacherDelta == 'removed' AND $configuration['modified'] > $deltaDate)
			{
				unset($configuration['teachers'][$teacherID]);
				continue;
			}

			$configuration['teacherDeltas'][$teacherID] = $teacherDelta;
			$configuration['teachers'][$teacherID]      = THM_OrganizerHelperTeachers::getLNFName($teacherID, true);
		}

		$configuration['roomDeltas'] = array();

		foreach ($configuration['rooms'] AS $roomID => $roomDelta)
		{
			if ($roomDelta == 'removed' AND $configuration['modified'] > $deltaDate)
			{
				unset($configuration['rooms'][$roomID]);
				continue;
			}

			$configuration['roomDeltas'][$roomID] = $roomDelta;
			$configuration['rooms'][$roomID]      = THM_OrganizerHelperRooms::getName($roomID);
		}
	}

	/**
	 * Resolves the given date to the start and end dates for the requested time period
	 *
	 * @param array $parameters the schedule configuration parameters
	 *
	 * @return array the corresponding start and end dates
	 */
	public static function getDates($parameters)
	{
		$date = $parameters['date'];
		$type = $parameters['format'] == 'ics' ? 'ics' : $parameters['dateRestriction'];

		$startDayNo = empty($parameters['startDay']) ? 1 : $parameters['startDay'];
		$endDayNo   = empty($parameters['endDay']) ? 6 : $parameters['endDay'];

		$startDayName = date('l', strtotime("Sunday + $startDayNo days"));
		$endDayName   = date('l', strtotime("Sunday + $endDayNo days"));

		switch ($type)
		{
			case 'day':

				$dates = array('startDate' => $date, 'endDate' => $date);
				break;

			case 'week':

				$startDate = date('Y-m-d', strtotime("$startDayName this week", strtotime($date)));
				$endDate   = date('Y-m-d', strtotime("$endDayName this week", strtotime($date)));
				$dates     = array('startDate' => $startDate, 'endDate' => $endDate);
				break;

			case 'month':

				$monthStart = date('Y-m-d', strtotime('first day of this month', strtotime($date)));
				$startDate  = date('Y-m-d', strtotime("$startDayName this week", strtotime($monthStart)));
				$monthEnd   = date('Y-m-d', strtotime('last day of this month', strtotime($date)));
				$endDate    = date('Y-m-d', strtotime("$endDayName this week", strtotime($monthEnd)));
				$dates      = array('startDate' => $startDate, 'endDate' => $endDate);
				break;

			case 'semester':

				$dbo   = JFactory::getDbo();
				$query = $dbo->getQuery(true);
				$query->select('startDate, endDate')
					->from('#__thm_organizer_planning_periods')
					->where("'$date' BETWEEN startDate AND endDate");
				$dbo->setQuery($query);

				try
				{
					$dates = $dbo->loadAssoc();
				}
				catch (Exception $exc)
				{
					JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

					return array();
				}

				break;

			case 'ics':

				// ICS calendars get the next 6 months of data
				$startDate  = date('Y-m-d', strtotime("$startDayName this week", strtotime($date)));
				$previewEnd = date('Y-m-d', strtotime('+6 month', strtotime($date)));
				$endDate    = date('Y-m-d', strtotime("$endDayName this week", strtotime($previewEnd)));
				$dates      = array('startDate' => $startDate, 'endDate' => $endDate);
				break;
		}

		return $dates;
	}

	/**
	 * Searches for the next and last date where lessons can be found and returns them.
	 *
	 * @param array $parameters the schedule configuration parameters
	 *
	 * @return array next and latest available dates
	 */
	public static function getNextAvailableDates($parameters)
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select("MIN(c.schedule_date) AS minDate");
		self::setLessonQuery($parameters, $query);
		$query->where("c.schedule_date > '" . $parameters['date'] . "'");
		$dbo->setQuery($query);

		try
		{
			$futureDate = $dbo->loadResult();
		}
		catch (Exception $exc)
		{
			return array();
		}

		$query = $dbo->getQuery(true);
		$query->select("MAX(c.schedule_date) AS maxDate");
		self::setLessonQuery($parameters, $query);
		$query->where("c.schedule_date < '" . $parameters['date'] . "'");
		$dbo->setQuery($query);

		try
		{
			$pastDate = $dbo->loadResult();
		}
		catch (Exception $exc)
		{
			return array();
		}

		return array("pastDate" => $pastDate, "futureDate" => $futureDate);
	}

	/**
	 * Modifies query to get lessons, constrained by parameters
	 *
	 * @param array          $parameters the schedule configuration parameters
	 * @param JDatabaseQuery &$query     the query object
	 *
	 * @return void
	 */
	private static function setLessonQuery($parameters, &$query)
	{
		$query->from('#__thm_organizer_lessons AS l');
		$query->innerJoin('#__thm_organizer_calendar AS c ON l.id = c.lessonID');
		$query->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id');
		$query->innerJoin('#__thm_organizer_lesson_configurations AS lc ON lc.lessonID = ls.id');
		$query->innerJoin('#__thm_organizer_calendar_configuration_map AS ccm ON ccm.calendarID = c.id AND ccm.configurationID = lc.id');
		$query->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.subjectID = ls.id');
		$query->innerJoin('#__thm_organizer_plan_subjects AS ps ON ls.subjectID = ps.id');
		$query->innerJoin('#__thm_organizer_plan_pools AS pool ON pool.id = lp.poolID');

		if (empty($parameters['delta']))
		{
			$query->where("lp.delta != 'removed'");
			$query->where("ls.delta != 'removed'");
			$query->where("l.delta != 'removed'");
			$query->where("c.delta != 'removed'");
		}
		else
		{
			$query->where("(lp.delta != 'removed' OR (lp.delta = 'removed' AND lp.modified > '" . $parameters['delta'] . "'))");
			$query->where("(ls.delta != 'removed' OR (ls.delta = 'removed' AND ls.modified > '" . $parameters['delta'] . "'))");
			$query->where("(l.delta != 'removed' OR (l.delta = 'removed' AND l.modified > '" . $parameters['delta'] . "'))");
			$query->where("(c.delta != 'removed' OR (c.delta = 'removed' AND c.modified > '" . $parameters['delta'] . "'))");
		}

		if (!empty($parameters['mySchedule']) AND !empty($parameters['userID']))
		{
			$query->innerJoin('#__thm_organizer_user_lessons AS u ON u.lessonID = l.id');
			$query->where('u.userID = ' . $parameters['userID']);
		}
		else
		{
			self::addResourceClauses($parameters, $query);
		}
	}

	/**
	 * Returns plan programs that belongs to given programs
	 *
	 * @param array &$programs objects with program data
	 *
	 * @return void
	 */
	private static function addPlanPrograms(&$programs)
	{
		$programClauses = array();
		foreach ($programs as $program)
		{
			$programClauses[] = "programID = " . $program['id'];
		}

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select("id, programID")
			->from('#__thm_organizer_plan_programs')
			->where($programClauses, "OR");

		$dbo->setQuery($query);

		try
		{
			$results = $dbo->loadAssocList('programID', 'id');
		}
		catch (Exception $e)
		{
			return;
		}

		if (!empty($results))
		{
			foreach ($programs as &$program)
			{
				if (isset($results[$program['id']]))
				{
					$program['planProgramID'] = $results[$program['id']];
				}
			}
		}
	}

	/**
	 * Filters given lessons by their ccmIDs for the logged in user
	 *
	 * @param array $lessons aggregated lessons
	 * @param int   $userID  the user id for personal lessons
	 *
	 * @return array lessonIDs as keys and ccmIDs as values
	 */
	private static function getUserFilteredLessons($lessons, $userID)
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select("lessonID, configuration")
			->from('#__thm_organizer_user_lessons')
			->where("userID = $userID");
		$dbo->setQuery($query);

		try
		{
			$results = $dbo->loadAssocList('lessonID');
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return array();
		}

		if (empty($results))
		{
			return array();
		}

		$configurations = array();
		foreach ($results as $index => $result)
		{
			$configurations[$index] = json_decode($result['configuration']);
		}

		foreach ($lessons as $date => $blockTimes)
		{
			foreach ($blockTimes as $times => $lessonSet)
			{
				foreach ($lessonSet as $lessonID => $lessonData)
				{
					if (empty($configurations[$lessonID]) OR !in_array($lessonData['ccmID'], $configurations[$lessonID]))
					{
						unset($lessons[$date][$times][$lessonID]);
					}
				}
			}
		}

		return $lessons;
	}
}
