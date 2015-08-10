<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewPool_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.edit.view');

/**
 * Class THM_OrganizerViewPool_Edit for component com_thm_organizer
 * Class provides methods to display the view course pool
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewPool_Edit extends THM_CoreViewEdit
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        $isNew = ($this->item->id == 0);
        $title = $isNew ? JText::_('COM_THM_ORGANIZER_POOL_EDIT_NEW_VIEW_TITLE') : JText::_('COM_THM_ORGANIZER_POOL_EDIT_EDIT_VIEW_TITLE');
        JToolbarHelper::title($title, 'organizer_subject_pools');
        JToolbarHelper::apply('pool.apply', $isNew ? 'COM_THM_ORGANIZER_ACTION_APPLY_NEW' : 'COM_THM_ORGANIZER_ACTION_APPLY_EDIT');
        JToolbarHelper::save('pool.save');
        JToolbarHelper::cancel('pool.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        $bar = JToolBar::getInstance('toolbar');

        $sub_imgTitle = 'addSubject';
        $sub_title = JText::_('COM_THM_ORGANIZER_ADD_SUBJECT');
        $sub_link = 'index.php?option=com_thm_organizer&amp;view=subject_selection&amp;tmpl=component';
        $sub_height = '600';
        $sub_width = '900';
        $sub_top = 0;
        $sub_left = 0;
        $bar->appendButton('Popup', $sub_imgTitle, $sub_title, $sub_link, $sub_width, $sub_height, $sub_top, $sub_left);

        $pool_imgTitle = 'addPool';
        $pool_title = JText::_('COM_THM_ORGANIZER_ADD_POOL');
        $pool_link = 'index.php?option=com_thm_organizer&amp;view=pool_selection&amp;tmpl=component';
        $pool_height = '600';
        $pool_width = '900';
        $pool_top = 0;
        $pool_left = 0;
        $bar->appendButton('Popup', $pool_imgTitle, $pool_title, $pool_link, $pool_width, $pool_height, $pool_top, $pool_left);


    }
}
