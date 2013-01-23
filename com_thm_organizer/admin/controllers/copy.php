<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerCopy
 * @description THM_OrganizerControllerCopy component admin controller
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * Class THM_OrganizerControllerCopy for component com_thm_organizer
 *
 * Class provides methods perform actions for copy
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerControllerCopy extends JControllerForm
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
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=copy&layout=edit&cid=' . $cid[0], false));
	}

	/**
	 * Method to perform copy
	 *
	 * @return  void
	 */
	public function copy()
	{
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$parent_id = $_POST['jform']['parent_id'];
		$model = $this->getModel('mapping');
		$stud_id = $_SESSION['stud_id'];

		// Copy the asset on the same level
		$model->copy($cid, $parent_id);
		$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=mappings&id=$stud_id", false));
	}

	/**
	 * Method to perform cancel
	 *
	 * @return  void
	 */
	public function cancel()
	{
		$stud_id = $_SESSION['stud_id'];
		$retVal = parent::cancel();
		if ($retVal)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=mappings&id=' . $stud_id, false));
		}
	}
}
