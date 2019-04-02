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
 * Class loads the monitor form into display context.
 */
class THM_OrganizerViewMonitor_Edit extends THM_OrganizerViewEdit
{
    /**
     * Adds joomla toolbar elements to the view context
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::save('monitor.save');
        \JToolbarHelper::save2new('monitor.save2new');
        if ($this->form->getValue('id') == 0) {
            \JToolbarHelper::title(\JText::_('COM_THM_ORGANIZER_MONITOR_EDIT_NEW_TITLE'), 'organizer_monitors');
            \JToolbarHelper::cancel('monitor.cancel', 'JTOOLBAR_CANCEL');
        } else {
            \JToolbarHelper::title(\JText::_('COM_THM_ORGANIZER_MONITOR_EDIT_EDIT_TITLE'), 'organizer_monitors');
            \JToolbarHelper::cancel('monitor.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
