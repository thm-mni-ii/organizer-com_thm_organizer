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
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $actions = $this->getModel()->actions;

        if (!$actions->{'core.admin'} and !$actions->{'organizer.menu.schedule'}) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        // Set batch template path
        $this->batch = array(
            'publishing' => JPATH_COMPONENT_ADMINISTRATOR . '/views/plan_pool_manager/tmpl/default_publishing.php'
        );

        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_PLAN_POOL_MANAGER_VIEW_TITLE'), 'organizer_plan_pools');
        JToolbarHelper::editList('plan_pool.edit');

        if ($this->getModel()->actions->{'core.admin'}) {
            JToolbarHelper::custom('plan_pool.mergeView', 'merge', 'merge', 'COM_THM_ORGANIZER_ACTION_MERGE', true);
        }

        $if = "alert('" . JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST') . "');";
        $else = "jQuery('#modal-publishing').modal('show'); return true;";
        $script = 'if(document.adminForm.boxchecked.value==0){' . $if . '}else{' . $else . '}';
        $batchButton = '<button id="pool-publishing" data-toggle="modal" class="btn btn-small" onclick="' . $script . '">';

        $title = JText::_('COM_THM_ORGANIZER_BATCH');
        $batchButton .= '<span class="icon-stack" title="' . $title . '"></span>' . " $title";

        $batchButton .= '</button>';

        $bar = JToolBar::getInstance('toolbar');
        $bar->appendButton('Custom', $batchButton, 'batch');

        if ($this->getModel()->actions->{'core.admin'}) {
            JToolbarHelper::divider();
            JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
