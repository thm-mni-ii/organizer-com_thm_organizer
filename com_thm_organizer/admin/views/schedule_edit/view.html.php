<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor view
 * @description provides a form for editing semester information
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */

defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersViewschedule_edit extends JView
{
    public function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        
        JHtml::_('behavior.framework', true);
        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');
        
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/schedule_edit.js'));

        $model = $this->getModel();
        $this->form = $this->get('Form');
        
        $title = JText::_('COM_THM_ORGANIZER').': ';
        if($this->form->getValue('id'))
        {
            $this->setLayout('edit');
            $this->legend = JText::_('JTOOLBAR_EDIT')." ".$this->form->getValue('plantypeID');
            $title .= JText::_('JTOOLBAR_EDIT')." ";
            $title .= " ".$this->form->getValue('plantypeID');
        }
        else
        {
            $this->setLayout('add');
            $this->legend = JText::_('JTOOLBAR_NEW')." ".$this->form->getValue('plantypeID');
            $title .= JText::_("JTOOLBAR_NEW");
            $title .= " ".JText::_('COM_THM_ORGANIZER_PLAN');
        }
        JToolBarHelper::title($title, "mni");
        $this->addToolBar();

        parent::display($tpl);
    }
    
    private function addToolBar()
    {
        if($this->form->getValue('id'))
        {
            JToolBarHelper::apply('schedule.apply', JText::_('COM_THM_ORGANIZER_APPLY'));
            JToolBarHelper::save('schedule.save', JText::_('COM_THM_ORGANIZER_SAVE'));
        }
        else
        {
            JToolBarHelper::custom('schedule.upload', 'upload', 'upload', 'COM_THM_ORGANIZER_SCH_UPLOAD', false);
        }
        JToolBarHelper::cancel('schedule.cancel', JText::_('COM_THM_ORGANIZER_CLOSE'));
    }
}?>
	