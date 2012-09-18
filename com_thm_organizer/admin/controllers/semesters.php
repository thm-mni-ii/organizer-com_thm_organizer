<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerSemesters
 * @description THM_OrganizerControllerSemesters component admin controller
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Class THM_OrganizerControllerSemesters for component com_thm_organizer
 *
 * Class provides methods perform actions for semesters
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerControllerSemesters extends JControllerAdmin
{
	/**
	 * Method to get the model
	 *
	 * @param   String  $name    Name	 (default: 'Semesters')
	 * @param   String  $prefix  Prefix  (default: 'THM_CurriculumModel')
	 *
	 * @return  Object
	 */
	public function getModel($name = 'Semesters', $prefix = 'THM_CurriculumModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}
