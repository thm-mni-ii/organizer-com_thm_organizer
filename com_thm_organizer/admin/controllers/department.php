<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerDepartment
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
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
class THM_OrganizerControllerDepartment extends JControllerLegacy
{
    /**
     * Performs access checks and redirects to the degree edit view
     *
     * @return void
     */
    public function add()
    {
        $this->setRedirect("index.php?option=com_thm_organizer&view=department_edit");
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the degree manager view
     *
     * @return  void
     */
    public function apply()
    {
        $deptID = $this->getModel('department')->save();
        if (!empty($deptID))
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=department_edit&id=$deptID", false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=department_edit', false), $msg, 'error');
        }
    }

    /**
     * Method to perform save
     *
     * @return  void
     */
    public function edit()
    {
        $cid = $this->input->post->get('cid', array(), 'array');

        // Only edit the first id in the list
        if (count($cid) > 0)
        {
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=department_edit&id=$cid[0]", false));
        }
        else
        {
            $this->setRedirect("index.php?option=com_thm_organizer&view=department_edit");
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the department manager view
     *
     * @return  void
     */
    public function save()
    {
        $deptID = $this->getModel('department')->save();
        if (!empty($deptID))
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=department_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=department_manager', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the department manager view
     *
     * @return  void
     */
    public function save2copy()
    {
        $deptID = $this->getModel('department')->save2copy();
        if (!empty($deptID))
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=department_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=department_manager', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the department creation view
     *
     * @return  void
     */
    public function save2new()
    {
        $oldId = $this->input->post->get('jform', array(), 'array')['id'];
        $deptID = $this->getModel('department')->save();
        if (!empty($deptID))
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=department_edit", false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=department_edit&id=$oldId", false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the degree manager view
     *
     * @return  void
     */
    public function delete()
    {
        $success = $this->getModel('department')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=department_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=department_manager', false), $msg, 'error');
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @return  void
     */
    public function cancel()
    {
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=department_manager', false));
    }

}
