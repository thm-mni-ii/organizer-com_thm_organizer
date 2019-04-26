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
 * Class loads persistent information a filtered set of degrees into the display context.
 */
class THM_OrganizerViewDegree_Manager extends THM_OrganizerViewList
{
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
        if (!Access::isAdmin()) {
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
        \JToolbarHelper::title(Languages::_('THM_ORGANIZER_DEGREE_MANAGER_VIEW_TITLE'), 'organizer_degrees');
        \JToolbarHelper::addNew('degree.add');
        \JToolbarHelper::editList('degree.edit');
        \JToolbarHelper::deleteList(Languages::_('THM_ORGANIZER_ACTION_DELETE_CONFIRM'), 'degree.delete');
        \JToolbarHelper::preferences('com_thm_organizer');
    }
}
