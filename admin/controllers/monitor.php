<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
class thm_organizersControllermonitor extends JController
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
        JRequest::setVar( 'view', 'monitor_edit' );
        JRequest::setVar( 'hidemainmenu', 1 );
        parent::display();
    }

    function save()
    {
        $model = $this->getModel('monitor_edit');
        $result = $model->store();
        $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_manager', $result);
    }
	
    function remove()
    {
        $model = $this->getModel('monitor_edit');
        $res = $model->delete();
        if(isset($res) and $res == true)
            $msg = JText::_('L&ouml;schen war erfolgreich');
        else
            $msg = JText::_('Fehler beim Löschen');
        $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_manager', $msg);
    }

    function cancel()
    {
        $msg = 'Cancelled';
        $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_manager', $msg );
    }
}