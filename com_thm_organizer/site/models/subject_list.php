<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSubject_List
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_COMPONENT . '/helpers/teacher.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class creates a model
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSubject_List extends JModelList
{
	public $fields = [];

	public $pools = [];

	public $subjects;

	public $teachers = [];

	/**
	 * Removes
	 * @return void
	 */
	private function aggregateSubjects()
	{
		$subjectIDMap = [];

		foreach ($this->subjects as $key => $subject)
		{
			if (empty($subjectIDMap[$subject->id]))
			{
				$subjectIDMap[$subject->id]       = $key;
				$this->subjects[$key]->mappings   = [];
				$this->subjects[$key]->mappings[] = ['left' => $subject->lft, 'right' => $subject->rgt];
				continue;
			}

			$subjectKey                              = $subjectIDMap[$subject->id];
			$this->subjects[$subjectKey]->mappings[] = ['left' => $subject->lft, 'right' => $subject->rgt];

			unset($this->subjects[$key]);
		}
	}

	/**
	 * Retrieves the pool's children and used the existing sorting while associating them with the pool.
	 *
	 * @return void
	 */
	private function getChildren()
	{
		foreach ($this->pools as $key => $pool)
		{
			$query = $this->_db->getQuery(true);
			$query->select('DISTINCT poolID, subjectID')->from('#__thm_organizer_mappings')->where("parentID = '{$pool['mapID']}'");
			$this->_db->setQuery($query);

			try
			{
				$children = $this->_db->loadAssocList();
			}
			catch (Exception $exc)
			{
				return;
			}

			$this->pools[$key]['pools']    = [];
			$this->pools[$key]['subjects'] = [];

			foreach ($children as $child)
			{
				if (!empty($child['subjectID']))
				{
					$subjectKey = $this->getSubjectKey($child['subjectID']);

					if ($subjectKey !== false)
					{
						$this->pools[$key]['subjects'][$subjectKey] = $child['subjectID'];
					}
				}

				if (!empty($child['poolID']))
				{
					$poolKey = $this->getPoolKey($child['poolID']);

					if ($poolKey)
					{
						$this->pools[$key]['pools'][$poolKey] = $child['poolID'];
					}
				}
			}

			uasort($this->pools[$key]['pools'], function ($a, $b) {
				$aKey = $this->getPoolKey($a);
				$bKey = $this->getPoolKey($b);

				// Php sorts letters with umlauts to the end of the alphabet, this replaces them with their equivalent
				$aName = iconv("utf-8", "ascii//TRANSLIT", $this->pools[$aKey]['name']);
				$bName = iconv("utf-8", "ascii//TRANSLIT", $this->pools[$bKey]['name']);

				return $aName > $bName;
			});

			ksort($this->pools[$key]['subjects']);
		}
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$this->subjects = parent::getItems();
		$this->aggregateSubjects();

		foreach ($this->subjects AS $index => $subject)
		{
			if (!empty($subject->subjectColor))
			{
				$this->subjects[$index]->textColor = THM_OrganizerHelperComponent::getTextColor($subject->subjectColor);
			}

			if (empty($subject->fieldID))
			{
				$this->fields[0] = [];
			}
			else
			{
				$this->fields[$subject->fieldID] = [];
			}

			$this->getTeachers($index);
			$this->getPools($index);
		}

		uasort($this->teachers, function ($a, $b) {
			if ($a['surname'] == $b['surname'])
			{
				return $a['forename'] > $b['forename'];
			}

			return $a['surname'] > $b['surname'];
		});

		uasort($this->pools, function ($a, $b) {
			$isAChild = $this->isChildPool($a);
			$isBChild = $this->isChildPool($b);

			// Child pools should come after normal pools
			if ($isAChild AND !$isBChild)
			{
				return true;
			}
			if ($isBChild AND !$isAChild)
			{
				return false;
			}

			$moveBack = $a['lft'] > $b['lft'];

			return $moveBack;
		});

		$this->getChildren();
		$this->populateFields();

		return $this->subjects;
	}

	/**
	 * Method to cache the last query constructed.
	 *
	 * This method ensures that the query is constructed only once for a given state of the model.
	 *
	 * @return  object  a JDatabaseQuery object
	 */
	protected function getListQuery()
	{
		$programInformation = $this->getProgramInformation();
		if (empty($programInformation))
		{
			return $this->_db->getQuery(true);
		}

		$menuID      = $this->state->get('menuID');
		$languageTag = $this->state->get('languageTag');
		$subjectLink = "'index.php?option=com_thm_organizer&view=subject_details&languageTag=$languageTag&Itemid=$menuID&id='";

		$query = $this->_db->getQuery(true);

		$select = "DISTINCT s.id, s.name_$languageTag AS name, s.creditpoints, s.externalID, s.fieldID, m.lft, m.rgt, ";
		$parts  = ["$subjectLink", "s.id"];
		$select .= $query->concatenate($parts, "") . " AS subjectLink";
		$query->select($select)
			->from('#__thm_organizer_subjects AS s')
			->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = s.id')
			->where("m.lft > '{$programInformation['lft']}' AND  m.rgt < '{$programInformation['rgt']}'");

		$this->setSearch($query);

		$query->order('name ASC');

		return $query;
	}

	/**
	 * Retrieves program information (name and nesting values)
	 *
	 * @return  mixed  array on success, otherwise false
	 */
	private function getProgramInformation()
	{
		$programID   = $this->state->get('programID');
		$languageTag = $this->state->get('languageTag');

		$query = $this->_db->getQuery(true);
		$parts = ["p.name_$languageTag", "' ('", "d.abbreviation", "' '", "p.version", "')'"];
		$query->select($query->concatenate($parts, "") . " AS name, lft, rgt");
		$query->from('#__thm_organizer_programs AS p');
		$query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
		$query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = p.id');
		$query->where("p.id = '$programID'");
		$this->_db->setQuery($query);

		try
		{
			$programData       = $this->_db->loadAssoc();
			$this->programName = $programData['name'];
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return [];
		}

		return $programData;
	}

	/**
	 * Resolves the pool id to the corresponding pool key
	 *
	 * @param int $poolID the id of the pool
	 *
	 * @return mixed int if the id could be resolved to a key, otherwise false
	 */
	private function getPoolKey($poolID)
	{
		foreach ($this->pools as $key => $pool)
		{
			if ($pool['id'] == $poolID)
			{
				return $key;
			}
		}

		return false;
	}

	/**
	 * Looks up the pools associated with the subject, adds the associations to the subjects, sets object pools
	 * and adds pool fields.
	 *
	 * @param int $index the index of the subject (item) being currently indexed
	 *
	 * @return void
	 */
	private function getPools($index)
	{
		$languageTag          = $this->state->get('languageTag');
		$poolEntriesContainer = [];

		foreach ($this->subjects[$index]->mappings as $mapping)
		{
			$query = $this->_db->getQuery(true);
			$query->select("p.id, p.name_$languageTag AS name, p.minCrP, p.maxCrP, p.fieldID, m.id AS mapID, m.rgt, m.lft, m.level")
				->from('#__thm_organizer_pools AS p')
				->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id')
				->where("(m.lft < '{$mapping['left']}' AND m.rgt > '{$mapping['right']}')")
				->order('m.lft');
			$this->_db->setQuery($query);

			try
			{
				$poolEntries = $this->_db->loadAssocList();
			}
			catch (Exception $exc)
			{
				return;
			}

			$poolEntriesContainer = array_merge($poolEntriesContainer, $poolEntries);
		}

		if (empty($poolEntriesContainer))
		{
			return;
		}

		foreach ($poolEntriesContainer as $poolEntry)
		{
			if (empty($this->pools[$poolEntry['id']]))
			{
				$pool            = [];
				$pool['id']      = $poolEntry['id'];
				$pool['name']    = $poolEntry['name'];
				$pool['minCrP']  = empty($poolEntry['minCrP']) ? '' : $poolEntry['minCrP'];
				$pool['maxCrP']  = empty($poolEntry['maxCrP']) ? '' : $poolEntry['maxCrP'];
				$pool['fieldID'] = empty($poolEntry['fieldID']) ? null : $poolEntry['fieldID'];
				$pool['mapID']   = $poolEntry['mapID'];
				$pool['lft']     = $poolEntry['lft'];
				$pool['rgt']     = $poolEntry['rgt'];

				$this->pools[$poolEntry['id']] = $pool;
			}
			elseif ($poolEntry['lft'] > $this->pools[$poolEntry['id']]['lft'])
			{
				$this->pools[$poolEntry['id']]['mapID'] = $poolEntry['mapID'];
				$this->pools[$poolEntry['id']]['lft']   = $poolEntry['lft'];
				$this->pools[$poolEntry['id']]['rgt']   = $poolEntry['rgt'];
			}

			if (empty($pool['fieldID']))
			{
				$this->fields[0] = [];
			}
			else
			{
				$this->fields[$pool['fieldID']] = [];
			}
		}
	}

	/**
	 * Resolves the subject id to the corresponding subject key
	 *
	 * @param int $subjectID the id of the subject
	 *
	 * @return mixed int if the id could be resolved to a key, otherwise false
	 */
	private function getSubjectKey($subjectID)
	{
		foreach ($this->subjects as $key => $subject)
		{
			if ($subject->id == $subjectID)
			{
				return $key;
			}
		}

		return false;
	}

	/**
	 * Looks up the teachers associated with the subject, adds the associations to the subjects, sets object teachers
	 * and adds teacher fields.
	 *
	 * @param int $index the index of the subject (item) being currently indexed
	 *
	 * @return void
	 */
	private function getTeachers($index)
	{
		$subjectID = $this->subjects[$index]->id;

		$query = $this->_db->getQuery(true);
		$query->select("t.id, t.surname, t.forename, t.fieldID, t.title, st.teacherResp")
			->from('#__thm_organizer_teachers AS t')
			->innerJoin('#__thm_organizer_subject_teachers AS st ON st.teacherID = t.id')
			->where("st.subjectID = '$subjectID'");
		$this->_db->setQuery($query);

		try
		{
			$teachers = $this->_db->loadAssocList();
		}
		catch (Exception $exc)
		{
			return;
		}

		if (empty($teachers))
		{
			return;
		}

		foreach ($teachers as $teacherEntry)
		{
			if (empty($this->teachers[$teacherEntry['id']]))
			{
				$teacher             = [];
				$teacher['id']       = $teacherEntry['id'];
				$teacher['surname']  = $teacherEntry['surname'];
				$teacher['forename'] = empty($teacherEntry['forename']) ? '' : $teacherEntry['forename'];
				$teacher['title']    = empty($teacherEntry['title']) ? '' : $teacherEntry['title'];
				$teacher['fieldID']  = empty($teacherEntry['fieldID']) ? null : $teacherEntry['fieldID'];

				$this->teachers[$teacherEntry['id']] = $teacher;
			}

			if (empty($this->subjects[$index]->teachers))
			{
				$this->subjects[$index]->teachers = [];
			}

			if (empty($this->subjects[$index]->teachers[$teacherEntry['teacherResp']]))
			{
				$this->subjects[$index]->teachers[$teacherEntry['teacherResp']] = [];
			}

			$this->subjects[$index]->teachers[$teacherEntry['teacherResp']][$teacherEntry['id']] = $teacherEntry['id'];

			if (empty($teacherEntry['fieldID']))
			{
				$this->fields[0] = [];
			}
			else
			{
				$this->fields[$teacherEntry['fieldID']] = [];
			}
		}
	}

	/**
	 * Checks whether the pool being iterated is a child
	 *
	 * @param array $pool
	 *
	 * @return bool true if the pool is a child pool, otherwise false
	 */
	private function isChildPool($pool)
	{
		foreach ($this->pools as $check)
		{
			if ($check['lft'] < $pool['lft'] AND $check['rgt'] > $pool['rgt'])
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return void
	 */
	private function populateFields()
	{
		$languageTag = $this->state->get('languageTag');
		$fieldIDs    = "('" . implode("','", array_keys($this->fields)) . "')";

		$query = $this->_db->getQuery(true);
		$query->select("f.id, f.field_$languageTag AS name, c.color AS backgroundColor")
			->from('#__thm_organizer_fields AS f')
			->leftJoin('#__thm_organizer_colors AS c ON c.id = f.colorID')
			->where("f.id IN $fieldIDs");
		$this->_db->setQuery($query);

		try
		{
			$fields = $this->_db->loadAssocList('id');
		}
		catch (Exception $exc)
		{
			return;
		}

		if (empty($fields))
		{
			return;
		}

		$params = JComponentHelper::getParams('com_thm_organizer');

		foreach ($fields as $fieldEntry)
		{
			if (empty($this->fields[$fieldEntry['id']]))
			{
				$field         = [];
				$field['name'] = $fieldEntry['name'];

				if (empty($fieldEntry['backgroundColor']))
				{
					$field['backgroundColor'] = $params['backgroundColor'];
					$field['textColor']       = $params['darkTextColor'];
				}
				else
				{
					$field['backgroundColor'] = $fieldEntry['backgroundColor'];
					$field['textColor']       = THM_OrganizerHelperComponent::getTextColor($field['backgroundColor']);
				}
				$this->fields[$fieldEntry['id']] = $field;
			}
		}

		// One or more items is not associated with a field
		if (isset($this->fields[0]))
		{
			$defaultField                    = [];
			$defaultField['name']            = JText::_('COM_THM_ORGANIZER_UNASSOCIATED');
			$defaultField['backgroundColor'] = $params['backgroundColor'];
			$defaultField['textColor']       = $params['darkTextColor'];
			$this->fields[0]                 = $defaultField;
		}

		uasort($this->fields, function ($a, $b) {
			return $a['name'] > $b['name'];
		});
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param string $ordering  An optional ordering field.
	 * @param string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$app = JFactory::getApplication();

		$menuID = $app->getUserStateFromRequest($this->context . '.menuID', 'Itemid');
		$this->state->set('menuID', $menuID);

		$params = $app->getMenu()->getItem($menuID)->params;

		$programID = $params->get('programID');
		$this->state->set('programID', $programID);

		$search = $app->input->get('search', '');
		$this->state->set('search', $search);

		$app->set('list_limit', '0');
		$limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '0');
		$this->state->set('list.limit', $limit);

		$menuLanguage = $params->get('initialLanguage', 'de');
		$languageTag  = $app->getUserStateFromRequest($this->context . '.languageTag', 'languageTag', $menuLanguage);
		$this->state->set('languageTag', $languageTag);

		$menuGroupBy = $params->get('groupBy');
		$groupBy     = $app->getUserStateFromRequest($this->context . '.groupBy', 'groupBy', $menuGroupBy);
		$this->state->set('groupBy', $groupBy);
	}

	/**
	 * Builds the search clause based upon user input
	 *
	 * @param object &$query the query object upon which search conditions will be set
	 *
	 * @return  void modifies the query
	 */
	private function setSearch(&$query)
	{
		$search = '%' . $this->_db->escape($this->state->get('search', ''), true) . '%';

		if ($search == '%%')
		{
			return;
		}

		$where = "(s.name_de LIKE '$search' OR s.name_en LIKE '$search' OR ";
		$where .= "s.short_name_de LIKE '$search' OR s.short_name_en LIKE '$search' OR ";
		$where .= "s.abbreviation_de LIKE '$search' OR s.abbreviation_en LIKE '$search' OR ";
		$where .= "s.externalID LIKE '$search')";
		$query->where($where);
	}
}
