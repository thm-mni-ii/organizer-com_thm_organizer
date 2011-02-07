<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        monitor controller
 * @description exectutes tasks from the monitor manager and monitor edit views
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
class thm_organizersControllermonitor extends JController
{
    public function display(){ parent::display(); }

    public function __construct()
    {
        parent::__construct();
        $this->registerTask( 'new', 'edit' );
    }
	
    public function edit()
    {
        JRequest::setVar( 'view', 'monitor_edit' );
        parent::display();
    }

    public function save()
    {
        $model = $this->getModel('monitor_edit');
        $result = $model->store();
        if($result)
        {
            $msg = JText::_("The monitor entry has been saved successfully.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("An error has occurred while saving the monitor entry.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
        }
    }

    public function save2new()
    {
        $model = $this->getModel('monitor_edit');
        $result = $model->store();
        if($result)
        {
            $msg = JText::_("The monitor entry has been saved successfully.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_edit&monitorID=0', $msg);
        }
        else
        {
            $msg = JText::_("An error has occurred while saving the monitor entry.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_edit&monitorID=0', $msg, 'error');
        }
    }
	
    public function delete()
    {
        $model = $this->getModel('monitor_edit');
        $result = $model->delete();
        if($result)
        {
            $msg = JText::_("The monitor entry has been deleted successfully.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("An error has occurred while deleting the monitor entry.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
        }
    }

    public function cancel()
    {
        JRequest::setVar( 'view', 'monitor_manager' );
        parent::display();
    }
}