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
use Organizer\Tables\Methods as MethodsTable;

/**
 * Class which manages stored (lesson) method data.
 */
class Method extends MergeModel
{
	protected $fkColumn = 'methodID';

	protected $tableName = 'methods';

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return Table A Table object
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function getTable($name = '', $prefix = '', $options = [])
	{
		return new MethodsTable;
	}

	/**
	 * Updates key references to the entry being merged.
	 *
	 * @return boolean  true on success, otherwise false
	 */
	protected function updateAssociations()
	{
		return $this->updateAssociation('lessons');
	}

	/**
	 * Processes the data for an individual schedule
	 *
	 * @param   object &$schedule  the schedule being processed
	 *
	 * @return void
	 */
	protected function updateSchedule(&$schedule)
	{
		$updateIDs = $this->selected;
		$mergeID   = array_shift($updateIDs);

		foreach ($schedule->lessons as $lessonIndex => $lesson)
		{
			if (isset($lesson->methodID) and in_array($lesson->methodID, $updateIDs))
			{
				$schedule->lessons->$lessonIndex->methodID = $mergeID;
			}
		}
	}
}
