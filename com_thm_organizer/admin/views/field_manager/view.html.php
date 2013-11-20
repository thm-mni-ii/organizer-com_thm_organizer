<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewField_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Class THM_OrganizerViewColors for component com_thm_organizer
 * Class provides methods to display the view colors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewField_Manager extends JView
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

        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");

        $items = $this->get('Items');
        $pagination = $this->get('Pagination');

        $this->items = $items;
        $this->pagination = $pagination;
        $this->state = $this->get('State');
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
        JToolBarHelper::title(JText::_('COM_THM_ORGANIZER_FLM_TOOLBAR_TITLE'), 'organizer_fields');
        JToolBarHelper::addNew('field.add', 'JTOOLBAR_NEW');
        JToolBarHelper::editList('field.edit', 'JTOOLBAR_EDIT');
        JToolBarHelper::deleteList('', 'field.delete', 'JTOOLBAR_DELETE');
    }
}
