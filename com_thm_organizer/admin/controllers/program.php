<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerProgram
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
class THM_OrganizerControllerProgram extends JControllerAdmin
{
    /**
     * Performs access checks and redirects to the degree program edit view
     *
     * @return void
     */
    public function add()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JFactory::getApplication()->input->set('view', 'program_edit');
        JFactory::getApplication()->input->set('id', '0');
        parent::display();
    }

    /**
     * Performs access checks and redirects to the degree program edit view
     *
     * @return  void
     */
    public function edit()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JFactory::getApplication()->input->set('view', 'program_edit');
        parent::display();
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the degree program edit view
     *
     * @return  void
     */
    public function apply()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('program')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=program_edit&id=$success", false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_SAVE_FAIL');
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
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('program')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_SAVE_FAIL');
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
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('program')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_edit', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_SAVE_FAIL');
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
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('program')->save2copy();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_SAVE_FAIL');
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
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('program')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_DELETE_FAIL');
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
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false));
    }

    /**
     * Perfoerms access checks and makes function calls for importing LSF Data
     *
     * @return  void
     */
    public function importLSFData()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('LSFProgram')->importBatch();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_FILL_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_PRM_FILL_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=program_manager', false), $msg, 'error');
        }
    }
}
