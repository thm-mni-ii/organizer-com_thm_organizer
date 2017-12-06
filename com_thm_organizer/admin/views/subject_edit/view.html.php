<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewSubject_Edit
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/edit.php';

/**
 * Class loadd persistent subject information into dispaly context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewSubject_Edit extends THM_OrganizerViewEdit
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
		if (empty($this->item->id))
		{
			$titleText  = JText::_('COM_THM_ORGANIZER_SUBJECT_EDIT_NEW_VIEW_TITLE');
			$applyText  = JText::_('COM_THM_ORGANIZER_ACTION_APPLY_NEW');
			$cancelText = JText::_('JTOOLBAR_CANCEL');
		}
		else
		{
			$titleText  = JText::_('COM_THM_ORGANIZER_SUBJECT_EDIT_EDIT_VIEW_TITLE');
			$applyText  = JText::_('COM_THM_ORGANIZER_ACTION_APPLY_EDIT');
			$cancelText = JText::_('JTOOLBAR_CLOSE');
		}

		JToolbarHelper::title($titleText, 'organizer_subjects');
		JToolbarHelper::apply('subject.apply', $applyText);
		JToolbarHelper::save('subject.save');
		JToolbarHelper::save2new('subject.save2new');
		JToolbarHelper::cancel('subject.cancel', $cancelText);
	}

	/**
	 * Adds resource files to the document
	 *
	 * @return  void
	 */
	protected function modifyDocument()
	{
		parent::modifyDocument();
		/** @noinspection PhpIncludeInspection */
		JFactory::getDocument()->addScript(JUri::root() . '/media/com_thm_organizer/js/subject_prep_course.js');
	}
}
