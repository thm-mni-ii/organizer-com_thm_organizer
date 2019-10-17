<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of runs.
 */
class Runs extends ListModel
{
    protected $defaultOrdering = 'name';
    /**
     * Method to get all runs from the database and set filters for term
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $tag = Languages::getTag();
        $query = $this->_db->getQuery(true);
        $query->select("r.id, r.name_$tag as name, r.run, r.termID, t.name as term")
            ->leftJoin('#__thm_organizer_terms AS t ON t.id = r.termID')
            ->from('#__thm_organizer_runs as r');

        $this->setSearchFilter($query, ['name_de', 'name_en']);
        $this->setValueFilters($query, ['termID']);

        $this->setOrdering($query);
        return $query;
    }
}