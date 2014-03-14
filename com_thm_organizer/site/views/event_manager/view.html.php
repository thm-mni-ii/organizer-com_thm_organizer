<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewEvent_manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('jquery.jquery');

/**
 * Build event list
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewEvent_Manager extends JView
{
    /**
     * Loads model data into context and sets variables used for html output
     *
     * @param   string  $tpl  the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/media/com_thm_organizer/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/event_manager.js'));

        $model = $this->getModel();

        $this->form = $this->get('Form');


        $events = $model->events;
        $this->assign('events', $events);
        $display_type = $model->display_type;
        $this->assign('display_type', $display_type);
 
        $categories = $model->categories;
        $this->assignRef('categories', $categories);
        $categoryID = ($model->getState('categoryID'))? $model->getState('categoryID') : - 1;
        $this->assignRef('categoryID', $categoryID);
        $this->makeCategorySelect($categories, $categoryID);

        $canWrite = $model->canWrite;
        $this->assignRef('canWrite', $canWrite);
        $canEdit = $model->canEdit;
        $this->assignRef('canEdit', $canEdit);
        $this->assign('itemID', JRequest::getInt('Itemid'));

        $total = $model->total;
        $this->assign('total', $total);
 
        // Create the pagination object
        $pageNav = $model->pagination;
        $this->assign('pageNav', $pageNav);

        // Form state variables
        $this->state = $this->get('State');
        $search = $model->getState('search');
        $search = (empty($search))? "" : $search;
        $this->assignRef('search', $search);
        $orderby = $model->getState('orderby');
        $orderby = (empty($orderby))? "startdate" : $orderby;
        $this->assign('orderby', $orderby);
        $orderbydir = $model->getState('orderbydir');
        $orderbydir = (empty($orderbydir))? "ASC" : $orderbydir;
        $this->assign('orderbydir', $orderbydir);
 
        parent::display($tpl);
    }

    /**
     * Method to build the category selection
     *
     * @param   object  $categories  the categories to be used
     * @param   object  $selected    the selected category
     *
     * @return void
     */
    private function makeCategorySelect($categories, $selected)
    {
        $nocategories = array(1 => array('id' => '-1', 'title' => JText::_('COM_THM_ORGANIZER_EL_ALL_CATEGORIES')));
        $categories = array_merge($nocategories, $categories);
        $categorySelect = JHTML::_('select.genericlist', $categories, 'categoryID',
                 'id="categoryID" class="inputbox" size="1"', 'id', 'title', $selected
                );
        $this->assignRef('categorySelect', $categorySelect);
    }
}
