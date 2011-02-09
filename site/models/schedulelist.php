<?php 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
/**
 * Room Model
 *
 */
class GiessenSchedulerModelScheduleList extends JModel
{	
	/**
	 * Creates the list of db entries and sets the text for the currently selected file.
	 */
	function getSchedules()
	{		
		$sid = JRequest::getVar('semesterid');
		$dbo = & JFactory::getDBO();
       	$user =& JFactory::getUser();
       	$username = $user->username;
       	$query = "SELECT author FROM #__thm_organizer_semester
   					WHERE author = '$username';";
   		$dbo->setQuery($query); 
   		$result = $dbo->query();
   		if(!empty($result))
   		{   		
	   		$query = "SELECT id, includedate, filename, active, description, sid 
	   					FROM #__thm_organizer_schedules
	   					WHERE sid = '$sid';";
	   		$dbo->setQuery($query); 
	   		$schedules = $dbo->loadObjectList();
			if ($dbo->getErrorNum())
			{
				return "empty";
			}
   			return $schedules;
   		}
   		else
   		{
			$app =& JFactory::getApplication();
			$app->redirect('index.php?option=com_thm_organizer&view=semesterlist', JText::_('Zugriff Verweigert'));
   		}	
	}
}
?>