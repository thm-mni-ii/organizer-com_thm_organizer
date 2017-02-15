<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewmonitor_manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';

/**
 * Class loading a list of persistent monitor entries into the view context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewMonitor_Manager extends THM_OrganizerViewList
{
	public $items;

	public $pagination;

	public $state;

	/**
	 * Loads data from the model into the view context
	 *
	 * @param string $tpl the name of the template to be used
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$actions = $this->getModel()->actions;

		if (!$actions->{'core.admin'} AND !$actions->{'organizer.fm'})
		{
			throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		parent::display($tpl);
	}

	/**
	 * Creates joomla toolbar elements
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_MONITOR_MANAGER_VIEW_TITLE'), 'organizer_monitors');
		JToolbarHelper::addNew('monitor.add');
		JToolbarHelper::editList('monitor.edit');
		JToolbarHelper::deleteList(JText::_('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM'), 'monitor.delete');

		$actions = $this->getModel()->actions;

		if ($actions->{'core.admin'})
		{
			JToolbarHelper::divider();
			JToolbarHelper::preferences('com_thm_organizer');
		}
	}
}
