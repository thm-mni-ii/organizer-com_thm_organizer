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
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/edit.php';

/**
 * Class loads the (degree) program form into display context.
 */
class THM_OrganizerViewProgram_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        $isNew = $this->form->getValue('id') == 0;
        $title = $isNew ? JText::_("COM_THM_ORGANIZER_PROGRAM_EDIT_NEW_VIEW_TITLE") : JText::_("COM_THM_ORGANIZER_PROGRAM_EDIT_EDIT_VIEW_TITLE");
        JToolbarHelper::title($title, 'organizer_degree_programs');
        $applyText = $isNew ? JText::_('COM_THM_ORGANIZER_ACTION_APPLY_NEW') : JText::_('JTOOLBAR_APPLY');
        JToolbarHelper::apply('program.apply', $applyText);
        JToolbarHelper::save('program.save');
        JToolbarHelper::save2new('program.save2new');
        if ($isNew) {
            JToolbarHelper::cancel('program.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolbarHelper::save2copy('program.save2copy');
            JToolbarHelper::cancel('program.cancel', 'JTOOLBAR_CLOSE');

            $toolbar = JToolbar::getInstance('toolbar');

            $poolIcon  = 'list';
            $poolTitle = JText::_('COM_THM_ORGANIZER_ADD_POOL');
            $poolLink  = 'index.php?option=com_thm_organizer&amp;view=pool_selection&amp;tmpl=component';
            $toolbar->appendButton('Popup', $poolIcon, $poolTitle, $poolLink);
        }
    }
}
