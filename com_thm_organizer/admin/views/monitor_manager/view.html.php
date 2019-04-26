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

require_once JPATH_COMPONENT . '/views/list.php';

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
     * @throws Exception => unauthorized access
     */
    public function display($tpl = null)
    {
        if (!Access::allowFMAccess()) {
            throw new \Exception(Languages::_('THM_ORGANIZER_401'), 401);
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
        \JToolbarHelper::title(Languages::_('THM_ORGANIZER_MONITOR_MANAGER_VIEW_TITLE'), 'organizer_monitors');
        \JToolbarHelper::addNew('monitor.add');
        \JToolbarHelper::editList('monitor.edit');
        \JToolbarHelper::deleteList(Languages::_('THM_ORGANIZER_ACTION_DELETE_CONFIRM'), 'monitor.delete');

        if (Access::isAdmin()) {
            \JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
