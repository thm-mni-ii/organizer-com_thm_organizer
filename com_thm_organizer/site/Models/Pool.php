<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Can;
use Organizer\Helpers\Input;
use Organizer\Tables\Pools as PoolsTable;

/**
 * Class which manages stored (subject) pool data.
 */
class Pool extends BaseModel
{
	/**
	 * Attempts to delete the selected subject pool entries and related mappings
	 *
	 * @return boolean true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function delete()
	{
		if (!Can::documentTheseDepartments())
		{
			throw new Exception(Languages::_('ORGANIZER_403'), 403);
		}

		if ($poolIDs = Input::getSelectedIDs())
		{
			foreach ($poolIDs as $poolID)
			{
				if (!Can::document('pool', $poolID))
				{
					throw new Exception(Languages::_('ORGANIZER_403'), 403);
				}

				if (!$this->deleteSingle($poolID))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Removes a single pool and mappings. No access checks because of the contexts in which it is called.
	 *
	 * @param   int  $poolID  the pool id
	 *
	 * @return boolean  true on success, otherwise false
	 */
	public function deleteSingle($poolID)
	{
		$model = new Mapping;

		if (!$model->deleteByResourceID($poolID, 'pool'))
		{
			return false;
		}

		$table = new PoolsTable;

		return $table->delete($poolID);
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
		return new PoolsTable;
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
		$data = empty($data) ? Input::getFormItems()->toArray() : $data;

		if (empty($data['id']))
		{
			if (!Can::documentTheseDepartments())
			{
				throw new Exception(Languages::_('ORGANIZER_403'), 403);
			}
		}
		elseif (is_numeric($data['id']))
		{
			if (!Can::document('pool', $data['id']))
			{
				throw new Exception(Languages::_('ORGANIZER_403'), 403);
			}
		}
		else
		{
			throw new Exception(Languages::_('ORGANIZER_400'), 400);
		}

		if (empty($data['fieldID']))
		{
			unset($data['fieldID']);
		}

		$table = new PoolsTable;

		if (!$table->save($data))
		{
			return false;
		}

		$mappingsIrrelevant = (empty($data['programID']) or empty($data['parentID']));

		// Successfully inserted a new pool
		if ($mappingsIrrelevant)
		{
			return $table->id;
		} // Process mapping information
		else
		{
			$model      = new Mapping;
			$data['id'] = $table->id;

			// No mappings desired
			if (empty($data['parentID']))
			{
				return $model->deleteByResourceID($table->id, 'pool') ? $table->id : false;
			}
			else
			{
				return $model->savePool($data) ? $table->id : false;
			}
		}
	}
}
