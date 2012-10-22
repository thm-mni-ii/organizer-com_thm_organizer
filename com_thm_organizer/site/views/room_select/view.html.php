<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        room select view
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
/**
 * Provides a form for room selection
 * 
 * @package  Joomla.Site
 * 
 * @since    2.5.4 
 */
class thm_organizerViewroom_select extends JView
{
    /**
     * Sets context variables for output
     * 
     * @param   string  $tpl  the name of the template to be used
     * 
     * @return  void 
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/event_edit.js'));
        $document->setTitle(JText::_('COM_THM_ORGANIZER_RS_TITLE'));

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        parent::display($tpl);
    }
}
