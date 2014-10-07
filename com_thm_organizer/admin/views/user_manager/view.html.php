<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewUser_Manager
 * @description view output file for user lists
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.view');

/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewUser_Manager extends JViewLegacy
{
    /**
     * loads data into view output context and initiates functions creating html
     * elements
     *
     * @param   string  $tpl  the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        THM_CoreListView::display($this);
        parent::display($tpl);
    }

    /**
     * creates a joomla administrative tool bar
     *
     * @return void
     */
    public function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_USERS');
        JToolbarHelper::title($title, 'organizer_users');

        $image = 'new';
        $title = JText::_('COM_THM_ORGANIZER_NEW');
        $link = 'index.php?option=com_thm_organizer&amp;view=user_select&amp;tmpl=component';
        $height = '550';
        $width = '875';
        $top = 0;
        $left = 0;
        $onClose = 'window.location.reload();';
        $bar = JToolBar::getInstance('toolbar');
        $bar->appendButton('Popup', $image, $title, $link, $width, $height, $top, $left, $onClose);

        JToolbarHelper::deleteList(JText::_('COM_THM_ORGANIZER_USM_DELETE_CONFIRM'), 'user.delete');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_thm_organizer');
    }
}
