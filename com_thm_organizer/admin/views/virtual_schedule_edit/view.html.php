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
class thm_organizersViewvirtual_schedule_edit extends JView
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
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/virtual_schedule_edit.js'));

        $model = $this->getModel();
        $this->form = $this->get('Form');
                        
        $task = JRequest::getVar('task', null, 'STRING');
        
        $cid = $model->getID();
        
        $title = JText::_('COM_THM_ORGANIZER').': ';
        $this->setLayout('default');
        $title = JText::_('COM_THM_ORGANIZER').': ';
        $title .= ($cid)? JText::_('JTOOLBAR_EDIT') : JText::_('JTOOLBAR_NEW');
        $title .= " ".JText::_('COM_THM_ORGANIZER_VIRTUAL_SCHEDULE');
        JToolBarHelper::title($title, "mni");
        $this->addToolBar();
        
        $this->legend = ($cid)? JText::_('JTOOLBAR_EDIT') : JText::_('JTOOLBAR_NEW');

        parent::display($tpl);
    }
    
    private function addToolBar()
    {
        JToolBarHelper::save('virtual_schedule.save');
        JToolBarHelper::cancel('virtual_schedule.cancel');
    }
}?>
	