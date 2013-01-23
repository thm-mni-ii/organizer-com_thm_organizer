<?php
/**
 * @version	    v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description the base file for the component
 * @author	    James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT . DS . 'controller.php';

$controller = "";
$handler = explode(".", JRequest::getVar('task'));
if (!empty($handler))
{
    if (count($handler) == 2)
    {
    	list($controller, $task) = $handler;
    }
    else 
    {
    	$task = JRequest::getVar('task');
    }
}
if (!empty($controller))
{
    $path = JPATH_COMPONENT . DS . 'controllers' . DS . $controller . '.php';
    file_exists($path)? require_once $path : $controller = '';
}
$classname = 'THM_OrganizerController' . $controller;
$controller = new $classname;
$controller->execute($task);
$controller->redirect();
 