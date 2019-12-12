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
use Organizer\Helpers\Can;
use Organizer\Helpers\Input;
use Organizer\Tables\Monitors as MonitorsTable;

/**
 * Class which manages stored monitor data.
 */
class Monitor extends BaseModel
{
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
		return new MonitorsTable;
	}

	/**
	 * save
	 *
	 * attempts to save the monitor form data
	 *
	 * @return bool true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function save()
	{
		$data = Input::getFormItems()->toArray();

		if (empty($data['roomID']))
		{
			unset($data['roomID']);
		}

		$data['content'] = $data['content'] == '-1' ? '' : $data['content'];

		return parent::save($data);
	}

	/**
	 * Saves the default behaviour as chosen in the monitor manager
	 *
	 * @return boolean  true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function saveDefaultBehaviour()
	{
		if (!Can::administrate())
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$monitorID   = Input::getID();
		$plausibleID = ($monitorID > 0);

		if ($plausibleID)
		{
			$table = new MonitorsTable;
			$table->load($monitorID);
			$table->set('useDefaults', Input::getInt('useDefaults'));

			return $table->store();
		}

		return false;
	}

	/**
	 * Toggles the monitor's use of default settings
	 *
	 * @return boolean  true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function toggle()
	{
		if (!Can::manage('facilities'))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$monitorID = Input::getID();
		$table     = new MonitorsTable;
		if (empty($monitorID) or !$table->load($monitorID))
		{
			return false;
		}

		$newValue = !$table->useDefaults;
		$table->set('useDefaults', $newValue);

		return $table->store();
	}
}
