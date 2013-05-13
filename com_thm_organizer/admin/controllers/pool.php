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
jimport('joomla.application.component.controllerform');

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
class THM_OrganizerControllerPool extends JControllerForm
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
        JRequest::setVar('layout', 'add');
        JRequest::setVar('id', '0');
        parent::display();
    }

    /**
     * Performs access checks and redirects to the pool edit view
     *
     * @param   Object  $key     Key		   (default: null)
     * @param   Object  $urlVar  Url variable  (default: null)
     *
     * @return  void
     */
    public function edit($key = null, $urlVar = null)
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('view', 'pool_edit');
        JRequest::setVar('layout', 'edit');
        parent::display();
    }

	/**
	 * Method to create a new pool
	 *
	 * @param   Object  $key     Key		   (default: null)
	 * @param   Object  $urlVar  Url variable  (default: null)
	 *
	 * @return  void
	 */
	public function apply($key = null, $urlVar = null)
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
	 * @param   Object  $key     Key		   (default: null)
	 * @param   Object  $urlVar  Url variable  (default: null)
	 *
	 * @return  void
	 */
	public function save($key = null, $urlVar = null)
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
	 * Method to perform cancel
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 * 
	 * @return  void
	 */
	public function cancel($key = null)
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=pool_manager', false));
	}
}
