<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTablePools
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 **/
defined('_JEXEC') or die;
jimport('joomla.application.component.table');

/**
 * Class representing the mapping table.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerTablePools extends JTable
{
	/**
	 * Constructor function for the class representing the mapping table
	 *
	 * @param JDatabaseDriver &$dbo A database connector object
	 */
	public function __construct(&$dbo)
	{
		parent::__construct('#__thm_organizer_pools', 'id', $dbo);
	}

	/**
	 * Overridden bind function
	 *
	 * @param array $array  named array
	 * @param mixed $ignore An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error string
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['rules']) && is_array($array['rules']))
		{
			THM_OrganizerHelperComponent::cleanRules($array['rules']);
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Sets the department asset name
	 *
	 * @return  string
	 */
	protected function _getAssetName()
	{
		return "com_thm_organizer.pool.$this->id";
	}

	/**
	 * Sets the parent as the component root
	 *
	 * @param   JTable  $table A JTable object for the asset parent.
	 * @param   integer $id    Id to look up
	 *
	 * @return  int  the asset id of the component root
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$asset = JTable::getInstance('Asset');
		$asset->loadByName("com_thm_organizer.department.$this->departmentID");

		return $asset->id;
	}
}
