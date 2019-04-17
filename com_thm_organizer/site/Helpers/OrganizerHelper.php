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

use Exception;
use ReflectionException;

defined('_JEXEC') or die;

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
        $cids         = self::getInput()->get('cid', [], '[]');
        $formattedIDs = "'" . implode("', '", $cids) . "'";

        $dbo   = \Factory::getDbo();
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
        $dbo = \Factory::getDbo();
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
        } catch (\RuntimeException $exc) {
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

    /**
     * Surrounds the call to the application with a try catch so that not every function needs to have a throws tag. If
     * the application has an error it would have never made it to the component in the first place.
     *
     * @return \Joomla\CMS\Application\CMSApplication|null
     */
    public static function getApplication()
    {
        try {
            return \Factory::getApplication();
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
        $fqName = is_string($object) ? $object : get_class($object);
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
        $app    = self::getApplication();
        $url    = \JUri::base();
        $menuID = $app->input->getInt('Itemid');

        if (!empty($menuID)) {
            $url .= $app->getMenu()->getItem($menuID)->route . '?';
        } else {
            $url .= '?option=com_thm_organizer&';
        }

        if (!empty($app->input->getString('languageTag'))) {
            $url .= '&languageTag=' . Languages::getShortTag();
        }

        return $url;
    }

    /**
     * TODO: Including this (someday) to the \Joomla Core!
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
     * Masks the \Joomla application enqueueMessage function
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
     * @throws Exception => task not found
     */
    public static function setUp($isAdmin = true)
    {
        if ($isAdmin) {
            require_once JPATH_COMPONENT_ADMINISTRATOR . '/Controller.php';
            $fqName = "Organizer\\Admin\\Controller";
        } else {
            require_once JPATH_COMPONENT_SITE . '/Controller.php';
            $fqName = "Organizer\\Controller";
        }

        $handler = explode('.', self::getInput()->getCmd('task', ''));
        if (count($handler) == 2) {
            $task = $handler[1];
        } else {
            $task = $handler[0];
        }


        $controllerObj = new $fqName;
        $controllerObj->execute($task);
        $controllerObj->redirect();
    }
}
