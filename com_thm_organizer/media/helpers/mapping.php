<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Provides general functions for mapping data retrieval.
 */
class THM_OrganizerHelperMapping
{
    /**
     * Retrieves a list of all available programs
     *
     * @return array  the ids and names of all available programs
     */
    public static function getAllPrograms()
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $dbo      = JFactory::getDbo();
        $query    = $dbo->getQuery(true);
        $parts    = ["dp.name_$shortTag", "' ('", 'd.abbreviation', "' '", 'dp.version', "')'"];
        $text     = $query->concatenate($parts, '') . ' AS text';
        $query->select("dp.id AS value, $text");
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON dp.id = m.programID');
        $query->order('text ASC');
        $dbo->setQuery($query);

        return THM_OrganizerHelperComponent::query('loadAssocList');
    }

    /**
     * Retrieves the mapping boundaries of the selected resource
     *
     * @param string  $resourceType      the type of the selected resource
     * @param int     $resourceID        the id of the selected resource
     * @param boolean $excludeChildPools whether the return values should have child pools filtered out
     *
     * @return mixed  array with boundary values on success, otherwise false
     */
    public static function getBoundaries($resourceType, $resourceID, $excludeChildPools = true)
    {
        $invalidID = (empty($resourceID) or $resourceID == 'null' or $resourceID == '-1');
        if ($invalidID) {
            return false;
        }

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("{$resourceType}ID, lft, rgt")->from('#__thm_organizer_mappings');
        $query->where("{$resourceType}ID = '$resourceID'");
        $dbo->setQuery($query);

        $ufBoundarySet = THM_OrganizerHelperComponent::query('loadAssocList', []);

        if ($resourceType == 'program' or $resourceType == 'subject' or !$excludeChildPools) {
            return $ufBoundarySet;
        }

        $filteredBoundarySet = [];

        foreach ($ufBoundarySet as $ufBoundaries) {
            $filteredBoundaries  = self::removeExclusions($ufBoundaries);
            $filteredBoundarySet = array_merge($filteredBoundarySet);
        }

        return $filteredBoundaries;
    }

    /**
     * Retrieves the ids of both direct and indirect pool children
     *
     * @param array &$mappings the current mappings of the pool
     *
     * @return array  the ids of the children of a pool
     */
    public static function getChildren(&$mappings)
    {
        $dbo      = JFactory::getDbo();
        $children = [];

        // The children should be the same regardless of which mapping is used, so we just take the last one
        $mapping = array_pop($mappings);

        // If mappings was empty mapping can be null
        if (empty($mapping)) {
            return [];
        }

        $childrenQuery = $dbo->getQuery(true);
        $childrenQuery->select('id')->from('#__thm_organizer_mappings');
        $childrenQuery->where("lft > '{$mapping['lft']}'");
        $childrenQuery->where("rgt < '{$mapping['rgt']}'");
        $dbo->setQuery($childrenQuery);

        return array_merge($children, THM_OrganizerHelperComponent::query('loadColumn', []));
    }

    /**
     * Provides an indentation according to the structural depth of a pool
     *
     * @param string $name         the name of the pool
     * @param int    $level        the pool's structural depth
     * @param bool   $withPrograms if programs will be listed with the pools
     *
     * @return string
     */
    public static function getIndentedPoolName($name, $level, $withPrograms = true)
    {
        if ($level == 1 and $withPrograms == false) {
            return $name;
        }

        $iteration = $withPrograms ? 0 : 1;
        $indent    = '';
        while ($iteration < $level) {
            $indent .= '&nbsp;&nbsp;&nbsp;';
            $iteration++;
        }

        return $indent . '|_' . $name;
    }

    /**
     * Retrieves a string value representing the degree programs to which the
     * pool is ordered. Used in subject manager view.
     *
     * @param int $resourceID the id of the resource
     *
     * @return string  string representing the associated program(s)
     */
    public static function getPoolName($resourceID)
    {
        $resourceRanges = self::getResourceRanges('subject', $resourceID);
        if (empty($resourceRanges)) {
            return JText::_('JNONE');
        }

        $pools = self::getSubjectPools($resourceRanges);
        if (empty($pools)) {
            return JText::_('JNONE');
        }

        if (count($pools) === 1) {
            return $pools[0];
        } else {
            return JText::_('COM_THM_ORGANIZER_MULTIPLE_POOLS');
        }
    }

    /**
     * Gets a HTML option based upon a pool mapping
     *
     * @param array &$mapping         the pool mapping entry
     * @param array &$selectedParents the selected parents
     *
     * @return string  HTML option
     */
    public static function getPoolOption(&$mapping, &$selectedParents)
    {
        $shortTag   = THM_OrganizerHelperLanguage::getShortTag();
        $poolsTable = JTable::getInstance('pools', 'THM_OrganizerTable');

        try {
            $poolsTable->load($mapping['poolID']);
        } catch (Exception $exc) {
            THM_OrganizerHelperComponent::message($exc->getMessage(), 'error');

            return '';
        }

        $nameColumn   = "name_$shortTag";
        $indentedName = self::getIndentedPoolName($poolsTable->$nameColumn, $mapping['level']);

        $selected = in_array($mapping['id'], $selectedParents) ? 'selected' : '';

        return "<option value='{$mapping['id']}' $selected>$indentedName</option>";
    }

    /**
     * Retrieves the mappings of superordinate programs
     *
     * @param array &$mappings the existing mappings of the element
     *
     * @return array  the superordinate program mappings
     */
    public static function getProgramEntries(&$mappings)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, programID, lft, rgt');
        $query->from('#__thm_organizer_mappings');

        $programs = [];
        foreach ($mappings as $mapping) {
            $query->clear('where');
            $query->where("lft < '{$mapping['lft']}'");
            $query->where("rgt > '{$mapping['rgt']}'");
            $query->where('parentID IS NULL');
            $dbo->setQuery($query);
            $program = THM_OrganizerHelperComponent::query('loadAssoc', []);

            if (!empty($program) and !in_array($program, $programs)) {
                $programs[] = $program;
            }
        }

        return $programs;
    }

    /**
     * Retrieves all mapping entries subordinate to associated degree programs
     *
     * @param array &$programEntries the program mappings themselves
     *
     * @return array  an array containing information for all program mappings
     */
    public static function getProgramMappings(&$programEntries)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_mappings');

        $programMappings = [];
        foreach ($programEntries as $programEntry) {
            $query->clear('where');
            $query->where("lft >= '{$programEntry['lft']}'");
            $query->where("rgt <= '{$programEntry['rgt']}'");
            $query->order('lft ASC');
            $dbo->setQuery($query);

            $results = THM_OrganizerHelperComponent::query('loadAssocList');
            if (empty($results)) {
                continue;
            }

            $programMappings = array_merge($programMappings, empty($results) ? [] : $results);
        }

        return $programMappings;
    }

    /**
     * Retrieves a string value representing the degree programs to which the
     * pool is ordered. Used in pool and subject manager views.
     *
     * @param string $resourceType the type of the mapped resource
     * @param int    $resourceID   the id of the resource
     *
     * @return string  string representing the associated program(s)
     */
    public static function getProgramName($resourceType, $resourceID)
    {
        $resourceRanges = self::getResourceRanges($resourceType, $resourceID);
        if (empty($resourceRanges)) {
            return JText::_('JNONE');
        }

        $programs = self::getResourcePrograms($resourceRanges);
        if (empty($programs)) {
            return JText::_('JNONE');
        }

        if (count($programs) === 1) {
            return $programs[0];
        } else {
            return JText::_('COM_THM_ORGANIZER_MULTIPLE_PROGRAMS');
        }
    }

    /**
     * Gets a HTML option based upon a program mapping
     *
     * @param array  &$mapping         the program mapping entry
     * @param array  &$selectedParents the selected parents
     * @param string  $resourceType    the type of resource
     *
     * @return string  HTML option
     */
    public static function getProgramOption(&$mapping, &$selectedParents, $resourceType)
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $dbo      = JFactory::getDbo();
        $query    = $dbo->getQuery(true);
        $parts    = ["dp.name_$shortTag", "' ('", 'd.abbreviation', "' '", 'dp.version', "')'"];
        $text     = $query->concatenate($parts, '') . ' AS text';
        $query->select($text);
        $query->from('#__thm_organizer_programs AS dp');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $query->where("dp.id = '{$mapping['programID']}'");
        $dbo->setQuery($query);

        $name = THM_OrganizerHelperComponent::query('loadResult');

        if (empty($name)) {
            return '';
        }

        if ($resourceType == 'subject') {
            $selected = '';
            $disabled = 'disabled';
        } else {
            $selected = in_array($mapping['id'], $selectedParents) ? 'selected' : '';
            $disabled = '';
        }

        return "<option value='{$mapping['id']}' $selected $disabled>$name</option>";
    }

    /**
     * Retrieves the names of the programs to which a resource is ordered. Used in self.
     *
     * @param array $resourceRanges the left and right values of the resource's mappings
     * @param bool  $getIDs         whether or not the program ids should be included in the return value
     *
     * @return mixed array the names of the programs to which the pool is ordered on success, otherwise false
     */
    public static function getResourcePrograms($resourceRanges, $getIDs = false)
    {
        $rangeClauses = [];
        foreach ($resourceRanges as $borders) {
            $rangeClauses[] = "( lft < '{$borders['lft']}' AND rgt > '{$borders['rgt']}')";
        }

        $dbo      = JFactory::getDbo();
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $query    = $dbo->getQuery(true);
        $parts    = ["dp.name_$shortTag", "' ('", 'd.abbreviation', "' '", 'dp.version', "')'"];
        $select   = 'DISTINCT ' . $query->concatenate($parts, '') . ' AS name, dp.id AS id';
        $query->select($select);
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $query->where($rangeClauses, 'OR');
        $query->order('name');
        $dbo->setQuery($query);

        if ($getIDs) {
            return THM_OrganizerHelperComponent::query('loadAssocList', null, 'id');
        }

        return THM_OrganizerHelperComponent::query('loadColumn', []);
    }

    /**
     * Retrieves the mapped left and right values for the resource's existing mappings.
     * Used in programs field, and self.
     *
     * @param string $resourceType the type of the mapped resource
     * @param int    $resourceID   the id of the mapped resource
     *
     * @return array contains the sought left and right values
     */
    public static function getResourceRanges($resourceType, $resourceID)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT lft, rgt')->from('#__thm_organizer_mappings');

        $allPrograms = ($resourceType == 'program' and $resourceID == '-1');
        $allPools    = ($resourceType == 'pool' and $resourceID == '-1');
        if ($allPrograms) {
            $query->where('programID IS NOT NULL');
        } elseif ($allPools) {
            $query->where('poolID IS NOT NULL');
        } else {
            $query->where("{$resourceType}ID = '$resourceID'");
        }

        $dbo->setQuery($query);

        return THM_OrganizerHelperComponent::query('loadAssocList', []);
    }

    /**
     * Retrieves the set of program boundaries for the programs to which this subject is associated.
     *
     * @param int $subjectID the id of the subject
     *
     * @return array the program boundaries
     */
    public static function getSubjectPrograms($subjectID)
    {
        $subjectBoundaries = THM_OrganizerHelperMapping::getBoundaries('subject', $subjectID);

        // Subject does not yet have any mappings. Improbable, but possible
        if (empty($subjectBoundaries)) {
            return [];
        }

        $programs = self::getResourcePrograms($subjectBoundaries, true);

        if (empty($programs)) {
            return [];
        }

        foreach (array_keys($programs) as $programID) {
            $programBoundaries = self::getBoundaries('program', $programID);

            if (!empty($programBoundaries)) {
                $programs[$programID]['lft'] = $programBoundaries[0]['lft'];
                $programs[$programID]['rgt'] = $programBoundaries[0]['rgt'];
            }
        }

        return $programs;
    }

    /**
     * Retrieves the names of the programs to which a resource is ordered
     *
     * @param array $ranges the left and right values of the resource's mappings
     *
     * @return array  the names of the programs to which the pool is ordered
     */
    public static function getSubjectPools($ranges)
    {
        $dbo      = JFactory::getDbo();
        $lftQuery = $dbo->getQuery(true);
        $lftQuery->select('lft');
        $lftQuery->from('#__thm_organizer_pools AS p');
        $lftQuery->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
        $lftQuery->order('lft DESC');

        $shortTag  = THM_OrganizerHelperLanguage::getShortTag();
        $nameQuery = $dbo->getQuery(true);
        $nameQuery->select("DISTINCT p.name_$shortTag AS name");
        $nameQuery->from('#__thm_organizer_pools AS p');
        $nameQuery->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');

        $pools = [];

        // Each range is a unique pool association
        foreach ($ranges as $borders) {
            $lftQuery->clear('where');
            $lftQuery->where('poolID IS NOT NULL');
            $lftQuery->where("( lft < '{$borders['lft']}' AND rgt > '{$borders['rgt']}')");
            $dbo->setQuery($lftQuery);

            $poolLFT = THM_OrganizerHelperComponent::query('loadResult');
            if (empty($poolLFT)) {
                continue;
            }

            $nameQuery->clear('where');
            $nameQuery->where("lft = '$poolLFT'");
            $dbo->setQuery($nameQuery);

            $pools[] = THM_OrganizerHelperComponent::query('loadResult');
        }

        return $pools;
    }

    /**
     * Retrieves the ids of associated degree programs
     *
     * @param array $ranges the ranges for the individual subject entries
     *
     * @return array  the ids of the associated programs
     */
    public static function getSelectedPrograms($ranges)
    {
        $dbo             = JFactory::getDbo();
        $rangeConditions = [];
        foreach ($ranges as $range) {
            $rangeConditions[] = "( lft < '{$range['lft']}' AND rgt > '{$range['rgt']}' )";
        }

        $rangesClause = implode(' OR ', $rangeConditions);

        $query = $dbo->getQuery(true);
        $query->select('DISTINCT dp.id');
        $query->from('#__thm_organizer_mappings AS m');
        $query->innerJoin('#__thm_organizer_programs AS dp ON m.programID = dp.id');
        $query->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->where($rangesClause);
        $dbo->setQuery($query);

        return THM_OrganizerHelperComponent::query('loadColumn', []);
    }

    /**
     * Retrieves the nested slice values for subjects associated with the
     * selected teacher
     *
     * @return mixed  array on success, otherwise null
     */
    public static function getTeacherMappingClauses()
    {
        $teacherID = THM_OrganizerHelperComponent::getInput()->getInt('teacherID', 0);
        if (empty($teacherID) or $teacherID == '-1' or $teacherID == 'null') {
            return null;
        }

        $dbo                  = JFactory::getDbo();
        $query                = $dbo->getQuery(true);
        $concateMappingClause = ["'m.lft <= '", 'm.lft', "' AND m.rgt >= '", 'm.rgt'];
        $mappingClause        = $query->concatenate($concateMappingClause);
        $query->select("DISTINCT $mappingClause");
        $query->from('#__thm_organizer_subject_teachers AS st');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = st.subjectID');
        $query->where("st.teacherID = '$teacherID'");
        $dbo->setQuery($query);

        return THM_OrganizerHelperComponent::query('loadColumn', []);
    }

    /**
     * Retrieves the mapping boundaries of the selected resource
     *
     * @param int $boundaries the boundaries of a single pool
     *
     * @return array  array of arrays with boundary values
     */
    public static function removeExclusions($boundaries)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('lft, rgt')->from('#__thm_organizer_mappings');
        $query->where('poolID IS NOT NULL');
        $query->where("lft > '{$boundaries['lft']}' AND rgt < '{$boundaries['rgt']}'");
        $query->order('lft');
        $dbo->setQuery($query);

        $exclusions = THM_OrganizerHelperComponent::query('loadAssocList');
        if (empty($exclusions)) {
            return [$boundaries];
        }

        $boundarySet = [];
        foreach ($exclusions as $exclusion) {
            // Child has no children => has no impact on output
            if ($exclusion['lft'] + 1 == $exclusion['rgt']) {
                continue;
            }

            // Not an immediate child
            if ($exclusion['lft'] != $boundaries['lft'] + 1) {
                // Create a new boundary from the current left to the exclusion
                $boundary = ['lft' => $boundaries['lft'], 'rgt' => $exclusion['lft']];

                // Change the new left to the other side of the exclusion
                $boundaries['lft'] = $exclusion['rgt'];

                $boundarySet[] = $boundary;
            } else {
                // Change the new left to the other side of the exclusion
                $boundaries['lft'] = $exclusion['rgt'];
            }

            if ($boundaries['lft'] >= $boundaries['rgt']) {
                break;
            }
        }

        // Remnants after exclusions still exist
        if ($boundaries['lft'] < $boundaries['rgt']) {
            $boundarySet[] = $boundaries;
        }

        return $boundarySet;
    }

    /**
     * Retrieves the parent ids of the resource in question. Used in parentpool field.
     *
     * @param int     $resourceID   the resource id
     * @param string  $resourceType the type of resource
     * @param array  &$mappings     an array to store the mappings in
     * @param array  &$mappingIDs   an array to store the mapping ids in
     * @param array  &$parentIDs    an array to store the parent ids in
     *
     * @return void
     */
    public static function setMappingData($resourceID, $resourceType, &$mappings, &$mappingIDs, &$parentIDs)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, parentID, lft, rgt');
        $query->from('#__thm_organizer_mappings');
        $query->where("{$resourceType}ID = '$resourceID'");
        $dbo->setQuery($query);
        $mappings   = array_merge($mappings, THM_OrganizerHelperComponent::query('loadAssocList', []));
        $mappingIDs = array_merge($mappingIDs, THM_OrganizerHelperComponent::query('loadColumn', []));
        $parentIDs  = array_merge($parentIDs, THM_OrganizerHelperComponent::query('loadColumn', [], 1));
    }

    /**
     * Sets the program id filter for a query. Used in pool manager and subject manager.
     *
     * @param object &$query            the query object
     * @param int     $resourceID       the id of the resource from the filter
     * @param string  $resourceType     the type of the resource from the filter
     * @param string  $formResourceType the type of the resource from the form
     *
     * @return void  sets query object variables
     */
    public static function setResourceIDFilter(&$query, $resourceID, $resourceType, $formResourceType)
    {
        $invalid = (empty($resourceID) or empty($resourceType) or empty($formResourceType));
        if ($invalid) {
            return;
        }

        $ranges = self::getResourceRanges($resourceType, $resourceID);
        if (empty($ranges)) {
            return;
        }

        $alias = $resourceType == 'pool' ? 'm1' : 'm2';
        $query->leftJoin("#__thm_organizer_mappings AS $alias ON $alias.{$formResourceType}ID = {$formResourceType[0]}.id");

        // No associations
        if ($resourceID == '-1') {
            // Mapping exists but erroneous
            $erray = [];

            foreach ($ranges as $range) {
                $erray[] = "( $alias.lft NOT BETWEEN '{$range['lft']}' AND '{$range['rgt']}' )";
                $erray[] = "( $alias.rgt NOT BETWEEN '{$range['lft']}' AND '{$range['rgt']}' )";
            }

            $errorClauses = implode(' AND ', $erray);
            $query->where("( ($errorClauses) OR $alias.id IS NULL ) ");

            return;
        }

        // Specific association
        $query->where("$alias.lft > '{$ranges[0]['lft']}'");
        $query->where("$alias.rgt < '{$ranges[0]['rgt']}'");
    }
}
