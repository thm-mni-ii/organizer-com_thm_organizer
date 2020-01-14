<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Can;
use Organizer\Helpers\Input;
use Organizer\Tables\Runs as RunsTable;

/**
 * Class which manages stored run data.
 */
class Run extends BaseModel
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
		return new RunsTable;
	}

	/**
	 * Attempts to save the resource.
	 *
	 * @param   array  $data  form data which has been preprocessed by inheriting classes.
	 *
	 * @return mixed int id of the resource on success, otherwise boolean false
	 * @throws Exception => unauthorized access
	 */
	public function save($data = [])
	{
		if (!Can::administrate())
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$data = empty($data) ? Input::getFormItems()->toArray() : $data;

		$runs  = [];
		$index = 1;
		foreach ($data['run'] as $row)
		{
			$runs[$index] = $row;
			++$index;
		}

		$run         = ['runs' => $runs];
		$data['run'] = json_encode($run, JSON_UNESCAPED_UNICODE);

		$table = new RunsTable;

		return $table->save($data) ? $table->id : false;
	}
}
