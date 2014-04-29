<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewProgram_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('jquery.jquery');

/**
 * Class loads program form information for editing
 *
 * @category    Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewProgram_Edit extends JViewLegacy
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');
        $document->addScript($this->baseurl . "/components/com_thm_organizer/assets/js/mapping.js");

        $this->form = $this->get('Form');
        $isNew = $this->form->getValue('id') == 0;
        $this->_layout = $isNew? 'add' : 'edit';
        if (!$isNew)
        {
            $this->children = $this->getModel()->children;
        }

        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $isNew = $this->form->getValue('id') == 0;
        $title = $isNew ? JText::_("COM_THM_ORGANIZER_PRM_NEW") : JText::_("COM_THM_ORGANIZER_PRM_EDIT");
        JToolbarHelper::title($title, 'organizer_degree_programs');
        $applyText = $isNew? JText::_('COM_THM_ORGANIZER_APPLY_NEW') : JText::_('COM_THM_ORGANIZER_APPLY_EDIT');
        JToolbarHelper::apply('program.apply', $applyText);
        JToolbarHelper::save('program.save');
        JToolbarHelper::save2new('program.save2new');
        if ($isNew)
        {
            JToolbarHelper::cancel('program.cancel', 'JTOOLBAR_CANCEL');
        }
        else
        {
            JToolbarHelper::save2copy('program.save2copy');
            JToolbarHelper::cancel('program.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
