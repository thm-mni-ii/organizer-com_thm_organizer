<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Provides persistence handling for subject pools
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool extends JModelLegacy
{
	/**
	 * Attempts to delete the selected subject pool entries and related mappings
	 *
	 * @return  boolean true on success, otherwise false
	 */
	public function delete()
	{
		$poolIDs = JFactory::getApplication()->input->get('cid', array(), 'array');
		if (!empty($poolIDs))
		{
			$this->_db->transactionStart();
			foreach ($poolIDs as $poolID)
			{
				$deleted = $this->deleteEntry($poolID);

				if (!$deleted)
				{
					$this->_db->transactionRollback();

					return false;
				}
			}
			$this->_db->transactionCommit();
		}

		return true;
	}

	/**
	 * Removes a single pool and mappings
	 *
	 * @param int $poolID the pool id
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	public function deleteEntry($poolID)
	{
		$model           = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');
		$mappingsDeleted = $model->deleteByResourceID($poolID, 'pool');

		if (!$mappingsDeleted)
		{
			return false;
		}

		$table       = JTable::getInstance('pools', 'thm_organizerTable');
		$poolDeleted = $table->delete($poolID);

		if (!$poolDeleted)
		{
			return false;
		}

		return true;
	}

	/**
	 * Saves
	 *
	 * @return  mixed  integer on successful pool creation, otherwise boolean
	 *                 true/false on success/failure
	 */
	public function save()
	{
		$data  = JFactory::getApplication()->input->get('jform', array(), 'array');
		$table = JTable::getInstance('pools', 'thm_organizerTable');

		$this->_db->transactionStart();

		if (empty($data['fieldID']))
		{
			unset($data['fieldID']);
		}

		$success = $table->save($data);

		// Successfully inserted a new pool
		if ($success AND empty($data['id']))
		{
			$this->_db->transactionCommit();

			return $table->id;
		}

		// New pool unsuccessfully inserted
		elseif (empty($data['id']))
		{
			$this->_db->transactionRollback();

			return false;
		}

		// Process mapping information
		else
		{
			$model = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');

			// No mappings desired
			if (empty($data['parentID']))
			{
				$mappingsDeleted = $model->deleteByResourceID($table->id, 'pool');
				if ($mappingsDeleted)
				{
					$this->_db->transactionCommit();

					return $table->id;
				}
				else
				{
					$this->_db->transactionRollback();

					return false;
				}
			}
			else
			{
				$mappingSaved = $model->savePool($data);
				if ($mappingSaved)
				{
					$this->_db->transactionCommit();

					return $table->id;
				}
				else
				{
					$this->_db->transactionRollback();

					return false;
				}
			}
		}
	}
}
