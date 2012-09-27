<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        schedule controller
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';
/**
 * Class performing access checks and model function calls for schedule actions 
 * 
 * @package  Admin
 * 
 * @since    2.5.4
 */
class thm_organizersControllerschedule extends JController
{
    /**
     * Redirects to the semester edit view to upload a new schedule
     * 
     * @return void
     */
    public function add()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        JRequest::setVar('view', 'schedule_edit');
        JRequest::setVar('scheduleID', '0');
        parent::display();
    }

    /**
     * Redirects to the semester edit view to edit an existing schedule
     * 
     * @return void
     */
    public function edit()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        JRequest::setVar('view', 'schedule_edit');
        parent::display();
    }

    /**
     * Performs access checks and uses the model's upload function to validate
     * and save the file to the database should validation be successful
     * 
     * @return void
     */
    public function upload()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        $url = "index.php?option=com_thm_organizer&view=schedule_edit";
        $fileType = $_FILES['file']['type'];
        if ($fileType == "text/xml")
        {
            $model = $this->getModel('schedule');
            $status = $model->upload();

            // The file contains critical inconsistancies and will not be uploaded
            if (isset($status['errors']))
            {
                $errorText = "<h3>" . JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_ERRORS") . ":</h3>";
                $msg = $errorText . $status['errors'];

                // Minor inconsistancies discovered
                if (isset($status['warnings']))
                {
                    $warningText = "<br /><h4>" . JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_ERRORS_WARNINGS") . ":</h4>";
                    $msg .= $warningText . $status['warnings'];
                }
                $this->setRedirect($url, $msg, 'error');
            }
            else
            {
                $url .= "&scheduleID={$status['scheduleID']}";

                // Minor inconsistancies discovered
                if (isset($status['warnings']))
                {
                    $warningText = "<h4>" . JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_WARNINGS") . ":</h4>";
                    $msg = $warningText . $status['warnings'];
                    $this->setRedirect($url, $msg, 'notice');
                }
                else
                {
                    $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_SUCCESS"));
                }
            }
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_TYPE_FAIL");
            $this->setRedirect($url, $msg, 'error');
        }
    }

    /**
     * adds or updates schedule information and redirects to the schedule
     * manager view
     * 
     * @return void
     */
    public function save()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        $model = $this->getModel('schedule');
        $result = $model->saveComment();
        $url = "index.php?option=com_thm_organizer&view=schedule_manager";
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_SCH_CHANGE_SUCCESS");
            $this->setRedirect($url, $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_SCH_CHANGE_FAIL");
            $this->setRedirect($url, $msg, 'error');
        }
    }

    /**
     * performs access checks, removes schedules from the database, and
     * redirects to the schedule manager view optionally to filtered to a
     * specific semester
     * 
     * @return void
     */
    public function delete()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }

        $semesterID = JRequest::getVar('semesterID');
        $url = "index.php?option=com_thm_organizer&view=schedule_manager";
        $url .= ($semesterID)? "&semesterID=$semesterID" : "";

        $dbo = JFactory::getDbo();
        $dbo->transactionStart();
        $scheduleIDs = JRequest::getVar('cid', array(), 'post', 'array');
        JTable::addIncludePath(JPATH_COMPONENT . DS . 'tables');
        $schedule = JTable::getInstance('schedules', 'thm_organizerTable');
        foreach ($scheduleIDs as $scheduleID)
        {
            $schedule->load($scheduleID);
            $model = $this->getModel();
            if ($schedule->active)
            {
                $model->deactivate($schedule->sid);
            }
            $model->delete($scheduleID);
        }
        if ($dbo->getErrorNum())
        {
            $dbo->transactionRollback();
            $msg = JText::_("COM_THM_ORGANIZER_SCH_DELETE_FAIL");
            $this->setRedirect($url, $msg, 'error');
        }
        else
        {
            $dbo->transactionCommit();
            $msg = JText::_("COM_THM_ORGANIZER_SCH_DELETE_SUCCESS");
            $this->setRedirect($url, $msg);
        }
    }

    /**
     * performs access checks, activates/deactivates the chosen schedule in the
     * context of its planning period, and redirects to the schedule manager view
     * 
     * @return void
     */
    public function setDefault()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        $semesterID = JRequest::getVar('semesterID');
        $url = "index.php?option=com_thm_organizer&view=schedule_manager";
        $url .= ($semesterID)? "&semesterID=$semesterID" : "";

        if (JRequest::getInt("boxchecked") > 1)
        {
            $model = $this->getModel();
            $activation_conflicts = $model->checkForActivationConflicts();
            if ($activation_conflicts)
            {
                $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_SCH_ACTIVATE_COUNT_FAIL"), 'error');
            }
        }
        $this->activate();
    }

    /**
     * Performs access checks and uses the model's activate/deactivate functions
     * should another schedule previously be activated for the same semester a
     * delta is created.
     * 
     * @return void
     */
    private function activate()
    {
        $semesterID = JRequest::getVar('semesterID');
        $url = "index.php?option=com_thm_organizer&view=schedule_manager";
        $url .= ($semesterID)? "&semesterID=$semesterID" : "";
        $scheduleIDs = JRequest::getVar('cid', array(), 'post', 'array');

        $return = array();
        $return['errors'] = array();
        $return['messsages'] = array();
        JTable::addIncludePath(JPATH_COMPONENT . DS . 'tables');
        $schedule = JTable::getInstance('schedules', 'thm_organizerTable');
        foreach ($scheduleIDs as $scheduleID)
        {
            $dbo = JFactory::getDbo();
            $dbo->transactionStart();
            $success = $schedule->load($scheduleID);
            if ($success)
            {
                $model = $this->getModel();
                ($schedule->active)? $model->deactivate($schedule->sid, $return, true): $model->activate($schedule, $return);
            }
            else
            {
                $return['errors'][] = JText::_("COM_THM_ORGANIZER_SCH_ACTIVATE_FIND_FAIL");
            }
            if ($dbo->getErrorNum())
            {
                    $dbo->transactionRollback();
                    $return['errors'][] = JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_DB_FAIL');
                    break;
            }
            else
            {
                $dbo->transactionCommit();
            }
        }
        $msg = "";
        if (count($return['errors']))
        {
            $msg .= "<br />" . implode("<br />", $return['errors']);
        }
        if (count($return['messages']))
        {
            $msg .= "<br />" . implode("<br />", $return['messages']);
        }
        if (count($return['errors']))
        {
            $this->setRedirect($url, $msg, 'error');
        }
        else
        {
            $this->setRedirect($url, $msg);
        }
    }

    /**
     * Performs access checks and redirects to the schedule manager view
     * 
     * @return void
     */
    public function cancel()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        $url = "index.php?option=com_thm_organizer&view=schedule_manager";
        $this->setRedirect($url);
    }
}
