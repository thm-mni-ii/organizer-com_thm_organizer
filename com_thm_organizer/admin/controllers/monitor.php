<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        monitor controller
 * @author      James Antrim, <James.Antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class performing access checks and model function calls for monitor actions 
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerControllermonitor extends JController
{
    /**
     * Performs access checks and redirects to the monitor edit view
     * 
     * @return void 
     */
    public function add()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        JRequest::setVar('view', 'monitor_edit');
        JRequest::setVar('monitorID', '0');
        parent::display();
    }

    /**
     * Performs access checks and redirects to the monitor edit view
     * 
     * @return void 
     */
    public function edit()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        JRequest::setVar('view', 'monitor_edit');
        parent::display();
    }

    /**
     * Performs access checks, saves the monitor currently being edited and
     * redirects to the monitor manager view
     * 
     * @return void 
     */
    public function save()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        $model = $this->getModel('monitor');
        $result = $model->save();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
        }
    }

    /**
     * Performs access checks, saves the monitor currently being edited and
     * redirects to the monitor edit view
     * 
     * @return void 
     */
    public function save2new()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        $model = $this->getModel('monitor');
        $result = $model->save();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_edit&monitorID=0', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_edit&monitorID=0', $msg, 'error');
        }
    }

    /**
     * Performs access checks and deletes a monitor entry from the database
     * 
     * @return void 
     */
    public function delete()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        $model = $this->getModel('monitor');
        $result = $model->delete();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_DELETE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_DELETE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
        }
    }

    /**
     * Performs access checks and redirects to the monitor manager
     * 
     * @return void 
     */
    public function cancel()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        JRequest::setVar('view', 'monitor_manager');
        parent::display();
    }
}
