<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Organizer\Helpers\Input;
use Organizer\Helpers\LSF;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Tables;
use Organizer\Tables\Mappings;

/**
 * Class which manages stored (curriculum) mapping data.
 */
class Mapping extends BaseModel
{
	/**
	 * Adds mappings as they exist in LSF for an imported degree program
	 *
	 * @param   int              $programID  the id of the program in the local
	 *                                       database
	 * @param   SimpleXMLObject &$lsfData    the data received from the LSF system
	 *
	 * @return boolean  true if the data was mapped, otherwise false
	 */
	public function addLSFMappings($programID, &$lsfData)
	{
		$table = new Mappings;

		if (!$table->load(['programID' => $programID]))
		{
			return false;
		}

		foreach ($lsfData->gruppe as $resource)
		{
			$type   = (string) $resource->pordtyp;
			$mapped = true;

			if ($type == 'M')
			{
				$mapped = $this->addLSFSubject($resource, $table->id);
			}
			elseif ($type == 'K')
			{
				$mapped = $this->addLSFPool($resource, $table->id);
			}

			if (!$mapped)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Adds a pool from LSF to the mappings table
	 *
	 * @param   object &$pool             the object representing the LSF pool
	 * @param   int     $parentMappingID  the id of the program mapping
	 *
	 * @return boolean  true if the pool is mapped, otherwise false
	 */
	private function addLSFPool(&$pool, $parentMappingID)
	{
		$lsfID = empty($pool->pordid) ? (string) $pool->modulid : (string) $pool->pordid;
		$blocked = !empty($pool->sperrmh) and strtolower((string) $pool->sperrmh) == 'x';
		$invalidTitle = LSF::invalidTitle($pool);
		$noChildren   = !isset($pool->modulliste->modul);
		$poolsTable   = new Tables\Pools;
		$poolExists   = $poolsTable->load(['lsfID' => $lsfID]);

		if ($poolExists)
		{
			if ($blocked or $invalidTitle or $noChildren)
			{
				$poolModel = new Pool;

				return $poolModel->deleteSingle($poolsTable->id);
			}

			$mappingsTable = new Mappings;

			if (!$mappingsTable->load(['parentID' => $parentMappingID, 'poolID' => $poolsTable->id]))
			{
				$poolMapping              = [];
				$poolMapping['parentID']  = $parentMappingID;
				$poolMapping['poolID']    = $poolsTable->id;
				$poolMapping['subjectID'] = null;
				$poolMapping['ordering']  = $this->getOrdering($parentMappingID, $poolsTable->id);
				$poolAdded                = $this->addPool($poolMapping);
				if (!$poolAdded)
				{
					OrganizerHelper::message('ORGANIZER_POOL_ADD_FAIL', 'error');

					return false;
				}

				$mappingsTable->load(['parentID' => $parentMappingID, 'poolID' => $poolsTable->id]);
			}

			foreach ($pool->modulliste->modul as $sub)
			{
				$type   = (string) $sub->pordtyp;
				$mapped = true;

				if ($type == 'K')
				{
					$mapped = $this->addLSFPool($sub, $mappingsTable->id);
				}
				elseif ($type == 'M')
				{
					$mapped = $this->addLSFSubject($sub, $mappingsTable->id);
				}

				if (!$mapped)
				{
					return false;
				}
			}

			return true;
		}

		if ($blocked or $invalidTitle or $noChildren)
		{
			return true;
		}

		OrganizerHelper::message('ORGANIZER_POOL_MAPPING_FAIL', 'error');

		return false;
	}

	/**
	 * Adds a subject from LSF to the mappings table
	 *
	 * @param   object &$subject          the subject object
	 * @param   int     $parentMappingID  the id of the parent element in the
	 *                                    mappings table
	 *
	 * @return boolean  true if the mapping exists, otherwise false
	 */
	private function addLSFSubject(&$subject, $parentMappingID)
	{
		$lsfID = (string) (empty($subject->modulid) ? $subject->pordid : $subject->modulid);
		$blocked = !empty($subject->sperrmh) and strtolower((string) $subject->sperrmh) == 'x';
		$invalidTitle = LSF::invalidTitle($subject);

		$subjectsTable = new Tables\Subjects;

		if ($subjectsTable->load(['lsfID' => $lsfID]))
		{
			$mappingsTable = new Mappings;
			$mappingExists = $mappingsTable->load(['parentID' => $parentMappingID, 'subjectID' => $subjectsTable->id]);

			if ($mappingExists)
			{
				if ($blocked or $invalidTitle)
				{
					return $this->deleteEntry($mappingsTable->id);
				}

				return true;
			}

			$subjectMapping              = [];
			$subjectMapping['parentID']  = $parentMappingID;
			$subjectMapping['poolID']    = null;
			$subjectMapping['subjectID'] = $subjectsTable->id;
			$subjectMapping['ordering']  = $this->getOrdering($parentMappingID, $subjectsTable->id, 'subject');
			$subjectAdded                = $this->addSubject($subjectMapping);

			if (!$subjectAdded)
			{
				OrganizerHelper::message('ORGANIZER_SUBJECT_ADD_FAIL', 'error');

				return false;
			}

			return true;
		}

		if ($blocked or $invalidTitle)
		{
			return true;
		}

		OrganizerHelper::message('ORGANIZER_SUBJECT_MAP_FAIL', 'error');

		return false;
	}

	/**
	 * Adds a pool mapping to a parent mapping
	 *
	 * @param   array &$pool  an array containing data about a pool and its
	 *                        children
	 *
	 * @return bool  true on success, otherwise false
	 */
	private function addPool(&$pool)
	{
		$parent = $this->getParent($pool['parentID']);

		$pool['level'] = $parent['level'] + 1;

		$pool['lft'] = $this->determineLft($pool['parentID'], $pool['ordering']);
		if (empty($pool['lft']))
		{
			return false;
		}

		$pool['rgt'] = (string) ($pool['lft'] + 1);

		if (!$this->shiftRight($pool['lft']))
		{
			return false;
		}

		if (!$this->shiftOrder($pool['parentID'], $pool['ordering']))
		{
			return false;
		}

		$mapping = new Mappings;

		if ($mapping->save($pool))
		{
			if (!empty($pool['children']))
			{
				foreach ($pool['children'] as $child)
				{
					$child['parentID'] = $mapping->id;
					if (isset($child['poolID']))
					{
						if (!$this->addPool($child))
						{
							return false;
						}
					}
					elseif (isset($child['subjectID']))
					{
						if (!is_numeric($child['subjectID']))
						{
							continue;
						}

						$child['level'] = $pool['level'] + 1;
						$childAdded     = $this->addSubject($child);
						if (!$childAdded)
						{
							return false;
						}
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Addsa a subject mapping to the parent mapping
	 *
	 * @param   array &$subject  an array containing data about a subject
	 *
	 * @return boolean
	 */
	private function addSubject(&$subject)
	{
		$parent = $this->getParent($subject['parentID']);

		$subject['level'] = $parent['level'] + 1;

		$subject['lft'] = $this->determineLft($subject['parentID'], $subject['ordering']);
		if (empty($subject['lft']))
		{
			return false;
		}

		$subject['rgt'] = (string) ($subject['lft'] + 1);

		if (!$this->shiftRight($subject['lft']))
		{
			return false;
		}

		if (!$this->shiftOrder($subject['parentID'], $subject['ordering']))
		{
			return false;
		}

		$mapping = new Mappings;

		if ($mapping->save($subject))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks whether a mapping exists for the selected resource
	 *
	 * @param   int     $resourceID    the id of the resource
	 * @param   string  $resourceType  the type of the resource
	 *
	 * @return bool true if the resource has an existing mapping, otherwise false
	 */
	public function checkForMapping($resourceID, $resourceType)
	{
		$query = $this->_db->getQuery(true);
		$query->select('COUNT(*)')->from('#__thm_organizer_mappings')->where("{$resourceType}ID = '$resourceID'");
		$this->_db->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('loadResult');
	}

	/**
	 * Deletes mappings of a specific pool.
	 *
	 * @param   int     $resourceID  the id of the mapping
	 * @param   string  $type        the mapping's type
	 *
	 * @return boolean true on success, otherwise false
	 */
	public function deleteByResourceID($resourceID, $type)
	{
		if ($type != 'program' and $type != 'pool' and $type != 'subject')
		{
			return false;
		}

		$mappingIDsQuery = $this->_db->getQuery(true);
		$mappingIDsQuery->select('id')->from('#__thm_organizer_mappings');
		switch ($type)
		{
			case 'program':
				$mappingIDsQuery->where("programID = '$resourceID'");
				break;
			case 'pool':
				$mappingIDsQuery->where("poolID = '$resourceID'");
				break;
			case 'subject':
				$mappingIDsQuery->where("subjectID = '$resourceID'");
				break;
		}

		$this->_db->setQuery($mappingIDsQuery);
		$mappingIDs = OrganizerHelper::executeQuery('loadColumn', []);

		if (!empty($mappingIDs))
		{
			foreach ($mappingIDs as $mappingID)
			{
				$success = $this->deleteEntry($mappingID);
				if (!$success)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Deletes the children of a specific mapping.
	 *
	 * @param   int  $mappingID  the id of the mapping
	 *
	 * @return boolean true on success, otherwise false
	 */
	private function deleteChildren($mappingID)
	{
		$mappingIDsQuery = $this->_db->getQuery(true);
		$mappingIDsQuery->select('id')->from('#__thm_organizer_mappings')->where("parentID = '$mappingID'");
		$this->_db->setQuery($mappingIDsQuery);
		$mappingIDs = OrganizerHelper::executeQuery('loadColumn', []);

		if (!empty($mappingIDs))
		{
			foreach ($mappingIDs as $mappingID)
			{
				$success = $this->deleteEntry($mappingID);
				if (!$success)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to delete a single entry
	 *
	 * @param   int  $entryID  the id value of the entry to be deleted
	 *
	 * @return bool  true on success, otherwise false
	 */
	private function deleteEntry($entryID)
	{
		// Retrieves information about the current mapping including its total width
		$mappingQuery = $this->_db->getQuery(true);
		$mappingQuery->select('*, (rgt - lft + 1) AS width')
			->from('#__thm_organizer_mappings')
			->where("id = '$entryID'");
		$this->_db->setQuery($mappingQuery);
		$mapping = OrganizerHelper::executeQuery('loadAssoc', []);
		if (empty($mapping))
		{
			return false;
		}

		// Deletes the mapping
		$deleteQuery = $this->_db->getQuery(true);
		$deleteQuery->delete('#__thm_organizer_mappings')->where("id = '{$mapping['id']}'");
		$this->_db->setQuery($deleteQuery);
		$success = (bool) OrganizerHelper::executeQuery('execute');
		if (!$success)
		{
			return false;
		}

		// Reduces the ordering of siblings with a greater ordering
		$siblingsQuery = $this->_db->getQuery(true);
		$siblingsQuery->update('#__thm_organizer_mappings');
		$siblingsQuery->set('ordering = ordering - 1');
		$siblingsQuery->where("parentID = '{$mapping['parentID']}'");
		$siblingsQuery->where("ordering > '{$mapping['ordering']}'");
		$this->_db->setQuery($siblingsQuery);
		$success = (bool) OrganizerHelper::executeQuery('execute');
		if (!$success)
		{
			return false;
		}

		/**
		 *  Reduces lft values at or above the mapping's rgt value according to
		 *  the mapping's width
		 */
		$updateLeftQuery = $this->_db->getQuery(true);
		$updateLeftQuery->update('#__thm_organizer_mappings');
		$updateLeftQuery->set("lft = lft - {$mapping['width']}");
		$updateLeftQuery->where("lft > '{$mapping['lft']}'");
		$this->_db->setQuery($updateLeftQuery);
		$success = (bool) OrganizerHelper::executeQuery('execute');
		if (!$success)
		{
			return false;
		}

		/**
		 *  Reduces rgt values at or above the mapping's rgt value according to
		 *  the mapping's width
		 */
		$updateRightQuery = $this->_db->getQuery(true);
		$updateRightQuery->update('#__thm_organizer_mappings');
		$updateRightQuery->set("rgt = rgt - {$mapping['width']}");
		$updateRightQuery->where("rgt > '{$mapping['lft']}'");
		$this->_db->setQuery($updateRightQuery);

		return OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Attempt to determine the left value for the mapping to be created
	 *
	 * @param   int    $parentID  the parent of the item to be inserted
	 * @param   mixed  $ordering  the targeted ordering on completion
	 *
	 * @return mixed  int the left value for the mapping to be created, or
	 *                 or boolean false on db error.
	 */
	private function determineLft($parentID, $ordering)
	{
		// Right value of the next lowest sibling
		$rgtQuery = $this->_db->getQuery(true);
		$rgtQuery->select('MAX(rgt)')->from('#__thm_organizer_mappings');
		$rgtQuery->where("parentID = '$parentID'")->where("ordering < '$ordering'");
		$this->_db->setQuery($rgtQuery);
		$rgt = OrganizerHelper::executeQuery('loadResult');
		if (!empty($rgt))
		{
			return $rgt + 1;
		}

		// No siblings => use parent left for reference
		$lftQuery = $this->_db->getQuery(true);
		$lftQuery->select('lft')->from('#__thm_organizer_mappings');
		$lftQuery->where("id = '$parentID'");
		$this->_db->setQuery($lftQuery);
		$lft = OrganizerHelper::executeQuery('loadResult');

		return empty($lft) ? false : $lft + 1;
	}

	/**
	 * Retrieves child mappings for a given pool
	 *
	 * @param   int     $resourceID  the resource id
	 * @param   string  $type        the resource id (defaults: pool)
	 * @param   bool    $deep        if the function should be used to find
	 *                               children iteratively or not (default: false)
	 *
	 * @return array  empty if no child data exists
	 */
	private function getChildren($resourceID, $type = 'pool', $deep = true)
	{
		$children = [];

		/**
		 * Subordinate structures are the same for every parent mapping,
		 * therefore only the first mapping needs to be found
		 */
		$existingQuery = $this->_db->getQuery(true);
		$existingQuery->select('id')->from('#__thm_organizer_mappings');
		$existingQuery->where("{$type}ID = '$resourceID'");
		$this->_db->setQuery($existingQuery, 0, 1);
		$firstID = OrganizerHelper::executeQuery('loadResult');

		if (!empty($firstID))
		{
			$childrenQuery = $this->_db->getQuery(true);
			$childrenQuery->select('poolID, subjectID, ordering');
			$childrenQuery->from('#__thm_organizer_mappings');
			$childrenQuery->where("parentID = '$firstID'");
			$childrenQuery->order('lft ASC');
			$this->_db->setQuery($childrenQuery);

			$results = OrganizerHelper::executeQuery('loadAssocList');

			if (!empty($results))
			{
				$children = $results;
				if ($deep)
				{
					foreach ($children as $key => $child)
					{
						if (!empty($child['poolID']))
						{
							$children[$key]['children'] = $this->getChildren($child['poolID']);
						}
					}
				}
			}
		}

		return $children;
	}

	/**
	 * Filters the resource's children out of the form's POST data
	 *
	 * @return array  an array containing the resource's children and ordering
	 */
	private function getChildrenFromForm()
	{
		$index    = 1;
		$children = [];
		while (Input::getInt("child{$index}Order"))
		{
			$ordering      = Input::getInt("child{$index}Order");
			$aggregateInfo = Input::getCMD("child{$index}");

			if (!empty($aggregateInfo))
			{
				$resourceID   = substr($aggregateInfo, 0, strlen($aggregateInfo) - 1);
				$resourceType = strpos($aggregateInfo, 'p') ? 'pool' : 'subject';

				if ($resourceType == 'subject')
				{
					$children[$ordering]['poolID']    = null;
					$children[$ordering]['subjectID'] = $resourceID;
					$children[$ordering]['ordering']  = $ordering;
				}

				if ($resourceType == 'pool')
				{
					$children[$ordering]['poolID']    = $resourceID;
					$children[$ordering]['subjectID'] = null;
					$children[$ordering]['ordering']  = $ordering;
					$children[$ordering]['children']  = $this->getChildren($resourceID);
				}
			}

			$index++;
		}

		return $children;
	}

	/**
	 * Retrieves the existing ordering of a pool to its parent item, or the
	 * value 'last'
	 *
	 * @param   int     $parentID    the id of the parent mapping
	 * @param   int     $resourceID  the id of the resource
	 * @param   string  $type        the type of resource being ordered
	 *
	 * @return int  the value of the highest existing ordering or 1 if none exist
	 */
	private function getOrdering($parentID, $resourceID, $type = 'pool')
	{
		// Check for an existing ordering as child of the parent element
		$existingOrderQuery = $this->_db->getQuery(true);
		$existingOrderQuery->select('ordering')->from('#__thm_organizer_mappings');
		$existingOrderQuery->where("parentID = '$parentID'");
		if ($type == 'subject')
		{
			$existingOrderQuery->where("subjectID = '$resourceID'");
		}
		else
		{
			$existingOrderQuery->where("poolID = '$resourceID'");
		}

		$this->_db->setQuery($existingOrderQuery);
		$existingOrder = OrganizerHelper::executeQuery('loadResult');

		if (!empty($existingOrder))
		{
			return $existingOrder;
		}

		/**
		 *  No order exists for parent element order is then either one more
		 *  the existing max value, or 1 if no children exist
		 */
		$maxOrderQuery = $this->_db->getQuery(true);
		$maxOrderQuery->select('MAX(ordering)')->from('#__thm_organizer_mappings')->where("parentID = '$parentID'");
		$this->_db->setQuery($maxOrderQuery);
		$maxOrder = OrganizerHelper::executeQuery('loadResult');

		return empty($maxOrder) ? 1 : $maxOrder + 1;
	}

	/**
	 * Retrieves parent data
	 *
	 * @param   int  $parentID  the id of the parent item
	 *
	 * @return array  the parent mapping
	 */
	private function getParent($parentID)
	{
		$parentQuery = $this->_db->getQuery(true);
		$parentQuery->select('*')->from('#__thm_organizer_mappings')->where("id = '$parentID'");
		$this->_db->setQuery($parentQuery);

		return OrganizerHelper::executeQuery('loadAssoc', []);
	}

	/**
	 * Saves pool and dependent mappings
	 *
	 * @param   array &$data  the pool form data from the post request
	 *
	 * @return boolean  true on success, otherwise false
	 */
	public function savePool(&$data)
	{
		$poolData                   = [];
		$poolData['programID']      = null;
		$poolData['poolID']         = $data['id'];
		$poolData['subjectID']      = null;
		$poolData['description_de'] = $data['description_de'];
		$poolData['description_en'] = $data['description_en'];
		$poolData['children']       = $this->getChildrenFromForm();

		$parentIDs = $data['parentID'];
		$orderings = [];
		foreach ($parentIDs as $parentID)
		{
			$orderings[$parentID] = $this->getOrdering($parentID, $poolData['poolID']);
		}

		$cleanSlate = $this->deleteByResourceID($poolData['poolID'], 'pool');
		if ($cleanSlate)
		{
			foreach ($parentIDs as $parentID)
			{
				$poolData['parentID'] = $parentID;
				$poolData['ordering'] = $orderings[$parentID];
				$poolAdded            = $this->addPool($poolData);
				if (!$poolAdded)
				{
					OrganizerHelper::message('ORGANIZER_POOL_ADD_FAIL', 'error');

					return false;
				}
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Checks whether the degree program root mapping has already been created. If it has not already been done the
	 * creation function is called.
	 *
	 * @param   int  $programID  the id of the degree program
	 *
	 * @return boolean  true if the program root mapping exists/was created,
	 *                   otherwise false
	 */
	public function saveProgram($programID)
	{
		$findQuery = $this->_db->getQuery(true);
		$findQuery->select('*')
			->from('#__thm_organizer_mappings')
			->where('parentID IS NULL')
			->where("programID = '$programID'");
		$this->_db->setQuery($findQuery);
		$rootMapping = OrganizerHelper::executeQuery('loadAssoc', []);

		if (empty($rootMapping))
		{
			$leftQuery = $this->_db->getQuery(true);
			$leftQuery->select('MAX(rgt)')->from('#__thm_organizer_mappings');
			$this->_db->setQuery($leftQuery);

			$maxRgt = OrganizerHelper::executeQuery('loadResult');
			if (empty($maxRgt))
			{
				return false;
			}

			$data              = [];
			$data['programID'] = $programID;
			$data['poolID']    = null;
			$data['subjectID'] = null;
			$data['lft']       = $maxRgt + 1;
			$data['rgt']       = $maxRgt + 2;
			$data['level']     = 0;
			$data['ordering']  = 0;

			$mappingsTable = new Mappings;

			return $mappingsTable->save($data);
		}
		else
		{
			$children   = $this->getChildrenFromForm();
			$cleanSlate = $this->deleteChildren($rootMapping['id']);
			if (!$cleanSlate)
			{
				return false;
			}

			if (!empty($children) and $cleanSlate)
			{
				foreach ($children as $child)
				{
					$child['parentID'] = $rootMapping['id'];
					if (isset($child['poolID']))
					{
						if (!$this->addPool($child))
						{
							return false;
						}
					}
					elseif (isset($child['subjectID']))
					{
						$child['level'] = $rootMapping['level'] + 1;

						if (!$this->addSubject($child))
						{
							return false;
						}
					}
				}
			}

			return true;
		}
	}

	/**
	 * Saves a subject mapping
	 *
	 * @param   array &$data  the subject form data from the post request
	 *
	 * @return boolean  true on success, otherwise false
	 *
	 * @see Subjects
	 */
	public function saveSubject(&$data)
	{
		$subjectTemplate              = [];
		$subjectTemplate['programID'] = null;
		$subjectTemplate['poolID']    = null;
		$subjectTemplate['subjectID'] = $data['id'];
		$selectedParents              = $data['parentID'];

		if ($existingMappings = $this->getExistingMappings($data['id']))
		{
			foreach ($existingMappings as $existingMapping)
			{
				if (!$this->deleteEntry($existingMapping['id']))
				{
					return false;
				}
			}
		}

		foreach ($selectedParents as $newParentID)
		{
			$parent = new Mappings;

			if (!$exists = $parent->load($newParentID) or empty($parent->poolID))
			{
				return false;
			}

			$subjectTemplate['ordering'] = $this->getOrdering($newParentID, $data['id'], 'subject');
			$parentMappings              = $this->getExistingMappings($parent->poolID, 'pool');
			foreach ($parentMappings as $parentMapping)
			{
				$subjectTemplate['parentID'] = $parentMapping['id'];
				$subjectAdded                = $this->addSubject($subjectTemplate);
				if (!$subjectAdded)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Retrieves existing mappings
	 *
	 * @param   int     $resourceID  the id of the resource in its resource table
	 * @param   string  $type        the type of resource entry being searched for
	 *
	 * @return mixed  array on success, otherwise false
	 */
	private function getExistingMappings($resourceID, $type = 'subject')
	{
		$query = $this->_db->getQuery(true);
		$query->select('*')->from('#__thm_organizer_mappings')->where("{$type}ID = '$resourceID'");
		$this->_db->setQuery($query);

		return OrganizerHelper::executeQuery('loadAssocList');
	}

	/**
	 * Shifts the ordering for existing siblings who have an ordering at or
	 * above the ordering to be inserted
	 *
	 * @param   int  $parentID     the id of the parent
	 * @param   int  $insertOrder  the ordering of the item to be inserted
	 *
	 * @return boolean  true on success, otherwise false
	 */
	private function shiftOrder($parentID, $insertOrder)
	{
		$query = $this->_db->getQuery(true);
		$query->update('#__thm_organizer_mappings')->set('ordering = ordering + 1');
		$query->where("ordering >= '$insertOrder'")->where("parentID = '$parentID'");
		$this->_db->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Shifts left and right values to allow for the values to be inserted
	 *
	 * @param   int  $value  the integer value above which left and right values
	 *                       need to be shifted
	 *
	 * @return bool  true on success, otherwise false
	 */
	private function shiftRight($value)
	{
		$lftQuery = $this->_db->getQuery(true);
		$lftQuery->update('#__thm_organizer_mappings')->set('lft = lft + 2')->where("lft >= '$value'");
		$this->_db->setQuery($lftQuery);
		$success = (bool) OrganizerHelper::executeQuery('execute');
		if (!$success)
		{
			return false;
		}

		$rgtQuery = $this->_db->getQuery(true);
		$rgtQuery->update('#__thm_organizer_mappings')->set('rgt = rgt + 2')->where("rgt >= '$value'");
		$this->_db->setQuery($rgtQuery);

		return (bool) OrganizerHelper::executeQuery('execute');
	}
}
