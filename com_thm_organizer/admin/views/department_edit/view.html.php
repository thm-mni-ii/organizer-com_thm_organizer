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

require_once JPATH_COMPONENT . '/views/edit.php';

/**
 * Class loads the department form into display context.
 */
class THM_OrganizerViewDepartment_Edit extends THM_OrganizerViewEdit
{

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::apply('department.apply');
        JToolbarHelper::save('department.save');
        JToolbarHelper::save2new('department.save2new');
        if (empty($this->item->id)) {
            JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_DEPARTMENT_EDIT_NEW_TITLE'), 'organizer_departments');
            JToolbarHelper::cancel('department.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolbarHelper::save2copy('department.save2copy');
            JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_DEPARTMENT_EDIT_EDIT_TITLE'), 'organizer_departments');
            JToolbarHelper::cancel('department.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
