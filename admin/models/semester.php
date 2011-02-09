<?php
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelSemester extends JModel
{
	function __construct()
	{
		parent::__construct();
		$ids = JRequest::getVar('cid');
		$id = $ids[0];
		$this->setId($id);
	}
	
	function setId($id)
	{
		$this->_id = $id;
		$this->_data = null;
	}
	
	function getData()
	{
		if (!empty( $this->_id ))
		{
			$ids = JRequest::getVar('cid',  '', '', 'array');
			$id = $ids[0];
			$query = "SELECT * FROM #__thm_organizer_semester 
						WHERE sid = '$id';";
			$this->_db->setQuery( $query );
			$result = $this->_db->loadObject();
			if($result)
			{
				$this->_id = $result->sid;
				$this->_data->sid = $result->sid;
				$this->_data->author = $result->author;
				$this->_data->orgunit = $result->orgunit;
				$this->_data->semester = $result->semester;
			}
		}
		if (!$this->_data)
		{
			$this->_data = new stdClass();
			$this->_data->sid = 0;
			$this->_data->author = '';
			$this->_data->orgunit = '';
			$this->_data->semester = '';
		}
		return $this->_data;
	}
	
	function store()
	{
		global $mainframe;
		//Sanitize
		$sid = JRequest::getVar('id');
		$author = trim(JRequest::getVar( 'author', '', 'post','string', JREQUEST_ALLOWRAW ));
		$orgunit = trim(JRequest::getVar( 'orgunit', '', 'post','string', JREQUEST_ALLOWRAW ));
		$semester = trim(JRequest::getVar( 'semester', '', 'post','string', JREQUEST_ALLOWRAW ));
		
		$dbo = & JFactory::getDBO();
		if($sid == 0)
			$query = "INSERT INTO #__thm_organizer_semester (author, orgunit, semester)
						VALUES ( '$author', '$orgunit', '$semester' );";
		else
			$query = "UPDATE #__thm_organizer_semester
							 SET author = '$author',
							 	 orgunit = '$orgunit',
							 	 semester = '$semester'
							WHERE sid = '$sid';";
		$dbo->setQuery($query);
		$dbo->query();
		if($dbo->getErrorNum())
		{
			return JText::_("Es darf nur ein Eintrag pro Org. Einheit & Semeseter geben.");
		}
		else return JText::_("Erfolgreich gespeichert.");
	}
	
	function delete()
	{
		global $mainframe;
		
		$ids = JRequest::getVar( 'cid' );
		if (count( $ids ))
		{
			$where = "";
			foreach($ids as $id)
			{
				if($where != "") $where .= ", ";
				$where .= "$id";
			}
			$dbo = & JFactory::getDBO();
			$query = "DELETE FROM #__thm_organizer_semester WHERE sid IN ( $where );";
			$dbo->setQuery( $query );
			$dbo->query();
			if ($dbo->getErrorNum())
			{
				return false;
			}
		}
		return true;
	}
}