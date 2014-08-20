<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewcategory_manager
 * @description view output file for event category lists
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('joomla.form.form');
jimport('listview.listview');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';
require_once JPATH_COMPONENT . '/assets/helpers/thm_dropDownListHelper.php';
require_once JPATH_COMPONENT . '/assets/helpers/thm_tableBodyHelper.php';
require_once JPATH_COMPONENT . '/assets/helpers/thm_tableHeaderHelper.php';
require_once JPATH_COMPONENT . '/assets/helpers/thm_inputHelper.php';
require_once JPATH_LIBRARIES . '/listview/classes/bodyItem.php';
require_once JPATH_LIBRARIES . '/listview/thm_list.php';
/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerViewCategory_Manager extends JViewLegacy
{
    /**
     * loads persistent information into the view context
     *
     * @param   string  $tpl  the name of the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $isAdmin = JFactory::getUser()->authorise('core.admin');
        if (!$isAdmin)
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }


        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');

        $model = $this->getModel();
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->items2 = $this->get('Items2');
        $this->pagination = $this->get('Pagination');
        $this->contentCategories = $model->contentCategories;
        $this->addToolBar();


        $edit_url = 'index.php?option=com_thm_organizer&view=category_edit&categoryID';
        $this->page_type = 'category_manager';
        $this->pageUrl = "index.php?option=com_thm_organizer";

        // Head
        /*$this->headers[] = array('name' => JText::_('COM_THM_ORGANIZER_NAME'),
            'field' => 'ectitle', 'sortable' => true);
        $this->headers[] = array('name' => JText::_('COM_THM_ORGANIZER_CAT_GLOBAL'),
            'field' => 'global', 'sortable' => true);
        $this->headers[] = array('name' => JText::_('COM_THM_ORGANIZER_CAT_RESERVES'),
            'field' => 'reserves', 'sortable' => true);
        $this->headers[] = array('name' => JText::_('COM_THM_ORGANIZER_CAT_CONTENT_CATEGORY'),
            'field' => 'cctitle', 'sortable' => true);*/
        $this->headers = $this->get('Headers');

        // Body
        $this->fields = array('ectitle', 'global', 'reserves', 'cctitle');
        $this->bodyItems = array();

        foreach ($this->items as $item)
        {
            $bodyItem = new THM_BodyItem($item->id);
            foreach ($this->fields as $f)
            {

                $htmlClass = null;
                if ($f == 'global' || $f == 'reserves')
                {
                    if ($item->$f)
                    {
                        $span = 'class="state publish"';
                        $name = "";
                        $htmlClass = "jgrid";
                    }
                    else
                    {
                        $span = 'class="state expired"';
                        $name = "";
                        $htmlClass = "jgrid";
                    }
                }
                else
                {
                    $name = $item->$f;
                }
                $attribute = new THM_BodyItemAttribute($name, $edit_url . '=' . $item->id);
                if ($htmlClass)
                {
                    $attribute->setHtmlClass($htmlClass);
                    $attribute->setSpan($span);
                }
                $bodyItem->addAttribute($attribute);
            }
            $this->bodyItems[] = $bodyItem;
        }

        // Filters
        $this->filters = array();

        $filter = array('name' => 'filter_global', 'options' =>
            "<option value='*'>" . JText::_('COM_THM_ORGANIZER_CAT_SEARCH_GLOBAL') . "</option>
                <option value='*'>" . JText::_('COM_THM_ORGANIZER_CAT_ALL_GLOBAL') . "</option>
                <option value='0'>" . JText::_('COM_THM_ORGANIZER_CAT_NOT_GLOBAL') . "</option>
                <option value='1'>" . JText::_('COM_THM_ORGANIZER_CAT_GLOBAL') . "</option>");
        $this->filters[] = $filter;

        $filter = array('name' => 'filter_reserves', 'options' =>
            "<option value='*'>" . JText::_('COM_THM_ORGANIZER_CAT_SEARCH_RESERVES') . "</option>
                <option value='*'>" . JText::_('COM_THM_ORGANIZER_CAT_ALL_RESERVES') . "</option>
                <option value='0'>" . JText::_('COM_THM_ORGANIZER_CAT_NOT_RESERVES') . "</option>
                <option value='1'>" . JText::_('COM_THM_ORGANIZER_CAT_RESERVES') . "</option>");
        $this->filters[] = $filter;

        $a = "<option value='*'>" . JText::_('COM_THM_ORGANIZER_CAT_SEARCH_CCATS') . "</option>
                <option value='*'>" . JText::_('COM_THM_ORGANIZER_CAT_ALL_CCATS') . "</option>";
        $a .= (JHtml::_('select.options', $this->contentCategories, 'id', 'title', $this->state->get('filter.content_cat')));

        $filter = array('name' => 'filter_content_cat', 'options' => $a);
        $this->filters[] = $filter;


        parent::display($tpl);
    }

    /**
     * generates joomla toolbar elements
     *
     * @return void
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_CAT_TITLE');
        JToolbarHelper::title($title, 'organizer_categories');
        JToolbarHelper::addNew('category.add');
        JToolbarHelper::editList('category.edit');
        JToolbarHelper::deleteList(JText::_('COM_THM_ORGANIZER_CAT_DELETE_CONFIRM'), 'category.delete');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_thm_organizer');
    }
}
