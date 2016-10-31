<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewMethod_Edit
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/edit.php';

/**
 * Class loads persistent room type information into display context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewMethod_Edit extends THM_OrganizerViewEdit
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
		$isNew = ($this->item->id == 0);
		$title = $isNew ?
			JText::_('COM_THM_ORGANIZER_METHOD_EDIT_NEW_VIEW_TITLE')
			: JText::_('COM_THM_ORGANIZER_METHOD_EDIT_EDIT_VIEW_TITLE');
		JToolbarHelper::title($title, 'organizer_methods');
		JToolbarHelper::apply('method.apply');
		JToolbarHelper::save('method.save');
		JToolbarHelper::cancel('method.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
