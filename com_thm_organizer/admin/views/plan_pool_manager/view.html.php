<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewPlan_Pool_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';

/**
 * Class provides methods to display a list of plan_programs
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewPlan_Pool_Manager extends THM_OrganizerViewList
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
		$actions = $this->getModel()->actions;

		if (!$actions->{'core.admin'} AND !$actions->{'organizer.menu.schedule'})
		{
			throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		parent::display($tpl);
	}

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return  void
	 */
	protected function addToolBar()
	{
		JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_PLAN_POOL_MANAGER_VIEW_TITLE'), 'organizer_plan_pools');
		JToolbarHelper::editList('plan_pool.edit');
		JToolbarHelper::custom('plan_pool.mergeView', 'merge', 'merge', 'COM_THM_ORGANIZER_ACTION_MERGE', true);
		JToolbarHelper::divider();
		JToolbarHelper::preferences('com_thm_organizer');
	}
}
