<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once 'access.php';
require_once 'date.php';
require_once 'html.php';
require_once 'languages.php';

use THM_OrganizerHelperLanguages as Languages;
use Joomla\CMS\Uri\Uri;

/**
 * Class provides generalized functions useful for several component files.
 */
class THM_OrganizerHelperComponent
{
    /**
     * Adds menu parameters to the object (id and route)
     *
     * @param object $object the object to add the parameters to, typically a view
     *
     * @return void modifies $object
     */
    public static function addMenuParameters(&$object)
    {
        $app    = self::getApplication();
        $menuID = $app->input->getInt('Itemid');

        if (!empty($menuID)) {
            $menuItem = $app->getMenu()->getItem($menuID);
            $menu     = ['id' => $menuID, 'route' => self::getRedirectBase()];

            $query = explode('?', $menuItem->link)[1];
            parse_str($query, $parameters);

            if (empty($parameters['option']) or $parameters['option'] != 'com_thm_organizer') {
                $menu['view'] = '';
            } elseif (!empty($parameters['view'])) {
                $menu['view'] = $parameters['view'];
            }

            $object->menu = $menu;
        }
    }

    /**
     * Generates a sidebar menu for administrative views.
     *
     * @param $viewName
     *
     * @return string
     */
    public static function adminSideBar($viewName)
    {
        \JHtmlSidebar::addEntry(
            \JText::_('THM_ORGANIZER'),
            'index.php?option=com_thm_organizer&amp;view=thm_organizer',
            $viewName == 'thm_organizer'
        );

        if (THM_OrganizerHelperAccess::allowSchedulingAccess()) {
            $spanText = '<span class="menu-spacer">' . \JText::_('THM_ORGANIZER_SCHEDULING') . '</span>';
            \JHtmlSidebar::addEntry($spanText, '', false);

            $scheduling = [];

            $scheduling[\JText::_('THM_ORGANIZER_PLAN_POOL_MANAGER_TITLE')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=plan_pool_manager',
                'active' => $viewName == 'plan_pool_manager'
            ];
            $scheduling[\JText::_('THM_ORGANIZER_PLAN_PROGRAM_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=plan_program_manager',
                'active' => $viewName == 'plan_program_manager'
            ];
            $scheduling[\JText::_('THM_ORGANIZER_SCHEDULE_MANAGER_TITLE')]     = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=schedule_manager',
                'active' => $viewName == 'schedule_manager'
            ];
            ksort($scheduling);

            // Uploading a schedule should always be the first menu item and will never be the active submenu item.
            $prepend    = [
                \JText::_('THM_ORGANIZER_SCHEDULE_UPLOAD') . ' <span class="icon-upload"></span>' => [
                    'url'    => 'index.php?option=com_thm_organizer&amp;view=schedule_edit',
                    'active' => false
                ]
            ];
            $scheduling = $prepend + $scheduling;
            foreach ($scheduling as $key => $value) {
                \JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (THM_OrganizerHelperAccess::allowDocumentAccess()) {
            $spanText = '<span class="menu-spacer">' . \JText::_('THM_ORGANIZER_MANAGEMENT_AND_DOCUMENTATION') . '</span>';
            \JHtmlSidebar::addEntry($spanText, '', false);

            $documentation = [];

            if (THM_OrganizerHelperAccess::isAdmin()) {
                $documentation[\JText::_('THM_ORGANIZER_DEPARTMENT_MANAGER_TITLE')] = [
                    'url'    => 'index.php?option=com_thm_organizer&amp;view=department_manager',
                    'active' => $viewName == 'department_manager'
                ];
            }
            $documentation[\JText::_('THM_ORGANIZER_POOL_MANAGER_TITLE')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=pool_manager',
                'active' => $viewName == 'pool_manager'
            ];
            $documentation[\JText::_('THM_ORGANIZER_PROGRAM_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=program_manager',
                'active' => $viewName == 'program_manager'
            ];
            $documentation[\JText::_('THM_ORGANIZER_SUBJECT_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=subject_manager',
                'active' => $viewName == 'subject_manager'
            ];
            ksort($documentation);
            foreach ($documentation as $key => $value) {
                \JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (THM_OrganizerHelperAccess::allowHRAccess()) {
            $spanText = '<span class="menu-spacer">' . \JText::_('THM_ORGANIZER_HUMAN_RESOURCES') . '</span>';
            \JHtmlSidebar::addEntry($spanText, '', false);
            \JHtmlSidebar::addEntry(
                \JText::_('THM_ORGANIZER_TEACHER_MANAGER_TITLE'),
                'index.php?option=com_thm_organizer&amp;view=teacher_manager',
                $viewName == 'teacher_manager'
            );
        }

        if (THM_OrganizerHelperAccess::allowFMAccess()) {
            $spanText = '<span class="menu-spacer">' . \JText::_('THM_ORGANIZER_FACILITY_MANAGEMENT') . '</span>';
            \JHtmlSidebar::addEntry($spanText, '', false);

            $fmEntries = [];

            $fmEntries[\JText::_('THM_ORGANIZER_BUILDING_MANAGER_TITLE')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=building_manager',
                'active' => $viewName == 'building_manager'
            ];
            $fmEntries[\JText::_('THM_ORGANIZER_CAMPUS_MANAGER_TITLE')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=campus_manager',
                'active' => $viewName == 'campus_manager'
            ];
            $fmEntries[\JText::_('THM_ORGANIZER_MONITOR_MANAGER_TITLE')]   = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=monitor_manager',
                'active' => $viewName == 'monitor_manager'
            ];
            $fmEntries[\JText::_('THM_ORGANIZER_ROOM_MANAGER_TITLE')]      = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=room_manager',
                'active' => $viewName == 'room_manager'
            ];
            $fmEntries[\JText::_('THM_ORGANIZER_ROOM_TYPE_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=room_type_manager',
                'active' => $viewName == 'room_type_manager'
            ];
            ksort($fmEntries);
            foreach ($fmEntries as $key => $value) {
                \JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (THM_OrganizerHelperAccess::isAdmin()) {
            $spanText = '<span class="menu-spacer">' . \JText::_('THM_ORGANIZER_ADMINISTRATION') . '</span>';
            \JHtmlSidebar::addEntry($spanText, '', false);

            $adminEntries = [];

            $adminEntries[\JText::_('THM_ORGANIZER_COLOR_MANAGER_TITLE')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=color_manager',
                'active' => $viewName == 'color_manager'
            ];
            $adminEntries[\JText::_('THM_ORGANIZER_DEGREE_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=degree_manager',
                'active' => $viewName == 'degree_manager'
            ];
            $adminEntries[\JText::_('THM_ORGANIZER_FIELD_MANAGER_TITLE')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=field_manager',
                'active' => $viewName == 'field_manager'
            ];
            $adminEntries[\JText::_('THM_ORGANIZER_GRID_MANAGER_TITLE')]   = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=grid_manager',
                'active' => $viewName == 'grid_manager'
            ];
            $adminEntries[\JText::_('THM_ORGANIZER_METHOD_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=method_manager',
                'active' => $viewName == 'method_manager'
            ];
            ksort($adminEntries);
            foreach ($adminEntries as $key => $value) {
                \JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        return \JHtmlSidebar::render();
    }

    /**
     * Attempts to delete entries from a standard table
     *
     * @param string $table the table name
     *
     * @return boolean  true on success, otherwise false
     */
    public static function delete($table)
    {
        $cids         = self::getInput()->get('cid', [], '[]');
        $formattedIDs = "'" . implode("', '", $cids) . "'";

        $dbo   = \JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->delete("#__thm_organizer_$table");
        $query->where("id IN ( $formattedIDs )");
        $dbo->setQuery($query);

        return (bool)self::executeQuery('execute');
    }

    /**
     * Determines whether the view was called from a dynamic context
     *
     * @return bool true if the view was called dynamically, otherwise false
     */
    public static function dynamic()
    {
        $app = self::getApplication();

        return (empty($app->getMenu()) or empty($app->getMenu()->getActive())) ? true : false;
    }

    /**
     * Surrounds the call to the application with a try catch so that not every function needs to have a throws tag. If
     * the application has an error it would have never made it to the component in the first place.
     *
     * @return \Joomla\CMS\Application\CMSApplication|null
     */
    public static function getApplication()
    {
        try {
            return \JFactory::getApplication();
        } catch (Exception $exc) {
            return null;
        }
    }

    /**
     * Returns the application's input object.
     *
     * @return \JInput
     */
    public static function getInput()
    {
        return self::getApplication()->input;
    }

    /**
     * Consolidates the application, component and menu parameters to a single registry with one call.
     *
     * @return \Joomla\Registry\Registry
     */
    public static function getParams()
    {
        $params = \JComponentHelper::getParams('com_thm_organizer');

        $app = self::getApplication();

        if (method_exists($app, 'getParams')) {
            $params->merge($app->getParams());

            if (!empty($app->getMenu()) and !empty($app->getMenu()->getActive())) {
                $params->merge($app->getMenu()->getActive()->getParams());
            }
        }

        return $params;
    }

    /**
     * Builds a the base url for redirection
     *
     * @return string the root url to redirect to
     */
    public static function getRedirectBase()
    {
        $url    = Uri::base();
        $input  = self::getInput();
        $menuID = $input->getInt('Itemid');

        if (!empty($menuID)) {
            $url .= self::getApplication()->getMenu()->getItem($menuID)->route . '?';
        } else {
            $url .= '?option=com_thm_organizer&';
        }

        if (!empty($input->getString('languageTag'))) {
            $url .= '&languageTag=' . Languages::getShortTag();
        }

        return $url;
    }

    /**
     * TODO: Including this (someday) to the Joomla Core!
     * Checks if the device is a smartphone, based on the 'Mobile Detect' library
     *
     * @return boolean
     */
    public static function isSmartphone()
    {
        $mobileCheckPath = JPATH_ROOT . '/components/com_jce/editor/libraries/classes/mobile.php';

        if (file_exists($mobileCheckPath)) {
            if (!class_exists('Wf_Mobile_Detect')) {
                // Load mobile detect class
                require_once $mobileCheckPath;
            }

            $checker = new \Wf_Mobile_Detect;
            $isPhone = ($checker->isMobile() and !$checker->isTablet());

            if ($isPhone) {
                return true;
            }
        }

        return false;
    }

    /**
     * Masks the Joomla application enqueueMessage function
     *
     * @param string $message the message to enqueue
     * @param string $type    how the message is to be presented
     *
     * @return void
     */
    public static function message($message, $type = 'message')
    {
        $message = Languages::getLanguage()->_($message);
        self::getApplication()->enqueueMessage($message, $type);
    }

    /**
     * Loads required files, calls the appropriate controller.
     *
     * @param boolean $isAdmin whether the file is being called from the backend
     *
     * @return void
     * @throws \Exception => task not found
     */
    public static function setUp()
    {
        $handler = explode('.', self::getInput()->getCmd('task', ''));
        if (count($handler) == 2) {
            $task = $handler[1];
        } else {
            $task = $handler[0];
        }

        require_once JPATH_ROOT . '/components/com_thm_organizer/controller.php';

        $controllerObj = new \THM_OrganizerController;
        $controllerObj->execute($task);
        $controllerObj->redirect();
    }

    /**
     * Executes a database query
     *
     * @param string $function the name of the query function to execute
     * @param mixed  $default  the value to return if an error occurred
     * @param mixed  $args     the arguments to use in the called function
     * @param bool   $rollback whether to initiate a transaction rollback on error
     *
     * @return mixed the various return values appropriate to the functions called.
     */
    public static function executeQuery($function, $default = null, $args = null, $rollback = false)
    {
        $dbo = \JFactory::getDbo();
        try {
            if ($args !== null) {
                if (is_string($args) or is_int($args)) {
                    return $dbo->$function($args);
                }
                if (is_array($args)) {
                    $reflectionMethod = new \ReflectionMethod($dbo, $function);

                    return $reflectionMethod->invokeArgs($dbo, $args);
                }
            }

            return $dbo->$function();
        } catch (RuntimeException $exc) {
            self::message($exc->getMessage(), 'error');
            if ($rollback) {
                $dbo->transactionRollback();
            }

            return $default;
        } catch (ReflectionException $exc) {
            self::message($exc->getMessage(), 'error');
            if ($rollback) {
                $dbo->transactionRollback();
            }
        } catch (Exception $exc) {
            self::message($exc->getMessage(), 'error');
            if ($rollback) {
                $dbo->transactionRollback();
            }
        }
    }
}
