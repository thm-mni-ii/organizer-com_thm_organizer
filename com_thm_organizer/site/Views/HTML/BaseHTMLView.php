<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use JHtmlSidebar;
use Organizer\Helpers\Access;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Views\BaseView;

/**
 * Base class for a Joomla View
 *
 * Class holding methods for displaying presentation data.
 */
abstract class BaseHTMLView extends BaseView
{
    public $submenu = null;

    /**
     * Adds the component menu to the view.
     *
     * @return void
     */
    protected function addMenu()
    {
        if (OrganizerHelper::getApplication()->isClient('site')) {
            return;
        }

        $viewName = strtolower($this->get('name'));

        JHtmlSidebar::addEntry(
            Languages::_('THM_ORGANIZER'),
            'index.php?option=com_thm_organizer&amp;view=organizer',
            $viewName == 'organizer'
        );

        if (Access::allowSchedulingAccess()) {
            $spanText = '<span class="menu-spacer">' . Languages::_('THM_ORGANIZER_SCHEDULING') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $scheduling = [];

            $scheduling[Languages::_('THM_ORGANIZER_PLAN_POOLS')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=plan_pool_manager',
                'active' => $viewName == 'plan_pool_manager'
            ];
            $scheduling[Languages::_('THM_ORGANIZER_PLAN_PROGRAMS')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=plan_program_manager',
                'active' => $viewName == 'plan_programs'
            ];
            $scheduling[Languages::_('THM_ORGANIZER_SCHEDULES')]     = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=schedule_manager',
                'active' => $viewName == 'schedules'
            ];
            ksort($scheduling);

            // Uploading a schedule should always be the first menu item and will never be the active submenu item.
            $prepend    = [
                Languages::_('THM_ORGANIZER_SCHEDULE_UPLOAD') . ' <span class="icon-upload"></span>' => [
                    'url'    => 'index.php?option=com_thm_organizer&amp;view=schedule_edit',
                    'active' => false
                ]
            ];
            $scheduling = $prepend + $scheduling;
            foreach ($scheduling as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (Access::allowDocumentAccess()) {
            $spanText = '<span class="menu-spacer">' . Languages::_('THM_ORGANIZER_MANAGEMENT_AND_DOCUMENTATION') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $documentation = [];

            if (Access::isAdmin()) {
                $documentation[Languages::_('THM_ORGANIZER_DEPARTMENTS')] = [
                    'url'    => 'index.php?option=com_thm_organizer&amp;view=department_manager',
                    'active' => $viewName == 'department_manager'
                ];
            }
            $documentation[Languages::_('THM_ORGANIZER_POOLS')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=pool_manager',
                'active' => $viewName == 'pool_manager'
            ];
            $documentation[Languages::_('THM_ORGANIZER_PROGRAMS')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=program_manager',
                'active' => $viewName == 'program_manager'
            ];
            $documentation[Languages::_('THM_ORGANIZER_SUBJECTS')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=subject_manager',
                'active' => $viewName == 'subject_manager'
            ];
            ksort($documentation);
            foreach ($documentation as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (Access::allowCourseAccess()) {
            $spanText = '<span class="menu-spacer">' . Languages::_('THM_ORGANIZER_COURSES') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $courseItems = [];

            $courseItems[Languages::_('THM_ORGANIZER_PARTICIPANTS')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=participant_manager',
                'active' => $viewName == 'participant_manager'
            ];
            $courseItems[Languages::_('THM_ORGANIZER_COURSES')]      = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=course_manager',
                'active' => $viewName == 'course_manager'
            ];
            ksort($courseItems);

            foreach ($courseItems as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (Access::allowHRAccess()) {
            $spanText = '<span class="menu-spacer">' . Languages::_('THM_ORGANIZER_HUMAN_RESOURCES') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);
            JHtmlSidebar::addEntry(
                Languages::_('THM_ORGANIZER_TEACHERS'),
                'index.php?option=com_thm_organizer&amp;view=teacher_manager',
                $viewName == 'teacher_manager'
            );
        }

        if (Access::allowFMAccess()) {
            $spanText = '<span class="menu-spacer">' . Languages::_('THM_ORGANIZER_FACILITY_MANAGEMENT') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $fmEntries = [];

            $fmEntries[Languages::_('THM_ORGANIZER_BUILDINGS')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=building_manager',
                'active' => $viewName == 'building_manager'
            ];
            $fmEntries[Languages::_('THM_ORGANIZER_CAMPUSES')]   = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=campus_manager',
                'active' => $viewName == 'campus_manager'
            ];
            $fmEntries[Languages::_('THM_ORGANIZER_MONITORS')]   = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=monitor_manager',
                'active' => $viewName == 'monitor_manager'
            ];
            $fmEntries[Languages::_('THM_ORGANIZER_ROOMS')]      = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=room_manager',
                'active' => $viewName == 'room_manager'
            ];
            $fmEntries[Languages::_('THM_ORGANIZER_ROOM_TYPES')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=room_type_manager',
                'active' => $viewName == 'room_type_manager'
            ];
            ksort($fmEntries);
            foreach ($fmEntries as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (Access::isAdmin()) {
            $spanText = '<span class="menu-spacer">' . Languages::_('THM_ORGANIZER_ADMINISTRATION') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $adminEntries = [];

            $adminEntries[Languages::_('THM_ORGANIZER_COLORS')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=color_manager',
                'active' => $viewName == 'color_manager'
            ];
            $adminEntries[Languages::_('THM_ORGANIZER_DEGREES')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=degree_manager',
                'active' => $viewName == 'degree_manager'
            ];
            $adminEntries[Languages::_('THM_ORGANIZER_FIELDS')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=field_manager',
                'active' => $viewName == 'field_manager'
            ];
            $adminEntries[Languages::_('THM_ORGANIZER_GRIDS')]   = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=grid_manager',
                'active' => $viewName == 'grid_manager'
            ];
            $adminEntries[Languages::_('THM_ORGANIZER_METHODS')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=method_manager',
                'active' => $viewName == 'method_manager'
            ];
            ksort($adminEntries);
            foreach ($adminEntries as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        $this->submenu = JHtmlSidebar::render();
    }
}
