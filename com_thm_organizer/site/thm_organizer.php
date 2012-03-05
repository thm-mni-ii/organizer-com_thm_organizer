<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.011
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
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
$classname = 'thm_organizerController'.$controller;
$controller = new $classname();
$controller->execute($task);
$controller->redirect();
