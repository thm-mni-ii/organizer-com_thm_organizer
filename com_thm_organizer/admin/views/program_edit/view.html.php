<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewProgram_Edit
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/edit.php';

/**
 * Class loads program form information for editing
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewProgram_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        parent::display($tpl);
    }

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
