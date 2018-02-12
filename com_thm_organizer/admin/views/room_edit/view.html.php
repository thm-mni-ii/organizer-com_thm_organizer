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
 * Class loads the room form into display context.
 */
class THM_OrganizerViewRoom_Edit extends THM_OrganizerViewEdit
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
        $title = $this->item->id == 0 ?
            JText::_("COM_THM_ORGANIZER_ROOM_EDIT_NEW_VIEW_TITLE") : JText::_("COM_THM_ORGANIZER_ROOM_EDIT_EDIT_VIEW_TITLE");
        JToolbarHelper::title($title, 'organizer_rooms');
        JToolbarHelper::save('room.save');
        JToolbarHelper::cancel('room.cancel', $this->item->id == 0 ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
