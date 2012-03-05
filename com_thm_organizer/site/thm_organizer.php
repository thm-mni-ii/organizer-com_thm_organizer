<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2012
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
require_once( JPATH_COMPONENT.DS.'controller.php' );

$controller = '';
$handler = explode(".", JRequest::getVar('task'));
if(!empty($handler))
{
    if(count($handler) == 2) list($controller, $task) = $handler;
    else $task = JRequest::getVar('task');
}
if($controller !== '')
{
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    file_exists($path)? require_once $path : $controller = '';
}
$classname = 'thm_organizerController'.$controller;
$controller = new $classname();
$controller->execute($task);
$controller->redirect();
