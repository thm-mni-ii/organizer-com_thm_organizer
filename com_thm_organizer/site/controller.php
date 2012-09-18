<?php
/**
 * @package     Joomla.Site | Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @author      Wolf Rost
 * @author      Markus Baier
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');

class thm_organizerController extends JController
{
    function display($cachable = false, $urlparams = false)
    {  
    	$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();

		if ($menu->params != null)
		{
			JRequest::setVar('lang', JRequest::getVar('lang', $menu->params->get('lsf_default_language')));
		}
		else
		{
			JRequest::setVar('lang', JRequest::getVar('lang'));
		}
		parent::display(); 
    }
} 
