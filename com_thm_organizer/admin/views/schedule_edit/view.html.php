<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        html view for schedule upload and editing
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';
/**
 * Class loading schedule data into output context
 * 
 * @package  Admin
 * 
 * @since    2.5.4 
 */
class THM_OrganizerViewschedule_edit extends JView
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
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        JHtml::_('behavior.framework', true);
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/schedule_edit.js'));

        $model = $this->getModel();
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
            JToolBarHelper::save('schedule.save');
        }
        else
        {
            $this->setLayout('add');
            $this->legend = JText::_('JTOOLBAR_NEW') . ' ' . JText::_('COM_THM_ORGANIZER_PLAN');
            $title .= JText::_("JTOOLBAR_NEW") . ' ' . JText::_('COM_THM_ORGANIZER_PLAN');
            JToolBarHelper::custom('schedule.upload', 'upload', 'upload', 'COM_THM_ORGANIZER_SCH_UPLOAD', false);
        }
        JToolBarHelper::title($title, "mni");
        JToolBarHelper::cancel('schedule.cancel');
    }
}
