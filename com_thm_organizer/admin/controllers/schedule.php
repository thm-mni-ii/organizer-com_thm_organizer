<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerSchedule
 * @author      James Antrim, <James.Antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

/**
 * Class performs access checks, redirects and model function calls for data persistence
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerControllerSchedule extends JControllerLegacy
{
    /**
     * Performs access checks and redirects to the schedule edit view
     *
     * @return void
     */
    public function add()
    {
        JFactory::getApplication()->input->set('view', 'schedule_edit');
        parent::display();
    }

    /**
     * Performs access checks and redirects to the schedule edit view
     *
     * @return void
     */
    public function edit()
    {
        JFactory::getApplication()->input->set('view', 'schedule_edit');
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
        $url = "index.php?option=com_thm_organizer&view=schedule_manager";
        $file = JFactory::getApplication()->input->files->get('file', array());
        $validType = (!empty($file['type']) AND $file['type'] == 'text/xml');
        if ($validType)
        {
            $model = $this->getModel('schedule');
            $statusReport = $model->upload();

            // The file contains critical inconsistancies and will not be uploaded
            if (isset($statusReport['errors']))
            {
                $errorText = "<h3>" . JText::_("COM_THM_ORGANIZER_MESSAGE_SCHEDULE_ERRORS") . "</h3>";
                $msg = $errorText . $statusReport['errors'];

                // Minor inconsistancies discovered
                if (isset($statusReport['warnings']))
                {
                    $warningText = "<br /><h4>" . JText::_("COM_THM_ORGANIZER_MESSAGE_SCHEDULE_WARNINGS") . "</h4>";
                    $msg .= $warningText . $statusReport['warnings'];
                }
                $this->setRedirect($url, $msg, 'error');
            }
            else
            {
                // Minor inconsistancies discovered
                if (isset($statusReport['warnings']))
                {
                    $warningText = "<h4>" . JText::_("COM_THM_ORGANIZER_MESSAGE_SCHEDULE_WARNINGS") . "</h4>";
                    $msg = $warningText . $statusReport['warnings'];
                    $this->setRedirect($url, $msg, 'notice');
                }
                else
                {
                    $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS"));
                }
            }
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_FILETYPE");
            $this->setRedirect($url, $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the schedule manager view
     *
     * @return void
     */
    public function save()
    {
        $model = $this->getModel('schedule');
        $result = $model->saveComment();
        $url = "index.php?option=com_thm_organizer&view=schedule_manager";
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS");
            $this->setRedirect($url, $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL");
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
        $success = $this->getModel('schedule')->delete();
        if ($success)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS");
            $this->setRedirect("index.php?option=com_thm_organizer&view=schedule_manager", $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL");
            $this->setRedirect("index.php?option=com_thm_organizer&view=schedule_manager", $msg, 'error');
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
        $url = "index.php?option=com_thm_organizer&view=schedule_manager";
        $count = JFactory::getApplication()->input->getInt('boxchecked', 0);
        if ($count === 1)
        {
            $model = $this->getModel('schedule');
            $active = $model->checkIfActive();
            if ($active)
            {
                $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_ACTIVE_YES"), 'error');
            }
            else
            {
                $success = $model->setReference();
                if ($success)
                {
                    $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_MESSAGE_REFERENCE_SUCCESS"));
                }
                else
                {
                    $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_MESSAGE_REFERENCE_FAIL"), 'error');
                }
            }
        }
        else
        {
            $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_ONE_ALLOWED"), 'error');
        }
    }

    /**
     * Performs access checks. Checks if the schedule is already active. If the
     * schedule is not already active, calls the activate function of the
     * schedule model.
     *
     * @return  void
     */
    public function activate()
    {
        $url = "index.php?option=com_thm_organizer&view=schedule_manager";
        $count = JFactory::getApplication()->input->getInt('boxchecked', 0);
        if ($count === 1)
        {
            $model = $this->getModel('schedule');
            $active = $model->checkIfActive();
            if ($active)
            {
                $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_ACTIVE_YES"), 'warning');
            }
            else
            {
                $success = $model->activate();
                if ($success)
                {
                    $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_MESSAGE_ACTIVATE_SUCCESS"));
                }
                else
                {
                    $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_MESSAGE_ACTIVATE_FAIL"), 'error');
                }
            }
        }
        else
        {
            $this->setRedirect($url, JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_ONE_ALLOWED"), 'error');
        }
    }
 
    /**
     * Performs access checks. Checks whether schedules qualify for a merge.
     * Merges schedules.
     *
     * @return  void
     */
    public function mergeView()
    {
        $url = 'index.php?option=com_thm_organizer&view=schedule_manager';
        $merge = $this->getModel('schedule')->checkMergeConstraints();
        switch ($merge)
        {
            case MERGE:
                JFactory::getApplication()->input->set('view', 'schedule_merge');
                parent::display();
                break;
            case TOO_FEW:
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_TOOFEW');
                $this->setRedirect(JRoute::_($url, false), $msg, 'warning');
                break;
            case CHECK_DEPARTMENTS:
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_COMPATIBILITY');
                $this->setRedirect(JRoute::_($url, false), $msg, 'warning');
                break;
            case CHECK_DATES:
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_COMPATIBILITY');
                $this->setRedirect(JRoute::_($url, false), $msg, 'warning');
                break;
            case NOT_ACTIVE:
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_ACTIVE_NO');
                $this->setRedirect(JRoute::_($url, false), $msg, 'warning');
                break;
 
        }
    }

    /**
     * Performs access checks. Checks if the schedule is already active. If the
     * schedule is not already active, calls the activate function of the
     * schedule model.
     *
     * @return  void
     */
    public function merge()
    {
        $url = "index.php?option=com_thm_organizer&view=schedule_manager";

        $model = $this->getModel('schedule');
        $success = $model->merge();
        switch ($success)
        {
            case ERROR:
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_FAIL');
                $this->setRedirect(JRoute::_($url, false), $msg, 'error');
                break;
            case MERGE:
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
                $this->setRedirect(JRoute::_($url, false), $msg);
                break;
            case TOO_FEW:
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_TOOFEW');
                $this->setRedirect(JRoute::_($url, false), $msg, 'warning');
                break;
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @return  void
     */
    public function cancel()
    {
        $this->setRedirect("index.php?option=com_thm_organizer&view=schedule_manager");
    }
}
