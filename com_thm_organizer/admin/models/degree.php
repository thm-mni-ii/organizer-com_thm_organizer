<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelDegree
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class provides methods to deal with degrees
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelDegree extends JModelLegacy
{
	/**
	 * Saves degree information to the database
	 *
	 * @return  boolean true on success, otherwise false
	 */
	public function save()
	{
		$data  = JFactory::getApplication()->input->get('jform', [], 'array');
		$table = JTable::getInstance('degrees', 'thm_organizerTable');

		return $table->save($data);
	}

	/**
	 * Deletes the chosen degrees from the database
	 *
	 * @return boolean true on success, otherwise false
	 */
	public function delete()
	{
		return THM_OrganizerHelper::delete('degrees');
	}
}
