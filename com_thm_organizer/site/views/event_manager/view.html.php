<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewEvent_manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
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
class THM_OrganizerViewEvent_Manager extends JViewLegacy
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
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/media/com_thm_organizer/css/event_manager.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/event_manager.js'));

        $model = $this->getModel();

        $this->form = $this->get('Form');

        $events = $model->events;
        $this->assign('events', $events);
        $display_type = $model->display_type;
        $this->assign('display_type', $display_type);
 
        $categories = $model->categories;
        $this->assignRef('categories', $categories);
        $categoryID = $model->getState('categoryID', '-1');
        $this->assignRef('categoryID', $categoryID);
        $this->makeCategorySelect($categories, $categoryID);

        $this->assignRef('canWrite', $model->canWrite);
        $this->assignRef('canEdit', $model->canEdit);
        $this->assign('itemID', JFactory::getApplication()->input->getInt('Itemid', 0));

        $total = $model->total;
        $this->assign('total', $total);
 
        // Create the pagination object
        $pageNav = $model->pagination;
        $this->assign('pageNav', $pageNav);

        // Form state variables
        $search = $model->getState('search');
        $search = (empty($search))? "" : $search;
        $this->assignRef('search', $search);
        $orderby = $model->getState('orderby', 'startdate');
        $this->assign('orderby', $orderby);
        $orderbydir = $model->getState('orderbydir', 'ASC');
        $this->assign('orderbydir', $orderbydir);
 
        $this->buildHTMLElements();

        parent::display($tpl);
    }

    /**
     * Build HTML elements from saved data
     *
     * @return void
     */
    private function buildHTMLElements()
    {
        $titleHead = $this->getColumnHead('title');
        $this->assignRef('titleHead', $titleHead);
        $authorHead = $this->getColumnHead('author');
        $this->assignRef('authorHead', $authorHead);
        $categoryHead = $this->getColumnHead('category', 'eventCategory');
        $this->assignRef('categoryHead', $categoryHead);
        $dateHead = $this->getColumnHead('date');
        $this->assignRef('dateHead', $dateHead);

        $resourceHead = "<span class='thm_organizer_el_th'>" . JText::_('COM_THM_ORGANIZER_EL_RESOURCE') . "</span>";
        $this->assignRef('resourceHead', $resourceHead);
    }

    /**
     * Gets the HTML for the table column headers
     *
     * @param   string  $columnName  the name of the column
     * @param   string  $queryName   the name used in the query for the column
     *
     * @return  string  HTML span with the name and sort function for the column
     */
    private function getColumnHead($columnName, $queryName = '')
    {
        $ascImage = JHtml::image('media/system/images/sort_asc.png', JText::_('COM_THM_ORGANIZER_EL_ASC_DESCRIPTION'), null, null, null);
        $descImage = JHtml::image('media/system/images/sort_desc.png', JText::_('COM_THM_ORGANIZER_EL_DESC_DESCRIPTION'), null, null, null);

        if (empty($queryName))
        {
            $queryName = $columnName;
        }

        $textConstant = 'COM_THM_ORGANIZER_EL_' . strtoupper($columnName);
        $text = JText::_($textConstant);
        if ($this->orderby == $queryName)
        {
            $text .= $this->orderbydir == 'ASC'? $ascImage : $descImage;
        }

        $attribs = array();
        $attribs['class'] = "thm_organizer_el_sortLink hasTip";
        $attribs['title'] = JText::_('COM_THM_ORGANIZER_EL_SORT');
        $link = "javascript:reSort(";
        if ($this->orderby == $queryName AND $this->orderbydir == 'ASC')
        {
            $attribs['title'] .= "::" . JText::_('COM_THM_ORGANIZER_EL_DESC_DESCRIPTION');
            $link .= "'$queryName', 'DESC')";
        }
        else
        {
            $attribs['title'] .= "::" . JText::_('COM_THM_ORGANIZER_EL_ASC_DESCRIPTION');
            $link .= "'$queryName', 'ASC')";
        }

        $columnHead = "<span class='thm_organizer_el_th'>";
        $columnHead .= JHtml::_('link', $link, $text, $attribs);
        $columnHead .= "</span>";
        return $columnHead;
    }

    /**
     * Method to build the category selection
     *
     * @param   object  $authCategories  the categories authorized to be used
     * @param   object  $selected        the selected category
     *
     * @return void
     */
    private function makeCategorySelect($authCategories, $selected)
    {
        $noCategories = array(1 => array('id' => '-1', 'title' => JText::_('COM_THM_ORGANIZER_EL_ALL_CATEGORIES')));
        $categories = array_merge($noCategories, $authCategories);
        $categorySelect = JHtml::_('select.genericlist', $categories, 'categoryID',
                 'id="categoryID" class="inputbox" size="1"', 'id', 'title', $selected
                );
        $this->assignRef('categorySelect', $categorySelect);
    }
}
