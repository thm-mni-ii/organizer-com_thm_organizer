<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMapping
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_COMPONENT . '/assets/helpers/lsfapi.php';

/**
 * Provides methods dealing with the persistence of mappings
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelMapping extends JModelLegacy
{
    /**
     * Adds mappings as they exist in LSF for an imported degree program
     *
     * @param int             $programID the id of the program in the local
     *                                   database
     * @param SimpleXMLObject &$lsfData  the data recieved from the LSF system
     *
     * @return  boolean  true if the data was mapped, otherwise false
     */
    public function addLSFMappings($programID, &$lsfData)
    {
        $mappingsTable        = JTable::getInstance('mappings', 'THM_OrganizerTable');
        $programMappingLoaded = $mappingsTable->load(['programID' => $programID]);
        if (!$programMappingLoaded) {
            return false;
        }

        foreach ($lsfData->gruppe AS $resource) {
            $type   = (string)$resource->pordtyp;
            $mapped = true;

            if ($type == 'M') {
                $mapped = $this->addLSFSubject($resource, $mappingsTable->id);
            } elseif ($type == 'K') {
                $mapped = $this->addLSFPool($resource, $mappingsTable->id);
            }

            if (!$mapped) {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds a pool from LSF to the mappings table
     *
     * @param object &$pool           the object representing the LSF pool
     * @param int    $parentMappingID the id of the program mapping
     *
     * @return  boolean  true if the pool is mapped, otherwise false
     */
    private function addLSFPool(&$pool, $parentMappingID)
    {
        $lsfID = empty($pool->pordid) ? (string)$pool->modulid : (string)$pool->pordid;
        $blocked = !empty($pool->sperrmh) AND strtolower((string)$pool->sperrmh) == 'x';
        $invalidTitle = THM_OrganizerLSFClient::invalidTitle($pool);
        $noChildren   = !isset($pool->modulliste->modul);
        $poolsTable   = JTable::getInstance('pools', 'THM_OrganizerTable');
        $poolExists   = $poolsTable->load(['lsfID' => $lsfID]);

        if ($poolExists) {
            if ($blocked OR $invalidTitle OR $noChildren) {
                $poolModel = JModelLegacy::getInstance('pool', 'THM_OrganizerModel');

                return $poolModel->deleteEntry($poolsTable->id);
            }

            $mappingsTable = JTable::getInstance('mappings', 'THM_OrganizerTable');
            $mappingExists = $mappingsTable->load(['parentID' => $parentMappingID, 'poolID' => $poolsTable->id]);

            if (!$mappingExists) {
                $poolMapping              = [];
                $poolMapping['parentID']  = $parentMappingID;
                $poolMapping['poolID']    = $poolsTable->id;
                $poolMapping['subjectID'] = null;
                $poolMapping['ordering']  = $this->getOrdering($parentMappingID, $poolsTable->id);
                $poolAdded                = $this->addPool($poolMapping);
                if (!$poolAdded) {
                    JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_POOL_ADD_FAIL', 'error');

                    return false;
                }

                $mappingsTable->load(['parentID' => $parentMappingID, 'poolID' => $poolsTable->id]);
            }

            foreach ($pool->modulliste->modul as $sub) {
                $type   = (string)$sub->pordtyp;
                $mapped = true;

                if ($type == 'K') {
                    $mapped = $this->addLSFPool($sub, $mappingsTable->id);
                } elseif ($type == 'M') {
                    $mapped = $this->addLSFSubject($sub, $mappingsTable->id);
                }

                if (!$mapped) {
                    return false;
                }
            }

            return true;
        }

        if ($blocked OR $invalidTitle OR $noChildren) {
            return true;
        }

        JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_POOL_MAP_FAIL', 'error');

        return false;
    }

    /**
     * Adds a subject from LSF to the mappings table
     *
     * @param object &$subject        the subject object
     * @param int    $parentMappingID the id of the parent element in the
     *                                mappings table
     *
     * @return  boolean  true if the mapping exists, otherwise false
     */
    private function addLSFSubject(&$subject, $parentMappingID)
    {
        $lsfID = (string)(empty($subject->modulid) ? $subject->pordid : $subject->modulid);
        $blocked = !empty($subject->sperrmh) AND strtolower((string)$subject->sperrmh) == 'x';
        $invalidTitle = THM_OrganizerLSFClient::invalidTitle($subject);

        $subjectsTable = JTable::getInstance('subjects', 'THM_OrganizerTable');
        $subjectExists = $subjectsTable->load(['lsfID' => $lsfID]);

        if ($subjectExists) {
            $mappingsTable = JTable::getInstance('mappings', 'THM_OrganizerTable');
            $mappingExists = $mappingsTable->load(['parentID' => $parentMappingID, 'subjectID' => $subjectsTable->id]);

            if ($mappingExists) {
                if ($blocked OR $invalidTitle) {
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

            if (!$subjectAdded) {
                JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_SUBJECT_ADD_FAIL', 'error');

                return false;
            }

            return true;
        }

        if ($blocked OR $invalidTitle) {
            return true;
        }

        // TODO: Language constant here!
        JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_SUBJECT_MAP_FAIL', 'error');

        return false;
    }

    /**
     * Adds a pool mapping to a parent mapping
     *
     * @param array &$pool an array containing data about a pool and its
     *                     children
     *
     * @return  bool  true on success, otherwise false
     */
    private function addPool(&$pool)
    {
        $parent = $this->getParent($pool['parentID']);

        $pool['level'] = $parent['level'] + 1;

        $pool['lft'] = $this->determineLft($pool['parentID'], $pool['ordering']);
        if (empty($pool['lft'])) {
            return false;
        }

        $pool['rgt'] = (string)($pool['lft'] + 1);

        $spaceMade = $this->shiftRight($pool['lft']);
        if (!$spaceMade) {
            return false;
        }

        $siblingsReordered = $this->shiftOrder($pool['parentID'], $pool['ordering']);
        if (!$siblingsReordered) {
            return false;
        }

        $mapping      = JTable::getInstance('mappings', 'THM_OrganizerTable');
        $mappingAdded = $mapping->save($pool);
        if ($mappingAdded) {
            if (!empty($pool['children'])) {
                foreach ($pool['children'] as $child) {
                    $child['parentID'] = $mapping->id;
                    if (isset($child['poolID'])) {
                        $childAdded = $this->addPool($child);
                        if (!$childAdded) {
                            return false;
                        }
                    } elseif (isset($child['subjectID'])) {
                        if (!is_numeric($child['subjectID'])) {
                            continue;
                        }

                        $child['level'] = $pool['level'] + 1;
                        $childAdded     = $this->addSubject($child);
                        if (!$childAdded) {
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
     * @param array &$subject an array containing data about a subject
     *
     * @return boolean
     */
    private function addSubject(&$subject)
    {
        $parent = $this->getParent($subject['parentID']);

        $subject['level'] = $parent['level'] + 1;

        $subject['lft'] = $this->determineLft($subject['parentID'], $subject['ordering']);
        if (empty($subject['lft'])) {
            return false;
        }

        $subject['rgt'] = (string)($subject['lft'] + 1);

        $spaceMade = $this->shiftRight($subject['lft']);
        if (!$spaceMade) {
            return false;
        }

        $siblingsReordered = $this->shiftOrder($subject['parentID'], $subject['ordering']);
        if (!$siblingsReordered) {
            return false;
        }

        $mapping      = JTable::getInstance('mappings', 'THM_OrganizerTable');
        $mappingAdded = $mapping->save($subject);
        if ($mappingAdded) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether a mapping exists for the selected resource
     *
     * @param int    $resourceID   the id of the resource
     * @param string $resourceType the type of the resource
     *
     * @return  bool true if the resource has an existing mapping, otherwise false
     */
    public function checkForMapping($resourceID, $resourceType)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('COUNT(*)')->from('#__thm_organizer_mappings')->where("{$resourceType}ID = '$resourceID'");
        $dbo->setQuery($query);

        try {
            $count = $dbo->loadResult();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return false;
        }

        return empty($count) ? false : true;
    }

    /**
     * Deletes mappings of a specific pool.
     *
     * @param int    $resourceID the id of the mapping
     * @param string $type       the mapping's type
     *
     * @return  boolean true on success, otherwise false
     */
    public function deleteByResourceID($resourceID, $type)
    {
        if ($type != 'program' AND $type != 'pool' AND $type != 'subject') {
            return false;
        }

        $dbo = JFactory::getDbo();

        $mappingIDsQuery = $dbo->getQuery(true);
        $mappingIDsQuery->select('id')->from('#__thm_organizer_mappings');
        switch ($type) {
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

        $dbo->setQuery($mappingIDsQuery);

        try {
            $mappingIDs = $dbo->loadColumn();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return false;
        }

        if (!empty($mappingIDs)) {
            foreach ($mappingIDs AS $mappingID) {
                $success = $this->deleteEntry($mappingID);
                if (!$success) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Deletes the children of a specific mapping.
     *
     * @param int $mappingID the id of the mapping
     *
     * @return  boolean true on success, otherwise false
     */
    public function deleteChildren($mappingID)
    {
        $dbo = JFactory::getDbo();

        $mappingIDsQuery = $dbo->getQuery(true);
        $mappingIDsQuery->select('id')->from('#__thm_organizer_mappings')->where("parentID = '$mappingID'");
        $dbo->setQuery($mappingIDsQuery);

        try {
            $mappingIDs = $dbo->loadColumn();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return false;
        }

        if (!empty($mappingIDs)) {
            foreach ($mappingIDs AS $mappingID) {
                $success = $this->deleteEntry($mappingID);
                if (!$success) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Method to delete a single entry
     *
     * @param int $entryID the id value of the entry to be deleted
     *
     * @return  bool  true on success, otherwise false
     */
    private function deleteEntry($entryID)
    {
        $dbo = JFactory::getDbo();

        // Retrieves information about the current mapping including its total width
        $mappingQuery = $dbo->getQuery(true);
        $mappingQuery->select('*, (rgt - lft + 1) AS width')->from('#__thm_organizer_mappings')->where("id = '$entryID'");
        $dbo->setQuery($mappingQuery);

        try {
            $mapping = $dbo->loadAssoc();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return false;
        }

        // Deletes the mapping
        $deleteQuery = $dbo->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_mappings')->where("id = '{$mapping['id']}'");
        $dbo->setQuery($deleteQuery);
        try {
            $dbo->execute();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return false;
        }

        // Reduces the ordering of siblings with a greater ordering
        $siblingsQuery = $dbo->getQuery(true);
        $siblingsQuery->update('#__thm_organizer_mappings');
        $siblingsQuery->set('ordering = ordering - 1');
        $siblingsQuery->where("parentID = '{$mapping['parentID']}'");
        $siblingsQuery->where("ordering > '{$mapping['ordering']}'");
        $dbo->setQuery($siblingsQuery);
        try {
            $dbo->execute();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

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
        $dbo->setQuery($updateLeftQuery);
        try {
            $dbo->execute();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

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
        $dbo->setQuery($updateRightQuery);
        try {
            $dbo->execute();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return false;
        }

        return true;
    }

    /**
     * Attempt to determine the left value for the mapping to be created
     *
     * @param int   $parentID the parent of the item to be inserted
     * @param mixed $ordering the targeted ordering on completion
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
        $dbo->setQuery($rgtQuery);
        try {
            $rgt = $dbo->loadResult();
            if (!empty($rgt)) {
                return $rgt + 1;
            }
        } catch (Exception $exc) {
            return false;
        }

        $lftQuery = $dbo->getQuery(true);
        $lftQuery->select('lft')->from('#__thm_organizer_mappings');
        $lftQuery->where("id = '$parentID'");
        $dbo->setQuery($lftQuery);
        try {
            $lft = $dbo->loadResult();

            return $lft + 1;
        } catch (Exception $exc) {
            return false;
        }
    }

    /**
     * Retrieves child mappings for a given pool
     *
     * @param int    $resourceID the resource id
     * @param string $type       the resource id (defaults: pool)
     * @param bool   $deep       if the function should be used to find
     *                           children iteratively or not (default: false)
     *
     * @return  array  empty if no child data exists
     */
    public function getChildren($resourceID, $type = 'pool', $deep = true)
    {
        $dbo      = JFactory::getDbo();
        $children = [];

        /**
         * Subordinate structures are the same for every parent mapping,
         * therefore only the first mapping needs to be found
         */
        $existingQuery = $dbo->getQuery(true);
        $existingQuery->select('id')->from('#__thm_organizer_mappings');
        $existingQuery->where("{$type}ID = '$resourceID'");
        $dbo->setQuery($existingQuery, 0, 1);

        try {
            $firstID = $dbo->loadResult();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return [];
        }

        if (!empty($firstID)) {
            $childrenQuery = $dbo->getQuery(true);
            $childrenQuery->select('poolID, subjectID, ordering');
            $childrenQuery->from('#__thm_organizer_mappings');
            $childrenQuery->where("parentID = '$firstID'");
            $childrenQuery->order('lft ASC');
            $dbo->setQuery($childrenQuery);

            try {
                $results = $dbo->loadAssocList();
            } catch (Exception $exc) {
                JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"),
                    'error');

                return [];
            }

            if (!empty($results)) {
                $children = $results;
                if ($deep) {
                    foreach ($children as $key => $child) {
                        if (!empty($child['poolID'])) {
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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getChildrenFromForm()
    {
        $children = [];
        JFactory::getApplication()->input->post;
        $childKeys = preg_grep('/^child[0-9]+$/', array_keys($_POST));
        foreach ($childKeys as $childKey) {
            $ordering      = substr($childKey, 5);
            $aggregateInfo = JFactory::getApplication()->input->getString($childKey, '');
            $resourceID    = substr($aggregateInfo, 0, strlen($aggregateInfo) - 1);
            $resourceType  = strpos($aggregateInfo, 'p') ? 'pool' : 'subject';

            if ($resourceType == 'subject') {
                $children[$ordering]['poolID']    = null;
                $children[$ordering]['subjectID'] = $resourceID;
                $children[$ordering]['ordering']  = $ordering;
            }

            if ($resourceType == 'pool') {
                $children[$ordering]['poolID']    = $resourceID;
                $children[$ordering]['subjectID'] = null;
                $children[$ordering]['ordering']  = $ordering;
                $children[$ordering]['children']  = $this->getChildren($resourceID);
            }
        }

        return $children;
    }

    /**
     * Retrieves the existing ordering of a pool to its parent item, or the
     * value 'last'
     *
     * @param int    $parentID   the id of the parent mapping
     * @param int    $resourceID the id of the resource
     * @param string $type       the type of resource being ordered
     *
     * @return  int  the value of the highest existing ordering or 1 if none exist
     */
    private function getOrdering($parentID, $resourceID, $type = 'pool')
    {
        $dbo = JFactory::getDbo();

        // Check for an existing ordering as child of the parent element
        $existingOrderQuery = $dbo->getQuery(true);
        $existingOrderQuery->select('ordering')->from('#__thm_organizer_mappings');
        $existingOrderQuery->where("parentID = '$parentID'");
        if ($type == 'subject') {
            $existingOrderQuery->where("subjectID = '$resourceID'");
        } else {
            $existingOrderQuery->where("poolID = '$resourceID'");
        }

        $dbo->setQuery($existingOrderQuery);

        try {
            $existingOrder = $dbo->loadResult();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
        }

        if (!empty($existingOrder)) {
            return $existingOrder;
        }

        /**
         *  No order exists for parent element order is then either one more
         *  the existing max value, or 1 if no children exist
         */
        $maxOrderQuery = $dbo->getQuery(true);
        $maxOrderQuery->select('MAX(ordering)')->from('#__thm_organizer_mappings')->where("parentID = '$parentID'");
        $dbo->setQuery($maxOrderQuery);

        try {
            $maxOrder = $dbo->loadResult();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
        }

        return empty($maxOrder) ? 1 : $maxOrder + 1;
    }

    /**
     * Retrieves parent data
     *
     * @param int $parentID the id of the parent item
     *
     * @return  array  the parent mapping
     */
    private function getParent($parentID)
    {
        $dbo         = JFactory::getDbo();
        $parentQuery = $dbo->getQuery(true);
        $parentQuery->select('*')->from('#__thm_organizer_mappings')->where("id = '$parentID'");
        $dbo->setQuery($parentQuery);

        try {
            $mappings = $dbo->loadAssoc();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return [];
        }

        return $mappings;
    }

    /**
     * Creates and returns instance of JTable for the DB Table Mappings
     *
     * @param string $name    The table name. Optional.
     * @param string $prefix  The class prefix. Optional.
     * @param array  $options Configuration array for model. Optional.
     *
     * @return  JTable
     */
    public function getTable($name = 'mappings', $prefix = 'THM_OrganizerTable', $options = [])
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    /**
     * Saves pool and dependent mappings
     *
     * @param array &$data the pool form data from the post request
     *
     * @return  boolean  true on success, otherwise false
     */
    public function savePool(&$data)
    {
        $poolData                   = [];
        $poolData['programID']      = null;
        $poolData['poolID']         = $data['id'];
        $poolData['subjectID']      = null;
        $poolData['description_de'] = $data['description_de'];
        $poolData['description_en'] = $data['description_en'];
        $poolData['display_type']   = ($data['display_type'] == 0) ? (0) : (1);
        $poolData['enable_desc']    = ($data['enable_desc'] == 0) ? (0) : (1);
        $poolData['children']       = $this->getChildrenFromForm();

        $parentIDs = $data['parentID'];
        $orderings = [];
        foreach ($parentIDs as $parentID) {
            $orderings[$parentID] = $this->getOrdering($parentID, $poolData['poolID']);
        }

        $cleanSlate = $this->deleteByResourceID($poolData['poolID'], 'pool');
        if ($cleanSlate) {
            foreach ($parentIDs as $parentID) {
                $poolData['parentID'] = $parentID;
                $poolData['ordering'] = $orderings[$parentID];
                $poolAdded            = $this->addPool($poolData);
                if (!$poolAdded) {
                    JFactory::getApplication()->enqueueMessage('admin.models.mapping.php: addPool is false', 'error');

                    return false;
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks whether the degree program root mapping has already been created.
     * If it has not already been done the creation function is called.
     *
     * @param int $programID the id of the degree program
     *
     * @return  boolean  true if the program root mapping exists/was created,
     *                   otherwise false
     */
    public function saveProgram($programID)
    {
        $dbo       = JFactory::getDbo();
        $findQuery = $dbo->getQuery(true);
        $findQuery->select('*')->from('#__thm_organizer_mappings')->where('parentID IS NULL')->where("programID = '$programID'");
        $dbo->setQuery($findQuery);

        try {
            $rootMapping = $dbo->loadAssoc();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return false;
        }

        if (empty($rootMapping)) {
            $leftQuery = $dbo->getQuery(true);
            $leftQuery->select("MAX(rgt)")->from('#__thm_organizer_mappings');
            $dbo->setQuery($leftQuery);

            try {
                $maxRgt = $dbo->loadResult();
            } catch (Exception $exc) {
                JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"),
                    'error');

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

            return $this->getTable()->save($data);
        } else {
            $children   = $this->getChildrenFromForm();
            $cleanSlate = $this->deleteChildren($rootMapping['id']);
            if (!$cleanSlate) {
                return false;
            }

            if (!empty($children) AND $cleanSlate) {
                foreach ($children as $child) {
                    $child['parentID'] = $rootMapping['id'];
                    if (isset($child['poolID'])) {
                        $childAdded = $this->addPool($child);
                        if (!$childAdded) {
                            return false;
                        }
                    } elseif (isset($child['subjectID'])) {
                        $child['level'] = $rootMapping['level'] + 1;
                        $childAdded     = $this->addSubject($child);
                        if (!$childAdded) {
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
     * @param array &$data the subject form data from the post request
     *
     * @return  boolean  true on success, otherwise false
     */
    public function saveSubject(&$data)
    {
        $subjectTemplate              = [];
        $subjectTemplate['programID'] = null;
        $subjectTemplate['poolID']    = null;
        $subjectTemplate['subjectID'] = $data['id'];

        $selectedParents  = $data['parentID'];
        $existingMappings = $this->getExistingMappings($data['id']);
        if (!empty($existingMappings)) {
            $success = $this->processExistingSubjects($selectedParents, $existingMappings);
            if (!$success) {
                return false;
            }
        }

        foreach ($selectedParents as $newParentID) {
            $subjectTemplate['ordering'] = $this->getOrdering($newParentID, $data['id'], 'subject');
            $subjectTemplate['parentID'] = $newParentID;
            $subjectAdded                = $this->addSubject($subjectTemplate);
            if (!$subjectAdded) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieves existing mappings
     *
     * @param int    $resourceID the id of the resource in its resource table
     * @param string $type       the type of resource entry being searched for
     *
     * @return  mixed  array on success, otherwise false
     */
    private function getExistingMappings($resourceID, $type = 'subject')
    {
        $query = $this->_db->getQuery(true);
        $query->select('*')->from('#__thm_organizer_mappings')->where("{$type}ID = '$resourceID'");

        try {
            $mappings = $this->_db->setQuery($query)->loadAssocList();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return false;
        }

        return $mappings;
    }

    /**
     * Processes existing subject entries against the selected parents, deleting
     * existing entries with parents no longer selected from the database, and
     * deleting selected parent entries which already exist from the selection.
     *
     * @param array &$selectedParents the parent pools selected by the user
     * @param array $existingMappings the existing mappings for the subject
     *
     * @return  boolean  true on success, otherwise false
     */
    private function processExistingSubjects(&$selectedParents, $existingMappings)
    {
        foreach ($existingMappings as $existingMapping) {
            $index = array_search($existingMapping['parentID'], $selectedParents);
            if ($index === false) {
                $deprecatedDeleted = $this->deleteEntry($existingMapping['id']);
                if (!$deprecatedDeleted) {
                    return false;
                }

                continue;
            }

            unset($selectedParents[$index]);
        }

        return true;
    }

    /**
     * Shifts the ordering for existing siblings who have an ordering at or
     * above the ordering to be inserted
     *
     * @param int $parentID    the id of the parent
     * @param int $insertOrder the ordering of the item to be inserted
     *
     * @return  boolean  true on success, otherwise false
     */
    private function shiftOrder($parentID, $insertOrder)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->update('#__thm_organizer_mappings')->set('ordering = ordering + 1');
        $query->where("ordering >= '$insertOrder'")->where("parentID = '$parentID'");
        $dbo->setQuery($query);
        try {
            $dbo->execute();
        } catch (Exception $exc) {
            return false;
        }

        return true;
    }

    /**
     * Shifts left and right values to allow for the values to be inserted
     *
     * @param int $value the integer value above which left and right values
     *                   need to be shifted
     *
     * @return  bool  true on success, otherwise false
     */
    private function shiftRight($value)
    {
        $dbo      = JFactory::getDbo();
        $lftQuery = $dbo->getQuery(true);
        $lftQuery->update('#__thm_organizer_mappings')->set('lft = lft + 2')->where("lft >= '$value'");
        $dbo->setQuery($lftQuery);
        try {
            $dbo->execute();
        } catch (Exception $exc) {
            return false;
        }

        $rgtQuery = $dbo->getQuery(true);
        $rgtQuery->update('#__thm_organizer_mappings')->set('rgt = rgt + 2')->where("rgt >= '$value'");
        $dbo->setQuery($rgtQuery);
        try {
            $dbo->execute();
        } catch (Exception $exc) {
            return false;
        }

        return true;
    }
}
