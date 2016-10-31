<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewField_Edit
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/edit.php';

/**
 * Class THM_OrganizerViewField for component com_thm_organizer
 *
 * Class provides methods to display the view field edit
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewField_Edit extends THM_OrganizerViewEdit
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
		$title = $isNew ? JText::_('COM_THM_ORGANIZER_FIELD_EDIT_NEW_VIEW_TITLE') : JText::_('COM_THM_ORGANIZER_FIELD_EDIT_EDIT_VIEW_TITLE');
		JToolbarHelper::title($title, 'organizer_fields');
		JToolbarHelper::save('field.save');
		JToolbarHelper::cancel('field.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
