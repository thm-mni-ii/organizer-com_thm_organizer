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
class thm_organizerModelICalTest extends JModel
{
	function getiCal()
	{
		$user =& JFactory::getUser();
		$username = $user->username;
		$ictl = new icaltestlib();
		$plan = $ictl->getResourcePlan($username,'I107', 1);
		return $plan;
	}
}
?>