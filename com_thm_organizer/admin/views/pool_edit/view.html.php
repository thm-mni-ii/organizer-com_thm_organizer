<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewPool_Edit
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/edit.php';

/**
 * Class THM_OrganizerViewPool_Edit for component com_thm_organizer
 * Class provides methods to display the view course pool
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.thm.de
 */
class THM_OrganizerViewPool_Edit extends THM_OrganizerViewEdit
{
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
		$resourceID = (isset($this->item->id) AND is_numeric($this->item->id)) ? $this->item->id : 0;
		$isNew      = ($resourceID == 0);
		$title      = $isNew ? JText::_('COM_THM_ORGANIZER_POOL_EDIT_NEW_VIEW_TITLE') : JText::_('COM_THM_ORGANIZER_POOL_EDIT_EDIT_VIEW_TITLE');
		JToolbarHelper::title($title, 'organizer_subject_pools');
		JToolbarHelper::apply('pool.apply', $isNew ? 'COM_THM_ORGANIZER_ACTION_APPLY_NEW' : 'COM_THM_ORGANIZER_ACTION_APPLY_EDIT');
		JToolbarHelper::save('pool.save');
		JToolbarHelper::cancel('pool.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

		$toolbar = JToolbar::getInstance('toolbar');

		$baseURL   = 'index.php?option=com_thm_organizer&amp;tmpl=component&amp;type=pool&amp;id=' . $resourceID . '&amp;';
		$poolIcon  = 'list';
		$poolTitle = JText::_('COM_THM_ORGANIZER_ADD_POOL');
		$poolLink  = $baseURL . 'view=pool_selection';
		$toolbar->appendButton('Popup', $poolIcon, $poolTitle, $poolLink);

		$subjectIcon  = 'book';
		$subjectTitle = JText::_('COM_THM_ORGANIZER_ADD_SUBJECT');
		$subjectLink  = $baseURL . 'view=subject_selection';
		$toolbar->appendButton('Popup', $subjectIcon, $subjectTitle, $subjectLink);
	}
}
