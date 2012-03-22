<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        monitor editor view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersViewmonitor_edit extends JView
{
    function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        
        JHtml::_('behavior.framework', true);
        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/monitor_edit.js'));

        $model = $this->getModel();
        $this->form = $this->get('Form');

        $attribs = array();
        $this->behaviour =  JHTML::_("select.genericlist", $model->behaviours, "display", $attribs, "id", "name", $this->form->getValue('display'));

        $titleText = JText::_('COM_THM_ORGANIZER_MON_EDIT_TITLE').": ";
        $titleText .= ($this->form->getValue('monitorID'))?
                JText::_('COM_THM_ORGANIZER_MON_EDIT_TITLE') : JText::_('COM_THM_ORGANIZER_MON_NEW_TITLE');
        JToolBarHelper::title( $titleText, 'generic.png' );
        $this->addToolBar();

        parent::display($tpl);
    }


    private function addToolBar()
    {
        JToolBarHelper::save('monitor.save', 'JTOOLBAR_SAVE');
        JToolBarHelper::custom('monitor.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        JToolBarHelper::cancel('monitor.cancel', 'JTOOLBAR_CANCEL');
    }
}
?>
	