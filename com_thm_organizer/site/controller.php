<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

/**
 * Site main controller
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerController extends JControllerLegacy
{
	/**
	 * Method to display
	 *
	 * @param bool  $cachable    If true, the view output will be cached
	 * @param array $urlparams   An array of safe url parameters and their variable types, for valid values
	 *                             see {@link JFilterInput::clean()}.
	 *
	 * @return    void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		parent::display($cachable, $urlparams);
	}
}
