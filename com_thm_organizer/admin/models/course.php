<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerModelCourse
 * @description THM_OrganizerModelCourse component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizerModelCourse for component com_thm_organizer
 * Class provides methods to deal with course
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelCourse extends JModelAdmin
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
			$courseID = $this->getState($this->getName() . '.id');

			// Write the responsible to the database
			self::saveResponsible($courseID, $data["responsible_id"]);

			// Write the lecturers to the database
			self::saveLecturer($courseID, JRequest::getVar('lecturers'));

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
		$dbo = JFactory::getDbo();
		$moduleID = JRequest::getVar('id');

		// An existent database row will be updated
		if (isset($moduleID))
		{
			// Determine the saved lecturers
			$query = $dbo->getQuery(true);
			$query->select("*");
			$query->from("#__thm_organizer_lecturers_assets");
			$query->where("modul_id = $moduleID");
			$dbo->setQuery($query);
			$rows = $dbo->loadObjectList();

			// Iterate over each found lecturer mapping
			foreach ($rows as $row)
			{
				// Is the current lecturer still part of the request?
				if (!in_array($row->lecturer_id, $lecturers))
				{
					// Delete the mapping
					$query = $dbo->getQuery(true);
					$query->delete("#__thm_organizer_lecturers_assets");
					$query->where("modul_id = $moduleID");
					$query->where("lecturer_id = $row->lecturer_id");
					$query->where("lecturer_type = 2");
					$dbo->setQuery($query);
					$dbo->query($query);
				}
			}
		}

		// Iterate over each lecuter of the POST request
		foreach ($lecturers as $lecturer)
		{
			$query = $dbo->getQuery(true);

			// Cast the value to a numeric
			$lec = intval($lecturer);

			// Write the actual lecutrer to the database
			$query->insert('#__thm_organizer_lecturers_assets');
			$query->set("modul_id = $assetId");
			$query->set("lecturer_id = $lec");
			$query->set("lecturer_type = 2");

			$dbo->setQuery($query);
			$dbo->query();
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
		$dbo = JFactory::getDbo();

		// Delete the mapping
		$deleteQuery = $dbo->getQuery(true);
		$deleteQuery->delete("#__thm_organizer_lecturers_assets");
		$deleteQuery->where("modul_id = $assetId");
		$deleteQuery->where("lecturer_type = 1");
		$dbo->setQuery((string) $deleteQuery);
		$dbo->query();

		$insertQuery = $dbo->getQuery(true);
		$insertQuery->insert('#__thm_organizer_lecturers_assets');
		$insertQuery->set("modul_id = $assetId");
		$insertQuery->set("lecturer_id = $lecturerID");
		$insertQuery->set("lecturer_type = 1");

		$dbo->setQuery($insertQuery);
		$dbo->query();

		return true;
	}
}
