<?php

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
 * RoomList Model
 *
 */
class thm_organizerModelScheduler extends JModel
{
	public $semesterID = null;
	 /**
	 * @var string msg
	 **/
	protected $msg;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
	}

	function getSessionID()
	{
       	$user =& JFactory::getUser();
       	if($user->username == NULL)
       		return "";
		//establish db object
		$dbo = & JFactory::getDBO();
		$dbo->setQuery("SELECT DISTINCT #__session.session_id, #__session.username, #__session.usertype, #__users.email FROM #__session LEFT OUTER JOIN #__users ON #__session.username = #__users.username WHERE #__session.username = '".$user->get('username')."' AND #__session.guest = 0");
		$rows = $dbo->loadObjectList();
		return $rows['0']->session_id;
	}

	function getSemesterAuthor()
	{
		$dbo = & JFactory::getDBO();
		$dbo->setQuery("SELECT DISTINCT author FROM #__thm_organizer_semester WHERE sid = ".$this->semesterID);
		$rows = $dbo->loadObjectList();
		if($rows == null)
			return "";
		return $rows['0']->author;
	}

	/**
	 * Get the message
	 * @return string The message to be displayed to the user
	 */
	public function getSemesterID()
	{
		if (!isset($this->semesterID))
		{
			$id = JRequest::getInt('id');

			$this->semesterID = $id;
		}
		return $this->semesterID;
	}
}