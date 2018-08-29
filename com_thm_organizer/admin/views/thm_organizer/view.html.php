<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/form.php';

/**
 * Class modifies the document for the output of a menu like list of resource management views.
 */
class THM_OrganizerViewTHM_Organizer extends THM_OrganizerViewForm
{
    public $menuItems;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->setMenuItems();
        parent::display($tpl);
    }

    /**
     * creates a joomla administratoristrative tool bar
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_MAIN_VIEW_TITLE'), 'organizer');

        if ($this->getModel()->actions->{'core.admin'}) {
            JToolbarHelper::divider();
            JToolbarHelper::preferences('com_thm_organizer');
        }
    }

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.framework', true);
        JHtml::_('behavior.formvalidation');
        JHtml::_('formbehavior.chosen', 'select');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . "/media/com_thm_organizer/fonts/iconfont.css");
        $document->addStyleSheet(JUri::root() . "/media/com_thm_organizer/css/backend.css");
    }

    /**
     * Sets the menu items to be output in the view
     * @return void
     */
    private function setMenuItems()
    {
        $actions = $this->getModel()->actions;

        $this->menuItems = [
            'administration' => [],
            'documentation' => [],
            'facilityManagement' => [],
            'humanResources' => [],
            'scheduling' => []
        ];

        if ($actions->{'core.admin'} or $actions->{'organizer.menu.schedule'}) {
            $scheduleItems = [];

            $scheduleItems[JText::_('COM_THM_ORGANIZER_PLAN_POOL_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=plan_pool_manager';
            $scheduleItems[JText::_('COM_THM_ORGANIZER_PLAN_PROGRAM_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=plan_program_manager';
            $scheduleItems[JText::_('COM_THM_ORGANIZER_SCHEDULE_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=schedule_manager';
            ksort($scheduleItems);

            // Uploading a schedule should always be the first menu item.
            $prepend = [JText::_('COM_THM_ORGANIZER_SCHEDULE_UPLOAD') . ' <span class="icon-upload"></span>'
                        => 'index.php?option=com_thm_organizer&view=schedule_edit'];
            $scheduleItems = $prepend + $scheduleItems;
            $this->menuItems['scheduling'] = $scheduleItems;
        }

        if ($actions->{'core.admin'} or $actions->{'organizer.menu.manage'}) {
            $docItems = [];

            if ($actions->{'organizer.menu.department'}) {
                $docItems[JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_TITLE')]
                    = 'index.php?option=com_thm_organizer&amp;view=department_manager';
            }
            $docItems[JText::_('COM_THM_ORGANIZER_POOL_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=pool_manager';
            $docItems[JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=program_manager';
            $docItems[JText::_('COM_THM_ORGANIZER_SUBJECT_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=subject_manager';
            ksort($docItems);
            $this->menuItems['documentation'] = $docItems;
        }

        if ($actions->{'core.admin'} or $actions->{'organizer.hr'}) {
            $hrItems = [];
            $hrItems[JText::_('COM_THM_ORGANIZER_TEACHER_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=teacher_manager';
            ksort($hrItems);
            $this->menuItems['humanResources'] = $hrItems;
        }

        if ($actions->{'core.admin'} or $actions->{'organizer.fm'}) {
            $fmItems = [];
            $fmItems[JText::_('COM_THM_ORGANIZER_BUILDING_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=building_manager';
            $fmItems[JText::_('COM_THM_ORGANIZER_CAMPUS_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=campus_manager';
            $fmItems[JText::_('COM_THM_ORGANIZER_MONITOR_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=monitor_manager';
            $fmItems[JText::_('COM_THM_ORGANIZER_ROOM_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=room_manager';
            $fmItems[JText::_('COM_THM_ORGANIZER_ROOM_TYPE_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=room_type_manager';
            ksort($fmItems);
            $this->menuItems['facilityManagement'] = $fmItems;
        }

        if ($actions->{'core.admin'}) {
            $adminItems = [];
            $adminItems[JText::_('COM_THM_ORGANIZER_COLOR_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=color_manager';
            $adminItems[JText::_('COM_THM_ORGANIZER_DEGREE_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=degree_manager';
            $adminItems[JText::_('COM_THM_ORGANIZER_FIELD_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=field_manager';
            $adminItems[JText::_('COM_THM_ORGANIZER_GRID_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=grid_manager';
            $adminItems[JText::_('COM_THM_ORGANIZER_METHOD_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=method_manager';
            ksort($adminItems);
            $this->menuItems['administration'] = $adminItems;
        }
    }
}
