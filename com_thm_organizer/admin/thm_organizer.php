<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        controller thm_organizer
 * @description the entry file for the administrative area of thm_organizer
 *              accepts the controller/task parameters and redirects to specific
 *              controllers
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once( JPATH_COMPONENT.DS.'controller.php' );
$controller = "";
$handler = explode(".", JRequest::getVar('task'));
if(!empty($handler))
{
    if(count($handler) == 2)
    {
        $controller = $handler[0];
        $task = $handler[1];
    }
    else
       $task = JRequest::getVar('task');
}
if(!empty($controller))
{
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) require_once $path;
    else $controller = '';
}
$classname = 'thm_organizersController'.$controller;
$controller = new $classname();
$controller->execute($task);
$controller->redirect();
 