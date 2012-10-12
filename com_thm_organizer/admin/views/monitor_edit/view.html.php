<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        monitor edit view
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 *@author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
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
 * Class loading a monitor entry into the view context 
 * 
 * @package  Admin
 * 
 * @since    2.5.4
 */
class thm_organizersViewmonitor_edit extends JView
{
    /**
     * loads monitor information into the view context
     * 
     * @param   string  $tpl  the name of the template to be used
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
        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/monitor_edit.js'));

        $model = $this->getModel();
        $this->form = $this->get('Form');

        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * Adds joomla toolbar elements to the view context
     * 
     * @return void
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER') . ': ';
        $title .= ($this->form->getValue('id'))? JText::_('JTOOLBAR_EDIT') : JText::_('JTOOLBAR_NEW');
        $title .= " " . JText::_('COM_THM_ORGANIZER_MONITOR');
        JToolBarHelper::title($title, 'mni');
        JToolBarHelper::save('monitor.save');
        JToolBarHelper::save2new('monitor.save2new');
        JToolBarHelper::cancel('monitor.cancel');
    }
}
