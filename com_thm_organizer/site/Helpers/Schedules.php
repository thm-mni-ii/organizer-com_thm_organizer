<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Exception;
use Joomla\CMS\Factory;

/**
 * Provides general functions for schedule access checks, data retrieval and display.
 */
class Schedules extends ResourceHelper
{
	/**
	 * Returns the id of the active schedule for the given department/term context
	 *
	 * @param   int  $departmentID  the id of the department context
	 * @param   int  $termID        the id of the term context
	 *
	 * @return int the id of the active schedule for the context or 0
	 */
	public static function getActiveID($departmentID, $termID)
	{
		if (empty($departmentID) or empty($termID))
		{
			return 0;
		}

		$table = self::getTable();

		return $table->load(['active' => 1, 'departmentID' => $departmentID, 'termID' => $termID]) ? $table->id : 0;
	}
}
