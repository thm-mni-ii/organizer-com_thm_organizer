<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewRoom_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Class provides methods to display a list of rooms
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewRoom_Manager extends JViewLegacy
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
        $doc = JFactory::getDocument();
        $doc->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/subject_list.css');

        $model = $this->getModel();
        $this->items = $this->get('Items');
        $this->buildings = $model->buildings;
        $this->floors = $model->floors;
        $this->types = $model->types;
        $this->pagination = $this->get('Pagination');
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
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_RMM_TOOLBAR_TITLE'), 'organizer_rooms');
        JToolbarHelper::addNew('room.add', 'JTOOLBAR_NEW');
        JToolbarHelper::editList('room.edit', 'JTOOLBAR_EDIT');
        JToolbarHelper::custom('room.mergeAll', 'merge', 'merge', 'COM_THM_ORGANIZER_MERGE_ALL', false);
        JToolbarHelper::custom('room.mergeView', 'merge', 'merge', 'COM_THM_ORGANIZER_MERGE', true);
        JToolbarHelper::deleteList('', 'room.delete', 'JTOOLBAR_DELETE');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_thm_organizer');
    }
}
