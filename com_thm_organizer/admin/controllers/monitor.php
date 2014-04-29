<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        monitor controller
 * @author      James Antrim, <James.Antrim@mni.thm.de>
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
class THM_OrganizerControllermonitor extends JControllerAdmin
{
    /**
     * Performs access checks and redirects to the monitor edit view
     *
     * @return void
     */
    public function add()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->input->set('view', 'monitor_edit');
        $this->input->set('monitorID', '0');
        parent::display();
    }

    /**
     * Performs access checks and redirects to the monitor edit view
     *
     * @return void
     */
    public function edit()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->input->set('view', 'monitor_edit');
        parent::display();
    }

    /**
     * Performs access checks, saves the monitor currently being edited and
     * redirects to the monitor manager view
     *
     * @return void
     */
    public function save()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $model = $this->getModel('monitor');
        $result = $model->save();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
        }
    }
    /**
     * Performs access checks, saves the monitor currently being edited and
     * redirects to the monitor manager view
     *
     * @return void
     */
    public function saveDefaultBehaviour()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $model = $this->getModel('monitor');
        $result = $model->saveDefaultBehaviour();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
        }
    }

    /**
     * Performs access checks, saves the monitor currently being edited and
     * redirects to the monitor edit view
     *
     * @return void
     */
    public function save2new()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $result = $this->getModel('monitor')->save();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_edit&monitorID=0', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_SAVE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_edit&monitorID=0', $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the monitor manager view
     *
     * @return  void
     */
    public function delete()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $result = $this->getModel('monitor')->delete();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_DELETE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MON_DELETE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
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
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=monitor_manager', false));
    }
}
