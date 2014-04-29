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
     * Retrieves a list of mapped programs
     *
     * @return  mixed  an array of mapped programs on success, otherwise null
     */
    public static function getPrograms()
    {
        $language = explode('-', JFactory::getLanguage()->getTag());
        $dbo = JFactory::getDbo();
        $nameQuery = $dbo->getQuery(true);
        $nameQuery->select("dp.id, CONCAT( dp.subject_{$language[0]}, ', (', d.abbreviation, ' ', dp.version, ')') AS name");
        $nameQuery->from('#__thm_organizer_programs AS dp');
        $nameQuery->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $nameQuery->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $nameQuery->order('name');
        $dbo->setQuery((string) $nameQuery);
        return $dbo->loadAssocList();
    }

    /**
     * Sets the children to be used in form output
     * 
     * @param   object  &$model    the model calling the function
     * @param   array   $children  the children of the resource modeled
     * 
     * @return  void
     */
    public static function setChildren(&$model, $children)
    {
        if (!empty($children))
        {
            $model->children = array();
            foreach ($children as $child)
            {
                $model->children[$child['ordering']] = array();
                if (!empty($child['poolID']))
                {
                    $formID = $child['poolID'] . 'p';
                }
                else
                {
                    $formID = $child['subjectID'] . 's';
                }
                $model->children[$child['ordering']]['id'] = $formID;
                $model->children[$child['ordering']]['name'] = self::getChildName($formID);
                $model->children[$child['ordering']]['poolID'] = $child['poolID'];
                $model->children[$child['ordering']]['subjectID'] = $child['subjectID'];
            }
        }
    }

    /**
     * Retrieves the child's name from the database
     * 
     * @param   string  $formID  the id used for the child element in the form
     * 
     * @return  string  the name of the child element
     */
    private static function getChildName($formID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $language = explode('-', JFactory::getLanguage()->getTag());
        $type = strpos($formID, 'p')? 'pool' : 'subject';
        $tableID = substr($formID, 0, strlen($formID) - 1);
 
        $query->select("name_{$language[0]}");
        $query->from("#__thm_organizer_{$type}s");
        $query->where("id = '$tableID'");

        $dbo->setQuery((string) $query);
        return $dbo->loadResult();
    }

    /**
     * Retrieves a list of all available programs
     * 
     * @return  array  the ids and names of all available programs
     */
    public static function getAllPrograms()
    {
        $language = explode('-', JFactory::getLanguage()->getTag());
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("dp.id AS value, CONCAT(dp.subject_{$language[0]}, ' (', d.abbreviation, ' ', dp.version, ')') AS program");
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
        return $dbo->loadColumn();
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
        $parentIDs = array_merge($parentIDs, $dbo->loadColumn());
        $ownIDs = array_merge($ownIDs, $dbo->loadColumn(1));
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
            $children = array_merge($children, $dbo->loadColumn());
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

        $nameColumn = 'name_' . $language[0];
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
     * @param   array    &$selectedParents  the selected parents
     * @param   boolean  $isSubject         if the calling element is a subject
     * 
     * @return  string  HTML option
     */
    public static function getProgramOption(&$mapping, &$selectedParents, $isSubject = false)
    {
        $language = explode('-', JFactory::getLanguage()->getTag());
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select(" CONCAT( dp.subject_{$language[0]}, ', (', d.abbreviation, ' ', dp.version, ')') AS name");
        $query->from('#__thm_organizer_programs AS dp');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $query->where("dp.id = '{$mapping['programID']}'");
        $dbo->setQuery((string) $query);
        $name = $dbo->loadResult();
        $selected = in_array($mapping['id'], $selectedParents)? 'selected' : '';
        $disabled = $isSubject? 'disabled' : '';
        return "<option value='{$mapping['id']}' $selected $disabled>$name</option>";
    }

    /**
     * Retrieves the mapping boundaries of the selected resource
     * 
     * @param   string  $resourceType  the type of the selected resource
     * @param   int     $resourceID    the id of the selected resource
     * 
     * @return  mixed  array with boundary values on success, otherwise false
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
        return $dbo->loadAssoc();
    }

    /**
     * Retrieves the nested slice values for subjects associated with the
     * selected teacher
     * 
     * @return  mixed  array on success, otherwise boolean false
     */
    public static function getTeacherMappingClauses()
    {
        $teacherID = JFactory::getApplication()->input->get('teacherID', null, 'INT');
        if (empty($teacherID) OR $teacherID == '-1' OR $teacherID == 'null')
        {
            return false;
        }

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT CONCAT('m.lft <= ', m.lft, ' AND m.rgt >= ', m.rgt)");
        $query->from('#__thm_organizer_subject_teachers AS st');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = st.subjectID');
        $query->where("st.teacherID = '$teacherID'");
        $dbo->setQuery((string) $query);
        return $dbo->loadColumn();
    }
}