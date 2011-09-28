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
	public function __construct()
	{
		parent::__construct();
	}

	public function getSessionID()
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

	public function getSemesterAuthor()
	{
		$dbo = & JFactory::getDBO();
		$dbo->setQuery("SELECT DISTINCT username as author FROM #__thm_organizer_semesters INNER JOIN #__users ON manager = #__users.id WHERE #__thm_organizer_semesters.id = ".$this->getSemesterID());
		$rows = $dbo->loadObjectList();
		if($rows == null)
			return "";
		return $rows['0']->author;
	}

	public function getSemesterID()
	{
		$session =& JFactory::getSession();
		return $session->get('scheduler_semID');
	}

	/**
	 * Check if the component is available
	 * @return Boolean
	 */
	public function getLSFStatus($com)
	{
		$dbo =& JFactory::getDBO();
		$query	= $dbo->getQuery(true);
		$query->select('extension_id AS "id", element AS "option", params, enabled');
		$query->from('#__extensions');
		$query->where('`type` = '.$dbo->quote('component'));
		$query->where('`element` = '.$dbo->quote($com));
		$dbo->setQuery($query);
    	if ($error = $dbo->getErrorMsg())
    		return false;

		$result = $dbo->loadObject();

		if($result === null)
			return false;

    	return true;
	}
}