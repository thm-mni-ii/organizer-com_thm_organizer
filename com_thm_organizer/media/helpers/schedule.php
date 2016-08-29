<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperSchedule
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class offering static schedule functions
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperSchedule
{
	/**
	 * Saves the planning period to the corresponding table if not already existent.
	 *
	 * @param   string $ppName    the abbreviation for the planning period
	 * @param   int    $startDate the integer value of the start date
	 * @param   int    $endDate   the integer value of the end date
	 *
	 * @return  void creates database entries
	 */
	public static function getPlanningPeriodID($ppName, $startDate, $endDate)
	{
		$data              = array();
		$data['startDate'] = date('Y-m-d', $startDate);
		$data['endDate']   = date('Y-m-d', $endDate);

		$table  = JTable::getInstance('planning_periods', 'thm_organizerTable');
		$exists = $table->load($data);
		if ($exists)
		{
			return $table->id;
		}

		$shortYear    = date('y', $endDate);
		$data['name'] = $ppName . $shortYear;
		$table->save($data);

		return $table->id;
	}
}
