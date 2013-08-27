<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerField
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
class THM_OrganizerControllerField extends JController
{
    /**
     * Performs access checks and redirects to the field edit view
     * 
     * @return void 
     */
    public function add()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('view', 'field_edit');
        JRequest::setVar('id', '0');
        parent::display();
    }

    /**
     * Performs access checks and redirects to the field edit view
     *
     * @return  void
     */
    public function edit()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('view', 'field_edit');
        parent::display();
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the field manager view
     *
     * @return  void
     */
    public function save()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('field')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_FLM_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=field_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_FLM_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=field_manager', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the field manager view
     *
     * @return  void
     */
    public function delete()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('field')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_FLM_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=field_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_FLM_DELETE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=field_manager', false), $msg, 'error');
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
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=field_manager', false));
    }
}
