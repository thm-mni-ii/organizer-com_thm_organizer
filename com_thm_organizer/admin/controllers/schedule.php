<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerSchedule
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

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
        $this->setRedirect("index.php?option=com_thm_organizer&view=schedule_edit");
    }

    /**
     * Performs access checks and uses the model's upload function to validate
     * and save the file to the database should validation be successful
     *
     * @return void
     */
    public function upload()
    {
        $url = "index.php?option=com_thm_organizer&view=schedule_";
        $form = JFactory::getApplication()->input->files->get('jform', array(), 'array');
        $file = $form['file'];
        $validType = (!empty($file['type']) AND $file['type'] == 'text/xml');
        if (!$validType)
        {
            $typeMessage = JText::_("COM_THM_ORGANIZER_MESSAGE_ERROR_FILETYPE");
            $this->setRedirect($url . 'edit', $typeMessage, 'error');
            return;
        }

        $model = $this->getModel('schedule');
        $statusReport = $model->upload();

        // The file contains critical inconsistencies and will not be uploaded
        if (isset($statusReport['errors']))
        {
            JFactory::getApplication()->enqueueMessage($statusReport['errors'], 'error');

            // Minor inconsistencies discovered
            if (isset($statusReport['warnings']))
            {
                JFactory::getApplication()->enqueueMessage($statusReport['warnings'], 'notice');
            }

            $this->setRedirect($url . 'edit');
            return;
        }

        // Minor inconsistencies discovered but will be uploaded
        if (isset($statusReport['warnings']))
        {
            JFactory::getApplication()->enqueueMessage($statusReport['warnings'], 'notice');
            $this->setRedirect($url . 'manager');
            return;
        }

        // Upload with no warnings
        $this->setRedirect($url . 'manager', JText::_("COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS"));
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
     * Method to cancel an edit.
     *
     * @return  void
     */
    public function cancel()
    {
        $this->setRedirect("index.php?option=com_thm_organizer&view=schedule_manager");
    }

    /**
     * Toggles category behaviour properties
     *
     * @return void
     */
    public function toggle()
    {
        $model = $this->getModel('schedule');
        $success = $model->toggle();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $type = 'message';
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $type = 'error';
        }

        $this->setRedirect("index.php?option=com_thm_organizer&view=schedule_manager", $msg, $type);
    }
}
