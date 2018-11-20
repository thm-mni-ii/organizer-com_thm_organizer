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
require_once JPATH_ROOT . '/media/com_thm_organizer/views/edit.php';

/**
 * Class loads the plan (degree) program / organizational grouping form into display context.
 */
class THM_OrganizerViewPlan_Program_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_PLAN_PROGRAM_EDIT_TITLE'), 'organizer_plan_programs');
        JToolbarHelper::save('plan_program.save');
        $cancelText = empty($this->item->id) ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE';
        JToolbarHelper::cancel('plan_program.cancel', $cancelText);
    }
}
