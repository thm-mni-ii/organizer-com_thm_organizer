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
 * Class loads the degree form into display context.
 */
class THM_OrganizerViewDegree_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        if ($this->form->getValue('id') == 0) {
            $title      = JText::_('COM_THM_ORGANIZER_DEGREE_EDIT_NEW_VIEW_TITLE');
            $cancelText = JText::_('COM_THM_ORGANIZER_ACTION_CANCEL');
        } else {
            $title      = JText::_('COM_THM_ORGANIZER_DEGREE_EDIT_EDIT_VIEW_TITLE');
            $cancelText = JText::_('COM_THM_ORGANIZER_CLOSE');
        }
        JToolbarHelper::title($title, 'organizer_degrees');
        JToolbarHelper::save('degree.save');
        JToolbarHelper::cancel('degree.cancel', $cancelText);
    }
}
