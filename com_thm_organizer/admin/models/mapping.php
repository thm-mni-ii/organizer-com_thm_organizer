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
     * Creates and returns instance of JTable for the DB Table Mappings
     * 
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
     * 
     * @return  JTable
     */
    public function getTable($name = '', $prefix = 'Table', $options = array())
    {
        return JTable::getInstance('mappings', 'THM_OrganizerTable');
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
        $findQuery->select('id')->from('#__thm_organizer_mappings')->where('parentID IS NULL')->where("programID = '$programID'");
        $dbo->setQuery((string) $findQuery);
        $alreadyExists = (bool) $dbo->loadResult();
        if (!$alreadyExists)
        {
            $dbo = JFactory::getDbo();

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
        return true;
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
        $poolData['children'] = array();
        if (!empty($data['children']))
        {
            foreach ($data['children'] as $ordering => $childID)
            {
                if (strpos($childID, 's'))
                {
                    $poolData['children'][$ordering]['poolID'] = null;
                    $poolData['children'][$ordering]['subjectID'] = str_replace('s', '', $childID);
                    $poolData['children'][$ordering]['ordering'] = $ordering;
                }
                if (strpos($childID, 'p'))
                {
                    $childPoolID = str_replace('p', '', $childID);
                    $poolData['children'][$ordering]['poolID'] = $poolID;
                    $poolData['children'][$ordering]['subjectID'] = null;
                    $poolData['children'][$ordering]['ordering'] = $ordering;
                    $poolData['children'][$ordering]['children'] = $this->getChildren($childPoolID);
                }
            }
        }

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
     * Retrieves child mappings for a given pool
     * 
     * @param   int     $resourceID  the resource id
     * @param   string  $type        the resource id (defaults: pool)
     * @param   bool    $deep        if the function should be used to find
     *                               children iteratively or not (default: false)
     * 
     * @todo ensure that there are no self refering children
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
     * Retrieves the existing ordering of a pool to its parent item, or the
     * value 'last'
     * 
     * @param   int  $parentID  the id of the parent mapping
     * @param   int  $poolID    the id of the pool
     * 
	 * @return  mixed  the int value of an existing ordering or string 'last' if
     *                 none exists
     */
    private function getOrdering($parentID, $poolID)
    {
        $dbo = JFactory::getDbo();
        
        // Check for an existing ordering as child of the parent element
        $existingOrderQuery = $dbo->getQuery(true);
        $existingOrderQuery->select('ordering')->from('#__thm_organizer_mappings');
        $existingOrderQuery->where("parentID = '$parentID'")->where("poolID = '$poolID'");
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

        $mapping = $this->getTable();
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
        $subject['lft'] = $this->determineLft($subject['parentID'], $subject['ordering']);
        if (empty($subject['lft']))
        {
            return false;
        }
        $subject['rgt'] = $subject['lft'] + 1;
        $spaceMade = $this->shiftRight($subject['lft']);
        if (!$spaceMade)
        {
            return false;
        }
        return $this->getTable()->save($subject);
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
     * Deletes mappings of a specific pool.
     * 
     * @param   int     $resourceID  the id of the mapping
     * @param   string  $type        the mapping's type
     * 
     * @return  boolean true on success, otherwise false
     */
    public function deleteByResourceID($resourceID, $type)
    {
        if ($type != 'degree' AND $type != 'pool' AND $type != 'subject')
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
     * Method to delete a single entry
     * 
     * @param   int   $entryID            the id value of the entry to be deleted
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
     * Attempts to delete the selected subject entries
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        $success = true;
        $subjectIDs = JRequest::getVar('cid', array(0), 'post', 'array');
        $table = JTable::getInstance('subjects', 'thm_organizerTable');
        if (!empty($subjectIDs))
        {
            $dbo = JFactory::getDbo();
            $dbo->transactionStart();
            foreach ($subjectIDs as $subjectID)
            {
                $success = $table->delete($subjectID);
                if (!$success)
                {
                    $dbo->transactionRollback();
                    return $success;
                }
            }
            $dbo->transactionCommit();
        }
        return $success;
    }
}
