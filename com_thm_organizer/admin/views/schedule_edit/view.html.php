<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewschedule_edit
 * @description html view for schedule upload and editing
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class loading schedule data into output context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewSchedule_Edit extends JViewLegacy
{
    /**
     * loads persistent data into view context and intitiates functions for the
     * creation of html elements
     *
     * @param   string  $tpl  the template to be used upon the view
     *
     * @return void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.framework', true);
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/schedule_edit.css');
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/schedule_errors.js'));

        $this->form = $this->get('Form');

        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * creates the joomla adminstrative toolbar
     *
     * @return void
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER') . ': ';
        if ($this->form->getValue('id'))
        {
            $this->setLayout('edit');
            $this->legend = JText::_('JTOOLBAR_EDIT') . ' ' . JText::_('COM_THM_ORGANIZER_PLAN');
            $title .= JText::_('JTOOLBAR_EDIT') . ' ' . JText::_('COM_THM_ORGANIZER_PLAN');
            JToolbarHelper::save('schedule.save');
        }
        else
        {
            $this->setLayout('add');
            $this->legend = JText::_('JTOOLBAR_NEW') . ' ' . JText::_('COM_THM_ORGANIZER_PLAN');
            $title .= JText::_("JTOOLBAR_NEW") . ' ' . JText::_('COM_THM_ORGANIZER_PLAN');
            JToolbarHelper::custom('schedule.upload', 'upload', 'upload', 'COM_THM_ORGANIZER_SCH_UPLOAD', false);
        }
        JToolbarHelper::title($title, "organizer_schedules");
        JToolbarHelper::cancel('schedule.cancel');
    }
}
