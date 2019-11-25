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

use Joomla\CMS\Table\Table;

interface ScheduleResource
{
	/**
	 * Processes the data for an individual schedule
	 *
	 * @param   Table  $schedule  the schedule being processed
	 *
	 * @return bool true if the schedule was changed, otherwise false
	 */
	public function updateSchedule($schedule);
}