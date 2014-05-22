<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerRoom
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
class THM_OrganizerControllerRoom extends JControllerLegacy
{
    /**
     * Performs access checks, sets the id variable to 0, and redirects to the
     * room edit view
     *
     * @return void
     */
    public function add()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('id', '0');
        $this->setRedirect("index.php?option=com_thm_organizer&view=room_edit");
    }

    /**
     * Performs access checks and redirects to the room edit view
     *
     * @return  void
     */
    public function edit()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->setRedirect("index.php?option=com_thm_organizer&view=room_edit");
    }

    /**
     * Performs access checks and calls the room model's autoMergeAll function
     * before redirecting to the room manager view. No output of success or
     * failure due to the merge of multiple entries.
     *
     * @return  void
     */
    public function mergeAll()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $model = $this->getModel('room');
        $model->autoMergeAll();
 
        $msg = JText::_('COM_THM_ORGANIZER_RMM_AUTO_MERGE');
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg);
    }
 
    /**
     * Performs access checks, calls the room model's autoMerge function. Should
     * the room entries be mergeable based upon plausibility constraints this is
     * done automatically, otherwise a redirect is made to the room merge view.
     *
     * @return  void
     */
    public function mergeView()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        if (count(JRequest::getVar('cid', array(), 'post', 'array')) == 1)
        {
            $msg = JText::_('COM_THM_ORGANIZER_RMM_MERGE_TOOFEW');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg, 'warning');
        }
        else
        {
            $model = $this->getModel('room');
            $success = $model->autoMerge();
            if ($success)
            {
                $msg = JText::_('COM_THM_ORGANIZER_RMM_MERGE_SUCCESS');
                $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg);
            }
            else
            {
                JRequest::setVar('view', 'room_merge');
                parent::display();
            }
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the room manager view
     *
     * @return  void
     */
    public function save()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('room')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_RMM_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_RMM_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's merge function, and
     * redirects to the room manager view
     *
     * @return  void
     */
    public function merge()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('room')->merge();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_RMM_MERGE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_RMM_MERGE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the room manager view
     *
     * @return  void
     */
    public function delete()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('room')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_RMM_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_RMM_DELETE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false), $msg, 'error');
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
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_manager', false));
    }
}
