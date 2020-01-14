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
 * Class retrieves the data regarding a filtered set of units.
 */
class Units extends ListModel
{
	use Filtered;
	protected $defaultOrdering = 'name';

	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$tag = Languages::getTag();

		$query    = $this->_db->getQuery(true);
		$subQuery = $this->_db->getQuery(true);

		$subQuery->select('MIN(date) AS start, MAX(date) AS end, i.unitID')
			->from('#__thm_organizer_blocks AS b')
			->innerJoin('#__thm_organizer_instances as i on i.blockID = b.id')
			->where("i.delta!='removed'")
			->group('i.unitID');

		$query->select('u.id')
			->select("ev.id as eventID, ev.name_$tag as name")
			->select("g.id AS gridID, g.name_$tag AS grid")
			->select("r.id AS runID, r.name_$tag AS run")
			->select("sq.start, sq.end");

		$query->from('#__thm_organizer_units AS u')
			->innerJoin('#__thm_organizer_instances AS i ON u.id = i.unitID')
			->innerJoin('#__thm_organizer_events AS ev ON ev.id = i.eventID')
			->innerJoin('#__thm_organizer_blocks as b on b.id = i.blockID')
			->innerJoin('#__thm_organizer_grids as g on g.id = u.gridID')
			->leftJoin('#__thm_organizer_runs as r on r.id = u.runID')
			->innerJoin("($subQuery) as sq on sq.unitID = u.id")
			->group('u.id');

		$this->setSearchFilter($query, ['ev.name_de', 'ev.name_en']);
		$this->setValueFilters($query, ['u.departmentID', 'u.termID', 'u.gridID', 'u.runID']);
		$this->setDateStatusFilter($query, 'status', 'sq.start', 'sq.end');

		return $query;
	}
}
