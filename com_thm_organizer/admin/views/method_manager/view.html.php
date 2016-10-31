<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewMethod_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';

/**
 * Class loads persistent information about all colors into display context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewMethod_Manager extends THM_OrganizerViewList
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
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
		JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_METHOD_MANAGER_VIEW_TITLE'), 'organizer_methods');

		$actions = $this->getModel()->actions;

		if ($actions->{'core.admin'})
		{
			JToolbarHelper::addNew('method.add');
			JToolbarHelper::editList('method.edit');
			JToolbarHelper::custom('method.mergeView', 'merge', 'merge', 'COM_THM_ORGANIZER_ACTION_MERGE', true);
			JToolbarHelper::deleteList('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM', 'method.delete');
			JToolbarHelper::divider();
			JToolbarHelper::preferences('com_thm_organizer');
		}
	}
}
