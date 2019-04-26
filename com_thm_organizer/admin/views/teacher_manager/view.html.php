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
 * Class loads persistent information a filtered set of teachers into the display context.
 */
class THM_OrganizerViewTeacher_Manager extends THM_OrganizerViewList
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
        if (!Access::allowHRAccess()) {
            throw new \Exception(Languages::_('THM_ORGANIZER_401'), 401);
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
        \JToolbarHelper::title(Languages::_('THM_ORGANIZER_TEACHER_MANAGER_VIEW_TITLE'), 'organizer_teachers');
        \JToolbarHelper::addNew('teacher.add');
        \JToolbarHelper::editList('teacher.edit');
        if (Access::isAdmin()) {
            \JToolbarHelper::custom('teacher.mergeView', 'attachment', 'attachment', 'THM_ORGANIZER_ACTION_MERGE',
                true);
            \JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
