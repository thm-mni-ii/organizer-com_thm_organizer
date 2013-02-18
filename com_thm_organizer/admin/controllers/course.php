<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerCourse
 * @description THM_OrganizerControllerCourse component admin controller
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * Class THM_OrganizerControllerCourse for component com_thm_organizer
 *
 * Class provides methods perform actions for course
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerControllerCourse extends JControllerForm
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
		parent::save($key, $urlVar);
		$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=assets', false));
	}

	/**
	 * Method to perform edit
	 *
	 * @param   Object  $key     Key		   (default: null)
	 * @param   Object  $urlVar  Url variable  (default: null)
	 *
	 * @return  void
	 */
	public function edit($key = null, $urlVar = null)
	{
		parent::edit($key, $urlVar);
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
		parent::cancel($key);
		$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=assets', false));
	}
}
