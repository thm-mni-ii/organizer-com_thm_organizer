<?php
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
/**
 * RoomList Model
 *
 */
class GiessenSchedulerModelSemesterList extends JModel
{
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
	}
	

	function getSemesters()
	{
		
       	$user =& JFactory::getUser();
        $username = $user->username;
        
		//establish db object
		$dbo = & JFactory::getDBO();
		$query = "SELECT sid, orgunit, semester FROM #__thm_organizer_semester WHERE author = '$username';";
		$dbo->setQuery( $query );
		$semesters = $dbo->loadObjectList();
		return $semesters;
	}
}