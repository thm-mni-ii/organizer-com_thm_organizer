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
 * Class loads the (subject) pool form into display context.
 */
class THM_OrganizerViewPool_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        $resourceID = (isset($this->item->id) and is_numeric($this->item->id)) ? $this->item->id : 0;
        $isNew      = ($resourceID == 0);
        $title      = $isNew ? JText::_('COM_THM_ORGANIZER_POOL_EDIT_NEW_VIEW_TITLE') : JText::_('COM_THM_ORGANIZER_POOL_EDIT_EDIT_VIEW_TITLE');
        JToolbarHelper::title($title, 'organizer_subject_pools');
        JToolbarHelper::apply('pool.apply',
            $isNew ? 'COM_THM_ORGANIZER_CREATE' : 'COM_THM_ORGANIZER_APPLY');
        JToolbarHelper::save('pool.save');
        JToolbarHelper::save2new('pool.save2new');

        if (!$isNew) {
            JToolbarHelper::save2copy('pool.save2copy');
        }

        JToolbarHelper::cancel('pool.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        $toolbar = JToolbar::getInstance('toolbar');

        if (!$isNew) {
            $baseURL   = 'index.php?option=com_thm_organizer&amp;tmpl=component&amp;type=pool&amp;id=' . $resourceID . '&amp;';
            $poolIcon  = 'list';
            $poolTitle = JText::_('COM_THM_ORGANIZER_ADD_POOL');
            $poolLink  = $baseURL . 'view=pool_selection';
            $toolbar->appendButton('Popup', $poolIcon, $poolTitle, $poolLink);

            $subjectIcon  = 'book';
            $subjectTitle = JText::_('COM_THM_ORGANIZER_ADD_SUBJECT');
            $subjectLink  = $baseURL . 'view=subject_selection';
            $toolbar->appendButton('Popup', $subjectIcon, $subjectTitle, $subjectLink);
        }
    }
}
