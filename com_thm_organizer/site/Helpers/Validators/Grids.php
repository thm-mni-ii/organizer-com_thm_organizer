<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers\Validators;

use Organizer\Helpers\Languages;
use Organizer\Helpers\ResourceHelper;
use stdClass;

/**
 * Class provides general functions for retrieving building data.
 */
class Grids extends ResourceHelper implements UntisXMLValidator
{
	/**
	 * Retrieves the table id if existent.
	 *
	 * @param   string  $untisID  the grid name in untis
	 *
	 * @return mixed int id on success, otherwise null
	 */
	public static function getID($untisID)
	{
		$table = self::getTable();

		return $table->load(['untisID' => $untisID]) ? $table->id : null;
	}

	/**
	 * Retrieves the grid id using the grid name. Creates the grid id if unavailable.
	 *
	 * @param   Schedules &$model     the validating schedule model
	 * @param   string     $gridName  the name of the grid
	 *
	 * @return void modifies the model, setting the id property of the resource
	 */
	public static function setID(&$model, $gridName)
	{
		if (empty($model->schedule->periods->$gridName))
		{
			return;
		}

		$grid       = $model->schedule->periods->$gridName;
		$grid->grid = json_encode($grid, JSON_UNESCAPED_UNICODE);
		$table      = self::getTable();

		// No overwrites for global resources
		if (!$table->load(['untisID' => $gridName]))
		{
			$table->save($grid);
		}

		$grid->id = $table->id;

		return;
	}

	/**
	 * Sets IDs for the grids collection.
	 *
	 * @param   Schedules &$model  the validating schedule model
	 *
	 * @return void modifies &$model
	 */
	public static function setIDs(&$model)
	{
		foreach (array_keys((array) $model->periods) as $gridName)
		{
			self::setID($model, $gridName);
		}
	}

	/**
	 * Checks whether pool nodes have the expected structure and required
	 * information
	 *
	 * @param   Schedules &$model  the validating schedule model
	 * @param   object    &$node   the time period node to be validated
	 *
	 * @return void
	 */
	public static function validate(&$model, &$node)
	{
		// Not actually referenced but evinces data inconsistencies in Untis
		$exportKey = trim((string) $node[0]['id']);
		$gridName  = (string) $node->timegrid;
		$day       = (int) $node->day;
		$periodNo  = (int) $node->period;
		$startTime = trim((string) $node->starttime);
		$endTime   = trim((string) $node->endtime);

		$invalidKeys   = (empty($exportKey) or empty($gridName) or empty($periodNo));
		$invalidTimes  = (empty($day) or empty($startTime) or empty($endTime));
		$invalidPeriod = ($invalidKeys or $invalidTimes);

		if ($invalidPeriod)
		{
			if (!in_array(Languages::_('THM_ORGANIZER_PERIODS_INCONSISTENT'), $model->errors))
			{
				$model->errors[] = Languages::_('THM_ORGANIZER_PERIODS_INCONSISTENT');
			}

			return;
		}

		// Set the grid if not already existent
		if (empty($model->periods->$gridName))
		{
			$model->periods->$gridName          = new stdClass;
			$model->periods->$gridName->periods = new stdClass;
		}

		$grid = $model->periods->$gridName;

		if (!isset($grid->startDay) or $grid->startDay > $day)
		{
			$grid->startDay = $day;
		}

		if (!isset($grid->endDay) or $grid->endDay < $day)
		{
			$grid->endDay = $day;
		}

		$periods = $grid->periods;

		$periods->$periodNo            = new stdClass;
		$periods->$periodNo->startTime = $startTime;
		$periods->$periodNo->endTime   = $endTime;

		$label = (string) $node->label;
		if ($label and preg_match("/[a-zA-ZäÄöÖüÜß]+/", $label))
		{
			$periods->$periodNo->label_de = $label;
			$periods->$periodNo->label_en = $label;

			// This is an assumption, which can later be rectified as necessary.
			$periods->$periodNo->type = 'break';
		}
	}
}
