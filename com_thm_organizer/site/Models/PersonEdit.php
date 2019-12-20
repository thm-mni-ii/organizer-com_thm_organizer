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

use Exception;
use Joomla\CMS\Table\Table;
use Organizer\Helpers;
use Organizer\Helpers\Can;
use Organizer\Tables\Persons as PersonsTable;

/**
 * Class loads a form for editing person data.
 */
class PersonEdit extends EditModel
{
	protected $deptResource = 'person';

	/**
	 * Checks for user authorization to access the view.
	 *
	 * @return bool  true if the user can access the view, otherwise false
	 */
	protected function allowEdit()
	{
		return Can::edit('person', $this->item->id);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return mixed    Object on success, false on failure.
	 * @throws Exception => unauthorized access
	 */
	public function getItem($pk = null)
	{
		$this->item               = parent::getItem($pk);
		$this->item->departmentID = Helpers\Persons::getDepartmentIDs($this->item->id);

		return $this->item;
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
		return new PersonsTable;
	}
}
