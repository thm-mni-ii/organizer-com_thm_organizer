<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2011 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT . DS . 'controller.php';

$handler = explode(".", JRequest::getVar('task'));
if (!empty($handler))
{
    if (count($handler) == 2)
    {
        $controller = $handler[0];
        $task = $handler[1];
    }
    else
    {
       $task = JRequest::getVar('task');
    }
}
if (!empty($controller))
{
    $path = JPATH_COMPONENT . DS . 'controllers' . DS . $controller . '.php';
    if (file_exists($path))
    {
        require_once $path;
    }
}
else
{
    $controller = '';
}
$classname = 'THM_OrganizerController' . $controller;
$controllerObj = new $classname;
$controllerObj->execute($task);
$controllerObj->redirect();
