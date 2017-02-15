<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewDegree_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';

/**
 * Class provides methods to display the view degrees
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewDegree_Manager extends THM_OrganizerViewList
{
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
		$actions = $this->getModel()->actions;

		if (!$actions->{'core.admin'})
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
		JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_DEGREE_MANAGER_VIEW_TITLE'), 'organizer_degrees');
		JToolbarHelper::addNew('degree.add');
		JToolbarHelper::editList('degree.edit');
		JToolbarHelper::deleteList(JText::_('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM'), 'degree.delete');
		JToolbarHelper::divider();
		JToolbarHelper::preferences('com_thm_organizer');
	}
}
