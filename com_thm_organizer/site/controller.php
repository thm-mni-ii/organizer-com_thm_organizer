<?php
/**
 * @version     v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2011 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

/**
 * Site main controller
 * 
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerController extends JController
{
	/**
	 * Method to display
	 * 
	 * @param   string  $cachable   (Default: false)
	 * @param   string  $urlparams  (Default: false)
	 * 
	 * @return    void
	 */
    public function display($cachable = false, $urlparams = false)
    {  
    	$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();

		if (isset($menu->params) && $menu->params != null)
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
