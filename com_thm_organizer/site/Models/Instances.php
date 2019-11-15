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

use Organizer\Helpers\Filtered;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Instances as InstancesHelper;

/**
 * Class retrieves information for a filtered set of instances.
 */
class Instances extends ListModel
{
	use Filtered;

	protected $defaultOrdering = 'name';

	/**
	 * Method to select all instance rows from the database
	 *
	 * @return \JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$tag = Languages::getTag();

		$conditions                     = InstancesHelper::getConditions();
		$conditions['isEventsRequired'] = true;

		$query = InstancesHelper::getInstanceQuery($conditions);

		$query->innerJoin('#__thm_organizer_terms as t on u.termID = t.id')
			->select("DISTINCT i.id")
			->select("e.name_$tag AS name")
			->select("t.name_$tag AS term")
			->select("u.id AS unitID")
			->select("b.date AS date");

		$this->setSearchFilter($query, ['e.name_de', 'e.name_en']);
		$this->setValueFilters($query, ['u.termID']);
		$this->setDateStatusFilter($query, 'status', 'b.date', 'b.date');
		$this->setTimeBlockFilter($query);
		$this->setOrdering($query);

		return $query;
	}

	/**
	 * Adds the filter settings for time blocks of an instance
	 *
	 * @param   object &$query  the query object
	 */
	private function setTimeBlockFilter(&$query)
	{

		$value   = $this->state->get("filter.timeBlock");
		$timings = explode(",", $value);

		if (sizeof($timings) == 2)
		{
			$query->where("startTime = '{$timings[0]}' and endTime = '{$timings[1]}'");
		}
	}
}