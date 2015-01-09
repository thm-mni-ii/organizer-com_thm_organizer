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

/**
 * Builds a list of events
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewEvent_Manager extends JViewLegacy
{
    public $state = null;

    public $items = null;

    public $pagination = null;

    public $filterForm = null;

    public $activeFilters = null;

    public $headers = null;

    /**
     * Method to create a list output
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        $this->headers = $this->get('Headers');
        $this->items = $this->get('Items');

        // Allows for view specific toolbar handling
        $this->addToolBar();
        parent::display();
    }

    /**
     * Adds any external scripts or stylesheets
     */
    private function modifyDocument()
    {
        $document = Jfactory::getDocument();
        $document -> addStyleSheet(JPATH_SITE . "/libraries/thm_core/fonts/iconfont.css");
        $document -> addStyleSheet(JPATH_SITE . "/media/com_thm_organizer/css/backend.css");

        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.multiselect');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('searchtools.form', '#adminForm', array());
    }

    /**
     * Creates HTML elements from saved data
     *
     * @return void
     */
    protected function addToolbar()
    {
        $this->buttons = array();

        $params = $this->getModel()->params;
        $canCreate = $params->get('access-create', false);
        $canEdit = $params->get('access-edit', false);
        $canDelete = $params->get('access-delete', false);
        if(!$canCreate AND !$canEdit AND !$canDelete)
        {
            return;
        }

        if($canCreate)
        {
            $createButton = '<button type="button" class="btn" onclick="Joomla.submitbutton(\'event.add\')">';
            $createButton .= '<span class="icon-new"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_NEW') . '</button>';
            $this->buttons[] = $createButton;
        }

        if($canEdit)
        {
            $editButton = '<button type="button" class="btn" onclick="Joomla.submitbutton(\'event.edit\')">';
            $editButton .= '<span class="icon-edit"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_EDIT') . '</button>';
            $this->buttons[] = $editButton;
        }

        if($canDelete)
        {
            $deleteButton = '<button type="button" class="btn" onclick="Joomla.submitbutton(\'event.delete\')">';
            $deleteButton .= '<span class="icon-delete"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_DELETE') . '</button>';
            $this->buttons[] = $deleteButton;
        }
    }
}
