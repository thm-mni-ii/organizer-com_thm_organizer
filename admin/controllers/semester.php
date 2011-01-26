<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
class thm_organizersControllerSemester extends JController
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
		JRequest::setVar( 'view', 'semester' );
		JRequest::setVar( 'hidemainmenu', 1 );
		parent::display();
	}
	
	function save()
	{
		$model = $this->getModel('semester');
		$result = $model->store();
		$this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_list', $result);
	}
	
	function remove()
	{
		$model = $this->getModel('semester');
		if($res = $model->delete())
		{
			$msg = JText::_('Semester erfolgreich entfernt').".";
		}
		else
		{
			$msg = JText::_('Ein Fehler ist aufgetretten').".";
		}
		$this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_list', $msg);
	}
	
	function cancel()
	{
		$msg = JText::_('Aktion abgebrochen');
		$this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_list', $msg );
	}
}