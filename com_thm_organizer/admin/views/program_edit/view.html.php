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

require_once JPATH_COMPONENT . '/views/edit.php';

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
        if ($this->form->getValue('id') == 0) {
            \JToolbarHelper::title(\JText::_('COM_THM_ORGANIZER_PROGRAM_EDIT_NEW_TITLE'), 'organizer_degree_programs');
            \JToolbarHelper::apply('program.apply', \JText::_('COM_THM_ORGANIZER_CREATE'));
            \JToolbarHelper::save('program.save');
            \JToolbarHelper::save2new('program.save2new');
            \JToolbarHelper::cancel('program.cancel', 'JTOOLBAR_CANCEL');
        } else {
            \JToolbarHelper::title(\JText::_('COM_THM_ORGANIZER_PROGRAM_EDIT_EDIT_TITLE'), 'organizer_degree_programs');
            \JToolbarHelper::apply('program.apply', \JText::_('JTOOLBAR_APPLY'));
            \JToolbarHelper::save('program.save');
            \JToolbarHelper::save2new('program.save2new');
            \JToolbarHelper::save2copy('program.save2copy');
            \JToolbarHelper::cancel('program.cancel', 'JTOOLBAR_CLOSE');

            $toolbar = \JToolbar::getInstance('toolbar');

            $poolLink = 'index.php?option=com_thm_organizer&view=pool_selection&tmpl=component';
            $toolbar->appendButton('Popup', 'list', \JText::_('COM_THM_ORGANIZER_ADD_POOL'), $poolLink);
        }
    }
}
