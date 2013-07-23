<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMapping
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Provides methods dealing with the persistence of mappings
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelMapping extends JModel
{
    /**
     * Adds mappings as they exist in LSF for an imported degree program
     * 
     * @param   int              $programID  the id of the program in the local
     *                                       database
     * @param   simpleXMLObject  &$lsfData   the data recieved from the LSF system
     * 
     * @return  boolean  true if the data was mapped, otherwise false
     */
    public function addLSFMappings($programID, &$lsfData)
    {
        $mappingsTable = JTable::getInstance('mappings', 'THM_OrganizerTable');
        $poolsTable = JTable::getInstance('pools', 'THM_OrganizerTable');
        $subjectsTable = JTable::getInstance('subjects', 'THM_OrganizerTable');

        $programMappingLoaded = $mappingsTable->load(array('programID' => $programID));
        if (!$programMappingLoaded)
        {
            return false;
        }
        $programMappingID = $mappingsTable->id;

        $child = array();
        $child['parentID'] = $programMappingID;
        foreach ($lsfData->gruppe AS $resource)
        {
            if ($resource->pordtyp == 'K')
            {
                $poolLoaded = $poolsTable->load(array('lsfID' => $resource->pordid));
                if (!$poolLoaded)
                {
                    return false;
                }

                $poolID = $poolsTable->id;
                $rowExists = $mappingsTable->load(array('parentID' => $programMappingID, 'poolID' => $poolID));
                if (!$rowExists)
                {
                    $child['poolID'] = $poolID;
                    $child['subjectID'] = null;
                    $child['ordering'] = $this->getOrdering($programMappingID, $poolID);
                    $poolAdded = $this->addPool($child);
                    if (!$poolAdded)
                    {
                        return false;
                    }
                    $mappingsTable->load(array('parentID' => $programMappingID, 'poolID' => $poolID));
                }
                if (isset($resource->modulliste->modul))
                {
                    $poolMappingID = $mappingsTable->id;

                    $subjectData = array();
                    $subjectData['parentID'] = $poolMappingID;
                    $subjectData['poolID'] = null;
                    foreach ($resource->modulliste->modul as $subject)
                    {
                        $lsfID = (string) (empty($subject->modulid)?  $subject->pordid : $subject->modulid);
                        $subjectLoaded = $subjectsTable->load(array('lsfID' => $lsfID));
                        if (!$subjectLoaded)
                        {
                            return false;
                        }
                        $rowExists = $mappingsTable->load(array('parentID' => $poolMappingID, 'subjectID' => $subjectsTable->id));
                        if ($rowExists)
                        {
                            continue;
                        }
                        
                        $subjectData['subjectID'] = $subjectsTable->id;
                        $subjectData['ordering'] = $this->getOrdering($poolMappingID, $subjectsTable->id, 'subject');
                        $subjectAdded = $this->addSubject($subjectData);
                        if (!$subjectAdded)
                        {
                            return false;
                        }
                    }
                }
            }
            else
            {
                $subjectLoaded = $subjectsTable->load(array('lsfID' => $resource->pordid));
                if (!$subjectLoaded)
                {
                    return false;
                }
                $rowExists = $mappingsTable->load(array('parentID' => $programMappingID, 'subjectID' => $subjectsTable->id));
                if ($rowExists)
                {
                    continue;
                }
                $child['poolID'] = null;
                $child['subjectID'] = $subjectsTable->id;
                $child['ordering'] = $this->getOrdering($programMappingID, $subjectsTable->id, 'subject');
                $subjectAdded = $this->addSubject($child);
                if (!$subjectAdded)
                {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Adds a pool mapping to a parent mapping
     * 
     * @param   array  &$pool  an array containing data about a pool and its
     *                         children
     * 
     * @return  bool  true on success, otherwise false
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

        $spaceMade = $this->shiftRight($pool['lft']);
        if (!$spaceMade)
        {
            return false;
        }

        $siblingsReordered = $this->shiftOrder($pool['parentID'], $pool['ordering']);
        if (!$siblingsReordered)
        {
            return false;
        }

        $mapping = JTable::getInstance('mappings', 'THM_OrganizerTable');
        $mappingAdded = $mapping->save($pool);
        if ($mappingAdded)
        {
            if (!empty($pool['children']))
            {
                foreach ($pool['children'] as $child)
                {
                    $child['parentID'] = $mapping->id;
                    if (isset($child['poolID']))
                    {
                        $childAdded = $this->addPool($child);
                        if (!$childAdded)
                        {
                            return false;
                        }
                    }
                    elseif (isset($child['subjectID']))
                    {
                        $child['level'] = $pool['level'] + 1;
                        $childAdded = $this->addSubject($child);
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
     * @param   array  &$subject  an array containing data about a subject
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

        $spaceMade = $this->shiftRight($subject['lft']);
        if (!$spaceMade)
        {
            return false;
        }

        $siblingsReordered = $this->shiftOrder($subject['parentID'], $subject['ordering']);
        if (!$siblingsReordered)
        {
            return false;
        }

        $mapping = JTable::getInstance('mappings', 'THM_OrganizerTable');
        $mappingAdded = $mapping->save($subject);
        if ($mappingAdded)
        {
            return true;
        }
        return false;
    }

    /**
     * Checks whether a mapping exists for the selected resource
     * 
     * @param   int     $resourceID    the id of the resource
     * @param   string  $resourceType  the type of the resource#
     * 
     * @return  bool true if the resource has an existing mapping, otherwise false
     */
    public function checkForMapping($resourceID, $resourceType)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('COUNT(*)')->from('#__thm_organizer_mappings')->where("{$resourceType}ID = '$resourceID'");
        $dbo->setQuery((string) $query);
        $count = $dbo->loadResult();
        return empty($count)? false : true;
    }

    /**
     * Deletes mappings of a specific pool.
     * 
     * @param   int     $resourceID  the id of the mapping
     * @param   string  $type        the mapping's type
     * 
     * @return  boolean true on success, otherwise false
     */
    public function deleteByResourceID($resourceID, $type)
    {
        if ($type != 'program' AND $type != 'pool' AND $type != 'subject')
        {
            return false;
        }
        $dbo = JFactory::getDbo();

        $mappingIDsQuery = $dbo->getQuery(true);
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
        $dbo->setQuery((string) $mappingIDsQuery);
        $mappingIDs = $dbo->loadResultArray();

        if (!empty($mappingIDs))
        {
            foreach ($mappingIDs AS $mappingID)
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
     * @return  boolean true on success, otherwise false
     */
    public function deleteChildren($mappingID)
    {
        $dbo = JFactory::getDbo();

        $mappingIDsQuery = $dbo->getQuery(true);
        $mappingIDsQuery->select('id')->from('#__thm_organizer_mappings')->where("parentID = '$mappingID'");
        $dbo->setQuery((string) $mappingIDsQuery);
        $mappingIDs = $dbo->loadResultArray();

        if (!empty($mappingIDs))
        {
            foreach ($mappingIDs AS $mappingID)
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
     * @return  bool  true on success, otherwise false
     */
    private function deleteEntry($entryID)
    {
        $dbo = JFactory::getDbo();

        // Retrieves information about the current mapping including its total width
        $mappingQuery = $dbo->getQuery(true);
        $mappingQuery->select('*, (rgt - lft + 1) AS width')->from('#__thm_organizer_mappings')->where("id = '$entryID'");
        $dbo->setQuery((string) $mappingQuery);
        $mapping = $dbo->loadAssoc();

        // Deletes the mapping
        $deleteQuery = $dbo->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_mappings')->where("id = '{$mapping['id']}'");
        $dbo->setQuery((string) $deleteQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            return false;
        }

        // Reduces the ordering of siblings with a greater ordering
        $siblingsQuery = $dbo->getQuery(true);
        $siblingsQuery->update('#__thm_organizer_mappings');
        $siblingsQuery->set('ordering = ordering - 1');
        $siblingsQuery->where("parentID = '{$mapping['parentID']}'");
        $siblingsQuery->where("ordering > '{$mapping['ordering']}'");
        $dbo->setQuery((string) $siblingsQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            return false;
        }

        /**
         *  Reduces lft values at or above the mapping's rgt value according to
         *  the mapping's width
         */
        $updateLeftQuery = $dbo->getQuery(true);
        $updateLeftQuery->update('#__thm_organizer_mappings');
        $updateLeftQuery->set("lft = lft - {$mapping['width']}");
        $updateLeftQuery->where("lft > '{$mapping['lft']}'");
        $dbo->setQuery((string) $updateLeftQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            return false;
        }

        /**
         *  Reduces rgt values at or above the mapping's rgt value according to
         *  the mapping's width
         */
        $updateRightQuery = $dbo->getQuery(true);
        $updateRightQuery->update('#__thm_organizer_mappings');
        $updateRightQuery->set("rgt = rgt - {$mapping['width']}");
        $updateRightQuery->where("rgt > '{$mapping['lft']}'");
        $dbo->setQuery((string) $updateRightQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            return false;
        }
        return true;
    }

    /**
     * Attempt to determine the left value for the mapping to be created
     * 
     * @param   int    $parentID  the parent of the item to be inserted
     * @param   mixed  $ordering  the targeted ordering on completion
     * 
     * @return  mixed  int the left value for the mapping to be created, or
     *                 or boolean false on db error.
     */
    private function determineLft($parentID, $ordering)
    {
        $dbo = JFactory::getDbo();
        
        // Try to find the right value of the next lowest sibling
        $rgtQuery = $dbo->getQuery(true);
        $rgtQuery->select('MAX(rgt)')->from('#__thm_organizer_mappings');
        $rgtQuery->where("parentID = '$parentID'")->where("ordering < '$ordering'");
        $dbo->setQuery((string) $rgtQuery);
        try
        {
            $rgt = $dbo->loadResult();
            if (!empty($rgt))
            {
                return $rgt + 1;
            }
        }
        catch (Exception $exc)
        {
            return false;
        }
        
        $lftQuery = $dbo->getQuery(true);
        $lftQuery->select('lft')->from('#__thm_organizer_mappings');
        $lftQuery->where("id = '$parentID'");
        $dbo->setQuery((string) $lftQuery);
        try
        {
            $lft = $dbo->loadResult();
            return $lft + 1;
        }
        catch (Exception $exc)
        {
            return false;
        }
    }

    /**
     * Retrieves child mappings for a given pool
     * 
     * @param   int     $resourceID  the resource id
     * @param   string  $type        the resource id (defaults: pool)
     * @param   bool    $deep        if the function should be used to find
     *                               children iteratively or not (default: false)
     * 
     * @return  array  empty if no child data exists
     */
    public function getChildren($resourceID, $type = 'pool', $deep = true)
    {
        $dbo = JFactory::getDbo();
        $children = array();
 
        /**
         * Subordinate structures are the same for every parent mapping,
         * therefore only the first mapping needs to be found
         */
        $existingQuery = $dbo->getQuery(true);
        $existingQuery->select('id')->from('#__thm_organizer_mappings');
        $existingQuery->where("{$type}ID = '$resourceID'");
        $dbo->setQuery((string) $existingQuery, 0, 1);
        $firstID = $dbo->loadResult();
        if (!empty($firstID))
        {
            $childrenQuery = $dbo->getQuery(true);
            $childrenQuery->select('poolID, subjectID, ordering');
            $childrenQuery->from('#__thm_organizer_mappings');
            $childrenQuery->where("parentID = '$firstID'");
            $childrenQuery->order('lft ASC');
            $dbo->setQuery((string) $childrenQuery);
            $results = $dbo->loadAssocList();

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
     * @return  array  an array containing the resource's children and ordering
     */
    private function getChildrenFromForm()
    {
        $children = array();
        $childKeys = preg_grep('/^child[1-9]+$/', array_keys($_POST));
        foreach ($childKeys as $childKey)
        {
            $ordering = substr($childKey, 5);
            $aggregateInfo = JRequest::getString($childKey);
            $resourceID = substr($aggregateInfo, 0, strlen($aggregateInfo) - 1);
            $resourceType = strpos($aggregateInfo, 'p')? 'pool' : 'subject';
            
            if ($resourceType == 'subject')
            {
                $children[$ordering]['poolID'] = null;
                $children[$ordering]['subjectID'] = $resourceID;
                $children[$ordering]['ordering'] = $ordering;
            }
            if ($resourceType == 'pool')
            {
                $children[$ordering]['poolID'] = $resourceID;
                $children[$ordering]['subjectID'] = null;
                $children[$ordering]['ordering'] = $ordering;
                $children[$ordering]['children'] = $this->getChildren($resourceID);
            }
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
	 * @return  mixed  the int value of an existing ordering or string 'last' if
     *                 none exists
     */
    private function getOrdering($parentID, $resourceID, $type = 'pool')
    {
        $dbo = JFactory::getDbo();
        
        // Check for an existing ordering as child of the parent element
        $existingOrderQuery = $dbo->getQuery(true);
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
        $dbo->setQuery((string) $existingOrderQuery);
        $existingOrder = $dbo->loadResult();
        if ( !empty($existingOrder))
        {
            return $existingOrder;
        }

        /**
         *  No order exists for parent element order is then either one more
         *  the existing max value, or 1 if no children exist
         */
        $maxOrderQuery = $dbo->getQuery(true);
        $maxOrderQuery->select('MAX(ordering)')->from('#__thm_organizer_mappings')->where("parentID = '$parentID'");
        $dbo->setQuery((string) $maxOrderQuery);
        $maxOrder = $dbo->loadResult();
        return empty($maxOrder)? 1 : $maxOrder + 1;
    }

    /**
     * Retrieves parent data
     * 
     * @param   int  $parentID  the id of the parent item
     * 
     * @return  array  the parent mapping
     */
    private function getParent($parentID)
    {
        $dbo = JFactory::getDbo();
        $parentQuery = $dbo->getQuery(true);
        $parentQuery->select('*')->from('#__thm_organizer_mappings')->where("id = '$parentID'");
        $dbo->setQuery((string) $parentQuery);
        return $dbo->loadAssoc();
    }

    /**
     * Creates and returns instance of JTable for the DB Table Mappings
     * 
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
     * 
     * @return  JTable
     */
    public function getTable($name = 'mappings', $prefix = 'THM_OrganizerTable', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    /**
     * Saves pool and dependent mappings
     * 
     * @param   array  &$data  the pool form data from the post request
     * 
     * @return  boolean  true on success, otherwise false
     */
    public function savePool(&$data)
    {
        $poolData = array();
        $poolData['programID'] = null;
        $poolData['poolID'] = $data['id'];
        $poolData['subjectID'] = null;
        $poolData['children'] = $this->getChildrenFromForm();

        $parentIDs = $data['parentID'];
        $orderings = array();
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
                $poolAdded = $this->addPool($poolData);
                if (!$poolAdded)
                {
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
     * Checks whether the degree program root mapping has already been created.
     * If it has not already been done the creation function is called.
     * 
     * @param   int  $programID  the id of the degree program
     * 
     * @return  boolean  true if the program root mapping exists/was created,
     *                   otherwise false
     */
    public function saveProgram($programID)
    {
        $dbo = JFactory::getDbo();
        $findQuery = $dbo->getQuery(true);
        $findQuery->select('*')->from('#__thm_organizer_mappings')->where('parentID IS NULL')->where("programID = '$programID'");
        $dbo->setQuery((string) $findQuery);
        $rootMapping = $dbo->loadAssoc();
        if (empty($rootMapping))
        {
            $leftQuery = $dbo->getQuery(true);
            $leftQuery->select("MAX(rgt)")->from('#__thm_organizer_mappings');
            $dbo->setQuery((string) $leftQuery);
            $maxRgt = $dbo->loadResult();

            $data = array();
            $data['programID'] = $programID;
            $data['poolID'] = null;
            $data['subjectID'] = null;
            $data['lft'] = $maxRgt + 1;
            $data['rgt'] = $maxRgt + 2;
            $data['level'] = 0;
            $data['ordering'] = 0;

            return $this->getTable()->save($data);
        }
        else
        {
            $children = $this->getChildrenFromForm();
            $cleanSlate = $this->deleteChildren($rootMapping['id']);
            if (!$cleanSlate)
            {
                return false;
            }
            if (!empty($children) AND $cleanSlate)
            {
                foreach ($children as $child)
                {
                    $child['parentID'] = $rootMapping['id'];
                    if (isset($child['poolID']))
                    {
                        $childAdded = $this->addPool($child);
                        if (!$childAdded)
                        {
                            return false;
                        }
                    }
                    elseif (isset($child['subjectID']))
                    {
                        $child['level'] = $rootMapping['level'] + 1;
                        $childAdded = $this->addSubject($child);
                        if (!$childAdded)
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
     * @param   array  &$data  the subject form data from the post request
     * 
     * @return  boolean  true on success, otherwise false
     */
    public function saveSubject(&$data)
    {
        $subjectData = array();
        $subjectData['programID'] = null;
        $subjectData['poolID'] = null;
        $subjectData['subjectID'] = $data['id'];

        $parentIDs = $data['parentID'];
        $orderings = array();
        foreach ($parentIDs as $parentID)
        {
            $orderings[$parentID] = $this->getOrdering($parentID, $subjectData['subjectID']);
        }
        
        $cleanSlate = $this->deleteByResourceID($subjectData['subjectID'], 'subject');
        if ($cleanSlate)
        {
            foreach ($parentIDs as $parentID)
            {
                $subjectData['parentID'] = $parentID;
                $subjectData['ordering'] = $orderings[$parentID];
                $subjectAdded = $this->addSubject($subjectData);
                if (!$subjectAdded)
                {
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
     * Shifts the ordering for existing siblings who have an ordering at or
     * above the ordering to be inserted
     * 
     * @param   int  $parentID     the id of the parent
     * @param   int  $insertOrder  the ordering of the item to be inserted
     * 
     * @return  boolean  true on success, otherwise false
     */
    private function shiftOrder($parentID, $insertOrder)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->update('#__thm_organizer_mappings')->set('ordering = ordering + 1');
        $query->where("ordering >= '$insertOrder'")->where("parentID = '$parentID'");
        $dbo->setQuery((string) $query);
        try
        {
            $dbo->query();
        }
        catch (Exception $exc)
        {
            return false;
        }
        return true;
    }

    /**
     * Shifts left and right values to allow for the values to be inserted
     * 
     * @param   int  $value  the integer value above which left and right values
     *                       need to be shifted
     * 
     * @return  bool  true on success, otherwise false
     */
    private function shiftRight($value)
    {
        $dbo = JFactory::getDbo();
        $lftQuery = $dbo->getQuery(true);
        $lftQuery->update('#__thm_organizer_mappings')->set('lft = lft + 2')->where("lft >= '$value'");
        $dbo->setQuery((string) $lftQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exc)
        {
            return false;
        }
        
        $rgtQuery = $dbo->getQuery(true);
        $rgtQuery->update('#__thm_organizer_mappings')->set('rgt = rgt + 2')->where("rgt >= '$value'");
        $dbo->setQuery((string) $rgtQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exc)
        {
            return false;
        }

        return true;
    }
}
