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
 * Class loads persistent information a filtered set of (subject) pools into the display context.
 */
class THM_OrganizerViewPool_Manager extends THM_OrganizerViewList
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
     * @throws Exception => unauthorized access
     */
    public function display($tpl = null)
    {
        if (!THM_OrganizerHelperAccess::allowDocumentAccess()) {
            throw new \Exception(\JText::_('COM_THM_ORGANIZER_401'), 401);
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
        $baseTitle = \JText::_('COM_THM_ORGANIZER_POOL_MANAGER_VIEW_TITLE');
        $title     = empty($this->programName) ? $baseTitle : $baseTitle . ' - ' . $this->programName;
        \JToolbarHelper::title($title, 'organizer_pools');
        \JToolbarHelper::addNew('pool.add');
        \JToolbarHelper::editList('pool.edit');
        \JToolbarHelper::deleteList('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM', 'pool.delete');

        if (THM_OrganizerHelperAccess::isAdmin()) {
            \JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
