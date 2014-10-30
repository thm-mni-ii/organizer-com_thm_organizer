<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewRoom_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.view');
/**
 * Class provides methods to display a list of rooms
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewRoom_Manager extends THM_CoreViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
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
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_ROOM_MANAGER_VIEW_TITLE'), 'organizer_rooms');
        JToolbarHelper::addNew('room.add');
        JToolbarHelper::editList('room.edit');
        JToolbarHelper::custom('room.mergeAll', 'merge', 'merge', 'COM_THM_ORGANIZER_ACTION_MERGE_AUTO', false);
        JToolbarHelper::custom('room.mergeView', 'merge', 'merge', 'COM_THM_ORGANIZER_ACTION_MERGE', true);
        JToolbarHelper::deleteList(JText::_('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM'), 'room.delete');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_thm_organizer');
    }
}
