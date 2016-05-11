<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerPool
 * @author      James Antrim, <James.Antrim@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class THM_OrganizerControllerPool for component com_thm_organizer
 *
 * Class provides methods perform actions for coursepool
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerControllerPool extends JControllerLegacy
{
    /**
     * Performs access checks and redirects to the pool edit view
     *
     * @return void
     */
    public function add()
    {
        $this->setRedirect("index.php?option=com_thm_organizer&view=pool_edit");
    }

    /**
     * Performs access checks and redirects to the pool edit view
     *
     * @return  void
     */
    public function edit()
    {
        $cid = $this->input->post->get('cid', array(), 'array');

        // Only edit the first id in the list
        if (count($cid) > 0)
        {
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=pool_edit&id=$cid[0]", false));
        }
        else
        {
            $this->setRedirect("index.php?option=com_thm_organizer&view=pool_edit");
        }
    }

    /**
     * Method to create a new pool
     *
     * @return  void
     */
    public function apply()
    {
        $success = $this->getModel('pool')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=pool_edit&layout=edit&id=$success", false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=pool_edit&layout=edit&id=0', false), $msg, 'error');
        }
    }

    /**
     * Method to perform save
     *
     * @return  void
     */
    public function save()
    {
        $success = $this->getModel('pool')->save();
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

        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=pool_manager', false), $msg, $msgType);
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the room manager view
     *
     * @return  void
     */
    public function delete()
    {
        $success = $this->getModel('pool')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=pool_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=pool_manager', false), $msg, 'error');
        }
    }

    /**
     * Method to perform cancel
     *
     * @return  void
     */
    public function cancel()
    {
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=pool_manager', false));
    }
}
