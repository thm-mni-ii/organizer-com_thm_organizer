<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use JDatabaseQuery;

/**
 * Class contains functions for department filtering.
 */
trait Filtered
{
	/**
	 * Adds a resource filter for a given resource.
	 *
	 * @param   JDatabaseQuery &$query  the query to modify
	 * @param   string          $alias  the alias for the linking table
	 */
	public function addCampusFilter(&$query, $alias)
	{
		$campusID = $this->state->get('filter.campusID');
		if (empty($campusID))
		{
			return;
		}

		if ($campusID === '-1')
		{
			$query->leftJoin("#__thm_organizer_campuses AS campusAlias ON campusAlias.id = $alias.campusID")
				->where("campusAlias.id IS NULL");
		}
		else
		{
			$query->innerJoin("#__thm_organizer_campuses AS campusAlias ON campusAlias.id = $alias.campusID")
				->where("(campusAlias.id = $campusID OR campusAlias.parentID = $campusID)");
		}
	}

	/**
	 * Adds a date status filter for a given resource.
	 *
	 * @param   object &$query   the query object
	 * @param   string  $status  name of the field in filter
	 * @param   string  $start   the name of the column containing the resource start date
	 * @param   string  $end     the name of the column containing the resource end date
	 */
	public function setDateStatusFilter(&$query, $status, $start, $end)
	{
		$value = $this->state->get("filter." . $status);

		switch ($value)
		{
			case '1' :
				$query->where($end . " < CURDATE()");
				break;
			case '2' :
				$query->where($start . " > CURDATE()");
				break;
			case '3' :
				$query->where("CURDATE() BETWEEN $start AND $end");
				break;
		}

	}
}
