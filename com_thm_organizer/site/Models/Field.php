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

/**
 * Class which manages stored field (of expertise) data.
 */
class Field extends MergeModel
{
	protected $fkColumn = 'fieldID';

	protected $tableName = 'fields';

	/**
	 * Updates key references to the entry being merged.
	 *
	 * @return boolean  true on success, otherwise false
	 */
	protected function updateAssociations()
	{
		if (!$this->updateAssociation('groups'))
		{
			return false;
		}

		if (!$this->updateAssociation('courses'))
		{
			return false;
		}

		if (!$this->updateAssociation('pools'))
		{
			return false;
		}

		if (!$this->updateAssociation('programs'))
		{
			return false;
		}

		if (!$this->updateAssociation('subjects'))
		{
			return false;
		}

		return $this->updateAssociation('persons');
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
		return;
	}
}
