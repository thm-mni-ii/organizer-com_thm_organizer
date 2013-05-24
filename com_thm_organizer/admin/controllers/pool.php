<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerPool
 * @author      James Antrim, <James.Antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

/**
 * Class THM_OrganizerControllerPool for component com_thm_organizer
 *
 * Class provides methods perform actions for coursepool
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerControllerPool extends JController
{
    /**
     * Performs access checks, sets the id variable to 0, and redirects to the
     * pool edit view
     * 
     * @return void 
     */
    public function add()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('view', 'pool_edit');
        JRequest::setVar('id', '0');
        parent::display();
    }

    /**
     * Performs access checks and redirects to the pool edit view
     *
     * @return  void
     */
    public function edit()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('view', 'pool_edit');
        parent::display();
    }

	/**
	 * Method to create a new pool
	 *
	 * @return  void
	 */
	public function apply()
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		$success = $this->getModel('pool')->save();
		if ($success)
		{
			$msg = JText::_('COM_THM_ORGANIZER_POM_SAVE_SUCCESS');
			$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=pool_edit&layout=edit&id=$success", false), $msg);
		}
		else
		{
			$msg = JText::_('COM_THM_ORGANIZER_POM_SAVE_FAIL');
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
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		$success = $this->getModel('pool')->save();
		if ($success)
		{
			$msg = JText::_('COM_THM_ORGANIZER_POM_SAVE_SUCCESS');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=pool_manager', false), $msg);
		}
		else
		{
			$msg = JText::_('COM_THM_ORGANIZER_POM_SAVE_FAIL');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=pool_manager', false), $msg, 'error');
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
		$success = $this->getModel('pool')->delete();
		if ($success)
		{
			$msg = JText::_('COM_THM_ORGANIZER_POM_DELETE_SUCCESS');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=pool_manager', false), $msg);
		}
		else
		{
			$msg = JText::_('COM_THM_ORGANIZER_POM_DELETE_FAIL');
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
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=pool_manager', false));
	}
}
