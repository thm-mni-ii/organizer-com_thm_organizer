<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerSoapqueries
 * @description THM_OrganizerControllerSoapqueries component admin controller
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Class THM_OrganizerControllerSoapqueries for component com_thm_organizer
 *
 * Class provides methods perform actions for soapqueries
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerControllerSoapqueries extends JControllerAdmin
{
	/**
	 * Method to get the model
	 *
	 * @param   String  $name    Name	 (default: 'soapqueries')
	 * @param   String  $prefix  Prefix  (default: 'THM_OrganizerModel')
	 *
	 * @return  Object
	 */
	public function getModel($name = 'soapqueries', $prefix = 'THM_OrganizerModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}
