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

use Organizer\Helpers\LSF;
use Organizer\Tables\Pools as PoolsTable;

/**
 * Class used to import lsf pool data.
 */
class PoolLSF extends BaseModel
{
	/**
	 * Creates a pool entry if none exists and calls
	 *
	 * @param   object &$stub          a SimpleXML object containing rudimentary subject data
	 * @param   int     $departmentID  the id of the department to which this data belongs
	 *
	 * @return mixed  int value of subject id on success, otherwise false
	 */
	public function processStub(&$stub, $departmentID)
	{
		if (empty($stub->pordid) and empty($stub->modulid))
		{
			return false;
		}

		$invalidTitle = LSF::invalidTitle($stub);
		$blocked = !empty($stub->sperrmh) and strtolower((string) $stub->sperrmh) == 'x';

		$lsfID = empty($stub->pordid) ? (int) $stub->modulid : (int) $stub->pordid;

		$pool = new PoolsTable;
		$pool->load(['lsfID' => $lsfID]);

		if (!empty($pool->id) and ($blocked or $invalidTitle))
		{
			$poolModel = new Pool;

			return $poolModel->deleteSingle($pool->id);
		}

		$pool->departmentID = $departmentID;
		$pool->lsfID        = $lsfID;
		$this->setAttribute($pool, 'abbreviation_de', (string) $stub->kuerzel);
		$this->setAttribute($pool, 'abbreviation_en', (string) $stub->kuerzelen, $pool->abbreviation_de);
		$this->setAttribute($pool, 'shortName_de', (string) $stub->kurzname);
		$this->setAttribute($pool, 'shortName_en', (string) $stub->kurznameen, $pool->shortName_de);
		$this->setAttribute($pool, 'name_de', (string) $stub->titelde);
		$this->setAttribute($pool, 'name_en', (string) $stub->titelen, $pool->name_de);

		if (!$pool->store())
		{
			return false;
		}

		return $this->processChildren($stub, $departmentID);
	}

	/**
	 * Sets the value of a generic attribute if available
	 *
	 * @param   object &$pool     the array where subject data is being stored
	 * @param   string  $key      the key where the value should be put
	 * @param   string  $value    the xpath value where the attribute value
	 *                            should be
	 * @param   string  $default  the default value
	 *
	 * @return void
	 */
	private function setAttribute(&$pool, $key, $value, $default = '')
	{
		if (empty($value))
		{
			$pool->$key = empty($pool->$key) ?
				$default : $pool->$key;
		}
		else
		{
			$pool->$key = $value;
		}
	}

	/**
	 * Processes the children of the stub element
	 *
	 * @param   object &$stub          the pool element
	 * @param   int     $departmentID  the id of the department to which this data belongs
	 *
	 * @return boolean true on success, otherwise false
	 */
	private function processChildren(&$stub, $departmentID)
	{
		$lsfSubjectModel = new SubjectLSF;

		foreach ($stub->modulliste->modul as $subStub)
		{
			$type    = LSF::determineType($subStub);
			$success = true;

			if ($type == 'subject')
			{
				$success = $lsfSubjectModel->processStub($subStub, $departmentID);
			}
			elseif ($type == 'pool')
			{
				$success = $this->processStub($subStub, $departmentID);
			}

			// Malformed xml, invalid/incomplete data, database errors
			if (!$success)
			{
				return false;
			}
		}

		return true;
	}
}
