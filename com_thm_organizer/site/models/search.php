<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSearch
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die();

/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class THM_OrganizerModelSchedule for loading the chosen schedule from the database
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSearch extends JModelLegacy
{
	private $lang;

	public $languageTag;

	private $programResults;

	public $results;

	private $terms;

	/**
	 * Aggregates inclusive conditions into one 'where' clause
	 *
	 * @param object &$query     the query object
	 * @param array  $conditions the conditions to be added to the query
	 *
	 * @return void modifies the query
	 */
	private function addInclusiveConditions(&$query, $conditions)
	{
		$query->where('(' . implode(' OR ', $conditions) . ')');

		return;
	}

	/**
	 * Iterates through the various match strengths and removes redundant entries in weaker strengths.
	 *
	 * @return void modifies the $results property
	 */
	private function cleanResults()
	{
		$strengths = array_keys($this->results);

		foreach ($strengths as $outerStrength)
		{
			$osResults = $this->results[$outerStrength];

			foreach ($osResults as $resource => $rResults)
			{
				foreach ($rResults as $resultID => $result)
				{
					foreach ($strengths as $innerStrength)
					{
						if ($outerStrength == $innerStrength)
						{
							continue;
						}

						if (!empty($this->results[$innerStrength][$resource])
							AND !empty($this->results[$innerStrength][$resource][$resultID]))
						{
							unset($this->results[$innerStrength][$resource][$resultID]);

							// Check if there is nothing left to avoid unnecessary iteration in the output
							if (empty($this->results[$innerStrength][$resource]))
							{
								unset($this->results[$innerStrength][$resource]);
							}
						}
					}
				}
			}
		}

		foreach ($this->results as $strength => $sResults)
		{
			foreach ($sResults as $resource => $rResults)
			{
				usort($this->results[$strength][$resource], array('THM_OrganizerModelSearch', 'sortItems'));
			}
		}
	}

	/**
	 * Filters lessons according to status and planning period
	 *
	 * @param object &$query       the query object to filter
	 * @param int    $planPeriodID the id of the planning period for lesson results
	 *
	 * @return void modifies the query
	 */
	private function filterLessons(&$query, $planPeriodID = null)
	{
		$query->where("(ls.delta IS NULL OR ls.delta != 'removed')")
			->where("(l.delta IS NULL OR l.delta != 'removed')");

		if (!empty($planPeriodID) AND is_int($planPeriodID))
		{
			$query->where("l.planningPeriodID = '$planPeriodID'");
		}

		return;
	}

	/**
	 * Finds degrees which can be associated with the terms. Possible return strengths exact and strong.
	 *
	 * @param array $terms the search terms
	 *
	 * @return array an array of degreeIDs, grouped by strength
	 */
	private function getDegrees($terms)
	{
		$query = $this->_db->getQuery(true);
		$query->select('*')
			->select("REPLACE(LOWER(abbreviation), '.', '') AS stdAbbr")
			->from('#__thm_organizer_degrees');
		$this->_db->setQuery($query);

		try
		{
			$degrees = $this->_db->loadAssocList('id');
		}
		catch (Exception $exception)
		{
			JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

			return [];
		}

		// Abbreviation or (title and type) matched
		$exactMatches = [];

		// Title or type matched
		$strongMatches = [];

		foreach ($degrees as $degreeID => $degree)
		{
			$key = array_search($degree['stdAbbr'], $terms);

			$nameParts = explode(' of ', $degree['name']);
			$title     = strtolower(array_shift($nameParts));
			$subject   = strtolower(implode(' of ', $nameParts));

			$titleFoundAt   = array_search($title, $terms);
			$subjectFoundAt = array_search($subject, $terms);

			$exactMatch = ($key !== false OR ($titleFoundAt !== false AND $subjectFoundAt !== false));

			if ($exactMatch)
			{
				// The abbreviated degree name only has relevance here, and can create false positives elsewhere => delete
				if ($key !== false)
				{
					unset($this->terms[$key]);
				}

				$exactMatches[$degreeID] = $degree;
			}
			elseif ($subjectFoundAt !== false OR $titleFoundAt !== false)
			{
				$strongMatches[$degreeID] = $degree;
			}
		}

		return ['exact' => $exactMatches, 'strong' => $strongMatches];
	}

	/**
	 * Searches for Organizer resources and creates links to relevant views
	 *
	 * @return array the results grouped by match strength
	 */
	public function getResults()
	{
		/**
		 * Exact     => exact match for the whole search independent of capitalization
		 * Strong    => exact match on one of the search terms
		 * Good      => similar to one or more of the search terms
		 * Related   => matches via a relation with an exact/partial/strong match
		 * Mentioned => one or more of the terms is a part of the extended text for the resource
		 */
		$this->results     = ['exact' => [], 'strong' => [], 'good' => [], 'related' => [], 'mentioned' => []];
		$this->lang        = THM_OrganizerHelperLanguage::getLanguage();
		$this->languageTag = THM_OrganizerHelperLanguage::getShortTag();

		$input     = JFactory::getApplication()->input;
		$rawSearch = trim($input->getString('search', ''));

		// New call or a hard reset
		if ($rawSearch === '')
		{
			return $this->results;
		}

		$this->setTerms($rawSearch);

		// Programs are searched for initially and set as an object property for use by departments, pools and programs
		$this->setPrograms();

		// Ordered by what I imagine their relative search frequency will be
		$this->searchSubjects();
		$this->searchPools();
		$this->searchPrograms();
		$this->searchTeachers();
		$this->searchRooms();
		$this->searchDepartments();

		$this->cleanResults();

		return $this->results;
	}

	/**
	 * Checks for room types which match the the capacity and unresolvable terms.
	 *
	 * @param array $misc     an array of terms which could not be resolved
	 * @param int   $capacity the requested capacity
	 *
	 * @return array the room type ids which matched the criteria
	 */
	private function getRoomTypes(&$misc, $capacity = 0)
	{
		if (empty($misc) AND empty($capacity))
		{
			return [];
		}

		$query = $this->_db->getQuery(true);
		$query->select('id')->from('#__thm_organizer_room_types');

		$typeIDs        = [];
		$standardClause = "(name_de LIKE '%XXX%' OR name_en LIKE '%XXX%' ";
		$standardClause .= "OR description_de LIKE '%XXX%' OR description_en LIKE '%XXX%')";

		if (!empty($misc))
		{
			foreach ($misc AS $key => $term)
			{
				$query->clear('where');
				if (!empty($capacity))
				{
					// Opens conjunctive clause and cap from type
					$query->where("(min_capacity IS NULL OR min_capacity = '0' OR min_capacity <= '$capacity')");
					$query->where("(max_capacity IS NULL OR max_capacity = '0' OR max_capacity >= '$capacity')");
				}

				$tempClause = str_replace('XXX', $term, $standardClause);
				$query->where($tempClause);
				$this->_db->setQuery($query);

				try
				{
					$typeResults = $this->_db->loadColumn();
				}
				catch (Exception $exc)
				{
					JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

					return;
				}

				if (!empty($typeResults))
				{
					unset($misc[$key]);
					$typeIDs = array_merge($typeIDs, $typeResults);
				}

			}
		}
		elseif (!empty($capacity))
		{
			$query->where("(min_capacity IS NULL OR min_capacity = '0' OR min_capacity <= '$capacity')");
			$query->where("(max_capacity IS NULL OR max_capacity = '0' OR max_capacity >= '$capacity')");

			// One must have a legitimate value for this to have meaning.
			$query->where("((min_capacity IS NOT NULL AND min_capacity > '0') OR (max_capacity IS NOT NULL AND max_capacity > '0'))");

			$this->_db->setQuery($query);

			try
			{
				$typeResults = $this->_db->loadColumn();
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

				return;
			}

			if (!empty($typeResults))
			{
				$typeIDs = array_merge($typeIDs, $typeResults);
			}
		}

		return array_unique($typeIDs);
	}

	/**
	 * Processes department/organization results into a standardized array for output
	 *
	 * @param array $results the department results
	 *
	 * @return void modifies the results propery
	 */
	private function processDepartments($results)
	{
		/** @noinspection PhpIncludeInspection */
		require_once JPATH_SITE . '/media/com_thm_organizer/helpers/departments.php';

		$departments = [];

		if (!empty($results))
		{
			foreach ($results AS $departmentID)
			{
				$departmentName = THM_OrganizerHelperDepartments::getName($departmentID);

				$departments[$departmentID] = [];
				$departments[$departmentID]['text']
				                            = $this->lang->_('COM_THM_ORGANIZER_DEPARTMENT') . ": {$departmentName}";

				$links = ['schedule' => "?option=com_thm_organizer&view=schedule&departmentIDs=$departmentID"];

				$departments[$departmentID]['links'] = $links;
			}
		}

		return $departments;
	}

	/**
	 * Processes pool results into a standardized array for output
	 *
	 * @param array  &$pools  the array that the pools are to be stored in
	 * @param array  $results the pool id results
	 * @param string $type    the type of pool ids being processed
	 *
	 * @return mixed
	 */
	private function processPools(&$pools, $results, $type)
	{
		/** @noinspection PhpIncludeInspection */
		require_once JPATH_SITE . '/media/com_thm_organizer/helpers/pools.php';

		foreach ($results AS $result)
		{
			if ($type == 'real')
			{
				$index = "d{$result['id']}";
				$text  = THM_OrganizerHelperPools::getName($result['id'], 'real');
				$links = ['subject_list' => "?option=com_thm_organizer&view=subject_list&poolIDs={$result['id']}"];
			}
			else
			{
				$index = "p{$result['id']}";
				$text  = THM_OrganizerHelperPools::getName($result['id'], 'plan');
				$links = ['schedule' => "?option=com_thm_organizer&view=schedule&poolIDs={$result['id']}"];
			}

			$pools[$index]          = [];
			$pools[$index]['text']  = $this->lang->_('COM_THM_ORGANIZER_POOL') . ": {$result['program']}, $text";
			$pools[$index]['links'] = $links;
		}

		return $pools;
	}

	/**
	 * Processes (plan) program results into a standardized array for output
	 *
	 * @param array $pResults  the program documentation results
	 * @param array $ppResults the program planning results lesson results
	 *
	 * @return void modifies the results property
	 */
	private function processPrograms($pResults, $ppResults)
	{
		/** @noinspection PhpIncludeInspection */
		require_once JPATH_SITE . '/media/com_thm_organizer/helpers/programs.php';

		$programs = [];

		if (!empty($pResults))
		{
			foreach ($pResults AS $program)
			{
				$invalidMapping = (empty($program['lft']) OR empty($program['rgt']) OR $program['rgt'] - $program['lft'] < 2);
				$noPlan         = empty($program['ppID']);

				// Any linked view would be empty
				if ($invalidMapping and $noPlan)
				{
					continue;
				}

				$programID = "d{$program['id']}";

				$programs[$programID]               = [];
				$programs[$programID]['programID']  = $program['id'];
				$programs[$programID]['pProgramID'] = $program['ppID'];
				$programs[$programID]['lft']        = $program['lft'];
				$programs[$programID]['rgt']        = $program['rgt'];

				$text                         = THM_OrganizerHelperPrograms::getName($program['id'], 'real');
				$programs[$programID]['name'] = $text;

				$programs[$programID]['text'] = $this->lang->_('COM_THM_ORGANIZER_PROGRAM') . ": $text";

				$links = [];

				$invalidMapping = (empty($program['lft']) OR empty($program['rgt']) OR $program['rgt'] - $program['lft'] < 2);

				// If the mapping is invalid only an empty data set would be displayed for subject list and curriculum
				if (!$invalidMapping)
				{
					$links['subject_list'] = "?option=com_thm_organizer&view=subject_list&programIDs={$program['id']}";
					$links['curriculum']   = "?option=com_thm_organizer&view=curriculum&programIDs={$program['id']}";
				}

				if (!$noPlan)
				{
					$links['schedule'] = "?option=com_thm_organizer&view=schedule&programIDs={$program['ppID']}";
				}

				$programs[$programID]['links'] = $links;
			}
		}

		if (!empty($ppResults))
		{
			foreach ($ppResults AS $program)
			{
				$planID       = "p{$program['ppID']}";
				$scheduleLink = "?option=com_thm_organizer&view=schedule&programIDs={$program['ppID']}";

				// Subject was found
				if (!empty($program['id']))
				{
					$programID = "d{$program['id']}";

					// No redundant subject entries
					if (!empty($programID) AND !empty($programs[$programID]))
					{
						$programs[$programID]['pProgramID']        = $program['ppID'];
						$programs[$programID]['links']['schedule'] = $scheduleLink;

						continue;
					}
				}

				$programs[$planID]               = [];
				$programs[$planID]['pProgramID'] = $program['ppID'];

				$text                      = THM_OrganizerHelperPrograms::getName($program['ppID'], 'plan');
				$programs[$planID]['name'] = $text;
				$programs[$planID]['text'] = $this->lang->_('COM_THM_ORGANIZER_PROGRAM') . ": $text";

				$links = [];

				$invalidMapping = (empty($program['lft']) OR empty($program['rgt']) OR $program['rgt'] - $program['lft'] < 2);

				if (!$invalidMapping)
				{
					$programs[$planID]['programID']  = $program['id'];
					$links['subject_list'] = "?option=com_thm_organizer&view=subject_list&programIDs={$program['id']}";
					$links['curriculum']   = "?option=com_thm_organizer&view=curriculum&programIDs={$program['id']}";
				}

				$links['schedule']          = $scheduleLink;
				$programs[$planID]['links'] = $links;
			}
		}

		return $programs;
	}

	/**
	 * Processes room results into a standardized array for output
	 *
	 * @param array &$results the room results
	 *
	 * @return array of formatted room results
	 */
	private function processRooms($results)
	{
		$rooms = [];

		if (!empty($results))
		{
			foreach ($results AS $room)
			{
				$roomID         = $room['id'];
				$rooms[$roomID] = [];

				$rooms[$roomID]['text'] = $this->lang->_('COM_THM_ORGANIZER_ROOM') . ": {$room['name']}";

				$description = empty($room['description']) ? $room['type'] : $room['description'];

				if (empty($room['capacity']))
				{
					$capacity = '';
				}
				else
				{
					$capacity = ' (~' . $room['capacity'] . ' ' . $this->lang->_('COM_THM_ORGANIZER_SEATS') . ')';
				}

				$rooms[$roomID]['description'] = "$description$capacity";

				$rooms[$roomID]['links'] = ['schedule' => "?option=com_thm_organizer&view=schedule&roomIDs={$room['id']}"];
			}
		}

		return $rooms;
	}

	/**
	 * Processes subject/lesson results into a standardized array for output
	 *
	 * @param array $sResults  the subject documentation results
	 * @param array $psResults the subject lesson results
	 *
	 * @return void modifies the results property
	 */
	private function processSubjects($sResults, $psResults)
	{
		/** @noinspection PhpIncludeInspection */
		require_once JPATH_SITE . '/media/com_thm_organizer/helpers/subjects.php';

		$subjects = [];

		if (!empty($sResults))
		{
			foreach ($sResults AS $sID => $subject)
			{
				$subjectID = "s$sID";

				$subjects[$subjectID] = [];

				$text = THM_OrganizerHelperSubjects::getName($sID, 'real', true);

				$subjects[$subjectID]['text'] = $this->lang->_('COM_THM_ORGANIZER_SUBJECT') . ": $text";

				$links = [];

				$links['subject_details'] = "?option=com_thm_organizer&view=subject_details&id=$sID";

				if (!empty($subject['psID']))
				{
					$links['schedule'] = "?option=com_thm_organizer&view=schedule&subjectIDs={$subject['psID']}";
				}

				$subjects[$subjectID]['links']       = $links;
				$subjects[$subjectID]['description'] = THM_OrganizerHelperSubjects::getPrograms($sID, 'real');
			}
		}

		if (!empty($psResults))
		{
			foreach ($psResults AS $pID => $plan)
			{
				$planID       = "p$pID";
				$scheduleLink = "?option=com_thm_organizer&view=schedule&subjectIDs=$pID";

				// Subject was found
				if (!empty($plan['sID']))
				{
					$subjectID = "s{$plan['sID']}";

					// No redundant subject entries
					if (!empty($subjects[$subjectID]))
					{
						if (empty($subjects[$subjectID]['links']['schedule']))
						{
							$subjects[$subjectID]['links']['schedule'] = $scheduleLink;
						}

						continue;
					}
				}

				$subjects[$planID] = [];

				$text = THM_OrganizerHelperSubjects::getName($pID, 'plan', true);

				$subjects[$planID]['text'] = $this->lang->_('COM_THM_ORGANIZER_SUBJECT') . ": $text";

				$links = [];

				if (!empty($plan['sID']))
				{
					$links['subject_details'] = "?option=com_thm_organizer&view=subject_details&id={$plan['sID']}";
				}

				$links['schedule']                = $scheduleLink;
				$subjects[$planID]['links']       = $links;
				$subjects[$planID]['description'] = THM_OrganizerHelperSubjects::getPrograms($pID, 'plan');
			}
		}

		return $subjects;
	}

	/**
	 * Processes teacher results into a standardized array for output
	 *
	 * @param array $results the teacher results
	 *
	 * @return void modifies the results propery
	 */
	private function processTeachers($results)
	{
		/** @noinspection PhpIncludeInspection */
		require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';

		$teachers = [];

		if (!empty($results))
		{
			foreach ($results AS $teacher)
			{
				$documented = THM_OrganizerHelperTeachers::teaches('subject', $teacher['id']);
				$teaches    = THM_OrganizerHelperTeachers::teaches('lesson', $teacher['id']);

				// Nothing to link
				if (!$documented AND !$teaches)
				{
					continue;
				}

				$teacherName = THM_OrganizerHelperTeachers::getDefaultName($teacher['id']);

				$teachers[$teacher['id']]         = [];
				$teachers[$teacher['id']]['text'] = $this->lang->_('COM_THM_ORGANIZER_TEACHER') . ": {$teacherName}";

				$links = [];

				if ($documented)
				{
					$links['subject_list'] = "?option=com_thm_organizer&view=subject_list&teacherIDs={$teacher['id']}";
				}

				if ($teaches)
				{
					$links['schedule'] = "?option=com_thm_organizer&view=schedule&teacherIDs={$teacher['id']}";
				}

				$teachers[$teacher['id']]['links'] = $links;
			}
		}

		return $teachers;
	}

	/**
	 * Retrieves prioritized department search results
	 *
	 * @return void adds to the results property
	 */
	private function searchDepartments()
	{
		$results  = [];
		$eWherray = [];
		$sWherray = [];

		foreach ($this->terms AS $term)
		{
			if (is_numeric($term))
			{
				$clause     = "name_de LIKE '$term %' OR name_en LIKE '$term %' ";
				$clause     .= "OR short_name_de LIKE '$term %' OR short_name_en LIKE '$term %'";
				$eWherray[] = $clause;
				$sWherray[] = $clause;
			}
			else
			{
				$eClause    = "short_name_de LIKE '%$term' OR short_name_en LIKE '%$term'";
				$eClause    .= " OR name_de LIKE '%$term' OR short_name_en LIKE '%$term'";
				$eWherray[] = $eClause;
				$sClause    = "short_name_de LIKE '%$term%' OR short_name_en LIKE '%$term%'";
				$sClause    .= " OR name_de LIKE '%$term%' OR short_name_en LIKE '%$term%'";
				$sWherray[] = $sClause;
			}
		}

		$query = $this->_db->getQuery(true);
		$query->select('p.id AS ppID, d.id AS departmentID')
			->from('#__thm_organizer_plan_programs AS p')
			->innerJoin('#__thm_organizer_department_resources AS dr ON dr.programID = p.ID')
			->innerJoin('#__thm_organizer_departments AS d on dr.departmentID = d.id');

		// Exact
		$this->addInclusiveConditions($query, $eWherray);
		$this->_db->setQuery($query);

		try
		{
			$associations = $this->_db->loadAssocList();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return;
		}

		$departmentIDs = [];

		foreach ($associations AS $association)
		{
			$departmentIDs[$association['departmentID']] = $association['departmentID'];
		}

		$this->results['exact']['departments'] = $this->processDepartments($departmentIDs);

		$programs                             = [];
		$this->results['related']['programs'] = $this->processPrograms($programs, $associations);

		// Strong Related programs will not be displayed => no selection and no secondary processing.
		$query->clear('select');
		$query->clear('where');

		$query->select('DISTINCT d.id');
		$this->addInclusiveConditions($query, $sWherray);
		$this->_db->setQuery($query);

		try
		{
			$departmentIDs = $this->_db->loadColumn();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return;
		}

		$this->results['strong']['departments'] = $this->processDepartments($departmentIDs);
	}

	/**
	 * Retrieves prioritized pool search results
	 *
	 * @return void adds to the results property
	 */
	private function searchPools()
	{
		foreach ($this->terms AS $index => $term)
		{
			if ($index === 0)
			{
				continue;
			}

			/*$epWherray[] = "REPLACE(LCASE(name), '.', '') LIKE '$term'";

			$eClause    = "REPLACE(LCASE(pl.name_de), '.', '') LIKE '$term' ";
			$eClause    .= "OR REPLACE(LCASE(pl.name_en), '.', '') LIKE '$term' ";
			$eClause    .= "OR REPLACE(LCASE(pl.short_name_de), '.', '') LIKE '$term' ";
			$eClause    .= "OR REPLACE(LCASE(pl.short_name_en), '.', '') LIKE '$term' ";
			$eClause    .= "OR REPLACE(LCASE(pl.abbreviation_de), '.', '') LIKE '$term' ";
			$eClause    .= "OR REPLACE(LCASE(pl.abbreviation_en), '.', '') LIKE '$term'";
			$eWherray[] = $eClause;*/

			$pWherray[] = "REPLACE(LCASE(name), '.', '') LIKE '%$term%'";

			$clause    = "REPLACE(LCASE(pl.name_de), '.', '') LIKE '%$term%' ";
			$clause    .= "OR REPLACE(LCASE(pl.name_en), '.', '') LIKE '%$term%' ";
			$clause    .= "OR REPLACE(LCASE(pl.short_name_de), '.', '') LIKE '%$term%' ";
			$clause    .= "OR REPLACE(LCASE(pl.short_name_en), '.', '') LIKE '%$term%' ";
			$clause    .= "OR REPLACE(LCASE(pl.abbreviation_de), '.', '') LIKE '%$term%' ";
			$clause    .= "OR REPLACE(LCASE(pl.abbreviation_en), '.', '') LIKE '%$term%'";
			$wherray[] = $clause;
		}

		// Plan programs have to be found in strings => standardized name as extra temp variable for comparison
		$ppQuery = $this->_db->getQuery(true);
		$ppQuery->from('#__thm_organizer_plan_pools');

		$pQuery = $this->_db->getQuery(true);
		$pQuery->from('#__thm_organizer_pools AS pl')
			->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = pl.id');

		foreach ($this->programResults AS $strength => $programs)
		{
			$pools = [];

			foreach ($programs As $program)
			{
				$ppQuery->clear('select');
				$ppQuery->clear('where');
				$pQuery->clear('select');
				$pQuery->clear('where');
				$poolID  = null;
				$pPoolID = null;

				if (!empty($program['pProgramID']))
				{
					$ppQuery->select("DISTINCT id, '{$program['name']}' AS program");

					$this->addInclusiveConditions($ppQuery, $pWherray);

					$ppQuery->where("programID = '{$program['pProgramID']}'");
					$this->_db->setQuery($ppQuery);

					try
					{
						$pPoolIDs = $this->_db->loadAssocList();
					}
					catch (Exception $exc)
					{
						JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

						return;
					}
				}

				if (!empty($pPoolIDs))
				{
					$this->processPools($pools, $pPoolIDs, 'plan');
				}

				if (!empty($program['programID']))
				{
					$pQuery->select("DISTINCT pl.id, '{$program['name']}' AS program");

					$this->addInclusiveConditions($pQuery, $wherray);

					$pQuery->where("(m.lft > '{$program['lft']}' AND m.rgt < '{$program['rgt']}')");
					$this->_db->setQuery($pQuery);

					try
					{
						$poolIDs = $this->_db->loadAssocList();
					}
					catch (Exception $exc)
					{
						JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

						return;
					}
				}

				if (!empty($poolIDs))
				{
					$this->processPools($pools, $poolIDs, 'real');
				}
			}

			if (!empty($pools))
			{
				$this->results[$strength]['pools'] = $pools;
			}
		}
	}

	/**
	 * Retrieves prioritized program search results
	 *
	 * @return void adds to the results property
	 */
	private function searchPrograms()
	{
		$programResults = $this->programResults;

		foreach ($programResults AS $strength => $programs)
		{
			$this->results[$strength]['programs'] = $programs;
		}
	}

	/**
	 * Retrieves prioritized room search results
	 *
	 * @return void adds to the results property
	 */
	private function searchRooms()
	{
		$select = "r.id , r.longname as name, r.capacity, ";
		$select .= "rt.name_{$this->languageTag} as type, rt.description_{$this->languageTag} as description";
		$query  = $this->_db->getQuery(true);
		$query->select($select)
			->from('#__thm_organizer_rooms AS r')
			->leftJoin('#__thm_organizer_room_types AS rt ON r.typeID = rt.id')
			->order('r.longname ASC');

		// EXACT

		$wherray = [];

		foreach ($this->terms as $term)
		{
			$wherray[] = "r.longname LIKE '$term'";
		}

		$this->addInclusiveConditions($query, $wherray);
		$this->_db->setQuery($query);

		try
		{
			$eRooms = $this->_db->loadAssocList();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return;
		}

		$this->results['exact']['rooms'] = $this->processRooms($eRooms);

		// STRONG => has name relevance
		$query->clear('where');

		$buildings = [];
		$capacity  = 0;
		$misc      = [];

		// Strong matches
		foreach ($this->terms as $index => $term)
		{
			// The reserved index for the complete search is irrelevant as such here
			if (count($this->terms) > 1 AND $index === 0)
			{
				continue;
			}

			// This could probably be done with one expression, but I don't want to invest the time right now.
			$isBuilding = preg_match("/^[\p{L}}][\d]{1,2}$/", $term, $matches);
			$isFloor    = preg_match("/^[\p{L}}][\d]{1,2}\.[\d]{1,2}\.*$/", $term, $matches);

			if (!empty($isBuilding) OR !empty($isFloor))
			{
				$buildings[] = $term;
				continue;
			}

			// Only a number, the only real context for a numerical search term
			$isCapacity = preg_match("/^\d+$/", $term, $matches);

			if (!empty($isCapacity))
			{
				$number = (int) $term;

				// The number most likely denotes a module sequence
				if ($number < 5)
				{
					continue;
				}

				// Bigger numbers will trump smaller ones in the search, so they are superfluous.
				$capacity = $number > $capacity ? (int) $term : $capacity;
				continue;
			}

			// Relevance cannot be determined, if relevant than a non-conforming name
			$misc[] = $term;
		}

		$typeIDs    = $this->getRoomTypes($misc, $capacity);
		$typeString = empty($typeIDs) ? '' : "'" . implode("', '", $typeIDs) . "'";

		if (!empty($misc))
		{
			foreach ($misc AS $term)
			{
				$query->where("(r.longname LIKE '%$term%')");
			}
		}

		if (!empty($buildings))
		{
			$query->where("(r.longname LIKE '" . implode("%' OR r.longname LIKE '", $buildings) . "%')");
		}

		$performStrongQuery = (!empty($misc) OR !empty($buildings));

		if ($performStrongQuery)
		{

			if (!empty($capacity) AND !empty($typeString))
			{
				// Opens main clause and room cap existent
				$query->where("((r.capacity >= '$capacity' OR r.capacity = '0') AND rt.id IN ($typeString))");
			}
			elseif (!empty($capacity))
			{
				$query->where("r.capacity >= '$capacity'");
			}
			elseif (!empty($typeString))
			{
				$query->where("rt.id IN ($typeString)");
			}
			$this->_db->setQuery($query);

			try
			{
				$sRooms = $this->_db->loadAssocList();
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

				return;
			}

			$this->results['strong']['rooms'] = $this->processRooms($sRooms);
		}

		// Related => has type or capacity relevance

		$query->clear('where');

		if (!empty($capacity) AND !empty($typeString))
		{
			// Opens main clause and room cap existent
			$query->where("((r.capacity >= '$capacity' OR r.capacity = '0') AND rt.id IN ($typeString))");
		}
		elseif (!empty($capacity))
		{
			$query->where("r.capacity >= '$capacity'");
		}
		elseif (!empty($typeString))
		{
			$query->where("rt.id IN ($typeString)");
		}

		$performRelatedQuery = (!empty($capacity) OR !empty($typeString));

		if ($performRelatedQuery)
		{
			$this->_db->setQuery($query);

			try
			{
				$rRooms = $this->_db->loadAssocList();
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

				return;
			}

			$this->results['related']['rooms'] = $this->processRooms($rRooms);
		}

	}

	/**
	 * Retrieves prioritized subject/lesson search results
	 *
	 * @return void adds to the results property
	 */
	private function searchSubjects()
	{
		$terms = $this->terms;

		$termCount = count($terms);

		// A plan subject does not necessarily have subject documentation
		$psQuery = $this->_db->getQuery(true);
		$psQuery->select('DISTINCT ps.id AS psID, s.id AS sID')
			->from('#__thm_organizer_plan_subjects AS ps')
			->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.subjectID = ps.id')
			->innerJoin('#__thm_organizer_lessons AS l ON ls.lessonID = l.id')
			->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id')
			->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');

		// Subject documentation does not necessarily have planned lesson instances
		$sQuery = $this->_db->getQuery(true);
		$sQuery->select("DISTINCT s.id AS sID, ps.id as psID")
			->from('#__thm_organizer_subjects AS s')
			->leftJoin('#__thm_organizer_subject_mappings AS sm on sm.subjectID = s.id')
			->leftJoin('#__thm_organizer_plan_subjects AS ps on sm.plan_subjectID = ps.id')
			->leftJoin('#__thm_organizer_lesson_subjects AS ls on ls.subjectID = ps.id')
			->leftJoin('#__thm_organizer_lessons AS l on ls.lessonID = l.id');

		// EXACT => exact (case independent) match for the search term
		$term = current($terms);

		$psClause = "(ps.name LIKE '$term' OR ps.subjectNo LIKE '$term'";

		$sClause = "(s.externalID LIKE '$term' OR s.name_de LIKE '$term' OR s.name_en LIKE '$term' OR ";
		$sClause .= "s.short_name_de LIKE '$term' OR s.short_name_en LIKE '$term' OR ";
		$sClause .= "s.abbreviation_de LIKE '$term' OR s.abbreviation_en LIKE '$term'";

		if ($termCount > 1)
		{
			foreach ($terms as $term)
			{
				$psClause .= " OR ps.subjectNo LIKE '$term'";
				$sClause  .= "OR s.externalID LIKE '$term'";
			}
		}

		$psClause .= ')';
		$sClause  .= ')';

		$this->filterLessons($psQuery);
		$psQuery->where($psClause);

		$this->filterLessons($sQuery);
		$sQuery->where($sClause);

		try
		{
			$this->_db->setQuery($psQuery);
			$planSubjects = $this->_db->loadAssocList('psID');
			$this->_db->setQuery($sQuery);
			$subjects = $this->_db->loadAssocList('sID');
		}
		catch (Exception $exception)
		{
			JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

			return;
		}

		$this->results['exact']['subjects'] = $this->processSubjects($subjects, $planSubjects);

		// STRONG => exact match on at least one term
		$psQuery->clear('where');
		$sQuery->clear('where');
		$nameDEArray = [];
		$nameENArray = [];

		foreach ($terms as $index => $term)
		{
			if ($termCount > 1 AND $index === 0)
			{
				continue;
			}

			$asNumber = false;

			preg_match("/^([ix|iv|v]{1}|[i]+)$/", $term, $matches);

			if (!empty($matches) OR is_numeric($term))
			{
				$asNumber = true;
			}

			// Numeric values will always be listed separately at the end of the names. Direct comparison delivers false positives.
			if ($asNumber)
			{
				$psQuery->where("ps.name LIKE '% $term'");
				$nameDEArray[] = "s.name_de LIKE '% $term'";
				$nameENArray[] = "s.name_en LIKE '% $term'";
			}
			else
			{
				$psQuery->where("ps.name LIKE '%$term%'");
				$nameDEArray[] = "s.name_de LIKE '%$term%'";
				$nameENArray[] = "s.name_en LIKE '%$term%'";
			}
		}

		$this->filterLessons($psQuery);
		$this->_db->setQuery($psQuery);

		$nameDEClause = '(' . implode(' AND ', $nameDEArray) . ')';
		$nameENClause = '(' . implode(' AND ', $nameENArray) . ')';
		$sQuery->where("($nameDEClause OR $nameENClause)");
		$this->filterLessons($sQuery);

		try
		{
			$this->_db->setQuery($psQuery);
			$planSubjects = $this->_db->loadAssocList('psID');
			$this->_db->setQuery($sQuery);
			$subjects = $this->_db->loadAssocList('sID');
		}
		catch (Exception $exception)
		{
			JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

			return;
		}

		$this->results['strong']['subjects'] = $this->processSubjects($subjects, $planSubjects);

		// Good
		$psQuery->clear('where');
		$sQuery->clear('where');

		$sWherray  = [];
		$psWherray = [];

		foreach ($terms as $index => $term)
		{
			// Numeric values deliver true for everything
			if ((count($this->terms) > 1 AND $index === 0))
			{
				continue;
			}

			$asNumber = false;

			preg_match("/^([ix|iv|v]{1}|[i]+)$/", $term, $matches);

			if (!empty($matches) OR is_numeric($term))
			{
				$asNumber = true;
			}

			// Numeric values will always be listed separately at the end of the names. Direct comparison delivers false positives.
			if ($asNumber)
			{
				$sClause     = "s.name_de LIKE '% $term' OR s.name_en LIKE '% $term' OR ";
				$sClause     .= "s.short_name_de REGEXP '%$term' OR s.short_name_en REGEXP '%$term' OR ";
				$sClause     .= "s.abbreviation_de REGEXP '%$term' OR s.abbreviation_en REGEXP '%$term'";
				$sWherray[]  = $sClause;
				$psWherray[] = "ps.name LIKE '% $term' OR ps.subjectNo REGEXP '%$term%'";
			}
			else
			{
				$sClause     = "s.name_de LIKE '%$term%' OR s.name_en LIKE '%$term%' OR ";
				$sClause     .= "s.short_name_de LIKE '%$term%' OR s.short_name_en LIKE '%$term%' OR ";
				$sClause     .= "s.abbreviation_de LIKE '%$term%' OR s.abbreviation_en LIKE '%$term%'";
				$sWherray[]  = $sClause;
				$psWherray[] = "ps.name REGEXP '%$term%' OR ps.subjectNo REGEXP '%$term%'";
			}

		}

		// There were only numeric values in the search so the conditions are empty => don't execute queries
		if (empty($psWherray) AND empty($sWherray))
		{
			return;
		}

		$this->filterLessons($psQuery);
		$this->addInclusiveConditions($psQuery, $psWherray);

		$this->filterLessons($sQuery);
		$this->addInclusiveConditions($sQuery, $sWherray);

		try
		{
			if (!empty($psWherray))
			{
				$this->_db->setQuery($psQuery);
				$planSubjects = $this->_db->loadAssocList('psID');
			}
			else
			{
				$planSubjects = null;
			}

			if (!empty($sWherray))
			{
				$this->_db->setQuery($sQuery);
				$subjects = $this->_db->loadAssocList('sID');
			}
			else
			{
				$subjects = null;
			}
		}
		catch (Exception $exception)
		{
			JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

			return;
		}

		$this->results['good']['subjects'] = $this->processSubjects($subjects, $planSubjects);

		// Mentioned Looks for mention of the terms in the differing text fields of the module descriptions.

		$sQuery->clear('where');
		$planSubjects = null;

		$sWherray = [];

		foreach ($terms as $index => $term)
		{
			// Numeric values deliver true for everything
			if (count($this->terms) > 1 AND $index === 0)
			{
				continue;
			}

			$sClause    = "s.content_de LIKE '% $term%' OR s.content_en LIKE '% $term%' OR ";
			$sClause    .= "s.description_de LIKE '% $term %' OR s.description_en LIKE '% $term%' OR ";
			$sClause    .= "s.objective_de LIKE '% $term%' OR s.objective_en LIKE '% $term%'";
			$sWherray[] = $sClause;
		}

		// There were only numeric values in the search so the conditions are empty => don't execute queries
		if (empty($sWherray))
		{
			return;
		}

		$this->filterLessons($sQuery);
		$this->addInclusiveConditions($sQuery, $sWherray);
		$this->_db->setQuery($sQuery);

		try
		{
			$subjects = $this->_db->loadAssocList('sID');
		}
		catch (Exception $exception)
		{
			JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

			return;
		}

		$this->results['mentioned']['subjects'] = $this->processSubjects($subjects, $planSubjects);

		// Related
		$psQuery->clear('where');
		$sQuery->clear('where');

		$psQuery->innerJoin('#__thm_organizer_lesson_teachers AS lt ON lt.subjectID = ls.id')
			->innerJoin('#__thm_organizer_teachers AS t on lt.teacherID = t.id');

		$sQuery->innerJoin('#__thm_organizer_subject_teachers AS st ON st.subjectID = s.id')
			->innerJoin('#__thm_organizer_teachers AS t on st.teacherID = t.id');


		if ($termCount == 1)
		{
			$psQuery->where("t.surname LIKE '%$term%'");
			$sQuery->where("t.surname LIKE '%$term%'");
		}
		else
		{
			$wherray    = [];
			$innerTerms = $terms;

			foreach ($terms AS $oKey => $outerTerm)
			{
				foreach ($terms AS $iKey => $innerTerm)
				{
					if ($outerTerm == $innerTerm)
					{
						unset($innerTerms[$iKey]);
						continue;
					}

					// lnf/fnf
					$wherray[] = "(t.surname LIKE '%$outerTerm%' AND t.forename LIKE '%$innerTerm%')";
					$wherray[] = "(t.surname LIKE '%$innerTerm%' AND t.forename LIKE '%$outerTerm%')";
				}
			}

			$this->addInclusiveConditions($psQuery, $wherray);
			$this->addInclusiveConditions($sQuery, $wherray);
		}

		try
		{
			$this->_db->setQuery($psQuery);
			$planSubjects = $this->_db->loadAssocList('psID');
			$this->_db->setQuery($sQuery);
			$subjects = $this->_db->loadAssocList('sID');
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return;
		}

		$this->results['related']['subjects'] = $this->processSubjects($subjects, $planSubjects);
	}

	/**
	 * Retrieves prioritized teacher search results
	 *
	 * @return void adds to the results property
	 */
	private function searchTeachers()
	{
		$query = $this->_db->getQuery(true);
		$query->select('id , surname, forename, title')
			->from('#__thm_organizer_teachers')
			->order('forename, surname ASC');

		// EXACT => requires a forename and surname match

		if (count($this->terms) >= 2)
		{
			$wherray    = [];
			$innerTerms = $this->terms;

			foreach ($this->terms AS $oKey => $outerTerm)
			{
				foreach ($innerTerms AS $iKey => $innerTerm)
				{
					if ($outerTerm == $innerTerm)
					{
						unset($innerTerms[$iKey]);
						continue;
					}

					// lnf/fnf
					$wherray[] = "(surname LIKE '%$outerTerm%' AND forename LIKE '%$innerTerm%')";
					$wherray[] = "(surname LIKE '%$innerTerm%' AND forename LIKE '%$outerTerm%')";
				}
			}

			$this->addInclusiveConditions($query, $wherray);
			$this->_db->setQuery($query);

			try
			{
				$eTeachers = $this->_db->loadAssocList();
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

				return;
			}

			$this->results['exact']['teachers'] = $this->processTeachers($eTeachers);
		}

		// Strong

		$query->clear('where');
		$wherray = [];

		foreach ($this->terms AS $term)
		{
			// lnf/fnf
			$wherray[] = "surname LIKE '$term'";
		}

		$this->addInclusiveConditions($query, $wherray);
		$this->_db->setQuery($query);

		try
		{
			$sTeachers = $this->_db->loadAssocList();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return;
		}

		$this->results['strong']['teachers'] = $this->processTeachers($sTeachers);

		// Good

		$query->clear('where');
		$wherray = [];

		foreach ($this->terms AS $term)
		{
			// lnf/fnf
			$wherray[] = "surname LIKE '%$term%' OR forename LIKE '%$term%'";
		}

		$this->addInclusiveConditions($query, $wherray);
		$this->_db->setQuery($query);

		try
		{
			$gTeachers = $this->_db->loadAssocList();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

			return;
		}

		$this->results['good']['teachers'] = $this->processTeachers($gTeachers);
	}

	/**
	 * Finds programs which can be associated with the terms. Possible return strengths exact and strong.
	 *
	 * @return void sets the object attribute programResults
	 */
	private function setPrograms()
	{
		// Clone for editing. First 'term' (full search) irrelevant here.
		$terms = $this->terms;
		unset($terms[0]);

		foreach ($terms AS $index => $term)
		{
			$terms[$index] = str_replace('.', '', $term);
		}

		$programResults = [];
		$degrees        = $this->getDegrees($terms);

		$ePWherray  = [];
		$sPWherray  = [];
		$ePPWherray = [];
		$sPPWherray = [];

		foreach ($terms AS $term)
		{
			$ePWherray[] = "p.name_de LIKE '$term$' OR p.name_en LIKE '$term%'";
			$sPWherray[] = "p.name_de LIKE '%$term%' OR p.name_en LIKE '%$term%'";

			// Plan program degrees have to be resolved by string comparison
			$ePPWherray[] = "REPLACE(LCASE(pp.name), '.', '') LIKE '$term%'";
			$sPPWherray[] = "REPLACE(LCASE(pp.name), '.', '') LIKE '%$term%'";
		}

		$pQuery = $this->_db->getQuery(true);
		$pQuery->select("p.id, name_{$this->languageTag} AS name, degreeID, pp.id AS ppID, lft, rgt")
			->from('#__thm_organizer_programs AS p')
			->innerJoin('#__thm_organizer_mappings AS m ON m.programID = p.ID')
			->leftJoin('#__thm_organizer_plan_programs AS pp ON pp.programID = p.ID');

		// Plan programs have to be found in strings => standardized name as extra temp variable for comparison
		$ppQuery = $this->_db->getQuery(true);
		$ppQuery->select("p.id, name_{$this->languageTag} AS name, degreeID, pp.id AS ppID, lft, rgt")
			->from('#__thm_organizer_plan_programs AS pp')
			->leftJoin('#__thm_organizer_programs AS p ON pp.programID = p.ID')
			->leftJoin('#__thm_organizer_mappings AS m ON m.programID = p.ID');

		// Exact => program name and degree
		if (!empty($degrees['exact']))
		{
			$degreeIDs = array_keys($degrees['exact']);
			$pQuery->where("p.degreeID IN ('" . implode("','", $degreeIDs) . "')");
			$this->addInclusiveConditions($pQuery, $ePWherray);

			$degreeWherray = [];
			$this->addInclusiveConditions($ppQuery, $ePPWherray);

			foreach ($degrees['exact'] as $degree)
			{
				$degreeWherray[] = "REPLACE(LCASE(pp.name), '.', '') LIKE '%{$degree['stdAbbr']}%'";
			}

			$this->addInclusiveConditions($ppQuery, $degreeWherray);

			try
			{
				$this->_db->setQuery($ppQuery);
				$planPrograms = $this->_db->loadAssocList();
				$this->_db->setQuery($pQuery);
				$programs = $this->_db->loadAssocList();
			}
			catch (Exception $exception)
			{
				JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

				return [];
			}

			$programResults['exact'] = $this->processPrograms($programs, $planPrograms);
		}

		// Strong => Degree exact and program similar.
		if (!empty($degrees['exact']))
		{
			// No plan program checks here.
			$planPrograms = null;

			$pQuery->clear('where');
			$degreeIDs = array_keys($degrees['exact']);
			$pQuery->where("p.degreeID IN ('" . implode("','", $degreeIDs) . "')");
			$this->addInclusiveConditions($pQuery, $sPWherray);

			try
			{
				$this->_db->setQuery($pQuery);
				$programs = $this->_db->loadAssocList();
			}
			catch (Exception $exception)
			{
				JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

				return [];
			}

			$programResults['strong'] = $this->processPrograms($programs, $planPrograms);
		}

		// Good => Degree strong and program similar

		if (!empty($degrees['strong']))
		{
			$pQuery->clear('where');
			$degreeIDs = array_keys($degrees['strong']);
			$pQuery->where("p.degreeID IN ('" . implode("','", $degreeIDs) . "')");
			$this->addInclusiveConditions($pQuery, $ePWherray);

			$ppQuery->clear('where');
			$degreeWherray = [];
			$this->addInclusiveConditions($ppQuery, $ePPWherray);

			foreach ($degrees['strong'] as $degree)
			{
				$degreeWherray[] = "REPLACE(LCASE(pp.name), '.', '') LIKE '%{$degree['stdAbbr']}%'";
			}

			$this->addInclusiveConditions($ppQuery, $degreeWherray);

			try
			{
				$this->_db->setQuery($pQuery);
				$programs = $this->_db->loadAssocList();
				$this->_db->setQuery($ppQuery);
				$planPrograms = $this->_db->loadAssocList();
			}
			catch (Exception $exception)
			{
				JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

				return [];
			}

			$programResults['good'] = $this->processPrograms($programs, $planPrograms);
		}

		$this->programResults = $programResults;
	}

	/**
	 * Set the search terms.
	 *
	 * @param string $rawSearch the raw string from the request
	 *
	 * @return void sets the $terms property
	 */
	private function setTerms($rawSearch)
	{
		$prohibited     = ['\\', '\'', '"', '%', '_', '(', ')'];
		$safeSearch     = str_replace($prohibited, '', $rawSearch);
		$standardSearch = strtolower($safeSearch);

		// Remove English and German ordinals
		$standardSearch = preg_replace("/(.*[1-9])(?:\.|st|nd|rd|th)(.*)/", "$1$2", $standardSearch);

		// Filter out semester terms so that both the number and the word semster are one term.
		preg_match_all("/[1-9] (semester|sem)/", $standardSearch, $semesters);

		$this->terms = [];

		// Remove the semester terms from the search and add them to the terms
		if (!empty($semesters))
		{
			foreach ($semesters[0] as $semester)
			{
				$this->terms[]  = $semester;
				$standardSearch = str_replace($semester, '', $standardSearch);
			}
		}

		// Add the original search to the beginning of the array
		array_unshift($this->terms, $standardSearch);

		$remainingTerms = explode(' ', $standardSearch);

		$whiteNoise = ['der', 'die', 'das', 'den', 'dem', 'des',
		               'einer', 'eine', 'ein', 'einen', 'einem', 'eines',
		               'und', 'the', 'a', 'and', 'oder', 'or',
		               'aus', 'von', 'of', 'from',
		];

		foreach ($remainingTerms as $term)
		{
			$isWhiteNoise   = in_array($term, $whiteNoise);
			$isSingleLetter = (!is_numeric($term) AND strlen($term) < 2);

			if ($isWhiteNoise OR $isSingleLetter)
			{
				continue;
			}

			$this->terms[] = $term;
		}

		// Remove non-unique terms to prevent bloated queries
		$this->terms = array_unique($this->terms);
	}

	/**
	 * Function used as a call back for sorting results by their names.
	 *
	 * @param array $itemOne the first item
	 * @param array $itemTwo the second item
	 *
	 * @return bool true if the text for the first item should come after the second item, otherwise false
	 */
	private function sortItems($itemOne, $itemTwo)
	{
		return $itemOne['text'] > $itemTwo['text'];
	}
}
