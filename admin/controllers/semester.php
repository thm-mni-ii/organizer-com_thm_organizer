<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester controller
 * @description exectutes tasks from the semester manager and semester edit views
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersControllerSemester extends JController
{
    function display(){ parent::display(); }

    public function __construct()
    {
        parent::__construct();
        $this->registerTask( 'new', 'edit' );
        $this->registerTask( 'a', 'edit' );
    }

    /**
     * public function edit
     *
     * redirects to the semester edit view
     */
    public function edit()
    {
        JRequest::setVar( 'view', 'semester_edit' );
        parent::display();
    }

    /**
     * public function save_semester
     *
     * saves the details of a semester to the database and redirects to the semster manager view
     */
    public function apply()
    {
        $model = $this->getModel('semester_edit');
        $result = $model->store();
        if(!empty($result))
        {
            $msg = JText::_("The semester entry has been saved successfully.");
            $this->setRedirect( "index.php?option=com_thm_organizer&view=semester_edit&semesterID=$result", $msg);
        }
        else
        {
            $msg = JText::_("An error has occurred while saving the semester entry.");
            $this->setRedirect("index.php?option=com_thm_organizer&view=semester_manager", $msg, 'error');
        }
    }

    /**
     * public function save_semester
     *
     * saves the details of a semester to the database and redirects to the semster manager view
     */
    public function save()
    {
        $model = $this->getModel('semester_edit');
        $result = $model->store();
        if(!empty($result))
        {
            $msg = JText::_("The semester entry has been saved successfully.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg);
        }
        else
        {
            $msg = JText::_("An error has occurred while saving the semester entry.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg, 'error');
        }
    }

    /**
     * public function delete_semester
     *
     * removes the semester
     */
    function delete_semester()
    {
        $model = $this->getModel('semester_edit');
        $result = $model->delete();
        if($result)
        {
            $msg = JText::_("The semester has been deleted successfully.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg);
        }
        else
        {
            $msg = JText::_("An error has occurred while deleting the semester.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg, 'error');
        }
    }

    /**
     * public function cancel
     *
     * returns to the semester manager view from the semester edit view
     * without saving
     */
    public function cancel()
    {
        JRequest::setVar( 'view', 'semester_manager' );
        parent::display();
    }

    /**
     * public function upload
     *
     * uploads a file to the database
     */
    public function upload_schedule()
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
    public function delete_schedule()
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
    public function activate_schedule()
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
    public function deactivate_schedule()
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
     * public function updateText
     *
     * adds or updates the description of the schedule.
     */
    public function edit_comment()
    {
        $allowedActions = thm_organizerHelper::getActions('semester_edit');
        if($allowedActions->get("core.edit"))
        {
            $id = JRequest::getInt('semesterID');
            $model = $this->getModel('schedule');
            $result = $model->edit_comment();
            if($result)
            {
                $msg = JText::_("The description(s) of the schedule(s) has been updated successfully.");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg);
            }
            else
            {
                $msg = JText::_("An error has occurred while updating the description(s) of the schedule(s).");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'error');
            }
        }
        else
        {
            $msg = JText::_("You do not have access to perform this action.");
            $this->setRedirect( 'index.php', $msg, 'error');
        }
    }
}