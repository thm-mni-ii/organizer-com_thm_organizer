<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use JDatabaseQuery;
use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of room types.
 */
class Roomtypes extends ListModel
{
	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$tag = Languages::getTag();

		$linkParts = ["'index.php?option=com_thm_organizer&view=roomtype_edit&id='", 't.id'];
		$query     = $this->_db->getQuery(true);
		$query->select("DISTINCT t.id, t.name_$tag AS name, t.minCapacity, t.maxCapacity, t.untisID")
			->select($query->concatenate($linkParts, '') . ' AS link')
			->select('count(r.roomtypeID) AS roomCount')
			->from('#__thm_organizer_roomtypes AS t')
			->leftJoin('#__thm_organizer_rooms AS r on r.roomtypeID = t.id')
			->group('t.id');

		$this->setSearchFilter($query, ['untisID', 'name_de', 'name_en', 'minCapacity', 'maxCapacity']);
		$this->setOrdering($query);

		return $query;
	}
}
