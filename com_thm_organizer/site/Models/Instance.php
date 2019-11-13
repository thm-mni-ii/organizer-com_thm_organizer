<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;

defined('_JEXEC') or die;

/**
 * Class which manages stored instance data.
 */
class Instance extends BaseModel
{
	/**
	 * Method to save instances
	 *
	 * @param   array  $data  the data to be used to create the instance
	 *
	 * @return Boolean
	 */
	public function save($data = [])
	{
		$data = empty($data) ? Input::getFormItems()->toArray() : $data;

		$table = $this->getTable();
		if (!$table->save($data))
		{
			return false;
		}

		$data['id'] = $table->id;

		return $this->saveResourceData($data) ? $table->id : false;
	}

	/**
	 * Method to check the new instance data and to save it
	 *
	 * @param   array  $data  the new instance data
	 *
	 * checkAssocID to check the existing assocID or create a new one
	 *
	 * @return Boolean
	 */
	private function saveResourceData($data)
	{
		$instanceID = $data['id'];
		$ipIDs      = [];
		foreach ($data['resources'] as $person)
		{
			$ipData  = ['instanceID' => $instanceID, 'personID' => $person["personID"]];
			$ipTable = $this->getTable('InstancePersons');
			$roleID  = !empty($person['roleID']) ? $person['roleID'] : 1;
			if ($ipTable->load($ipData))
			{
				if ($ipTable->delta === 'removed')
				{
					$ipTable->delta = 'new';
				}
				else
				{
					if ($ipTable->roleID != $roleID)
					{
						$ipTable->delta  = 'changed';
						$ipTable->roleID = $roleID;
					}
					else
					{
						$ipTable->delta = '';
					}
				}

				if (!$ipTable->store())
				{
					return false;
				}
			}
			else
			{
				$ipData['delta']  = 'new';
				$ipData['roleID'] = $roleID;
				if (!$ipTable->save($ipData))
				{
					return false;
				}
			}

			$ipID    = $ipTable->id;
			$ipIDs[] = $ipID;

			$igIDs = [];
			foreach ($person['groups'] as $group)
			{
				$igData  = ['assocID' => $ipID, 'groupID' => $group['groupID']];
				$igTable = $this->getTable('InstanceGroups');
				if ($igTable->load($igData))
				{
					$igTable->delta = $igTable->delta === 'removed' ? 'new' : '';
					if (!$igTable->store())
					{
						return false;
					}
				}
				else
				{
					$igData['delta'] = 'new';
					if (!$igTable->save($igData))
					{
						return false;
					}
				}

				$igIDs[] = $igTable->id;
			}

			$this->setRemoved('instance_groups', 'assocID', $ipID, $igIDs);

			$irIDs = [];
			foreach ($person['rooms'] as $room)
			{
				$irData  = ['assocID' => $ipID, 'roomID' => $room['roomID']];
				$irTable = $this->getTable('InstanceRooms');
				if ($irTable->load($irData))
				{
					$irTable->delta = $irTable->delta === 'removed' ? 'new' : '';
					if (!$irTable->store())
					{
						return false;
					}
				}
				else
				{
					$irData['delta'] = 'new';
					if (!$irTable->save($irData))
					{
						return false;
					}
				}

				$irIDs[] = $irTable->id;
			}

			$this->setRemoved('instance_rooms', 'assocID', $ipID, $irIDs);
		}

		$this->setRemoved('instance_persons', 'instanceID', $instanceID, $ipIDs);

		return true;
	}

	/**
	 * Sets resource associations which are no longer current to 'removed';
	 *
	 * @param   string  $suffix       the unique table name ending
	 * @param   string  $assocColumn  the name of the column referencing an association
	 * @param   int     $assocValue   the value of the referenced association's id
	 * @param   array   $idValues     the values of the current resource association ids
	 *
	 * @return bool
	 */
	private function setRemoved($suffix, $assocColumn, $assocValue, $idValues)
	{
		$table = "#__thm_organizer_$suffix";
		$query = $this->_db->getQuery(true);
		$query->update($table)
			->set("delta = 'removed'")
			->where("$assocColumn = $assocValue")
			->where('id NOT IN (' . implode(',', $idValues) . ')');

		$this->_db->setQuery($query);

		return OrganizerHelper::executeQuery('execute', false) ? true : false;
	}

	/**
	 * Method to save existing instances as copies
	 *
	 * @param   array  $data  the data to be used to create the instance
	 *
	 * @return $saveInstance
	 */
	public function save2copy($data = [])
	{
		$data = empty($data) ? Input::getFormItems()->toArray() : $data;

		unset($data['id']);

		return $this->save($data);
	}
}
