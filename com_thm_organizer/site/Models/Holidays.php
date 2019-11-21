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

use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of holidays.
 */
class Holidays extends ListModel
{
	const EXPIRED = 1, PENDING = 2, CURRENT = 3;
	protected $defaultOrdering = 'name';

	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return \JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$tag   = Languages::getTag();
		$query = $this->_db->getQuery(true);
		$query->select("id, name_$tag as name, type, startDate, endDate")
			->from('#__thm_organizer_holidays');
		$this->setSearchFilter($query, ['name_de', 'name_en', 'startDate', 'endDate']);
		$this->setValueFilters($query, ['type']);
		$this->setStatusFilter($query);
		$this->setYearFilter($query);
		$this->setOrdering($query);

		return $query;
	}

	/**
	 * Adds the filter settings for status of holiday
	 *
	 * @param   object &$query  the query object
	 *
	 * @return void
	 */
	private function setStatusFilter(&$query)
	{
		$listValue   = $this->state->get("list.status");
		$filterValue = $this->state->get("filter.status");

		if (empty($listValue) and empty($filterValue))
		{
			return;
		}

		$value = empty($filterValue) ? $listValue : $filterValue;

		switch ($value)
		{
			case self::EXPIRED :
				$query->where("endDate < CURDATE()");
				break;
			case self::PENDING:
				$query->where("startDate > CURDATE()");
				break;
			default:
				$query->where("endDate BETWEEN CURDATE() AND date_add(CURDATE(), interval 1 YEAR)");
				break;
		}
	}

	/**
	 * Adds the filter settings for displaying year
	 *
	 * @param   object &$query  the query object
	 *
	 * @return void
	 */
	private function setYearFilter(&$query)
	{
		$listValue   = $this->state->get("list.year");
		$filterValue = $this->state->get("filter.year");

		if (empty($listValue) and empty($filterValue))
		{
			return;
		}

		$value = empty($filterValue) ? $listValue : $filterValue;

		$query->where("Year(startDate) = $value");
	}
}