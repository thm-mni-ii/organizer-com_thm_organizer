<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
class thm_organizersControllerRoom_IP extends JController
{
	function display()
	{
		parent::display();
	} 
	
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'add', 'edit' );
	}
	
	function edit()
	{
		JRequest::setVar( 'view', 'room_ip' );
		JRequest::setVar( 'hidemainmenu', 1 );
		parent::display();
	}
	
	function save()
	{
		$model = $this->getModel('room_ip');
		$result = $model->store();
		$this->setRedirect( 'index.php?option=com_thm_organizer&view=room_ip_list', $result);
	}
	
	function remove()
	{
		$model = $this->getModel('room_ip');
		if($res = $model->delete())
		{
			$msg = JText::_('L&ouml;schen war erfolgreich');
		}
		else
		{
			$msg = JText::_('Fehler beim L�schen');
		}
		$this->setRedirect( 'index.php?option=com_thm_organizer&view=room_ip_list', $msg);
	}
	
	function cancel()
	{
		$msg = 'Aktion abgebrochen';
		$this->setRedirect( 'index.php?option=com_thm_organizer&view=room_ip_list', $msg );
	}
}