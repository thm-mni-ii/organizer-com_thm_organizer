<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        provides functions useful to multiple component files
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

/**
 * Class providing functions usefull to multiple component files
 *
 * @category  Joomla.Component.Admin
 * @package   thm_organizer
 */
class THM_OrganizerHelper
{
    /**
     * Calls the appropriate controller
     * 
     * @param   boolean  $isAdmin  whether the file is being called from the backend
     * 
     * @return  void
     */
    public static function callController($isAdmin = true)
    {
        $basePath = $isAdmin? JPATH_COMPONENT_ADMINISTRATOR : JPATH_COMPONENT_SITE;

        $handler = explode(".", JFactory::getApplication()->input->getCmd('task', ''));
        if (count($handler) == 2)
        {
            list($controller, $task) = $handler;
        }
        else
        {
            $task = $handler[0];
        }

        if (!empty($controller) AND file_exists($basePath . '/controllers/' . $controller . '.php'))
        {
            require_once $basePath . '/controllers/' . $controller . '.php';
            $className = 'THM_OrganizerController' . $controller;
        }
        else
        {
            require_once $basePath . '/controller.php';
            $className = 'THM_OrganizerController';
        }

        $controllerObj = new $className;
        $controllerObj->execute($task);
        $controllerObj->redirect();
    }

    /**
     * Attempts to delete entries from a standard table
     * 
     * @param   string  $table  the table name
     * 
     * @return  boolean  true on success, otherwise false
     */
    public static function delete($table)
    {
        $cids = JFactory::getApplication()->input->get('cid', array(), 'array');
        $formattedIDs = "'" . implode("', '", $cids) . "'";

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->delete("#__thm_organizer_$table");
        $query->where("id IN ( $formattedIDs )");
        $dbo->setQuery($query);
        try
        {
            $dbo->execute();
        }
        catch (Exception $exception)
        {
            return false;
        }
        return true;
    }
}
