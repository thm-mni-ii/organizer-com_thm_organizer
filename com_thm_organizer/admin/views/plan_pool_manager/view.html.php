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
 * Class loads persistent information a filtered set of (scheduled subject) pools into the display context.
 */
class THM_OrganizerViewPlan_Pool_Manager extends THM_OrganizerViewList
{
    public $batch;

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
        if (!THM_OrganizerHelperAccess::allowSchedulingAccess()) {
            throw new \Exception(Languages::_('THM_ORGANIZER_401'), 401);
        }

        // Set batch template path
        $this->batch = [
            'publishing' => JPATH_COMPONENT_ADMINISTRATOR . '/views/plan_pool_manager/tmpl/default_publishing.php'
        ];

        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::title(Languages::_('THM_ORGANIZER_PLAN_POOL_MANAGER_VIEW_TITLE'), 'organizer_pools');
        \JToolbarHelper::editList('plan_pool.edit');

        $if          = "alert('" . Languages::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST') . "');";
        $else        = "jQuery('#modal-publishing').modal('show'); return true;";
        $script      = 'if(document.adminForm.boxchecked.value==0){' . $if . '}else{' . $else . '}';
        $batchButton = '<button id="pool-publishing" data-toggle="modal" class="btn btn-small" onclick="' . $script . '">';

        $title       = Languages::_('THM_ORGANIZER_BATCH');
        $batchButton .= '<span class="icon-stack" title="' . $title . '"></span>' . " $title";

        $batchButton .= '</button>';

        $bar = \JToolBar::getInstance('toolbar');
        $bar->appendButton('Custom', $batchButton, 'batch');

        if (THM_OrganizerHelperAccess::isAdmin()) {
            \JToolbarHelper::custom('plan_pool.mergeView', 'attachment', 'attachment', 'THM_ORGANIZER_ACTION_MERGE',
                true);
            \JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
