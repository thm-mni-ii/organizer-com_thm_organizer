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
class THM_OrganizerViewProgram_Manager extends JViewLegacy
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
        $document->addStyleSheet(JURI::root() . "media/com_thm_organizer/css/thm_organizer.css");

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
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_PRM_TOOLBAR_TITLE'), 'organizer_degree_programs');
        JToolbarHelper::addNew('program.add', 'JTOOLBAR_NEW');
        JToolbarHelper::editList('program.edit', 'JTOOLBAR_EDIT');
        JToolbarHelper::custom(
                               'program.importLSFData',
                               'export',
                               '',
                               'COM_THM_ORGANIZER_PRM_IMPORT',
                               true
                              );
        JToolbarHelper::deleteList('COM_THM_ORGANIZER_PRM_DELETE_CONFIRM', 'program.delete', 'JTOOLBAR_DELETE');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_thm_organizer', '500');
    }
}
