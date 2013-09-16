<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerHelperMapping
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

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
     * Retrieves a list of all available programs
     * 
     * @return  array  the ids and names of all available programs
     */
    public static function getAllPrograms()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("dp.id AS value, CONCAT(dp.subject, ' (', d.abbreviation, ' ', dp.version, ')') AS program");
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON dp.id = m.programID');
        $query->order('program ASC');
        $dbo->setQuery((string) $query);
        return $dbo->loadAssocList();
    }

    /**
     * Retrieves the ranges for the resource mappings
     * 
     * @param   string  $column      the name of the column to be searched
     * @param   int     $resourceID  the id of the resource in its native table
     * 
     * @return  array  the left and right values of the resource's mappings
     */
    public static function getRanges($column, $resourceID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('lft, rgt');
        $query->from('#__thm_organizer_mappings');
        $query->where("$column = '$resourceID'");
        $dbo->setQuery((string) $query);
        return $dbo->loadAssocList();
    }

    /**
     * Retrieves the ids of associated degree programs
     * 
     * @param   array  $ranges  the ranges for the individual subject entries
     * 
     * @return  array  the ids of the associated programs
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
        return $dbo->loadResultArray();
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
     */
    public static function getMappingData($resourceID, &$mappings, &$parentIDs, &$ownIDs, $isSubject = false)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('parentID, id, lft, rgt');
        $query->from('#__thm_organizer_mappings');
        $query->where($isSubject? "subjectID = '$resourceID'" : "poolID = '$resourceID'");
        $dbo->setQuery((string) $query);
        $mappings = array_merge($mappings, $dbo->loadAssocList());
        $parentIDs = array_merge($parentIDs, $dbo->loadResultArray());
        $ownIDs = array_merge($ownIDs, $dbo->loadResultArray(1));
    }

    /**
     * Retrieves the ids of pool children both direct and indirect
     * 
     * @param   array  &$mappings  the current mappings of the pool
     * 
     * @return  array  the ids of the children of a pool
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
            $children = array_merge($children, $dbo->loadResultArray());
        }
        return $children;
    }
    /**
     * Retrieves the mappings of superordinate programs
     * 
     * @param   array  &$mappings  the existing mappings of the element
     * 
     * @return  array  the superordinate program mappings
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
            $program = $dbo->loadAssoc();
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
            $results = $dbo->loadAssocList();
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
     */
    public static function getPoolOption(&$mapping, $language, &$selectedParents)
    {
        $poolsTable = JTable::getInstance('pools', 'THM_OrganizerTable');
        $poolsTable->load($mapping['poolID']);

        $level = 0;
        $indent = '';
        while ($level < $mapping['level'])
        {
            $indent .= "&nbsp;&nbsp;&nbsp;";
            $level++;
        }
        
        $nameColumn = 'name_' . $language[0];
        $name = $indent . "|_" . $poolsTable->$nameColumn;
        $selected = in_array($mapping['id'], $selectedParents)? 'selected' : '';
        return "<option value='{$mapping['id']}' $selected>$name</option>";
    }

    /**
     * Gets a HTML option based upon a program mapping
     * 
     * @param   array    &$mapping          the program mapping entry
     * @param   array    &$selectedParents  the selected parents
     * @param   boolean  $isSubject         if the calling element is a subject
     * 
     * @return  string  HTML option
     */
    public static function getProgramOption(&$mapping, &$selectedParents, $isSubject = false)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select(" CONCAT( dp.subject, ', (', d.abbreviation, ' ', dp.version, ')') AS name");
        $query->from('#__thm_organizer_programs AS dp');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $query->where("dp.id = '{$mapping['programID']}'");
        $dbo->setQuery((string) $query);
        $name = $dbo->loadResult();
        $selected = in_array($mapping['id'], $selectedParents)? 'selected' : '';
        $disabled = $isSubject? 'disabled' : '';
        return "<option value='{$mapping['id']}' $selected $disabled>$name</option>";
    }
}