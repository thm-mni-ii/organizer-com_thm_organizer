<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTablePlan_Pools
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 **/
defined('_JEXEC') or die;
jimport('joomla.application.component.table');

/**
 * Class representing the plan_pools table.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerTablePlan_Pools extends JTable
{
	/**
	 * Constructor function for the class representing the mapping table
	 *
	 * @param JDatabaseDriver &$dbo A database connector object
	 */
	public function __construct(&$dbo)
	{
		parent::__construct('#__thm_organizer_plan_pools', 'id', $dbo);
	}

	/**
	 * Set the table column names which are allowed to be null
	 *
	 * @return  boolean  true
	 */
	public function check()
	{
		if (empty($this->fieldID))
		{
			$this->fieldID = null;
		}
		if (empty($this->poolID))
		{
			$this->poolID = null;
		}

		return true;
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 *
	 * @param boolean $updateNulls True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		return parent::store(true);
	}
}
