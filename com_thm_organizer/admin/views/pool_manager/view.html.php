<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewPool_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';

/**
 * Class THM_OrganizerViewPool_Manager for component com_thm_organizer
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewPool_Manager extends THM_OrganizerViewList
{
	public $items;

	public $pagination;

	public $state;

	/**
	 * Method to get display
	 *
	 * @param   Object $tpl template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		parent::display($tpl);
	}

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return  void
	 */
	protected function addToolBar()
	{
		$baseTitle = JText::_("COM_THM_ORGANIZER_POOL_MANAGER_VIEW_TITLE");
		$title     = empty($this->programName) ? $baseTitle : $baseTitle . " - " . $this->programName;
		JToolbarHelper::title($title, 'organizer_subject_pools');
		JToolbarHelper::addNew('pool.add');
		JToolbarHelper::editList('pool.edit');
		JToolbarHelper::deleteList('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM', 'pool.delete');
		JToolbarHelper::divider();
		JToolbarHelper::preferences('com_thm_organizer');
	}
}
