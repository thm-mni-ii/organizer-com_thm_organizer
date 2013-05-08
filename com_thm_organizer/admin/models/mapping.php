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
    private function getTable()
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
            return $this->createProgram($programID);
        }
        return true;
    }

    /**
     * Creates a degree program root node in the mappings table
     * 
     * @param   int  $programID  the id of the degree program
     * 
     * @return  boolean true on success, otherwise false
     */
    private function createProgram($programID)
    {
        $data = array();
        $data['programID'] = $programID;
        $data['ordering'] = 0;
        $data['level'] = 0;
        
        $dbo = JFactory::getDbo();
        
        $leftQuery = $dbo->getQuery(true);
        $leftQuery->select("MAX(rgt)")->from('#__thm_organizer_mappings');
        $dbo->setQuery((string) $leftQuery);
        $maxRgt = $dbo->loadResult();
        $data['lft'] = $maxRgt + 1;
        $data['rgt'] = $maxRgt + 2;
        
        return $this->getTable()->save($data);
    }

    public function savePool(&$data)
    {
        $poolData = array();
        $poolData['poolID'] = $data['id'];
        $poolData['subjectID'] = NULL;
        $poolData['children'] = array();
        if (!empty($data['children']))
        {
            foreach ($data['children'] as $ordering => $childID)
            {
                if (strpos($childID, 's'))
                {
                    $poolData['children'][$ordering]['poolID'] = NULL;
                    $poolData['children'][$ordering]['subjectID'] = str_replace('s', '', $childID);
                    $poolData['children'][$ordering]['ordering'] = $ordering;
                }
                if (strpos($childID, 'p'))
                {
                    $poolID = str_replace('p', '', $childID);
                    $poolData['children'][$ordering]['poolID'] = $poolID;
                    $poolData['children'][$ordering]['subjectID'] = NULL;
                    $poolData['children'][$ordering]['ordering'] = $ordering;
                    $poolData['children'][$ordering]['children'] = $this->getChildren($poolID);
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
        if($cleanSlate)
        {
            foreach ($parentIDs as $parentID)
            {
                $poolData['ordering'] = $orderings[$parentID];
                $poolAdded = $this->addPool($parentID, $poolData);
                if (!$poolAdded)
                {
                    return false;
                }
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * Retrieves child mappings for a given pool
     * 
     * @param   int  $poolID  the pool resource id
     * 
     * @return  array  empty if no child data exists
     */
    private function getChildren($poolID)
    {
        $dbo = JFactory::getDbo();
        $children = array();
        
        $existingQuery = $dbo->getQuery(true);
        $existingQuery->select('id')->from('#__thm_organizer_mappings')->where("poolID = '$poolID'");
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
                foreach ($children as $key => $child)
                {
                    if (!empty($child['poolID']))
                    {
                        $children[$key]['children'] = $this->getChildren($child['poolID']);
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
        $query = $dbo->getQuery(true);
        $query->select('ordering')->from('#__thm_organizer_mappings');
        $query->where("parentID = '$parentID'")->where("poolID = '$poolID'");
        $dbo->setQuery((string) $query);
        $existingOrder = $dbo->loadResult();
        return empty($existingOrder)? 'last' : $existingOrder;
    }

    /**
     * Adds a pool mapping to a parent mapping
     * 
     * @param   int  $parentID  
     * @param   int  $poolData
     * 
     * 
     */
    private function addPool($parentID, $poolData)
    {
        $dbo = JFactory::getDbo();
        $parentQuery = $dbo->getQuery(true);
        $parentQuery->select('*')->from('#__thm_organizer_mappings')->where("id = '$parentID'");
        $dbo->setQuery((string) $parentQuery);
        $parent = $dbo->loadAssoc();

        if($poolData['ordering'] == 'last')
        {
            $poolData['lft'] = $parent['']
        }
        
        foreach ($poolData['children'] as $child)
        {
            
        }
    }

    /**
     * Deletes mappings of a specific pool.
     * 
     * @param   int     $resourceID  the id of the mapping
     * @param   string  $type        the mapping's type
     * 
     * return  boolean true on success, otherwise false
     */
    public function deleteByResourceID($resourceID, $type)
    {
        if ($type != 'degree' AND $type != 'pool' AND $type != 'subject')
        {
            return false;
        }
        $dbo = JFactory::getDbo();
        $dbo->transactionStart();

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
                $manageTransaction = false;
                $success = $this->deleteEntry($mappingID, $manageTransaction);
                if(!$success)
                {
                    $dbo->transactionRollback();
                    return false;
                }
            }
        }
        $dbo->transactionCommit();
        return true;
    }

    /**
     * Method to delete a single entry
     * 
     * @param type $entryID
     * @param type $manageTransaction
     * 
     * @return  boolean  true on success, otherwise false
     */
    private function deleteEntry($entryID, $manageTransaction)
    {
        $dbo = JFactory::getDbo();
        if ($manageTransaction)
        {
            $dbo->transactionStart();
        }
        
        $mappingQuery = $dbo->getQuery(true);
        $mappingQuery->select('*, (rgt - lft) AS width')->from('#__thm_organizer_mappings')->where("id = '$entryID'");
        $dbo->setQuery((string) $mappingQuery);
        $mapping = $dbo->loadAssoc();
        
        $deleteQuery = $dbo->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_mappings')->where("id = '{$mapping['id']}'");
        $dbo->setQuery((string) $deleteQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            if ($manageTransaction)
            {
                $dbo->transactionRollback();
            }
            return false;
        }

        $siblingsQuery = $dbo->getQuery(true);
        $siblingsQuery->update('#__thm_organizer_mappings');
        $siblingsQuery->set('ordering = ordering - 1');
        $siblingsQuery->where("parentID = '{$mapping['parentId']}'");
        $siblingsQuery->where("ordering > '{$mapping['ordering']}'");
        $dbo->setQuery((string) $siblingsQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            if ($manageTransaction)
            {
                $dbo->transactionRollback();
            }
            return false;
        }

        $updateLeftQuery = $dbo->getQuery(true);
        $updateLeftQuery->update('#__thm_organizer_mappings');
        $updateLeftQuery->set("lft = lft - {$mapping['width']}");
        $updateLeftQuery->where("lft > '{$mapping['right']}'");
        $dbo->setQuery((string) $updateLeftQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            if ($manageTransaction)
            {
                $dbo->transactionRollback();
            }
            return false;
        }

        $updateRightQuery = $dbo->getQuery(true);
        $updateRightQuery->update('#__thm_organizer_mappings');
        $updateRightQuery->set("rgt = rgt - {$mapping['width']}");
        $updateRightQuery->where("rgt > '{$mapping['right']}'");
        $dbo->setQuery((string) $updateRightQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            if ($manageTransaction)
            {
                $dbo->transactionRollback();
            }
            return false;
        }
        if ($manageTransaction)
        {
            $dbo->transactionCommit();
        }
        return true;
    }

 	/**
	 * Saves
	 *
	 * @return  mixed  integer on successful pool creation, otherwise boolean
     *                 true/false on success/failure
	 */
	public function save()
	{
        $data = JRequest::getVar('jform', null, null, null, 4);        
        $poolID = $data['id'];
        $dbo = JFactory::getDbo();
        

        // directly subordinate to degree program ordering must still be worked out
        if (in_array('-1', $data['parentID']))
        {
            $rootKeys = array_keys($data['parentID'], '-1');
            if (!empty($rootKeys))
            {
                foreach ($rootKeys as $rootKey)
                {
                    unset($data['parentID'][$rootKey]);
                }
                $databaseName = JFactory::getConfig()->get('db');
                $autoIncQuery = $dbo->getQuery(true);
                $autoIncQuery->select('AUTO_INCREMENT')->from('information_schema.TABLES');
                $autoIncQuery->where("TABLE_NAME = '#__thm_organizer_mappings'");
                $autoIncQuery->where("TABLE_SCHEMA = '$databaseName'");
                $dbo->setQuery((string) $autoIncQuery);
                $data['parentID'][] = $dbo->loadResult();
            }
        }
	}

    

	/**
	* Method to ensure the hierarchial ordering of left, right, ordering, and
     *level values.
	*
	* @param   string  $where  WHERE clause to use for limiting the selection of rows to
	*                           compact the ordering values.
	*
	* @return  mixed   Boolean true on success.
	*/
	public function clean($selectionID = null, $selectionType = null)
	{
        $dbo = JFactory::getDbo();
        $dbo->transactionStart();

        // Get the pool entries to be processed
        $poolsQuery = $dbo->getQuery(true);
        $poolsQuery->select('*')->from('#__thm_organizer_pools');
        if (!empty($selectionID) AND !empty($selectionType))
        {
            switch ($selectionType)
            {
                case 'program':
                    $poolsQuery->where("programID = '$selectionID'");
                    break;
                case 'pool':
                    $children = "'" . implode("', '", $this->findChildren($selectionID, 'all')) . "'";   
                    $poolsQuery->where("id IN ( $children )");      
                    break;
            }
        }
        $dbo->setQuery((string) $query);
        
	
		return true;
	}

    public function findChildren($poolID, $which)
    {
        $children = array();
        $children[] = $poolID;

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')->from('#__thm_organizer_pools');
        switch ($which)
        {
            case 'all':
                do
                {
                    $foundChildren = "'" . implode("', '", $children) . "'";
                    $query->clear('where');
                    $query->where("parentID IN ( $foundChildren )")->where("id NOT IN ( $foundChildren )");
                    $dbo->setQuery((string) $query);
                    $results = $dbo->loadResultArray();
                    if (!empty($results))
                    {
                        $children = array_merge($children, $results);
                    }
                }
                while(!empty($results));
                break;
            case 'direct':
                $query->where("parentID = '$poolID'")->where("id != '$poolID'");
                $dbo->setQuery((string) $query);
                $results = $dbo->loadResultArray();
                $children = !empty($results)? $results : array();
                break;
        }
        return $children;
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
