<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerAssets
 * @description THM_OrganizerControllerAssets component admin controller
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla controllerform library
jimport('joomla.application.component.controlleradmin');

require_once JPATH_COMPONENT_SITE . DS . 'helper/lsfapi.php';
require_once JPATH_COMPONENT_SITE . DS . 'models/details.php';

/**
 * Class THM_OrganizerControllerAssets for component com_thm_organizer
 *
 * Class provides methods perform actions for assets
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerControllerAssets extends JControllerAdmin
{
	/**
	 * Method to get the model
	 *
	 * @param   String  $name    Name	 (default: 'Asset')
	 * @param   String  $prefix  Prefix  (default: 'THM_OrganizerModel')
	 *
	 * @return  Object  The model
	 */
	public function getModel($name = 'Asset', $prefix = 'THM_OrganizerModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Method to import courses from LSF
	 *
	 * @return  void
	 */
	public function import()
	{
		$model = $this->getModel('assets');
		$majors = JRequest::getVar("cid");
		$model->import($majors);
	}

	/**
	 * AJAX-callback which returns all related semester-ids from a given asset-id
	 *
	 * @return  void
	 */
	public function getSemester()
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

		// Get the current selected major-id
		$majorId = JRequest::getVar('id');

		$query = $db->getQuery(true);

		// Get the semester-ids from the database
		$query->select("semesters.id as id");
		$query->select("semesters.name as name");
		$query->from('#__thm_organizer_semesters_majors as semester_majors');
		$query->join('inner', '#__thm_organizer_semesters as semesters ON semester_majors.semester_id = semesters.id');
		$query->where("major_id = $majorId");

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Return the string which includes the determined semester ids
		echo json_encode($rows);
		$mainframe->close();
	}

	/**
	 * Method to get the module code
	 *
	 * @return  void
	 */
	public function getModuleCode()
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$params = JComponentHelper::getParams('com_thm_organizer');

		// Get the current selected major-id
		$assetID = JRequest::getVar('id');

		$query = $db->getQuery(true);

		// Get the semester-ids from the database
		$query->select("*");
		$query->from('#__thm_organizer_assets as assets');
		$query->where("id = $assetID");

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (!empty($rows[0]->lsf_course_code))
		{
			echo $params->get("default_ecolloboration_link") . $rows[0]->lsf_course_code;
		}
		else
		{
			echo $params->get("default_ecolloboration_link") . $rows[0]->his_course_code;
		}

		$mainframe->close();
	}
}
