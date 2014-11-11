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
        
        $controller = "";
        $handler = explode(".", JFactory::getApplication()->input->getCmd('task', ''));
        if (!empty($handler))
        {
            if (count($handler) == 2)
            {
                list($controller, $task) = $handler;
            }
            else
            {
                $task = JFactory::getApplication()->input->getString('task', '');
            }
        }

        if (!empty($controller))
        {
            $path = $basePath . '/controllers/' . $controller . '.php';
            if (file_exists($path))
            {
                require_once $path;
            }
            else
            {
                require_once $basePath . '/controller.php';
                $controller = '';
            }
        }
        else
        {
            require_once $basePath . '/controller.php';
        }
        $classname = 'THM_OrganizerController' . $controller;
        $controllerObj = new $classname;
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
