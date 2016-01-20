<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewmonitor_edit
 * @description monitor edit view
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('thm_core.edit.view');

/**
 * Class loading a monitor entry into the view context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewMonitor_Edit extends THM_CoreViewEdit
{
    /**
     * loads monitor information into the view context
     *
     * @param   object  $tpl  the template object
     *
     * @return void
     */
    public function display($tpl = null)
    {
        parent::display($tpl);
    }

    /**
     * Adds joomla toolbar elements to the view context
     *
     * @return void
     */
    protected function addToolBar()
    {
        $title = ($this->form->getValue('id'))?
            JText::_('COM_THM_ORGANIZER_MONITOR_EDIT_EDIT_VIEW_TITLE') : JText::_('COM_THM_ORGANIZER_MONITOR_EDIT_NEW_VIEW_TITLE');
        JToolbarHelper::title($title, 'organizer_monitors');
        JToolbarHelper::save('monitor.save');
        JToolbarHelper::save2new('monitor.save2new');
        JToolbarHelper::cancel('monitor.cancel');
    }
}
