<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewSchedule_Manager
 * @description view output file for schedule lists
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewSchedule_Manager extends THM_OrganizerViewList
{
	public $items;

	public $pagination;

	public $state;

	/**
	 * loads data into view output context and initiates functions creating html
	 * elements
	 *
	 * @param string $tpl the template to be used
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		parent::display($tpl);
	}

	/**
	 * creates a joomla administrative tool bar
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_SCHEDULE_MANAGER_VIEW_TITLE'), 'organizer_schedules');
		if (count(THM_OrganizerHelperComponent::getAccessibleDepartments()))
		{
			JToolbarHelper::addNew('schedule.add');
			JToolbarHelper::makeDefault('schedule.activate', 'COM_THM_ORGANIZER_ACTION_ACTIVATE');
			JToolbarHelper::custom('schedule.setReference', 'diff', 'diff', 'COM_THM_ORGANIZER_ACTION_REFERENCE', true);
			JToolbarHelper::deleteList(JText::_('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM'), 'schedule.delete');
		}
		// No departments are available and must first be created
		elseif ($this->getModel()->actions->{'core.admin'})
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_NO_DEPARTMENTS'), 'notice');
		}

		if ($this->getModel()->actions->{'core.admin'})
		{
			JToolbarHelper::divider();
			JToolbarHelper::custom('schedule.migrate', 'arrow-right-4', 'arrow-right-4', 'COM_THM_ORGANIZER_ACTION_MIGRATE', true);
			JToolbarHelper::preferences('com_thm_organizer');
		}
	}
}
