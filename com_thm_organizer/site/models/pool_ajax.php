<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool_Ajax
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/pools.php';

/**
 * Class provides methods to retrieve data for pool ajax calls
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool_Ajax extends JModelLegacy
{
	/**
	 * Retrieves pool options for a given curriculum element
	 *
	 * @return  string
	 */
	public function parentOptions()
	{
		$input          = JFactory::getApplication()->input;
		$resourceID     = $input->getInt('id', 0);
		$resourceType   = $input->getString('type', '');
		$programIDs     = explode(',', $input->getString('programIDs', ''));
		$programEntries = $this->getProgramEntries($programIDs);
		$options        = array();
		$options[]      = '<option value="-1">' . JText::_('JNONE') . '</option>';

		$invalidRequest = (empty($resourceID) OR empty($resourceType));
		$none           = ($invalidRequest OR empty($programEntries));
		if ($none)
		{
			return $options[0];
		}

		$programMappings     = THM_OrganizerHelperMapping::getProgramMappings($programEntries);
		$onlyProgramMappings = count($programEntries) == count($programMappings);
		if ($onlyProgramMappings AND $resourceType == 'subject')
		{
			return $options[0];
		}

		$mappings = $mappingIDs = $parentIDs = array();
		THM_OrganizerHelperMapping::setMappingData($resourceID, $resourceType, $mappings, $mappingIDs, $parentIDs);
		$unSelectableMappings = $this->getUnselectableMappings($mappings, $mappingIDs, $resourceType);
		$this->fillOptions($options, $programMappings, $unSelectableMappings, $parentIDs, $resourceType);

		return implode('', $options);
	}

	/**
	 * Gets the pool options as a string
	 *
	 * @param bool $short whether or not the options should use abbreviated names
	 *
	 * @return string the concatenated plan pool options
	 */
	public function getPlanOptions($short = false)
	{
		$planOptions = THM_OrganizerHelperPools::getPlanPools($short);

		return json_encode($planOptions);
	}

	/**
	 * Retrieves the mappings of superordinate programs
	 *
	 * @param array $programIDs the requested program ids
	 *
	 * @return  array  the superordinate program mappings
	 */
	private function getProgramEntries($programIDs)
	{
		$query = $this->_db->getQuery(true);
		$query->select('id, programID, lft, rgt');
		$query->from('#__thm_organizer_mappings');
		$query->where("programID IN ( '" . implode("', '", $programIDs) . "' )");
		$query->order('lft ASC');
		$this->_db->setQuery((string) $query);

		try
		{
			return $this->_db->loadAssocList();
		}
		catch (Exception $exc)
		{
			return array();
		}
	}

	/**
	 * Retrieves an array of mappings which should not be available for selection
	 * as the parent of the resource
	 *
	 * @param array  &$mappings    the existing mappings of the resource
	 * @param array  &$mappingIDs  the mapping ids for the resource
	 * @param string $resourceType the resource's type
	 *
	 * @return  array  the ids which should be unselectable
	 */
	private function getUnselectableMappings(&$mappings, &$mappingIDs, $resourceType)
	{
		if ($resourceType == 'subject')
		{
			return array();
		}

		$children = THM_OrganizerHelperMapping::getChildren($mappings);

		return array_merge($mappingIDs, $children);
	}

	/**
	 * Determines whether association options should be offered
	 *
	 * @param array   &$programMappings     the program mappings retrieved
	 * @param array   &$programIDArray      the requested program ids
	 * @param boolean $isSubject            whether or not the request was sent
	 *                                      from the subject edit view
	 *
	 * @return  boolean  true if association options should be offered, otherwise
	 *                   false
	 */
	private function offerOptions(&$programMappings, &$programIDArray, $isSubject)
	{
		// No valid mappings
		if (empty($programMappings))
		{
			return false;
		}

		// If there are only program mappings, subjects cannot be mapped
		if (count($programIDArray) == count($programMappings) AND $isSubject)
		{
			return false;
		}

		return true;
	}

	/**
	 * Fills the options array with HTML pool options
	 *
	 * @param array   &$options             an array to store the options in
	 * @param array   &$programMappings     mappings belonging to one of the
	 *                                      requested programs
	 * @param array   &$unelectableMappings mappings which would lead to data
	 *                                      inconsistency
	 * @param array   &$parentIDs           previously mapped parents
	 * @param boolean $resourceType         the resource's type
	 *
	 * @return  void
	 */
	private function fillOptions(&$options, &$programMappings, &$unelectableMappings, &$parentIDs, $resourceType)
	{
		foreach ($programMappings as $mapping)
		{
			if (!empty($mapping['subjectID'])
				OR (!empty($unelectableMappings) AND in_array($mapping['id'], $unelectableMappings))
			)
			{
				continue;
			}

			if (!empty($mapping['poolID']))
			{
				$options[] = THM_OrganizerHelperMapping::getPoolOption($mapping, $parentIDs);
			}
			else
			{
				$options[] = THM_OrganizerHelperMapping::getProgramOption($mapping, $parentIDs, $resourceType);
			}
		}
	}

	/**
	 * Retrieves pool entries from the database based upon selected program and
	 * teacher
	 *
	 * @return  string  the subjects which fit the selected resource
	 */
	public function poolsByProgramOrTeacher()
	{
		$input           = JFactory::getApplication()->input;
		$selectedProgram = $input->getInt('programID', 0);
		if (empty($selectedProgram) OR $selectedProgram == '-1')
		{
			return '[]';
		}

		$programBounds  = THM_OrganizerHelperMapping::getBoundaries('program', $selectedProgram);
		$teacherClauses = THM_OrganizerHelperMapping::getTeacherMappingClauses();

		if (empty($programBounds))
		{
			return '[]';
		}

		$lang  = JFactory::getApplication()->input->getString('languageTag', 'de');
		$query = $this->_db->getQuery(true);
		$query->select("p.id, p.name_{$lang} AS name, m.level");
		$query->from('#__thm_organizer_pools AS p');
		$query->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
		if (!empty($programBounds[0]))
		{
			$query->where("m.lft >= '{$programBounds[0]['lft']}'");
			$query->where("m.rgt <= '{$programBounds[0]['rgt']}'");
		}

		if (!empty($teacherClauses))
		{
			$query->where("( ( " . implode(') OR (', $teacherClauses) . ") )");
		}

		$query->order('lft');
		$this->_db->setQuery((string) $query);
		try
		{
			$pools = $this->_db->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '[]';
		}

		if (empty($pools))
		{
			return '[]';
		}

		foreach ($pools AS $key => $value)
		{
			$pools[$key]->name = THM_OrganizerHelperMapping::getIndentedPoolName($value->name, $value->level, false);
		}

		return json_encode($pools);
	}
}
