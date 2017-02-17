<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelGrid
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class THM_OrganizerModelGrid for component com_thm_organizer
 * Class provides methods to deal with grids for schedules.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelGrid extends JModelLegacy
{
	/**
	 * Save the form data for a new grid
	 *
	 * @return bool true on success, otherwise false
	 */
	public function save()
	{
		$data  = JFactory::getApplication()->input->get('jform', array(), 'array');
		$table = JTable::getInstance('grids', 'thm_organizerTable');

		return $table->save($data);
	}

	/**
	 * Removes grid entries from the database
	 *
	 * @return boolean true on success, otherwise false
	 */
	public function delete()
	{
		return THM_OrganizerHelper::delete('grids');
	}
}
