<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use JDatabaseQuery;
use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of events.
 */
class Events extends ListModel
{
	protected $defaultOrdering = 'name,department';

	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$tag   = Languages::getTag();
		$query = $this->_db->getQuery(true);
		$query->select("DISTINCT ev.id AS id, ev.name_$tag as name, ev.departmentID, ev.campusID")
			->select("ev.maxParticipants, ev.registrationType, ev.subjectNo, ev.preparatory")
			->select("d.id AS departmentID, d.shortName_$tag AS department")
			->select("cp.id AS campusID, cp.name_$tag AS campus")
			->from('#__thm_organizer_events AS ev')
			->leftJoin('#__thm_organizer_departments as d on d.id = ev.departmentID')
			->leftJoin('#__thm_organizer_campuses as cp on cp.id = ev.campusID');

		$this->setSearchFilter($query, ['ev.name_de', 'ev.name_en', 'ev.subjectNo']);
		$this->setValueFilters($query, ['ev.departmentID', 'ev.campusID', 'ev.preparatory']);

		$this->setOrdering($query);

		return $query;
	}
}