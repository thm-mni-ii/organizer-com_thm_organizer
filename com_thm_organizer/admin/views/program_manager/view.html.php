<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewProgram_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Class THM_OrganizerViewProgram_Manager for component com_thm_organizer
 * Class provides methods to display the view degree program manager
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewProgram_Manager extends JView
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
        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->degrees = $model->degrees;
        $this->versions = $model->versions;
        $this->fields = $model->fields;
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
        JToolBarHelper::title(JText::_('COM_THM_ORGANIZER_PRM_TOOLBAR_TITLE'), 'organizer_degree_programs');
        JToolBarHelper::addNew('program.add', 'JTOOLBAR_NEW');
        JToolBarHelper::editList('program.edit', 'JTOOLBAR_EDIT');
        JToolBarHelper::custom(
                               'program.importLSFData',
                               'export',
                               '',
                               'COM_THM_ORGANIZER_PRM_IMPORT',
                               true,
                               true
                              );
        JToolBarHelper::deleteList('COM_THM_ORGANIZER_PRM_DELETE_CONFIRM', 'program.delete', 'JTOOLBAR_DELETE');
        JToolBarHelper::divider();
        JToolBarHelper::preferences('com_thm_organizer', '500');
    }
}
