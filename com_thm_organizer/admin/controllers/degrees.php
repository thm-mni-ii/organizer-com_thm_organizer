<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerDegrees
 * @description THM_OrganizerControllerDegrees component admin controller
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla controllerform library
jimport('joomla.application.component.controlleradmin');

/**
 * Class THM_OrganizerControllerDegrees for component com_thm_organizer
 *
 * Class provides methods perform actions for degrees
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerControllerDegrees extends JControllerAdmin
{
	/**
	 * Method to get the model
	 *
	 * @param   String  $name    Name	 (default: 'Degrees')
	 * @param   String  $prefix  Prefix  (default: 'THM_CurriculumModel')
	 *
	 * @return  Object
	 */
	public function getModel($name = 'Degrees', $prefix = 'THM_CurriculumModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}
