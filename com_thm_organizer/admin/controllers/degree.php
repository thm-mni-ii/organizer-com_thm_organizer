<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerDegree
 * @description THM_OrganizerControllerDegree component admin controller
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
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
class THM_OrganizerControllerDegree extends JControllerLegacy
{
    /**
     * Performs access checks and redirects to the degree edit view
     *
     * @return void
     */
    public function add()
    {
        JRequest::setVar('id', '0');
        $this->setRedirect("index.php?option=com_thm_organizer&view=degree_edit");
    }

    /**
     * Method to perform save
     *
     * @return  void
     */
    public function edit()
    {
        $this->setRedirect("index.php?option=com_thm_organizer&view=degree_edit");
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the degree manager view
     *
     * @return  void
     */
    public function save()
    {
        $success = $this->getModel('degree')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=degree_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=degree_manager', false), $msg, 'error');
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
        $success = $this->getModel('degree')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=degree_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=degree_manager', false), $msg, 'error');
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @return  void
     */
    public function cancel()
    {
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=degree_manager', false));
    }

}
