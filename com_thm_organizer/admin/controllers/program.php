<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerProgram
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
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
class THM_OrganizerControllerProgram extends JControllerLegacy
{
    /**
     * Performs access checks and redirects to the degree program edit view
     *
     * @return void
     */
    public function add()
    {
        $this->setRedirect("index.php?option=com_thm_organizer&view=program_edit");
    }

    /**
     * Performs access checks and redirects to the degree program edit view
     *
     * @return  void
     */
    public function edit()
    {
        $cid = $this->input->post->get('cid', array(), 'array');

        // Only edit the first id in the list
        if (count($cid) > 0)
        {
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=program_edit&id=$cid[0]", false));
        }
        else
        {
            $this->setRedirect("index.php?option=com_thm_organizer&view=program_edit");
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the degree program edit view
     *
     * @return  void
     */
    public function apply()
    {
        $success = $this->getModel('program')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=program_edit&id=$success", false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the degree program manager view
     *
     * @return  void
     */
    public function save()
    {
        $success = $this->getModel('program')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg, 'error');
        }
    }


    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the degree program manager view
     *
     * @return  void
     */
    public function save2new()
    {
        $success = $this->getModel('program')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_edit', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_edit', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the degree program manager view
     *
     * @return  void
     */
    public function save2copy()
    {
        $success = $this->getModel('program')->save2copy();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg, 'error');
        }
    }

    /**
     * Method to perform delete
     *
     * @return  void
     */
    public function delete()
    {
        $success = $this->getModel('program')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg, 'error');
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @return  void
     */
    public function cancel()
    {
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false));
    }

    /**
     * Performs access checks and makes function calls for importing LSF Data
     *
     * @return  void
     */
    public function importLSFData()
    {
        $success = $this->getModel('LSFProgram')->importBatch();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_IMPORT_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_IMPORT_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg, 'error');
        }
    }
}
