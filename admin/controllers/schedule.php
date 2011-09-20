<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        controller schedule
 * @description performs access checks and makes calls for data manipulation
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     1.7.0
 */

defined( '_JEXEC' ) or die;
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersControllerschedule_manager extends JController
{
    /**
     * public function upload
     *
     * uploads a file to the database
     */
    public function upload()
    {
        $id = JRequest::getVar('semesterID');
        $fileType = $_FILES['file']['type'];
        $allowedActions = thm_organizerHelper::getActions('semester_edit');
        if($allowedActions->get("core.edit"))
        {
            $model = $this->getModel('schedule');
            $id = JRequest::getVar('semesterID');
            $fileType = $_FILES['file']['type'];
            $wrongType = false;;
            if($fileType == "text/xml")
            {
                $result = $model->upload();
                $msg = "<pre>".print_r($result, true)."</pre>";
                if($result === true)
                {
                    $msg = JText::_("The schedule has been successfully uploaded.");
                    $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg);
                }
                else if($result === false)
                {
                    $msg = JText::_("An error has occured while uploading the file.");
                    $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'error');
                }
                else if(isset($result['errors']))
                {
                    $errorText = "<h3>".JText::_('The file was not saved to the database due to the following critical inconsistencies:')."</h3>";
                    $msg = $errorText.$result['errors'];
                    if(isset($result['warnings']))
                    {
                        $warningText = "<br /><h4>".JText::_('The file additionally displayed the following minor inconsistencies:')."</h4>";
                        $msg .= $warningText.$result['warnings'];
                    }
                    $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'error');
                }
                else if(isset($result['warnings']))
                {
                    $warningText =  "<h4>".JText::_('The file has been saved, however, it displayed the following minor inconsistencies:')."</h4>";
                    $msg = $warningText.$result['warnings'];
                    $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'notice');
                }
                else if(is_string($result))
                {
                    $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $result, 'error');
                }
                else
                {
                    $msg = JText::_("An unknown error has occurred while uploading the file.");
                    $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'error');
                }
            }
            else
            {
                $msg = JText::_("This file is of an unsupported type.");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'error');
            }
        }
        else
        {
            $msg = JText::_("You do not have access to perform this action.");
            $this->setRedirect( 'index.php', $msg, 'error');
        }
    }

    /**
     * function delete_schedule
     *
     * removes schedules from a given semester
     */
    public function delete()
    {
        $allowedActions = thm_organizerHelper::getActions('semester_edit');
        if($allowedActions->get("core.delete"))
        {
            $id = JRequest::getVar('semesterID');
            $model = $this->getModel('schedule');
            $result = $model->delete();
            if($result)
            {
                $msg = JText::_("The selected schedule(s) has been successfully removed.");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg);
            }
            else
            {
                $msg = JText::_("An error has occurred while removing the schedule(s).");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'error');
            }
        }
        else
        {
            $msg = JText::_("You do not have access to perform this action.");
            $this->setRedirect( 'index.php', $msg, 'error');
        }
    }

    /**
     * function deactivate
     *
     * activates the chosen schedule in its context
     */
    public function activate()
    {
        $allowedActions = thm_organizerHelper::getActions('semester_edit');
        if($allowedActions->get("core.edit"))
        {
            $id = JRequest::getVar('semesterID');
            $model = $this->getModel('schedule');
            $result = $model->activate();
            if($result)
            {
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $result);
            }
            else
            {
                $msg = JText::_("An error has occurred while activating the schedule.");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'error');
            }
        }
        else
        {
            $msg = JText::_("You do not have access to perform this action.");
            $this->setRedirect( 'index.php', $msg, 'error');
        }
    }

    /**
     * function deactivate
     *
     * deactivates the chosen schedule
     */
    public function deactivate()
    {
        $allowedActions = thm_organizerHelper::getActions('semester_edit');
        if($allowedActions->get("core.edit"))
        {
            $id = JRequest::getVar('semesterID');
            $model = $this->getModel('schedule');
            $result = $model->deactivate();
            if($result)
            {
                $msg = JText::_("The schedule has been successfully deactivated.");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg);
            }
            else
            {
                $msg = JText::_("An error has occurred while deactivating the schedule.");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'error');
            }
        }
        else
        {
            $msg = JText::_("You do not have access to perform this action.");
            $this->setRedirect( 'index.php', $msg, 'error');
        }
    }

    /**
     * public function apply
     *
     * adds or updates the description and dates associated with a schedule
     */
    public function apply()
    {
        if(thm_organizerHelper::getActions('semester_edit')->get("core.admin"))
        {
            $id = JRequest::getInt('semesterID');
            $model = $this->getModel('schedule');
            $result = $model->apply();
            if($result)
            {
                $msg = JText::_("COM_THM_ORGANIZER_SM_SCHEDULE_CHANGE_SUCCESS");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg);
            }
            else
            {
                $msg = JText::_("COM_THM_ORGANIZER_SM_SCHEDULE_CHANGE_FAIL");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'error');
            }
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_NO_ACCESS");
            $this->setRedirect( 'index.php', $msg, 'error');
        }
    }


} ?>
