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

use THM_OrganizerHelperHTML as HTML;
use Joomla\CMS\Uri\Uri;

/**
 * Class modifies the document for the output of a menu like list of resource management views.
 */
class THM_OrganizerViewTHM_Organizer extends \Joomla\CMS\MVC\View\HtmlView
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
        $this->modifyDocument();
        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * Creates a toolbar
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::title(Languages::_('THM_ORGANIZER_MAIN_VIEW_TITLE'), 'organizer');

        if (THM_OrganizerHelperAccess::isAdmin()) {
            \JToolbarHelper::preferences('com_thm_organizer');
        }
    }

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        HTML::_('bootstrap.tooltip');
        HTML::_('behavior.framework', true);
        HTML::_('behavior.formvalidation');
        HTML::_('formbehavior.chosen', 'select');

        $document = \JFactory::getDocument();
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/backend.css');
    }

    /**
     * Sets the menu items to be output in the view
     * @return void
     */
    private function setMenuItems()
    {
        $this->menuItems = [
            'administration'     => [],
            'documentation'      => [],
            'facilityManagement' => [],
            'humanResources'     => [],
            'scheduling'         => []
        ];

        if (THM_OrganizerHelperAccess::allowSchedulingAccess()) {
            $scheduleItems = [];

            $scheduleItems[Languages::_('THM_ORGANIZER_PLAN_POOL_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=plan_pool_manager';
            $scheduleItems[Languages::_('THM_ORGANIZER_PLAN_PROGRAM_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=plan_program_manager';
            $scheduleItems[Languages::_('THM_ORGANIZER_SCHEDULE_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=schedule_manager';
            ksort($scheduleItems);

            $scheduleHeaderText = '<h3>' . Languages::_('THM_ORGANIZER_SCHEDULING') . '</h3>';
            $scheduleHeader     = [$scheduleHeaderText => ''];
            $scheduleUploadText = Languages::_('THM_ORGANIZER_SCHEDULE_UPLOAD') . ' <span class="icon-upload"></span>';
            $scheduleUpload     = [$scheduleUploadText => 'index.php?option=com_thm_organizer&view=schedule_edit'];
            $scheduleItems      = $scheduleHeader + $scheduleUpload + $scheduleItems;

            $this->menuItems['scheduling'] = $scheduleItems;
        } else {
            $this->menuItems['scheduling'] = [];
        }

        if (THM_OrganizerHelperAccess::allowDocumentAccess()) {
            $docItems = [];

            if (THM_OrganizerHelperAccess::isAdmin()) {
                $docItems[Languages::_('THM_ORGANIZER_DEPARTMENT_MANAGER_TITLE')]
                    = 'index.php?option=com_thm_organizer&amp;view=department_manager';
            }
            $docItems[Languages::_('THM_ORGANIZER_POOL_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=pool_manager';
            $docItems[Languages::_('THM_ORGANIZER_PROGRAM_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=program_manager';
            $docItems[Languages::_('THM_ORGANIZER_SUBJECT_MANAGER_TITLE')]
                = 'index.php?option=com_thm_organizer&amp;view=subject_manager';
            ksort($docItems);

            $docHeaderText = '<h3>' . Languages::_('THM_ORGANIZER_MANAGEMENT_AND_DOCUMENTATION') . '</h3>';
            $docHeader     = [$docHeaderText => ''];

            $this->menuItems['documentation'] = $docHeader + $docItems;
        } else {
            $this->menuItems['documentation'] = [];
        }

        if (THM_OrganizerHelperAccess::allowHRAccess()) {
            $hrItems = [];
            $hrItems[Languages::_('THM_ORGANIZER_TEACHER_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=teacher_manager';
            ksort($hrItems);

            $hrHeaderText = '<h3>' . Languages::_('THM_ORGANIZER_HUMAN_RESOURCES') . '</h3>';
            $hrHeader     = [$hrHeaderText => ''];

            $this->menuItems['humanResources'] = $hrHeader + $hrItems;
        } else {
            $this->menuItems['humanResources'] = [];
        }

        if (THM_OrganizerHelperAccess::allowFMAccess()) {
            $fmItems = [];
            $fmItems[Languages::_('THM_ORGANIZER_BUILDING_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=building_manager';
            $fmItems[Languages::_('THM_ORGANIZER_CAMPUS_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=campus_manager';
            $fmItems[Languages::_('THM_ORGANIZER_MONITOR_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=monitor_manager';
            $fmItems[Languages::_('THM_ORGANIZER_ROOM_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=room_manager';
            $fmItems[Languages::_('THM_ORGANIZER_ROOM_TYPE_MANAGER_TITLE')]
                     = 'index.php?option=com_thm_organizer&amp;view=room_type_manager';
            ksort($fmItems);

            $fmHeaderText = '<h3>' . Languages::_('THM_ORGANIZER_FACILITY_MANAGEMENT') . '</h3>';
            $fmHeader     = [$fmHeaderText => ''];

            $this->menuItems['facilityManagement'] = $fmHeader + $fmItems;
        } else {
            $this->menuItems['facilityManagement'] = [];
        }

        if (THM_OrganizerHelperAccess::isAdmin()) {
            $adminItems = [];
            $adminItems[Languages::_('THM_ORGANIZER_COLOR_MANAGER_TITLE')]
                        = 'index.php?option=com_thm_organizer&amp;view=color_manager';
            $adminItems[Languages::_('THM_ORGANIZER_DEGREE_MANAGER_TITLE')]
                        = 'index.php?option=com_thm_organizer&amp;view=degree_manager';
            $adminItems[Languages::_('THM_ORGANIZER_FIELD_MANAGER_TITLE')]
                        = 'index.php?option=com_thm_organizer&amp;view=field_manager';
            $adminItems[Languages::_('THM_ORGANIZER_GRID_MANAGER_TITLE')]
                        = 'index.php?option=com_thm_organizer&amp;view=grid_manager';
            $adminItems[Languages::_('THM_ORGANIZER_METHOD_MANAGER_TITLE')]
                        = 'index.php?option=com_thm_organizer&amp;view=method_manager';
            ksort($adminItems);

            $adminHeaderText = '<h3>' . Languages::_('THM_ORGANIZER_ADMINISTRATION') . '</h3>';
            $adminHeader     = [$adminHeaderText => ''];

            $this->menuItems['administration'] = $adminHeader + $adminItems;
        } else {
            $this->menuItems['administration'] = [];
        }
    }
}
