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
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';

/**
 * Class loads persistent information a filtered set of monitors into the display context.
 */
class THM_OrganizerViewMonitor_Manager extends THM_OrganizerViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Loads data from the model into the view context
     *
     * @param string $tpl the name of the template to be used
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
     * Creates joomla toolbar elements
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_MONITOR_MANAGER_VIEW_TITLE'), 'organizer_monitors');
        JToolbarHelper::addNew('monitor.add');
        JToolbarHelper::editList('monitor.edit');
        JToolbarHelper::deleteList(JText::_('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM'), 'monitor.delete');

        if (THM_OrganizerHelperComponent::isAdmin()) {
            JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
