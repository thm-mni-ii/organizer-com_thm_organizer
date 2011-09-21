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
defined( '_JEXEC' ) or die;
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
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
        if(!thm_organizerHelper::isAdmin('monitor')) thm_organizerHelper::noAccess ();
        JRequest::setVar( 'view', 'monitor_edit' );
        parent::display();
    }

    public function save()
    {
        if(!thm_organizerHelper::isAdmin('monitor')) thm_organizerHelper::noAccess ();
        $model = $this->getModel('monitor');
        $result = $model->save();
        if($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_SUCCESS");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_FAIL");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
        }
    }

    public function save2new()
    {
        if(!thm_organizerHelper::isAdmin('monitor')) thm_organizerHelper::noAccess ();
        $model = $this->getModel('monitor');
        $result = $model->save();
        if($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_SUCCESS");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_edit&monitorID=0', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_FAIL");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_edit&monitorID=0', $msg, 'error');
        }
    }
	
    public function delete()
    {
        if(!thm_organizerHelper::isAdmin('monitor')) thm_organizerHelper::noAccess ();
        $model = $this->getModel('monitor');
        $result = $model->delete();
        if($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_DELETE_SUCCESS");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_DELETE_FAIL");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
        }
    }

    public function cancel()
    {
        JRequest::setVar( 'view', 'monitor_manager' );
        parent::display();
    }
}