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

use Joomla\CMS\Table\Table;
use Organizer\Helpers\Can;
use Organizer\Tables\Roomtypes as RoomtypesTable;

/**
 * Class which manages stored room type data.
 */
class Roomtype extends MergeModel
{
	protected $fkColumn = 'roomtypeID';

	/**
	 * Provides room type specific user access checks
	 *
	 * @return boolean  true if the user may edit the given resource, otherwise false
	 */
	protected function allowEdit()
	{
		return Can::manage('facilities');
	}

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
		return new RoomtypesTable;
	}

	/**
	 * Updates key references to the entry being merged.
	 *
	 * @return boolean  true on success, otherwise false
	 */
	protected function updateAssociations()
	{
		return $this->updateDirectAssociation('rooms');
	}
}
