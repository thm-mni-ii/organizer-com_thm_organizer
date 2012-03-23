<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        teacher editor view
 * @description provides a form for editing teacher information
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @author      Markus Bader markusDOTbaderATmniDOTthmDOTde
 * @author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2012
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5.0
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewteacher_edit extends JView
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
		$document->addScript(JRoute::_('components/com_thm_organizer/assets/js/submitbutton.js'));
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/teacher_edit.js'));

	
        $model = $this->getModel();
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->gpuntisids = $model->getAllGpuntisIds();
        
        // get task for template (gpuntisID might be hidden)
        $this->task = JRequest::getVar('task', null, 'post','STRING');
        
        if ($this->task == 'teacher.add') {
        	// take correct title
        	$this->item->id = 0;

        	// set values in form
        	$formElements = $this->form->getFieldset();
        	foreach ($formElements as $value) {
        		$this->form->setValue($value->fieldname, null, '');
        	}
        }
        

        $this->setLayout('edit');

        $this->addToolBar();       
        parent::display($tpl);
    }
    
    private function addToolBar()
    {
        JRequest::setVar('hidemainmenu', true);
        $title = JText::_('COM_THM_ORGANIZER').': ';
        $title .= ($this->item->id == 0)? JText::_('JTOOLBAR_NEW') : JText::_('JTOOLBAR_EDIT');
        $title .= " ".JText::_('COM_THM_ORGANIZER_TR');        
        JToolBarHelper::title( $title, 'mni' );
        JToolBarHelper::save('teacher.save');
        JToolBarHelper::cancel('teacher.cancel', $this->item->id == 0 ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}?>
	