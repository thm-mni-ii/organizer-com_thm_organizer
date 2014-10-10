<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerColor
 * @author      James Antrim, <james.antrim@mni.thm.de>
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
class THM_OrganizerControllerColor extends JControllerLegacy
{
    /**
     * Performs access checks and redirects to the color edit view
     *
     * @return void
     */
    public function add()
    {
        JFactory::getApplication()->input->set('view', 'color_edit');
        parent::display();
    }

    /**
     * Performs access checks and redirects to the color edit view
     *
     * @return  void
     */
    public function edit()
    {
        JFactory::getApplication()->input->set('view', 'color_edit');
        parent::display();
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the color manager view
     *
     * @return  void
     */
    public function save()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }
        $success = $this->getModel('color')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=color_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=color_manager', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the color manager view
     *
     * @return  void
     */
    public function delete()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }
        $success = $this->getModel('color')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=color_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=color_manager', false), $msg, 'error');
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @return  void
     */
    public function cancel()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=color_manager', false));
    }
}
