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
use Organizer\Tables\Fields as FieldsTable;

/**
 * Class which manages stored field (of expertise) data.
 */
class Field extends MergeModel
{
	protected $fkColumn = 'fieldID';

	protected $tableName = 'fields';

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
		return new FieldsTable;
	}

	/**
	 * Updates key references to the entry being merged.
	 *
	 * @return boolean  true on success, otherwise false
	 */
	protected function updateAssociations()
	{
		if (!$this->updateDirectAssociation('events'))
		{
			return false;
		}

		if (!$this->updateDirectAssociation('groups'))
		{
			return false;
		}

		if (!$this->updateDirectAssociation('persons'))
		{
			return false;
		}

		if (!$this->updateDirectAssociation('pools'))
		{
			return false;
		}

		if (!$this->updateDirectAssociation('programs'))
		{
			return false;
		}

		return $this->updateDirectAssociation('subjects');
	}
}
