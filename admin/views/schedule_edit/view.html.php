<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor view
 * @description provides a form for editing semester information
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
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
        JHtml::_('behavior.framework', true);
        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');
        
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/schedule_edit.js'));

        $model = $this->getModel();
        $this->form = $this->get('Form');
        
        $title = JText::_("COM_THM_ORGANIZER_SCH_TITLE").": ";
        $title .= JText::_("COM_THM_ORGANIZER_EDIT")." ";
        $title .= $this->form->getValue('plantypeID');
        JToolBarHelper::title($title);
        if(thm_organizerHelper::isAdmin('schedule_edit')) $this->addToolBar();

        parent::display($tpl);
    }
    
    private function addToolBar()
    {
        JToolBarHelper::apply('schedule.apply', JText::_('COM_THM_ORGANIZER_APPLY'));
        JToolBarHelper::save('schedule.save', JText::_('COM_THM_ORGANIZER_SAVE'));
        JToolBarHelper::cancel('schedule.cancel', JText::_('COM_THM_ORGANIZER_CLOSE'));
    }
}?>
	