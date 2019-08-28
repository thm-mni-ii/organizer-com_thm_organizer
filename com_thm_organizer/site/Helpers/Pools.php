<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;

/**
 * Provides general functions for (subject) pool access checks, data retrieval and display.
 */
class Pools extends ResourceHelper implements Selectable
{
    use Filtered;

    /**
     * Fills the options array with HTML pool options
     *
     * @param array   &$options             an array to store the options in
     * @param array   &$programMappings     mappings belonging to one of the requested programs
     * @param array   &$unelectableMappings mappings which would lead to data inconsistency
     * @param array   &$parentIDs           previously mapped parents
     * @param boolean  $resourceType        the resource's type
     *
     * @return void
     */
    private static function fillOptions(&$options, &$programMappings, &$unelectableMappings, &$parentIDs, $resourceType)
    {
        foreach ($programMappings as $mapping) {
            if (!empty($mapping['subjectID'])
                or (!empty($unelectableMappings) and in_array($mapping['id'], $unelectableMappings))
            ) {
                continue;
            }

            if (!empty($mapping['poolID'])) {
                $options[] = Mappings::getPoolOption($mapping, $parentIDs);
            } else {
                $options[] = Mappings::getProgramOption($mapping, $parentIDs, $resourceType);
            }
        }
    }

    /**
     * Creates a text for the required pool credit points
     *
     * @param object $pool the pool
     *
     * @return string  the required amount of credit points
     */
    public static function getCrPText($pool)
    {
        $minCrPExists = !empty($pool['minCrP']);
        $maxCrPExists = !empty($pool['maxCrP']);
        if ($maxCrPExists and $minCrPExists) {
            return $pool['maxCrP'] === $pool['minCrP'] ?
                "{$pool['maxCrP']} CrP" : "{$pool['minCrP']} - {$pool['maxCrP']} CrP";
        } else {
            if ($maxCrPExists) {
                return "max. {$pool['maxCrP']} CrP";
            } elseif ($minCrPExists) {
                return "min. {$pool['minCrP']} CrP";
            }
        }

        return '';
    }

    /**
     * Retrieves the pool's full name if existent.
     *
     * @param int $poolID the table's pool id
     *
     * @return string the full name, otherwise an empty string
     */
    public static function getFullName($poolID)
    {
        $table  = self::getTable();
        $exists = $table->load($poolID);

        return $exists ? $table->full_name : '';
    }

    /**
     * Retrieves the selectable options for the resource.
     *
     * @param string $access any access restriction which should be performed
     *
     * @return array the available options
     */
    public static function getOptions($access = '')
    {
        $options = [];
        foreach (self::getResources($access) as $pool) {
            $options[] = HTML::_('select.option', $pool['id'], $pool['name']);
        }

        return $options;
    }

    /**
     * Retrieves pool options for a given curriculum element
     *
     * @return string
     */
    public static function getParentOptions()
    {
        $resourceID     = Input::getID();
        $resourceType   = Input::getCMD('type');
        $programIDs     = Input::getFilterIDs('program');
        $programEntries = self::getProgramEntries($programIDs);
        $options        = [];
        $options[]      = '<option value="-1">' . Languages::_('JNONE') . '</option>';

        $invalidRequest = (empty($resourceID) or empty($resourceType));
        $none           = ($invalidRequest or empty($programEntries));
        if ($none) {
            return $options[0];
        }

        $programMappings     = Mappings::getProgramMappings($programEntries);
        $onlyProgramMappings = count($programEntries) == count($programMappings);
        if ($onlyProgramMappings and $resourceType == 'subject') {
            return $options[0];
        }

        $mappings = $mappingIDs = $parentIDs = [];
        Mappings::setMappingData($resourceID, $resourceType, $mappings, $mappingIDs, $parentIDs);
        $unSelectableMappings = self::getUnselectableMappings($mappings, $mappingIDs, $resourceType);
        self::fillOptions($options, $programMappings, $unSelectableMappings, $parentIDs, $resourceType);

        return implode('', $options);
    }

    /**
     * Retrieves the mappings of superordinate programs
     *
     * @param array $programIDs the requested program ids
     *
     * @return array  the superordinate program mappings
     */
    private static function getProgramEntries($programIDs)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, programID, lft, rgt');
        $query->from('#__thm_organizer_mappings');
        $query->where("programID IN ( '" . implode("', '", $programIDs) . "' )");
        $query->order('lft ASC');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList');
    }

    /**
     * Gets an array modelling the attributes of the resource.
     *
     * @param $resourceID
     *
     * @return array
     */
    public static function getResource($resourceID)
    {
        $table  = self::getTable();
        $exists = $table->load($resourceID);

        if (!$exists) {
            return [];
        }

        $tag  = Languages::getTag();
        $pool = [
            'abbreviation' => $table->{"abbreviation_$tag"},
            'bgColor'      => Fields::getColor($table->fieldID),
            'description'  => $table->{"description_$tag"},
            'field'        => Fields::getName($table->fieldID),
            'fieldID'      => $table->fieldID,
            'id'           => $table->id,
            'maxCrP'       => $table->maxCrP,
            'minCrP'       => $table->minCrP,
            'name'         => $table->{"name_$tag"},
            'shortName'    => $table->{"shortName_$tag"},
        ];

        return $pool;
    }

    /**
     * Retrieves the resource items.
     *
     * @param string $access any access restriction which should be performed
     *
     * @return array the available resources
     */
    public static function getResources($access = '')
    {
        $programID = Input::getFilterID('program');
        $poolID    = Input::getInput()->get->getInt('poolID');
        if (empty($programID) and empty($poolID)) {
            return [];
        }

        $ranges = $poolID ?
            Mappings::getResourceRanges('pool', $poolID) : Mappings::getResourceRanges('program', $programID);
        if (empty($ranges)) {
            return [];
        }

        $tag   = Languages::getTag();
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT p.*, p.name_$tag AS name")
            ->from('#__thm_organizer_pools AS p')
            ->innerJoin('#__thm_organizer_mappings AS m ON p.id = m.poolID')
            ->where("lft > '{$ranges[0]['lft']}'")
            ->where("rgt < '{$ranges[0]['rgt']}'")
            ->order('name ASC');

        if (!empty($access)) {
            self::addAccessFilter($query, 'p', $access);
        }

        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }

    /**
     * Retrieves an array of mappings which should not be available for selection
     * as the parent of the resource
     *
     * @param array  &$mappings     the existing mappings of the resource
     * @param array  &$mappingIDs   the mapping ids for the resource
     * @param string  $resourceType the resource's type
     *
     * @return array  the ids which should be unselectable
     */
    private static function getUnselectableMappings(&$mappings, &$mappingIDs, $resourceType)
    {
        if ($resourceType == 'subject') {
            return [];
        }

        $children = Mappings::getChildMappingIDs($mappings);

        return array_merge($mappingIDs, $children);
    }

    /**
     * Retrieves pool entries from the database based upon selected program and
     * person
     *
     * @return string  the subjects which fit the selected resource
     */
    public static function poolsByProgramOrPerson()
    {
        $selectedProgram = Input::getInt('programID');
        if (empty($selectedProgram) or $selectedProgram == '-1') {
            return '[]';
        }

        $programBounds = Mappings::getMappings('program', $selectedProgram);
        $personClauses = Mappings::getPersonMappingClauses();

        if (empty($programBounds)) {
            return '[]';
        }

        $dbo   = Factory::getDbo();
        $tag   = Languages::getTag();
        $query = $dbo->getQuery(true);
        $query->select("p.id, p.name_{$tag} AS name, m.level");
        $query->from('#__thm_organizer_pools AS p');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
        if (!empty($programBounds[0])) {
            $query->where("m.lft >= '{$programBounds[0]['lft']}'");
            $query->where("m.rgt <= '{$programBounds[0]['rgt']}'");
        }

        if (!empty($personClauses)) {
            $query->where('( ( ' . implode(') OR (', $personClauses) . ') )');
        }

        $query->order('lft');
        $dbo->setQuery($query);

        $pools = OrganizerHelper::executeQuery('loadObjectList');
        if (empty($pools)) {
            return '[]';
        }

        foreach ($pools as $key => $value) {
            $pools[$key]->name = Mappings::getIndentedPoolName($value->name, $value->level, false);
        }

        return $pools;
    }
}
