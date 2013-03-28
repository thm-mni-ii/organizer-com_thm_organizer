<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerColor
 * @description THM_OrganizerControllerColor component admin controller
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');

/**
 * Class THM_OrganizerControllerColor for component com_thm_organizer
 *
 * Class provides methods perform actions for color
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerControllerColor extends JControllerForm
{
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
        if (!thm_organizerHelper::isAdmin('color'))
        {
            thm_organizerHelper::noAccess();
        }

		$success = parent::save($key, $urlVar);
		if ($success)
		{
			$msg = JText::_('COM_THM_ORGANIZER_CLM_SAVE_SUCCESS');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=colors', false));
		}
		else
		{
			$msg = JText::_('COM_THM_ORGANIZER_CLM_SAVE_FAIL');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=colors', $msg, 'error'));
		}
	}

	/**
	 * Method to perform cancel
	 *
	 * @return  void
	 */
	public function cancel()
	{
        if (!thm_organizerHelper::isAdmin('color'))
        {
            thm_organizerHelper::noAccess();
        }

		$success = parent::cancel();
		if ($success)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=colors', false));
		}
	}

	/**
	 * Method to perform delete
	 *
	 * @return  void
	 */
	public function delete()
	{
        if (!thm_organizerHelper::isAdmin('color'))
        {
            thm_organizerHelper::noAccess();
        }

		$success = $this->getModel('color')->delete();
		if ($success)
		{
			$msg = JText::_('COM_THM_ORGANIZER_CLM_DELETE_SUCCESS');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=colors', $msg));
		}
		else
		{
			$msg = JText::_('COM_THM_ORGANIZER_CLM_DELETE_FAIL');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=colors', $msg, 'error'));
		}

	}
}
