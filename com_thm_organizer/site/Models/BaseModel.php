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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Access;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Named;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which manages stored building data.
 */
abstract class BaseModel extends BaseDatabaseModel
{
	use Named;

	/**
	 * BaseModel constructor.
	 *
	 * @param   array  $config
	 */
	public function __construct($config = array())
	{
		try
		{
			parent::__construct($config);
		}
		catch (Exception $exception)
		{
			OrganizerHelper::message($exception->getMessage(), 'error');

			return;
		}

		$this->setContext();
	}

	/**
	 * Authenticates the user
	 */
	protected function allow()
	{
		return Access::isAdmin();
	}

	/**
	 * Removes entries from the database.
	 *
	 * @return boolean true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function delete()
	{
		if (!$this->allow())
		{
			throw new Exception(Languages::_('COM_THM_ORGANIZER_403'), 403);
		}

		$selectedIDs = Input::getSelectedIDs();
		$success     = true;
		foreach ($selectedIDs as $selectedID)
		{
			$table   = $this->getTable();
			$success = ($success and $table->delete($selectedID));
		}

		// TODO: create a message with an accurate count of successes.

		return $success;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return Table  A Table object
	 */
	public function getTable($name = '', $prefix = '', $options = [])
	{
		if (empty($name))
		{
			$className = OrganizerHelper::getClass($this);
			$name      = OrganizerHelper::getPlural($className);
		}

		return OrganizerHelper::getTable($name);
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
		if (!$this->allow())
		{
			throw new Exception(Languages::_('COM_THM_ORGANIZER_403'), 403);
		}

		$data  = empty($data) ? Input::getFormItems()->toArray() : $data;
		$table = $this->getTable();

		return $table->save($data) ? $table->id : false;
	}

	/**
	 * Attempts to save an existing resource as a new resource.
	 *
	 * @param   array  $data  form data which has been preprocessed by inheriting classes.
	 *
	 * @return mixed int id of the new resource on success, otherwise boolean false
	 * @throws Exception => unauthorized access
	 */
	public function save2copy($data = [])
	{
		$data = empty($data) ? Input::getFormItems()->toArray() : $data;
		unset($data['id']);

		return $this->save($data);
	}
}
