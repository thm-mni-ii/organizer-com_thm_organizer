<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        thm_organizer
 * @description the base file for the component
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2012
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     0.1.1
 */
defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT . DS . 'controller.php';
$controllerName = "";
$handler = explode(".", JRequest::getVar('task'));
if (!empty($handler))
{
	if (count($handler) == 2)
	{
		list($controllerName, $task) = $handler;
	}
	else
	{
		$task = JRequest::getVar('task');
	}
}
if (!empty($controller))
{
	$path = JPATH_COMPONENT . DS . 'controllers' . DS . $controllerName . '.php';
	file_exists($path) ? require_once $path : $controllerName = '';
}
$classname = 'THM_OrganizerController' . $controllerName;
$class = new $classname;
$class->execute($task);
$class->redirect();
