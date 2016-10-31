<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewRoom_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';

/**
 * Class provides methods to display a list of rooms
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewRoom_Manager extends THM_OrganizerViewList
{
	public $items;

	public $pagination;

	public $state;

	/**
	 * Method to get display
	 *
	 * @param Object $tpl template  (default: null)
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
		JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_ROOM_MANAGER_VIEW_TITLE'), 'organizer_rooms');

		$actions = $this->getModel()->actions;

		if ($actions->{'core.create'})
		{
			JToolbarHelper::addNew('room.add');
		}

		if ($actions->{'core.edit'})
		{
			JToolbarHelper::editList('room.edit');
		}

		if ($actions->{'core.edit'} AND $actions->{'core.delete'})
		{
			JToolbarHelper::custom('room.mergeView', 'merge', 'merge', 'COM_THM_ORGANIZER_ACTION_MERGE', true);
		}

		if ($actions->{'core.delete'})
		{
			JToolbarHelper::deleteList('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM', 'room.delete');
		}

		if ($actions->{'core.admin'})
		{
			JToolbarHelper::divider();
			JToolbarHelper::preferences('com_thm_organizer');
		}
	}
}
