<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerUser
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;

/**
 * Class performing access checks and model function calls for category actions
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerControllerUser extends JControllerLegacy
{

    /**
     * redirects to the category_edit.scss view for the editing of existing categories
     *
     * @return void
     */
    public function add()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $model = $this->getModel('user');
        $success = $model->add();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_USM_ADD_SUCCESS');
            $type = 'message';
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_USM_ADD_FAIL');
            $type = 'error';
        }
        $this->setRedirect("index.php?option=com_thm_organizer&view=user_select&tmpl=component", $msg, $type);
    }

    /**
     * redirects to the category_edit.scss view for the editing of existing categories
     *
     * @return void
     */
    public function toggle()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $model = $this->getModel('user');
        $success = $model->toggle();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_USM_TOGGLE_SUCCESS');
            $type = 'message';
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_USM_TOGGLE_FAIL');
            $type = 'error';
        }
        $this->setRedirect("index.php?option=com_thm_organizer&view=user_manager", $msg, $type);
    }


    /**
     * Checks access and deletes selected users
     *
     * @return void
     */
    public function delete()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $model = $this->getModel('user');
        $result = $model->delete();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_USM_DELETE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=user_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_USM_DELETE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=user_manager', $msg, 'error');
        }
    }

    /**
     * redirects to the category manager view without making any persistent changes
     *
     * @return void
     */
    public function cancel()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->setRedirect('index.php?option=com_thm_organizer&view=category_manager');
    }
}
