<?php
/**
 * 
 * EditEvent Model for Giessen Times Component
 * 
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
require_once(JPATH_SITE.DS.'components'.DS.'com_thm_organizer'.DS.'assets'.DS.'ICalender'.DS.'ical.php');
 
/**
 * Room Model
 *
 */
class GiessenSchedulerModelICalTest extends JModel
{
	function getiCal()
	{
		$user =& JFactory::getUser();
		$username = $user->username;
		$ictl = new icaltestlib();
		$plan = $ictl->getResourcePlan('jant89', 'I007', 1);
		//$plan = $ictl->getEvents('jant89', 3);
		return $plan;
	}
}
?>