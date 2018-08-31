<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;;
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';

/**
 * Class loads a filtered set of campuses into the display context.
 */
class THM_OrganizerViewCampus_Manager extends THM_OrganizerViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        if (!THM_OrganizerHelperComponent::allowFMAccess()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_CAMPUS_MANAGER_VIEW_TITLE'), 'organizer_campuses');
        JToolbarHelper::addNew('campus.add');
        JToolbarHelper::editList('campus.edit');
        JToolbarHelper::deleteList('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM', 'campus.delete');
        if (THM_OrganizerHelperComponent::isAdmin()) {
            JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
