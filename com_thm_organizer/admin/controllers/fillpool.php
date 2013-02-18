<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerFillpool
 * @description THM_OrganizerControllerFillpool component admin controller
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');

/**
 * Class THM_OrganizerControllerFillpool for component com_thm_organizer
 * Class provides methods perform actions for fillpool
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerControllerFillpool extends JControllerForm
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
		// Get Major-ID from the current session
		$stud_id = $_SESSION['stud_id'];

		$retVal = parent::save($key, $urlVar);
		if ($retVal)
		{
			$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=mappings&id=$stud_id", false));
		}
	}

	/**
	 * Method to perform cancel
	 *
	 * @return  void
	 */
	public function cancel()
	{
		// Get Major-ID from the current session
		$stud_id = $_SESSION['stud_id'];

		$retVal = parent::cancel();
		if ($retVal)
		{
			$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=mappings&id=$stud_id", false));
		}
	}
}
