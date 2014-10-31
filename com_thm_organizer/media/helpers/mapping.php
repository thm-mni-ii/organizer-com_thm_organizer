<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerHelperMapping
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.helpers.corehelper');

/**
 * Class provides methods used by organizer files for mappings
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerHelperMapping
{
    /**
     * Retrieves a string value representing the degree programs to which the
     * pool is ordered. Used in pool and subject manager views.
     *
     * @param   string  $resourceType  the type of the mapped resource
     * @param   int     $resourceID    the id of the resource
     *
     * @return  string  string representing the associated program(s)
     */
    public static function getProgramName($resourceType, $resourceID)
    {
        $resourceRanges = THM_OrganizerHelperMapping::getResourceRanges($resourceType, $resourceID);
        if (empty($resourceRanges))
        {
            return JText::_('COM_THM_ORGANIZER_NONE');
        }
        $programs = THM_OrganizerHelperMapping::getResourcePrograms($resourceRanges);
        if (empty($programs))
        {
            return JText::_('COM_THM_ORGANIZER_NONE');
        }
        if (count($programs) === 1)
        {
            return $programs[0];
        }
        else
        {
            return JText::_('COM_THM_ORGANIZER_MULTIPLE_PROGRAMS');
        }
    }

    /**
     * Retrieves a string value representing the degree programs to which the
     * pool is ordered. Used in subject manager view.
     *
     * @param   int     $resourceID    the id of the resource
     *
     * @return  string  string representing the associated program(s)
     */
    public static function getPoolName($resourceID)
    {
        $resourceRanges = THM_OrganizerHelperMapping::getResourceRanges('subject', $resourceID);
        if (empty($resourceRanges))
        {
            return JText::_('COM_THM_ORGANIZER_NONE');
        }
        $pools = THM_OrganizerHelperMapping::getSubjectPools($resourceRanges);
        if (empty($pools))
        {
            return JText::_('COM_THM_ORGANIZER_NONE');
        }
        if (count($pools) === 1)
        {
            return $pools[0];
        }
        else
        {
            return JText::_('COM_THM_ORGANIZER_MULTIPLE_POOLS');
        }
    }


    /**
     * Retrieves the mapped left and right values for the resource's existing mappings.
     * Used in programs field, and self.
     *
     * @param   string  $resourceType  the type of the mapped resource
     * @param   int     $resourceID    the id of the mapped resource
     *
     * @return  array contains the sought left and right values
     */
    public static function getResourceRanges($resourceType, $resourceID)
    {
        $query = JFactory::getDbo()->getQuery(true);
        $query->select('DISTINCT lft, rgt')->from('#__thm_organizer_mappings');

        $allPrograms = ($resourceType == 'program' AND $resourceID == '-1');
        $allPools = ($resourceType == 'pool' AND $resourceID == '-1');
        if ($allPrograms)
        {
            $query->where("programID IS NOT NULL");
        }
        elseif ($allPools)
        {
            $query->where("poolID IS NOT NULL");
        }
        else
        {
            $query->where("{$resourceType}ID = '$resourceID'");
        }
        JFactory::getDbo()->setQuery((string) $query);

        try
        {
            $ranges = JFactory::getDbo()->loadAssocList();
            return $ranges;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Retrieves the names of the programs to which a resource is ordered. Used in self.
     *
     * @param   array  $resourceRanges  the left and right values of the resource's mappings
     *
     * @return  array  the names of the programs to which the pool is ordered
     */
    public static function getResourcePrograms($resourceRanges)
    {
        $rangeClauses = array();
        foreach ($resourceRanges AS $borders)
        {
            $rangeClauses[] = "( lft < '{$borders['lft']}' AND rgt > '{$borders['rgt']}')";
        }

        $shortTag = THM_CoreHelper::getLanguageShortTag();
        $query = JFactory::getDbo()->getQuery(true);
        $parts = array("dp.subject_$shortTag","' ('", "d.abbreviation", "' '", "dp.version", "')'");
        $select = "DISTINCT " . $query->concatenate($parts, "") . " As name";
        $query->select($select);
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $query->where($rangeClauses, 'OR');
        $query->order('name');
        JFactory::getDbo()->setQuery((string) $query);

        try
        {
            $programs = JFactory::getDbo()->loadColumn();
            return $programs;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Retrieves the names of the programs to which a resource is ordered
     *
     * @param   array  $resourceRanges  the left and right values of the resource's mappings
     *
     * @return  array  the names of the programs to which the pool is ordered
     */
    public static function getSubjectPools($ranges)
    {
        $dbo= JFactory::getDbo();
        $lftQuery = $dbo->getQuery(true);
        $lftQuery->select("lft");
        $lftQuery->from('#__thm_organizer_pools AS p');
        $lftQuery->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
        $lftQuery->order('lft DESC');

        $shortTag = THM_CoreHelper::getLanguageShortTag();
        $nameQuery = $dbo->getQuery(true);
        $nameQuery->select("DISTINCT p.name_$shortTag As name");
        $nameQuery->from('#__thm_organizer_pools AS p');
        $nameQuery->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');


        $pools = array();
        // Each range is a unique pool association
        foreach ($ranges AS $borders)
        {
            $lftQuery->clear('where');
            $lftQuery->where("poolID IS NOT NULL");
            $lftQuery->where("( lft < '{$borders['lft']}' AND rgt > '{$borders['rgt']}')");
            $dbo->setQuery((string) $lftQuery);

            try
            {
                $poolLFT = $dbo->loadResult();
                $nameQuery->clear('where');
                $nameQuery->where("lft = '$poolLFT'");
                $dbo->setQuery((string) $nameQuery);

                try
                {
                    $pools[] = $dbo->loadResult();
                }
                catch (Exception $exc)
                {
                    JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                    return array();
                }
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return array();
            }
        }
        return $pools;
    }

    /**
     * Retrieves a list of all available programs
     *
     * @return  array  the ids and names of all available programs
     *
     * @throws  exception
     */
    public static function getAllPrograms()
    {
        $shortTag = THM_CoreHelper::getLanguageShortTag();
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $parts = array("dp.subject_$shortTag","' ('", "d.abbreviation", "' '", "dp.version", "')'");
        $text = $query->concatenate($parts, "") . " As text";
        $query->select("dp.id AS value, $text");
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON dp.id = m.programID');
        $query->order('text ASC');
        $dbo->setQuery((string) $query);
        
        try 
        {
            return  $dbo->loadAssocList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Retrieves the ids of associated degree programs
     * 
     * @param   array  $ranges  the ranges for the individual subject entries
     * 
     * @return  array  the ids of the associated programs
     *
     * @throws  exception
     */
    public static function getSelectedPrograms($ranges)
    {
        $dbo = JFactory::getDbo();
        $rangeConditions = array();
        foreach ($ranges as $range)
        {
            $rangeConditions[] = "( lft < '{$range['lft']}' AND rgt > '{$range['rgt']}' )";
        }
        $rangesClause = implode(' OR ', $rangeConditions);

        $query = $dbo->getQuery(true);
        $query->select("DISTINCT dp.id");
        $query->from('#__thm_organizer_mappings AS m');
        $query->innerJoin('#__thm_organizer_programs AS dp ON m.programID = dp.id');
        $query->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->where($rangesClause);
        $dbo->setQuery((string) $query);
        
        try 
        {
            return $dbo->loadColumn();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Retrieves the parent ids of the resource in question
     *
     * @param   int      $resourceID  the resource id
     * @param   array    &$mappings   an array to store the mappings in
     * @param   array    &$parentIDs  an array to store the parent ids in
     * @param   array    &$ownIDs     an array to store the mapping ids in
     * @param   boolean  $isSubject   if the calling element is a subject
     * 
     * @return  void
     *
     * @throws  exception
     */
    public static function getMappingData($resourceID, &$mappings, &$parentIDs, &$ownIDs, $isSubject = false)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('parentID, id, lft, rgt');
        $query->from('#__thm_organizer_mappings');
        $query->where($isSubject? "subjectID = '$resourceID'" : "poolID = '$resourceID'");
        $dbo->setQuery((string) $query);

        try 
        {
            $mappings = array_merge($mappings, $dbo->loadAssocList());
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        try 
        {
            $parentIDs = array_merge($parentIDs, $dbo->loadColumn());
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        try
        {
            $ownIDs = array_merge($ownIDs, $dbo->loadColumn(1));
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
    }

    /**
     * Retrieves the ids of pool children both direct and indirect
     * 
     * @param   array  &$mappings  the current mappings of the pool
     * 
     * @return  array  the ids of the children of a pool
     *
     * @throws  exception
     */
    public static function getChildren(&$mappings)
    {
        $dbo = JFactory::getDbo();
        $children = array();
        foreach ($mappings AS $mapping)
        {
            $childrenQuery = $dbo->getQuery(true);
            $childrenQuery->select('id')->from('#__thm_organizer_mappings');
            $childrenQuery->where("lft > '{$mapping['lft']}'");
            $childrenQuery->where("rgt < '{$mapping['rgt']}'");
            $childrenQuery->where("parentID IS NULL");
            $dbo->setQuery((string) $childrenQuery);
            try 
            {
                return array_merge($children, $dbo->loadColumn());
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return array();
            }
        }
    }

    /**
     * Retrieves the mappings of superordinate programs
     * 
     * @param   array  &$mappings  the existing mappings of the element
     * 
     * @return  array  the superordinate program mappings
     *
     * @throws  exception
     */
    public static function getProgramEntries(&$mappings)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, programID, lft, rgt');
        $query->from('#__thm_organizer_mappings');

        $programs = array();
        foreach ($mappings AS $mapping)
        {
            $query->clear('where');
            $query->where("lft < '{$mapping['lft']}'");
            $query->where("rgt > '{$mapping['rgt']}'");
            $query->where("parentID IS NULL");
            $dbo->setQuery((string) $query);
            
            try 
            {
                $program = $dbo->loadAssoc();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return array();
            }
            
            if (!in_array($program, $programs))
            {
                $programs[] = $program;
            }
        }
        return $programs;
    }

    /**
     * Retrieves all mapping entries subordinate to associated degree programs
     * 
     * @param   array  &$programEntries  the program mappings themselves
     * 
     * @return  array  an array containing information for all program mappings
     *
     * @throws  exception
     */
    public static function getProgramMappings(&$programEntries)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_mappings');
        
        $programMappings = array();
        foreach ($programEntries as $programEntry)
        {
            $query->clear('where');
            $query->where("lft >= '{$programEntry['lft']}'");
            $query->where("rgt <= '{$programEntry['rgt']}'");
            $query->order('lft ASC');
            $dbo->setQuery((string) $query);
            
            try 
            {
                $results = $dbo->loadAssocList();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return array();
            }
            
            $programMappings = array_merge($programMappings, empty($results)? array() : $results);
        }
        return $programMappings;
    }

    /**
     * Gets a HTML option based upon a pool mapping
     * 
     * @param   array   &$mapping          the pool mapping entry
     * @param   string  $language          the display language
     * @param   array   &$selectedParents  the selected parents
     * 
     * @return  string  HTML option
     *
     * @throws  exception
     */
    public static function getPoolOption(&$mapping, $language, &$selectedParents)
    {
        $poolsTable = JTable::getInstance('pools', 'THM_OrganizerTable');
        
        try 
        {
            $poolsTable->load($mapping['poolID']);
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        $nameColumn = 'name_' . $language;
        $indentedName = self::getIndentedPoolName($poolsTable->$nameColumn, $mapping['level']);
        
        $selected = in_array($mapping['id'], $selectedParents)? 'selected' : '';
        return "<option value='{$mapping['id']}' $selected>$indentedName</option>";
    }

    /**
     * Provides an indentation according to the structural depth of a pool
     * 
     * @param   string  $name          the name of the pool
     * @param   int     $level         the pool's structural depth
     * @param   bool    $withPrograms  if programs will be listed with the pools
     * 
     * @return  string
     */
    public static function getIndentedPoolName($name, $level, $withPrograms = true)
    {
        if ($level == 1 and $withPrograms == false)
        {
            return $name;
        }

        $iteration = $withPrograms? 0 : 1;
        $indent = '';
        while ($iteration < $level)
        {
            $indent .= "&nbsp;&nbsp;&nbsp;";
            $iteration++;
        }

        return $indent . "|_" . $name;
    }

    /**
     * Gets a HTML option based upon a program mapping
     * 
     * @param   array    &$mapping          the program mapping entry
     * @param   string   $shortTag          the display language
     * @param   array    &$selectedParents  the selected parents
     * @param   boolean  $isSubject         if the calling element is a subject
     * 
     * @return  string  HTML option
     *
     * @throws  exception
     */
    public static function getProgramOption(&$mapping, $shortTag, &$selectedParents, $isSubject = false)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $parts = array("dp.subject_$shortTag","' ('", "d.abbreviation", "' '", "dp.version", "')'");
        $text = $query->concatenate($parts, "") . " As text";
        $query->select($text);
        $query->from('#__thm_organizer_programs AS dp');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $query->where("dp.id = '{$mapping['programID']}'");
        $dbo->setQuery((string) $query);
        try 
        {
            $name = $dbo->loadResult();
            $selected = in_array($mapping['id'], $selectedParents)? 'selected' : '';
            $disabled = $isSubject? 'disabled' : '';
            return "<option value='{$mapping['id']}' $selected $disabled>$name</option>";
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return '';
        }

    }

    /**
     * Retrieves the mapping boundaries of the selected resource
     *
     * @param   string  $resourceType  the type of the selected resource
     * @param   int     $resourceID    the id of the selected resource
     *
     * @return  mixed  array with boundary values on success, otherwise false
     *
     * @throws  exception
     */
    public static function getBoundaries($resourceType, $resourceID)
    {
        if ($resourceID == '-1')
        {
            return false;
        }
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('lft, rgt')->from('#__thm_organizer_mappings');
        $query->where("{$resourceType}ID = '$resourceID'");
        $dbo->setQuery((string) $query);
        
        try 
        {
            return $dbo->loadAssoc();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Retrieves the nested slice values for subjects associated with the
     * selected teacher
     * 
     * @return  mixed  array on success, otherwise boolean false
     *
     * @throws  exception
     */
    public static function getTeacherMappingClauses()
    {
        $teacherID = JFactory::getApplication()->input->getInt('teacherID', 0);
        if (empty($teacherID) OR $teacherID == '-1' OR $teacherID == 'null')
        {
            return false;
        }

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $concateMappingClause = array("'m.lft <= '", 'm.lft', "' AND m.rgt >= '", 'm.rgt');
        $mappingClause = $query->concatenate($concateMappingClause);
        $query->select("DISTINCT $mappingClause");
        $query->from('#__thm_organizer_subject_teachers AS st');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = st.subjectID');
        $query->where("st.teacherID = '$teacherID'");
        $dbo->setQuery((string) $query);
        
        try 
        {
            return $dbo->loadColumn();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }
}
