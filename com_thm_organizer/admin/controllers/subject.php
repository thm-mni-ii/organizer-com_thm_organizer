<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerSubject
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;


/**
 * Class performs access checks, redirects and model function calls for data persistence
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerControllerSubject extends JControllerLegacy
{
    /**
     * Performs access checks and redirects to the subject edit view
     *
     * @return  void
     */
    public function edit()
    {
        $this->setRedirect("index.php?option=com_thm_organizer&view=subject_edit");
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the subject manager view
     *
     * @return  void
     */
    public function apply()
    {
        $success = $this->getModel('subject')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_SUM_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=subject_edit&id=$success", false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_SUM_SAVE_FAIL');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=subject_edit&id=0", false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the subject manager view
     *
     * @return  void
     */
    public function save()
    {
        $success = $this->getModel('subject')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $msgType = 'message';
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $msgType = 'error';
        }
        $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=subject_manager", false), $msg, $msgType);
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the subject manager view
     *
     * @return  void
     */
    public function save2new()
    {
        $success = $this->getModel('subject')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=subject_edit", false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=subject_edit", false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the subject manager view
     *
     * @return  void
     */
    public function delete()
    {
        $success = $this->getModel('subject')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false), $msg, 'error');
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @return  void
     */
    public function cancel()
    {
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false));
    }
 
    /**
     * Perfoerms access checks and makes function calls for importing LSF Data
     *
     * @return  void
     */
    public function importLSFData()
    {
        $success = JModel::getInstance('LSFSubject', 'THM_OrganizerModel')->importBatch();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_IMPORT_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_IMPORT_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false), $msg, 'error');
        }
    }

    /**
     * edit
     *
     * performs access checks for the current user against the id of the event
     * to be edited, or content (event) creation access if id is missing or 0
     *
     * @return void
     */
    public function updateAll()
    {
        $model = JModel::getInstance('LSFSubject', 'THM_OrganizerModel');
        $model->updateAll();
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false));
    }
}
