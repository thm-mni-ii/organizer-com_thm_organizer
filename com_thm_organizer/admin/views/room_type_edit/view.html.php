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
 * Class loads the room type form into display context.
 */
class THM_OrganizerViewRoom_Type_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        $isNew = ($this->item->id == 0);
        $title = $isNew ?
            JText::_('COM_THM_ORGANIZER_ROOM_TYPE_EDIT_NEW_VIEW_TITLE')
            : JText::_('COM_THM_ORGANIZER_ROOM_TYPE_EDIT_EDIT_VIEW_TITLE');
        JToolbarHelper::title($title, 'organizer_room_types');
        JToolbarHelper::apply('room_type.apply');
        JToolbarHelper::save('room_type.save');
        JToolbarHelper::cancel('room_type.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
