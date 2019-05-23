<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Organizer\Helpers\Groups;
use Organizer\Helpers\Mappings;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class retrieves dynamic pool options.
 */
class Pool_Ajax extends BaseModel
{
    /**
     * Retrieves pool options for a given curriculum element
     *
     * @return string
     */
    public function parentOptions()
    {
        $input          = OrganizerHelper::getInput();
        $resourceID     = $input->getInt('id', 0);
        $resourceType   = $input->getString('type', '');
        $programIDs     = explode(',', $input->getString('programIDs', ''));
        $programEntries = $this->getProgramEntries($programIDs);
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
        $unSelectableMappings = $this->getUnselectableMappings($mappings, $mappingIDs, $resourceType);
        $this->fillOptions($options, $programMappings, $unSelectableMappings, $parentIDs, $resourceType);

        return implode('', $options);
    }

    /**
     * Gets the pool options as a string
     *
     * @return string the concatenated group options
     */
    public function getPlanOptions()
    {
        $groupOptions = Groups::getOptions();

        return json_encode($groupOptions);
    }

    /**
     * Retrieves the mappings of superordinate programs
     *
     * @param array $programIDs the requested program ids
     *
     * @return array  the superordinate program mappings
     */
    private function getProgramEntries($programIDs)
    {
        $query = $this->_db->getQuery(true);
        $query->select('id, programID, lft, rgt');
        $query->from('#__thm_organizer_mappings');
        $query->where("programID IN ( '" . implode("', '", $programIDs) . "' )");
        $query->order('lft ASC');
        $this->_db->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList');
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
    private function getUnselectableMappings(&$mappings, &$mappingIDs, $resourceType)
    {
        if ($resourceType == 'subject') {
            return [];
        }

        $children = Mappings::getChildren($mappings);

        return array_merge($mappingIDs, $children);
    }

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
    private function fillOptions(&$options, &$programMappings, &$unelectableMappings, &$parentIDs, $resourceType)
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
     * Retrieves pool entries from the database based upon selected program and
     * teacher
     *
     * @return string  the subjects which fit the selected resource
     */
    public function poolsByProgramOrTeacher()
    {
        $input           = OrganizerHelper::getInput();
        $selectedProgram = $input->getInt('programID', 0);
        if (empty($selectedProgram) or $selectedProgram == '-1') {
            return '[]';
        }

        $programBounds  = Mappings::getBoundaries('program', $selectedProgram);
        $teacherClauses = Mappings::getTeacherMappingClauses();

        if (empty($programBounds)) {
            return '[]';
        }

        $lang  = OrganizerHelper::getInput()->getString('languageTag', 'de');
        $query = $this->_db->getQuery(true);
        $query->select("p.id, p.name_{$lang} AS name, m.level");
        $query->from('#__thm_organizer_pools AS p');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
        if (!empty($programBounds[0])) {
            $query->where("m.lft >= '{$programBounds[0]['lft']}'");
            $query->where("m.rgt <= '{$programBounds[0]['rgt']}'");
        }

        if (!empty($teacherClauses)) {
            $query->where('( ( ' . implode(') OR (', $teacherClauses) . ') )');
        }

        $query->order('lft');
        $this->_db->setQuery($query);

        $pools = OrganizerHelper::executeQuery('loadObjectList');
        if (empty($pools)) {
            return '[]';
        }

        foreach ($pools as $key => $value) {
            $pools[$key]->name = Mappings::getIndentedPoolName($value->name, $value->level, false);
        }

        return json_encode($pools);
    }
}
