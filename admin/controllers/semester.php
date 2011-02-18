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

    function __construct()
    {
        parent::__construct();
        $this->registerTask( 'new', 'edit' );
    }

    function edit()
    {
        JRequest::setVar( 'view', 'semester_edit' );
        parent::display();
    }
	
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

    public function save2new()
    {
        $model = $this->getModel('semester_edit');
        $result = $model->store();
        if(!empty($result))
        {
            $msg = JText::_("The semester entry has been saved successfully.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_edit&semesterID=0', $msg);
        }
        else
        {
            $msg = JText::_("An error has occurred while saving the semester entry.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_edit&semesterID=0', $msg, 'error');
        }
    }


    public function save2schedules()
    {
        $model = $this->getModel('semester_edit');
        $result = $model->store();
        if(!empty($result))
        {
            $msg = JText::_("The semester entry has been saved successfully.");
            $this->setRedirect( "index.php?option=com_thm_organizer&view=semester_schedules&semesterID=$result", $msg);
        }
        else
        {
            $msg = JText::_("An error has occurred while saving the semester entry.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_edit&semesterID=0', $msg, 'error');
        }
    }
	
    function delete()
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
    public function upload()
    {
        $model = $this->getModel('semester_edit');
        $id = JRequest::getVar('semesterID');
        $fileType = $_FILES['file']['type'];
        $allowedActions = thm_organizerHelper::getActions('semester_edit');
        if($allowedActions->get("core.edit"))
        {
            $model = $this->getModel('semester_edit');
            $id = JRequest::getVar('semesterID');
            $fileType = $_FILES['file']['type'];
            $wrongType = false;
            $errors = array('dberrors' => false, 'dataerrors' => array());
            switch($fileType)
            {
                case "text/xml":
                    $result = $model->uploadXML(&$errors);
                    if(!$errors['dberrors'] and empty($errors['dataerrors']))
                    {
                        $msg = JText::_("The schedule has been successfully uploaded.");
                        $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $result);
                    }
                    else if(!empty($errors['dataerrors']))
                    {
                        foreach($errors['dataerrors'] as $k => $v) $errors['dataerrors'][$k] = JText::_($v);
                        $errorstring = "<br />".implode("<br />", $erray)."<br />";
                        $messagestring = JText::_('The file was not saved to the database due to the following data inconsistencies:');
                        $msg = $messagestring.$errorstring;
                        $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'notice');
                    }
                    else if(!empty($errors['dberrors']))
                    {
                        $msg = JText::_("An error has occured while uploading the file.");
                        $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$id", $msg, 'error');
                    }
                    break;
                default:
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
     * function deactivate
     * 
     * removes a schedule
     */
    public function deactivate()
    {
        $allowedActions = thm_organizerHelper::getActions('semester_edit');
        if($allowedActions->get("core.edit"))
        {
            $model = $this->getModel('semester_edit');
            $result = $model->deactivate();
            if($result)
            {
                $msg = JText::_("The description of the schedule has been updated successfully.");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$result", $msg);
            }
            else
            {
                $msg = JText::_("An error has occurred while updating the description of the schedule.");
                $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg, 'error');
            }
        }
        else
        {
            $msg = JText::_("You do not have access to perform this action.");
            $this->setRedirect( 'index.php', $msg, 'error');
        }
    }

    /**
     * function schedule_delete
     *
     * removes a schedule
     */
    public function schedule_delete()
    {
        $allowedActions = thm_organizerHelper::getActions('semester_edit');
        if($allowedActions->get("core.delete"))
        {
            $model = $this->getModel('semester_edit');
            $result = $model->schedule_delete();
            if($result)
            {
                $msg = JText::_("The description of the schedule has been updated successfully.");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$result", $msg);
            }
            else
            {
                $msg = JText::_("An error has occurred while updating the description of the schedule.");
                $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg, 'error');
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
    public function updateText()
    {
        $allowedActions = thm_organizerHelper::getActions('semester_edit');
        if($allowedActions->get("core.edit"))
        {
            $model = $this->getModel('semester_edit');
            $result = $model->updateText();
            if($result)
            {
                $msg = JText::_("The description of the schedule has been updated successfully.");
                $this->setRedirect("index.php?option=com_thm_organizer&view=semester_edit&semesterID=$result", $msg);
            }
            else
            {
                $msg = JText::_("An error has occurred while updating the description of the schedule.");
                $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg, 'error');
            }
        }
        else
        {
            $msg = JText::_("You do not have access to perform this action.");
            $this->setRedirect( 'index.php', $msg, 'error');
        }
    }
}