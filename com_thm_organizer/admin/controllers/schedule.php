<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        schedule controller
 * @author      James Antrim, <James.Antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class performing access checks and model function calls for schedule actions 
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerControllerschedule extends JController
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
        $fileType = $_FILES['file']['type'];
        if ($fileType == "text/xml")
        {
            $model = $this->getModel('schedule');
            $statusReport = $model->upload();

            // The file contains critical inconsistancies and will not be uploaded
            if (isset($statusReport['errors']))
            {
                $errorText = "<h3>" . JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_ERRORS") . ":</h3>";
                $msg = $errorText . $statusReport['errors'];

                // Minor inconsistancies discovered
                if (isset($statusReport['warnings']))
                {
                    $warningText = "<br /><h4>" . JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_ERRORS_WARNINGS") . ":</h4>";
                    $msg .= $warningText . $statusReport['warnings'];
                }
                $this->setRedirect("index.php?option=com_thm_organizer&view=schedule_edit", $msg, 'error');
            }
            else
            {
                $url = "index.php?option=com_thm_organizer&view=schedule_edit";

                // Minor inconsistancies discovered
                if (isset($statusReport['warnings']))
                {
                    $warningText = "<h4>" . JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_WARNINGS") . ":</h4>";
                    $msg = $warningText . $statusReport['warnings'];
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
     * adds or updates schedule commentary and redirects to the schedule
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
     * Performs access checks, removes schedules from the database, and
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

        $url = "index.php?option=com_thm_organizer&view=schedule_manager";
        $model = $this->getModel('schedule');
        $success = $model->delete();
        if ($success)
        {
            $msg = JText::_("COM_THM_ORGANIZER_SCH_DELETE_SUCCESS");
            $this->setRedirect($url, $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_SCH_DELETE_FAIL");
            $this->setRedirect($url, $msg, 'error');
        }
    }

    /**
     * performs access checks, activates/deactivates the chosen schedule in the
     * context of its planning period, and redirects to the schedule manager view
     * 
     * @return void
     */
    public function setReference()
    {
        if (!thm_organizerHelper::isAdmin('schedule'))
        {
            thm_organizerHelper::noAccess();
        }
        $url = "index.php?option=com_thm_organizer&view=schedule_manager";

        if (JRequest::getInt("boxchecked") === 1)
        {
            $model = $this->getModel('schedule');
            $active = $model->checkIfActive();
            if ($active)
            {
                $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_SCH_ALREADY_ACTIVE"), 'error');
            }
            else
            {
                $success = $model->setReference();
                if ($success)
                {
                    $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_SCH_REFERENCE_SUCCESS"));
                }
                else
                {
                    $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_SCH_REFERENCE_FAIL"), 'error');
                }
            }
        }
        else
        {
            $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_SCH_REFERENCE_COUNT"), 'error');
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
