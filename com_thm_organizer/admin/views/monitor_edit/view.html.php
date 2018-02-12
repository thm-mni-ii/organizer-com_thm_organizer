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
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/edit.php';

/**
 * Class loads the monitor form into display context.
 */
class THM_OrganizerViewMonitor_Edit extends THM_OrganizerViewEdit
{
    /**
     * loads monitor information into the view context
     *
     * @param object $tpl the template object
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
        $title = ($this->form->getValue('id')) ?
            JText::_('COM_THM_ORGANIZER_MONITOR_EDIT_EDIT_VIEW_TITLE') : JText::_('COM_THM_ORGANIZER_MONITOR_EDIT_NEW_VIEW_TITLE');
        JToolbarHelper::title($title, 'organizer_monitors');
        JToolbarHelper::save('monitor.save');
        JToolbarHelper::save2new('monitor.save2new');
        JToolbarHelper::cancel('monitor.cancel');
    }
}
