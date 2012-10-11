<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        category edit view
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
 * Class loading persistent data into the view context 
 * 
 * @package  Admin
 * 
 * @since    2.5.4
 */
class thm_organizersViewcategory_edit extends JView
{
    /**
     * loads model data into view context
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
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();
        $this->form = $this->get('Form');
        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * generates joomla toolbar elements
     * 
     * @return void
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER') . ': ';
        $title .= ($this->form->getValue('id'))? JText::_('JTOOLBAR_EDIT') : JText::_('JTOOLBAR_NEW');
        $title .= ' ' . JText::_('JCATEGORY');
        JToolBarHelper::title($title, 'mni');
        JToolBarHelper::save('category.save');
        JToolBarHelper::save2new('category.save2new');
        JToolBarHelper::cancel('category.cancel');
    }
}
