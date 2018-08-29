<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/views/edit.php';

/**
 * Class loads the subject form into display context.
 */
class THM_OrganizerViewSubject_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        if (empty($this->item->id)) {
            $titleText  = JText::_('COM_THM_ORGANIZER_SUBJECT_EDIT_NEW_VIEW_TITLE');
            $applyText  = JText::_('COM_THM_ORGANIZER_CREATE');
            $cancelText = JText::_('JTOOLBAR_CANCEL');
        } else {
            $titleText  = JText::_('COM_THM_ORGANIZER_SUBJECT_EDIT_EDIT_VIEW_TITLE');
            $applyText  = JText::_('COM_THM_ORGANIZER_APPLY');
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
     * @return void
     */
    protected function modifyDocument()
    {
        parent::modifyDocument();
        JFactory::getDocument()->addScript(JUri::root() . '/media/com_thm_organizer/js/subject_prep_course.js');
    }
}
