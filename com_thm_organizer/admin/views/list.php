<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use \THM_OrganizerHelperHTML as HTML;

require_once JPATH_COMPONENT . '/layouts/list.php';
require_once JPATH_COMPONENT . '/layouts/list_modal.php';

/**
 * Class loads a filtered set of resources into the display context. Specific resource determined by extending class.
 */
abstract class THM_OrganizerViewList extends \Joomla\CMS\MVC\View\HtmlView
{
    public $state = null;

    public $items = null;

    public $pagination = null;

    public $filterForm = null;

    public $headers = null;

    public $hiddenFields = null;

    /**
     * Method to create a list output
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Don't know which of these filters does what if anything active had no effect on the active highlighting
        $this->filterForm = $this->get('FilterForm');

        // Items common across list views
        $this->headers      = $this->get('Headers');
        $this->hiddenFields = $this->get('HiddenFields');
        $this->items        = $this->get('Items');

        $this->addSubmenu();

        // Allows for view specific toolbar handling
        $this->addToolBar();
        parent::display($tpl);
    }

    /**
     * Adds the component menu to the view.
     *
     * @return void
     */
    public function addSubmenu()
    {
        $viewName = $this->get('name');

        // No submenu creation while editing a resource
        if (strpos($viewName, 'edit')) {
            return;
        }

        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER'),
            'index.php?option=com_thm_organizer&amp;view=thm_organizer',
            $viewName == 'thm_organizer'
        );

        if (THM_OrganizerHelperAccess::allowSchedulingAccess()) {
            $spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_SCHEDULING') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $scheduling = [];

            $scheduling[JText::_('COM_THM_ORGANIZER_PLAN_POOL_MANAGER_TITLE')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=plan_pool_manager',
                'active' => $viewName == 'plan_pool_manager'
            ];
            $scheduling[JText::_('COM_THM_ORGANIZER_PLAN_PROGRAM_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=plan_program_manager',
                'active' => $viewName == 'plan_program_manager'
            ];
            $scheduling[JText::_('COM_THM_ORGANIZER_SCHEDULE_MANAGER_TITLE')]     = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=schedule_manager',
                'active' => $viewName == 'schedule_manager'
            ];
            ksort($scheduling);

            // Uploading a schedule should always be the first menu item and will never be the active submenu item.
            $prepend    = [
                JText::_('COM_THM_ORGANIZER_SCHEDULE_UPLOAD') . ' <span class="icon-upload"></span>' => [
                    'url'    => 'index.php?option=com_thm_organizer&amp;view=schedule_edit',
                    'active' => false
                ]
            ];
            $scheduling = $prepend + $scheduling;
            foreach ($scheduling as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (THM_OrganizerHelperAccess::allowDocumentAccess()) {
            $spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_MANAGEMENT_AND_DOCUMENTATION') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $documentation = [];

            if (THM_OrganizerHelperAccess::isAdmin()) {
                $documentation[JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_TITLE')] = [
                    'url'    => 'index.php?option=com_thm_organizer&amp;view=department_manager',
                    'active' => $viewName == 'department_manager'
                ];
            }
            $documentation[JText::_('COM_THM_ORGANIZER_POOL_MANAGER_TITLE')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=pool_manager',
                'active' => $viewName == 'pool_manager'
            ];
            $documentation[JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=program_manager',
                'active' => $viewName == 'program_manager'
            ];
            $documentation[JText::_('COM_THM_ORGANIZER_SUBJECT_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=subject_manager',
                'active' => $viewName == 'subject_manager'
            ];
            ksort($documentation);
            foreach ($documentation as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (THM_OrganizerHelperAccess::allowHRAccess()) {
            $spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_HUMAN_RESOURCES') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);
            JHtmlSidebar::addEntry(
                JText::_('COM_THM_ORGANIZER_TEACHER_MANAGER_TITLE'),
                'index.php?option=com_thm_organizer&amp;view=teacher_manager',
                $viewName == 'teacher_manager'
            );
        }

        if (THM_OrganizerHelperAccess::allowFMAccess()) {
            $spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_FACILITY_MANAGEMENT') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $fmEntries = [];

            $fmEntries[JText::_('COM_THM_ORGANIZER_BUILDING_MANAGER_TITLE')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=building_manager',
                'active' => $viewName == 'building_manager'
            ];
            $fmEntries[JText::_('COM_THM_ORGANIZER_CAMPUS_MANAGER_TITLE')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=campus_manager',
                'active' => $viewName == 'campus_manager'
            ];
            $fmEntries[JText::_('COM_THM_ORGANIZER_MONITOR_MANAGER_TITLE')]   = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=monitor_manager',
                'active' => $viewName == 'monitor_manager'
            ];
            $fmEntries[JText::_('COM_THM_ORGANIZER_ROOM_MANAGER_TITLE')]      = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=room_manager',
                'active' => $viewName == 'room_manager'
            ];
            $fmEntries[JText::_('COM_THM_ORGANIZER_ROOM_TYPE_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=room_type_manager',
                'active' => $viewName == 'room_type_manager'
            ];
            ksort($fmEntries);
            foreach ($fmEntries as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (THM_OrganizerHelperAccess::isAdmin()) {
            $spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_ADMINISTRATION') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $adminEntries = [];

            $adminEntries[JText::_('COM_THM_ORGANIZER_COLOR_MANAGER_TITLE')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=color_manager',
                'active' => $viewName == 'color_manager'
            ];
            $adminEntries[JText::_('COM_THM_ORGANIZER_DEGREE_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=degree_manager',
                'active' => $viewName == 'degree_manager'
            ];
            $adminEntries[JText::_('COM_THM_ORGANIZER_FIELD_MANAGER_TITLE')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=field_manager',
                'active' => $viewName == 'field_manager'
            ];
            $adminEntries[JText::_('COM_THM_ORGANIZER_GRID_MANAGER_TITLE')]   = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=grid_manager',
                'active' => $viewName == 'grid_manager'
            ];
            $adminEntries[JText::_('COM_THM_ORGANIZER_METHOD_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=method_manager',
                'active' => $viewName == 'method_manager'
            ];
            ksort($adminEntries);
            foreach ($adminEntries as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        $this->sidebar = JHtmlSidebar::render();
    }

    /**
     * Concrete classes are supposed to use this method to add a toolbar.
     *
     * @return void  sets context variables
     */
    abstract protected function addToolBar();

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/fonts/iconfont.css');
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/backend.css');

        HTML::_('bootstrap.tooltip');
        HTML::_('behavior.multiselect');
        HTML::_('formbehavior.chosen', 'select');
        HTML::_('searchtools.form', '#adminForm', []);
    }
}
