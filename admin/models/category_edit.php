<?php
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelCategory extends JModel
{
	function __construct()
	{
		parent::__construct();
		$ids = JRequest::getString('cid',  0, '', 'array');
		$id = $ids[0];
		$this->setId($id);
	}
	
	function setId($ip)
	{
		$this->_id = $id;
		$this->_data = null;
	}
	
	function getData()
	{
		if (empty( $this->_id ))
		{
			$ids = JRequest::getVar('cid',  0, '', 'array');
			$id = $ids[0];
			$query = "SELECT * FROM #__giessen_scheduler_categories 
						WHERE ecid = '$id';";
			$this->_db->setQuery( $query );
			$result = $this->_db->loadObject();
			if($result)
			{
				$this->_id = $result->ecid;
				$this->_data->ecid = $result->ecid;
				$this->_data->ecname = $result->ecname;
				$this->_data->ecdescription = $result->ecdescription;
				$this->_data->ecimage = $result->ecimage;
				$this->_data->access = $result->access;
				$this->_data->globalp = $result->globalp;
				$this->_data->reservingp = $result->reservingp;
			}
		}
		if (!$this->_data)
		{
			$this->_data = new stdClass();
			$this->_data->ecid = 0;
			$this->_data->ecname = '';
			$this->_data->ecdescription = '';
			$this->_data->ecimage = '';
			$this->_data->ecimage = 0;
			$this->_data->globalp = 0;
			$this->_data->reservingp = 0;
		}
		return $this->_data;
	}
	
	function store()
	{
		global $mainframe;
		$post = print_r($_POST, true);

		//Sanitize
		$ecid = JRequest::getVar('id');
		$ecname = trim(JRequest::getVar( 'ecname', '', 'post','string', JREQUEST_ALLOWRAW ));
		$ecalias = str_replace(' ', '-', strtolower($data['ecname']));
		$ecdescription = JRequest::getVar( 'ecdescription', '', 'post','string', JREQUEST_ALLOWRAW );
		$access = JRequest::getVar( 'access', '', 'post','int', JREQUEST_ALLOWRAW );
		$globalp = JRequest::getVar( 'globalp', '', 'post','int', JREQUEST_ALLOWRAW );
		$reservingp = JRequest::getVar( 'reservingp', '', 'post','int', JREQUEST_ALLOWRAW );
		$file = JRequest::getVar('ecimage', '', 'files');
		$ecimage = $file['name'];
		$image = JRequest::getVar( 'image', '', 'post','string', JREQUEST_ALLOWRAW );
		if($ecimage)
		{
			$ecimage = $file['name'];
			if(strpos($file['type'], 'image') === 0 && $file['size'] <= 1024000 && $ecimage != '')
			{
				jimport('joomla.filesystem.file');
				$extpoint = strlen($file['name']) - 4;
				if(strpos($ecimage, '.') != $extpoint)
				{
					$msg = JText::_('Bitte ersetzen Sie die sonderzeichen in der Dateiname').".";
					$mainframe->redirect('index.php?option=com_thm_organizer&view=category_list', $msg);
				}
				
				$base_Dir = JPATH_SITE.'/images/thm_organizer/categories/';
				if(JFile::exists( $base_Dir.$ecimage))
				{
					$beforeep = substr($ecimage, 0, $extpoint);
					$afterep = substr($ecimage, $extpoint);
					$next = 2;
					while( JFile::exists( $base_Dir.$beforeep.$next.$afterep) )
					{
			   			$next++;
					}
					$ecimage = $beforeep.$next.$afterep;
				}
				
				$filepath = $base_Dir.$ecimage;
				
				if (!JFile::upload($file['tmp_name'], $filepath))
				{
					$this->setError( JText::_( 'UPLOAD FAILED' ) );
					return false;
				}				
			}
			else
			{
				$msg = JText::_('Falsche Dateityp oder Datei ist zu gro&szlig;').".";
				$mainframe->redirect('index.php?option=com_thm_organizer&view=category_list', $msg);
			}
		}
		elseif($image)
		{
			$ecimage = $image;
		}
		if($ecid != 0)
		{
			$query = "UPDATE #__giessen_scheduler_categories
						 SET ecname = '$ecname',
						 	 ecalias = '$ecalias',
						 	 ecdescription = '$ecdescription',
						 	 access = '$access',
						 	 globalp = '$globalp',
						 	 reservingp = '$reservingp'";
			if($ecimage) $query .= ", ecimage = '$ecimage' WHERE ecid = '$ecid';";
			else $query .= " WHERE ecid = '$ecid';";
		}
		else
		{
			if($ecimage)
				$query = "INSERT INTO #__giessen_scheduler_categories (ecname, ecalias, ecdescription, ecimage, access, globalp, reservingp)
							VALUES ( '$ecname', '$ecalias', '$ecdescription', '$ecimage', '$access', '$globalp','$reservingp' );";
			else
				$query = "INSERT INTO #__giessen_scheduler_categories (ecname, ecalias, ecdescription, access, globalp, reservingp)
							VALUES ( '$ecname', '$ecalias', '$ecdescription', '$access', '$globalp','$reservingp' );";
		}
		$dbo = & JFactory::getDBO();
		$dbo->setQuery($query);
		$dbo->query();
		if($dbo->getErrorNum())
		{
			return JText::_("Fehler beim Speichern.");
		}
		else return JText::_("Erfolgreich gespeichert.");
	}
	
	function delete()
	{
		global $mainframe;
		
		$ids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		if (count( $ids ))
		{
			$where = "";
			foreach($ids as $id)
			{
				if($where != "") $where .= ",";
				$where .= " '$id'";
			}
			$dbo = & JFactory::getDBO();
			$query = "DELETE FROM #__giessen_scheduler_categories WHERE ecid IN ( $where );";
			$dbo->setQuery( $query );
			$dbo->query();
			if ($dbo->getErrorNum())
			{
				return false;
			}
		}
		return true;
	}
	
	function getUserGroups()
	{
		global $mainframe;
		$dbo = & JFactory::getDBO();
		$query = "SELECT id, name 
					FROM #__core_acl_aro_groups 
					WHERE name NOT IN ('ROOT', 'USERS', 'Public Frontend', 'Public Backend' )
					ORDER BY id";
		$dbo->setQuery( $query );
		$usergroups = $dbo->loadObjectList();
		$public = array(1=>array(id=>'0',name=>'Public'));
		return array_merge($public, $usergroups);
	}
}