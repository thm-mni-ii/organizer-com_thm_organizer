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
 * Class loads persistent information a filtered set of schedules into the display context.
 */
class THM_OrganizerViewSchedule_Manager extends THM_OrganizerViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * loads data into view output context and initiates functions creating html
     * elements
     *
     * @param string $tpl the template to be used
     *
     * @return void
     * @throws Exception => unauthorized access
     */
    public function display($tpl = null)
    {
        if (!THM_OrganizerHelperAccess::allowSchedulingAccess()) {
            throw new \Exception(\JText::_('COM_THM_ORGANIZER_401'), 401);
        }

        parent::display($tpl);
    }

    /**
     * creates a joomla administrative tool bar
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::title(\JText::_('COM_THM_ORGANIZER_SCHEDULE_MANAGER_VIEW_TITLE'), 'organizer_schedules');
        \JToolbarHelper::addNew('schedule.add');
        \JToolbarHelper::makeDefault('schedule.activate', 'COM_THM_ORGANIZER_ACTION_ACTIVATE');
        \JToolbarHelper::custom('schedule.setReference', 'tree', 'tree', 'COM_THM_ORGANIZER_ACTION_REFERENCE', true);
        \JToolbarHelper::deleteList(\JText::_('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM'), 'schedule.delete');

        if (THM_OrganizerHelperAccess::isAdmin()) {
            \JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
