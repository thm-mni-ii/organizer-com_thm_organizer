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

require_once JPATH_COMPONENT . '/views/merge.php';

/**
 * Class loads the room type merge form into display context.
 */
class THM_OrganizerViewRoom_Type_Merge extends THM_OrganizerViewMerge
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_ROOM_TYPE_MERGE_VIEW_TITLE'));
        JToolbarHelper::custom('room_type.merge', 'attachment', 'attachment', 'COM_THM_ORGANIZER_ACTION_MERGE', false);
        JToolbarHelper::cancel('room_type.cancel', 'JTOOLBAR_CANCEL');
    }
}
