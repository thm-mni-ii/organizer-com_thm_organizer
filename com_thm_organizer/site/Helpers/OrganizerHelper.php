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

namespace Organizer\Helpers;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Organizer\Controller;
use ReflectionMethod;
use RuntimeException;

/**
 * Class provides generalized functions useful for several component files.
 */
class OrganizerHelper
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
     * Attempts to delete entries from a standard table
     *
     * @param string $table the table name
     *
     * @return boolean  true on success, otherwise false
     */
    public static function delete($table)
    {
        $selectedIDs  = self::getSelectedIDs();
        $formattedIDs = "'" . implode("', '", $selectedIDs) . "'";

        $dbo   = Factory::getDbo();
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
        $dbo = Factory::getDbo();
        try {
            if ($args !== null) {
                if (is_string($args) or is_int($args)) {
                    return $dbo->$function($args);
                }
                if (is_array($args)) {
                    $reflectionMethod = new ReflectionMethod($dbo, $function);

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
        } catch (Exception $exc) {
            self::message($exc->getMessage(), 'error');
            if ($rollback) {
                $dbo->transactionRollback();
            }
        }
    }

    /**
     * Surrounds the call to the application with a try catch so that not every function needs to have a throws tag. If
     * the application has an error it would have never made it to the component in the first place.
     *
     * @return CMSApplication|null
     */
    public static function getApplication()
    {
        try {
            return Factory::getApplication();
        } catch (Exception $exc) {
            return null;
        }
    }

    /**
     * Gets the name of an object's class without its namespace.
     *
     * @param mixed $object the object whose namespace free name is requested or the fq name of the class to be loaded
     *
     * @return string the name of the class without its namespace
     */
    public static function getClass($object)
    {
        $fqName   = is_string($object) ? $object : get_class($object);
        $nsParts  = explode('\\', $fqName);
        $lastItem = array_pop($nsParts);
        if (empty($lastItem)) {
            return 'Organizer';
        } elseif (strpos($lastItem, '_') !== false) {
            return ucwords($lastItem, "_");
        } else {
            return ucfirst($lastItem);
        }
    }

    /**
     * Retrieves the request form.
     *
     * @return array with the request data if available
     */
    public static function getFormInput()
    {
        return self::getInput()->get('jform', [], 'array');
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
     * @return Registry
     */
    public static function getParams()
    {
        $app = self::getApplication();

        return method_exists($app, 'getParams') ? $app->getParams() : ComponentHelper::getParams('com_thm_organizer');
    }

    /**
     * Creates the plural of the given resource.
     *
     * @param string $resource the resource for which the plural is needed
     *
     * @return string the plural of the resource name
     */
    public static function getPlural($resource)
    {
        switch (true) {
            case $resource == 'equipment':
                return 'equipment';
            case mb_substr($resource, -1) == 's':
                return $resource . 'es';
            case mb_substr($resource, -1) == 'y':
                return mb_substr($resource, 0, mb_strlen($resource) - 1) . 'ies';
                break;
            default:
                return $resource . 's';
        }
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
     * Returns the application's input object.
     *
     * @return array the selected ids
     */
    public static function getSelectedIDs()
    {
        $input = self::getInput();

        // List Views
        $selectedIDs = $input->get('cid', [], 'array');
        $selectedIDs = ArrayHelper::toInteger($selectedIDs);

        if (!empty($selectedIDs)) {
            return $selectedIDs;
        }

        // Forms
        $formData = OrganizerHelper::getFormInput();
        if (!empty($formData)) {

            // Merge Views
            if (isset($formData['ids'])) {
                $selectedIDs = array_filter(ArrayHelper::toInteger(explode(',', $formData['ids'])));
                if (!empty($selectedIDs)) {
                    asort($selectedIDs);

                    return $selectedIDs;
                }
            }

            // Edit Views
            if (isset($formData['id'])) {
                return [(int)$formData['id']];
            }
        }

        // Default: explicit GET/POST parameter
        $selectedID = $input->getInt('id', 0);

        return empty($selectedID) ? [] : [$selectedID];
    }

    /**
     * Instantiates an Organizer table with a given name
     *
     * @param string $name the table name
     *
     * @return Table
     */
    public static function getTable($name)
    {
        $dbo = Factory::getDbo();
        $fqn = "\\Organizer\\Tables\\$name";

        return new $fqn($dbo);
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
        $message = Languages::_($message);
        self::getApplication()->enqueueMessage($message, $type);
    }

    /**
     * Instantiates the controller.
     *
     * @return void
     * @throws Exception => task not found
     */
    public static function setUp()
    {
        $handler = explode('.', self::getInput()->getCmd('task', ''));
        if (count($handler) == 2) {
            $task = $handler[1];
        } else {
            $task = $handler[0];
        }

        $controllerObj = new Controller;
        $controllerObj->execute($task);
        $controllerObj->redirect();
    }
}
