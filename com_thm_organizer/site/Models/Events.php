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
 * Class retrieves information for a filtered set of events.
 */
class Events extends ListModel
{
    protected $defaultOrdering = 'name,department';

    /**
     * Method to select all event rows from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $tag = Languages::getTag();
        $query = $this->_db->getQuery(true);
        $query->select("DISTINCT ev.id AS id, ev.name_$tag as name, ev.departmentID, ev.campusID, ev.maxParticipants")
            ->select("d.id AS departmentID, d.shortName_$tag AS department")
            ->select("cp.id AS campusID, cp.name_$tag AS campus");
        $query->from('#__thm_organizer_events as ev')
            ->innerJoin('#__thm_organizer_departments as d on d.id = ev.departmentID')
            ->innerJoin('#__thm_organizer_campuses as cp on cp.id = ev.campusID');

        $this->setSearchFilter($query, ['ev.name_de', 'ev.name_en']);
        $this->setValueFilters($query, ['ev.departmentID', 'ev.campusID']);

        $this->setOrdering($query);

        return $query;
    }
}