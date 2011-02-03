<?php
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelRoom_IP extends JModel
{
	var $_id = null;
	var $_data = null;
	
	function __construct()
	{
		parent::__construct();
	}
	
	function getData()
	{
		if (empty( $this->_id ))
		{ 
			$ids = JRequest::getVar('cid',  0, '', 'array');
			$id = $ids[0];
			$query = "SELECT * 
						FROM #__giessen_scheduler_roomip AS rip
							LEFT JOIN #__giessen_scheduler_semester AS s
								ON s.sid = rip.sid
						WHERE ip = '$id';";
			//echo $query;
			$this->_db->setQuery( $query );
			$result = $this->_db->loadObject();
			if($result)
			{
				$this->_id = $result->ip;
				$this->_data->ip = $result->ip;
				$this->_data->room = $result->room;
				$this->_data->orgunit = $result->orgunit;
				$this->_data->semester = $result->semester;
				$this->_data->semester = $result->sid;
			}
		}
		if (!$this->_data)
		{
			$this->_data = new stdClass();
			$this->_data->ip = '';
			$this->_data->room = '';
			$this->_data->orgunit = '';
			$this->_data->semester = '';
			$this->_data->sid = '';
		}
		return $this->_data;
	}
	
	function store()
	{
		$dbo = & JFactory::getDBO();
		$query = "SELECT * FROM #__giessen_scheduler_roomip WHERE ip ='".$_POST['ip']."'";
		$dbo->setQuery( $query );
		$result = $dbo->loadAssocList();
		if(count($result) == 0)
		{
			$query = "INSERT INTO #__giessen_scheduler_roomip (room, ip, sid)
						VALUES ( '".$_POST['room']."', '".$_POST['ip']."', '".$_POST['semester']."' );";
		} 
		else
		{ 
			$query = "UPDATE #__giessen_scheduler_roomip 
						SET room = '".$_POST['room']."',
							sid = '".$_POST['semester']."'
						WHERE ip ='".$_POST['ip']."';";
		}
		$dbo->setQuery( $query );
		$dbo->query();	
		if ($dbo->getErrorNum())
		{
			return "Ein Fehler is aufgetretten.";
		}
		return "Erfolgreich gespeichert.";
	}
	
	function delete()
	{
		$ips = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		if (count( $ips ))
		{
			$where = "";
			foreach($ips as $ip)
			{
				if($where != "") $where .= ",";
				$where .= " '$ip'";
			}
			$dbo = & JFactory::getDBO();
			$query = "DELETE FROM #__giessen_scheduler_roomip WHERE ip IN ( $where );";
			$dbo->setQuery( $query );
			$result = $dbo->query();
			if ($dbo->getErrorNum())
			{
				return false;
			}
		}
		return true;
	}
	
	function getSemesters()
	{
			$dbo = & JFactory::getDBO();
			$query = "SELECT sid, CONCAT(orgunit, '-', semester) AS name 
						FROM #__giessen_scheduler_semester;";
			$dbo->setQuery( $query );
			return $dbo->loadObjectList();
	}
}