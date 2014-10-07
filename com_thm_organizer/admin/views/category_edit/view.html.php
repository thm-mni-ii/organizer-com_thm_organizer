<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewcategory_edit
 * @description category edit view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.edit.view');
/**
 * Class loading persistent data into the view context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewCategory_Edit extends JViewLegacy
{
    /**
     * loads model data into view context
     *
     * @param   string  $tpl  the name of the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        THM_CoreEditView::setUp($this);
        parent::display($tpl);
    }

    /**
     * generates joomla toolbar elements
     *
     * @return void
     */
    public function addToolBar()
    {
        if ($this->form->getValue('id') == 0)
        {
            JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_CATEGORY_EDIT_NEW_VIEW_TITLE'), 'organizer_categories');
            $applyText = JText::_('COM_THM_ORGANIZER_ACTION_APPLY_NEW');
            $cancelText = JText::_('COM_THM_ORGANIZER_ACTION_CANCEL');
        }
        else
        {
            JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_CATEGORY_EDIT_EDIT_VIEW_TITLE'), 'organizer_categories');
            $applyText = JText::_('COM_THM_ORGANIZER_ACTION_APPLY_EDIT');
            $cancelText = JText::_('COM_THM_ORGANIZER_ACTION_CLOSE');
        }
        JToolbarHelper::apply('category.apply', $applyText);
        JToolbarHelper::save('category.save');
        JToolbarHelper::save2new('category.save2new');
        JToolbarHelper::cancel('category.cancel', $cancelText);
    }
}
