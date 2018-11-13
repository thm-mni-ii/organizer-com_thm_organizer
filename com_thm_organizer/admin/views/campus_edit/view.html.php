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
 * Class loads the campus form into display context.
 */
class THM_OrganizerViewCampus_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::save('campus.save');
        if (empty($this->item->id)) {
            JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_CAMPUS_EDIT_NEW_TITLE'), 'organizer_campuses');
            JToolbarHelper::cancel('campus.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_CAMPUS_EDIT_EDIT_TITLE'), 'organizer_campuses');
            JToolbarHelper::cancel('campus.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
