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

use Exception;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Can;
use Organizer\Helpers\Input;
use Organizer\Tables\Campuses as CampusesTable;

/**
 * Class which manages stored campus data.
 */
class Campus extends BaseModel
{
	/**
	 * Authenticates the user
	 */
	protected function allow()
	{
		return Can::administrate();
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return Table  A Table object
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function getTable($name = '', $prefix = '', $options = [])
	{
		return new CampusesTable;
	}

	/**
	 * Attempts to save the resource.
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function save()
	{
		if ($parentID = Input::getInt('parentID'))
		{
			$table = new CampusesTable;
			$table->load($parentID);
			if (!empty($table->parentID))
			{
				// TODO: add a message saying that it failed because the maximum depth was reached.
				return false;
			}
		}

		return parent::save();
	}
}
