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

use Organizer\Helpers\Filtered;
use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of rooms.
 */
class Rooms extends ListModel
{
	use Filtered;

	protected $defaultOrdering = 'r.name';

	protected $filter_fields = ['campusID', 'buildingID', 'roomtypeID'];

	/**
	 * Method to get all rooms from the database
	 *
	 * @return \JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$tag   = Languages::getTag();
		$query = $this->_db->getQuery(true);

		$linkParts = ["'index.php?option=com_thm_organizer&view=room_edit&id='", 'r.id'];
		$query->select('r.id, r.untisID, r.name AS roomName')
			->select("t.id AS roomtypeID, t.name_$tag AS roomType")
			->select('b.id AS buildingID, b.name AS buildingName')
			->select($query->concatenate($linkParts, '') . ' AS link')
			->from('#__thm_organizer_rooms AS r')
			->leftJoin('#__thm_organizer_roomtypes AS t ON r.roomtypeID = t.id')
			->leftJoin('#__thm_organizer_buildings AS b ON b.id = r.buildingID')
			->leftJoin('#__thm_organizer_campuses AS c ON (c.id = b.campusID OR c.parentID = b.campusID)');

		$this->setSearchFilter($query, ['r.name', 'b.name', 't.name_de', 't.name_en']);
		$this->setValueFilters($query, ['buildingID', 'roomtypeID']);
		self::addCampusFilter($query, 'b');

		$this->setOrdering($query);

		return $query;
	}
}
