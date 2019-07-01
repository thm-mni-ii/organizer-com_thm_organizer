<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Organizer\Views\HTML;

use JHtmlSidebar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
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
    public $disclaimer = '';

    public $languageLinks = '';

    public $submenu = '';

    /**
     * Adds a legal disclaimer to the view.
     *
     * @return void modifies the class property disclaimer
     */
    protected function addDisclaimer()
    {
        if (OrganizerHelper::getApplication()->isClient('administrator')) {
            return;
        }

        $documentationViews = ['Curriculum', 'Subject_Details', 'Subjects'];
        if (!in_array(OrganizerHelper::getClass($this), $documentationViews)) {
            return;
        }

        $lsfLink = HTML::link(
            'https://studien-sb-service.th-mittelhessen.de/docu/online.html',
            Languages::_('THM_ORGANIZER_DISCLAIMER_LSF_TITLE')
        );
        $ambLink = HTML::link(
            'http://www.thm.de/amb/pruefungsordnungen',
            Languages::_('THM_ORGANIZER_DISCLAIMER_AMB_TITLE')
        );
        $poLink  = HTML::link(
            'http://www.thm.de/site/studium/sie-studieren/pruefungsordnung.html',
            Languages::_('THM_ORGANIZER_DISCLAIMER_PO_TITLE')
        );

        $disclaimer = '<div class="legal-disclaimer">';
        $disclaimer .= '<h4>' . Languages::_('THM_ORGANIZER_DISCLAIMER_HEADER') . '</h4>';
        $disclaimer .= '<ul>';
        $disclaimer .= '<li>' . sprintf(Languages::_('THM_ORGANIZER_DISCLAIMER_LSF_TEXT'), $lsfLink) . '</li>';
        $disclaimer .= '<li>' . sprintf(Languages::_('THM_ORGANIZER_DISCLAIMER_AMB_TEXT'), $ambLink) . '</li>';
        $disclaimer .= '<li>' . sprintf(Languages::_('THM_ORGANIZER_DISCLAIMER_PO_TEXT'), $poLink) . '</li>';
        $disclaimer .= '</ul>';
        $disclaimer .= '</div>';

        $this->disclaimer = $disclaimer;
    }

    /**
     * Adds a language links to the view.
     *
     * @param array $params the parameters used to call the current view.
     *
     * @return void modifies the class property languageLinks
     */
    protected function addLanguageLinks($params)
    {
        if (OrganizerHelper::getApplication()->isClient('administrator')) {
            return;
        }

        $current            = Languages::getShortTag();
        $supportedLanguages = [
            'de' => Languages::_('THM_ORGANIZER_GERMAN'),
            'en' => Languages::_('THM_ORGANIZER_ENGLISH')
        ];
        unset($supportedLanguages[$current]);

        $links            = '<div class="tool-wrapper language-links">';
        $params['option'] = 'com_thm_organizer';
        $menuID           = OrganizerHelper::getInput()->getInt('Itemid');
        if (!empty($menuID)) {
            $params['Itemid'] = $menuID;
        }

        foreach ($supportedLanguages as $languageTag => $text) {
            $params['languageTag'] = $languageTag;

            $href  = Uri::buildQuery($params);
            $links .= '<a href="index.php?' . $href . '"><span class="icon-world"></span>' . $text . '</a>';
        }

        $links               .= '</div>';
        $this->languageLinks = $links;
    }

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

            $scheduling[Languages::_('THM_ORGANIZER_GROUPS')]     = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=groups',
                'active' => $viewName == 'groups'
            ];
            $scheduling[Languages::_('THM_ORGANIZER_CATEGORIES')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=categories',
                'active' => $viewName == 'categories'
            ];
            $scheduling[Languages::_('THM_ORGANIZER_SCHEDULES')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=schedules',
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
            $spanText = '<span class="menu-spacer">' . Languages::_('THM_ORGANIZER_DOCUMENTATION') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $documentation = [];

            $documentation[Languages::_('THM_ORGANIZER_POOLS')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=pools',
                'active' => $viewName == 'pools'
            ];
            $documentation[Languages::_('THM_ORGANIZER_PROGRAMS')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=programs',
                'active' => $viewName == 'programs'
            ];
            $documentation[Languages::_('THM_ORGANIZER_SUBJECTS')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=subjects',
                'active' => $viewName == 'subjects'
            ];
            ksort($documentation);
            foreach ($documentation as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (Access::allowCourseAccess()) {
            $spanText = '<span class="menu-spacer">' . Languages::_('THM_ORGANIZER_COURSES') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $courseItems                                             = [];
            $courseItems[Languages::_('THM_ORGANIZER_COURSES')]      = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=courses',
                'active' => $viewName == 'courses'
            ];
            $courseItems[Languages::_('THM_ORGANIZER_EVENTS')]      = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=lessons',
                'active' => $viewName == 'lessons'
            ];
            $courseItems[Languages::_('THM_ORGANIZER_PARTICIPANTS')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=participants',
                'active' => $viewName == 'participants'
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
                'index.php?option=com_thm_organizer&amp;view=teachers',
                $viewName == 'teachers'
            );
        }

        if (Access::allowFMAccess()) {
            $spanText = '<span class="menu-spacer">' . Languages::_('THM_ORGANIZER_FACILITY_MANAGEMENT') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $fmEntries = [];

            $fmEntries[Languages::_('THM_ORGANIZER_BUILDINGS')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=buildings',
                'active' => $viewName == 'buildings'
            ];
            $fmEntries[Languages::_('THM_ORGANIZER_CAMPUSES')]   = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=campuses',
                'active' => $viewName == 'campuses'
            ];
            $fmEntries[Languages::_('THM_ORGANIZER_MONITORS')]   = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=monitors',
                'active' => $viewName == 'monitors'
            ];
            $fmEntries[Languages::_('THM_ORGANIZER_ROOMS')]      = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=rooms',
                'active' => $viewName == 'rooms'
            ];
            $fmEntries[Languages::_('THM_ORGANIZER_ROOM_TYPES')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=room_types',
                'active' => $viewName == 'room_types'
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

            $adminEntries[Languages::_('THM_ORGANIZER_DEPARTMENTS')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=departments',
                'active' => $viewName == 'departments'
            ];
            $adminEntries[Languages::_('THM_ORGANIZER_COLORS')]      = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=colors',
                'active' => $viewName == 'colors'
            ];
            $adminEntries[Languages::_('THM_ORGANIZER_DEGREES')]     = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=degrees',
                'active' => $viewName == 'degrees'
            ];
            $adminEntries[Languages::_('THM_ORGANIZER_FIELDS')]      = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=fields',
                'active' => $viewName == 'fields'
            ];
            $adminEntries[Languages::_('THM_ORGANIZER_GRIDS')]       = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=grids',
                'active' => $viewName == 'grids'
            ];
            $adminEntries[Languages::_('THM_ORGANIZER_METHODS')]     = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=methods',
                'active' => $viewName == 'methods'
            ];
            ksort($adminEntries);
            foreach ($adminEntries as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        $this->submenu = JHtmlSidebar::render();
    }
}
