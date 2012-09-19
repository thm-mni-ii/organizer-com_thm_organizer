<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizersModelCourse
 * @description THM_OrganizersModelCourse component admin model
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizersModelCourse for component com_thm_organizer
 *
 * Class provides methods to deal with course
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizersModelCourse extends JModelAdmin
{
	/**
	 * Method to get the table
	 *
	 * @param   String  $type    Type  (default: 'assets')
	 * @param   String  $prefix  Type  (default: 'THM_OrganizerTable')
	 * @param   Array   $config  Type  (default: 'Array')
	 *
	 * @return  JTable object
	 */
	public function getTable($type = 'assets', $prefix = 'THM_OrganizerTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the form
	 *
	 * @param   Array    $data      Type  (default: Array)
	 * @param   Boolean  $loadData  Type  (default: true)
	 *
	 * @return  A Form object
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_thm_organizer.course', 'course', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to load the form data
	 *
	 * @return  Object
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_thm_organizer.edit.course.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}

	/**
	 * Overwritten save method
	 *
	 * @param   Array  $data  Data
	 *
	 * @return  Boolean
	 */
	public function save($data)
	{
		// Save the a course to the database
		if (parent::save($data))
		{
			// Get the inserted course id
			$id = $this->getState($this->getName() . '.id');

			// Write the responsible to the database
			self::saveResponsible($id, $data["responsible_id"]);

			// Write the lecturers to the database
			self::saveLecturer($id, JRequest::getVar('lecturers'));

			return true;
		}
	}

	/**
	 * Method to insert the given persons as course lecturers
	 *
	 * @param   Integer  $assetId    Asset id
	 * @param   Array    $lecturers  Lecturers
	 *
	 * @return  void
	 */
	private function saveLecturer($assetId, $lecturers)
	{
		$db = JFactory::getDbo();
		$pk = JRequest::getVar('id');

		// An existent database row will be updated
		if (isset($pk))
		{
			// Determine the saved lecturers
			$query = $db->getQuery(true);
			$query->select("*");
			$query->from("#__thm_organizer_lecturers_assets");
			$query->where("modul_id = $pk");
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			// Iterate over each found lecturer mapping
			foreach ($rows as $row)
			{
				// Is the current lecturer still part of the request?
				if (!in_array($row->lecturer_id, $lecturers))
				{
					// Delete the mapping
					$query = $db->getQuery(true);
					$query->delete("#__thm_organizer_lecturers_assets");
					$query->where("modul_id = $pk");
					$query->where("lecturer_id = $row->lecturer_id");
					$query->where("lecturer_type = 2");
					$db->setQuery($query);
					$db->query($query);
				}
			}
		}

		// Iterate over each lecuter of the POST request
		foreach ($lecturers as $lecturer)
		{
			$query = $db->getQuery(true);

			// Cast the value to a numeric
			$lec = intval($lecturer);

			// Write the actual lecutrer to the database
			$query->insert('#__thm_organizer_lecturers_assets');
			$query->set("modul_id = $assetId");
			$query->set("lecturer_id = $lec");
			$query->set("lecturer_type = 2");

			$db->setQuery($query);
			$db->query();
		}
	}

	/**
	 * Method to write the given person as a responsible to the database
	 *
	 * @param   Integer  $assetId     Asset id
	 * @param   Integer  $lecturerID  Lecturer id
	 *
	 * @return  Boolean
	 */
	private function saveResponsible($assetId, $lecturerID)
	{
		$db = JFactory::getDbo();
		$pk = JRequest::getVar('id');

		// Delete the mapping
		$query = $db->getQuery(true);
		$query->delete("#__thm_organizer_lecturers_assets");
		$query->where("modul_id = $assetId");
		$query->where("lecturer_type = 1");
		$db->setQuery($query);
		$db->query($query);

		$query = $db->getQuery(true);
		$query->insert('#__thm_organizer_lecturers_assets');
		$query->set("modul_id = $assetId");
		$query->set("lecturer_id = $lecturerID");
		$query->set("lecturer_type = 1");

		$db->setQuery($query);
		$db->query($query);

		return true;
	}
}
