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
 * Class retrieves information for a filtered set of fields (of expertise).
 */
class Fields extends ListModel
{
	protected $defaultOrdering = 'field';

	protected $filter_fields = ['colorID'];

	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$tag   = Languages::getTag();
		$query = $this->_db->getQuery(true);

		$query->select("f.id, untisID, f.name_$tag AS field, f.colorID")
			->from('#__thm_organizer_fields AS f')
			->select("c.name_$tag AS color")
			->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

		$this->setSearchFilter($query, ['f.name_de', 'f.name_en', 'untisID', 'color']);
		$this->setValueFilters($query, ['colorID']);

		$this->setOrdering($query);

		return $query;
	}
}
