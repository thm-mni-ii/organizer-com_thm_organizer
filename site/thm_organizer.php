<?php
/**
 * @package     Joomla.Site | Joomla.Administrator
 * @subpackage  [typ]_thm_[name]
 * @author      [Vorname] [Nachname] [Email]
 * @copyright   TH Mittelhessen <Jahr>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     [versionsnr]
 */



defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');

// Require the base controller
 
require_once( JPATH_COMPONENT.DS.'controller.php' );
 
// Require specific controller if requested
$controllername = 'thm_organizerController';
$specificControllerName = JRequest::getVar('controller');
if(isset($specificControllerName))
{
    require_once JPATH_COMPONENT.DS.'controllers'.DS.$specificControllerName.'.php';
    $controllerName .= $specificControllerName;
}
$controller = JController::getInstance($controllerName);
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
