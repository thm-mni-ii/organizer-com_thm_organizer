<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Organizer\Helpers\Mappings;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class retrieves information for a filtered set of (subject) pools. Modal view.
 */
class Pool_Selection extends ListModel
{
    /**
     * Method to get a \JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return \JDatabaseQuery   A \JDatabaseQuery object to retrieve the data set.
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $shortTag = Languages::getShortTag();
        $select   = "DISTINCT p.id, p.name_$shortTag AS name, field_$shortTag as field, color, ";
        $parts    = ["'index.php?option=com_thm_organizer&view=pool_selection&id='", 'p.id'];
        $select   .= $query->concatenate($parts, '') . ' AS link ';
        $query->select($select);

        $query->from('#__thm_organizer_pools AS p');
        $query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->leftJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');

        $searchColumns = [
            'name_de',
            'short_name_de',
            'abbreviation_de',
            'description_de',
            'name_en',
            'short_name_en',
            'abbreviation_en',
            'description_en'
        ];
        $this->setSearchFilter($query, $searchColumns);
        $this->setLocalizedFilters($query, ['p.name']);
        $this->setValueFilters($query, ['fieldID']);

        // Only pools
        $query->where('m.programID IS NULL AND m.subjectID IS NULL');

        $programID = $this->state->get('filter.programID', '');

        // Program filter selection made
        if (!empty($programID)) {
            // Pools unassociated with programs => no mappings
            if ($programID == -1) {
                $query->where('m.id IS NULL');
            } else {
                $this->setProgramFilter($query, $programID);
            }
        }

        // Mapping filters are irrelevant without mappings :)
        if ($programID != -1) {
            $this->setMappingFilters($query);
        }

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed  The data for the form.
     */
    protected function loadFormData()
    {
        $data               = parent::loadFormData();
        $data->list['type'] = $this->state->{'list.type'};
        $data->list['id']   = $this->state->{'list.id'};

        return $data;
    }

    /**
     * Sets exclusions for parent and child pools based on mapping values.
     *
     * @param object &$query the query object
     */
    private function setMappingFilters(&$query)
    {
        $type       = $this->state->{'list.type'};
        $resourceID = $this->state->{'list.id'};

        $invalid = (($type != 'program' and $type != 'pool') or $resourceID == 0);
        if ($invalid) {
            return;
        }

        $boundarySets = Mappings::getBoundaries($type, $resourceID);
        if (empty($boundarySets)) {
            return;
        }

        $newQuery = $this->_db->getQuery(true);
        $newQuery->select('poolID')->from('#__thm_organizer_mappings');
        $newQuery->where('poolID IS NOT NULL');
        foreach ($boundarySets as $boundarySet) {
            $newQuery->where("(lft BETWEEN '{$boundarySet['lft']}' AND '{$boundarySet['rgt']}')");
            $query->where("NOT (m.lft < '{$boundarySet['lft']}' AND m.rgt > '{$boundarySet['rgt']}')");
        }

        $query->where('p.id NOT IN (' . (string)$newQuery . ')');
    }

    /**
     * Sets the program id filter for a query. Used in pool manager and subject manager.
     *
     * @param object &$query     the query object
     * @param int     $programID the id of the resource from the filter
     *
     * @return void  sets query object variables
     */
    public function setProgramFilter(&$query, $programID)
    {
        if (!is_numeric($programID)) {
            return;
        }

        $ranges = Mappings::getResourceRanges('program', $programID);
        if (empty($ranges)) {
            return;
        }

        // Specific association
        $query->where("m.lft > '{$ranges[0]['lft']}'");
        $query->where("m.rgt < '{$ranges[0]['rgt']}'");
    }
}
