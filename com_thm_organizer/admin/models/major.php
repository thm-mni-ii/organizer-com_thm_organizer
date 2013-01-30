<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerModelMajor
 * @description THM_OrganizerModelMajor component admin model
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizerModelMajor for component com_thm_organizer
 *
 * Class provides methods to deal with major
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerModelMajor extends JModelAdmin
{
	/**
	 * Method to overwrite save method
	 *
	 * @param   Array  $data  Data
	 *
	 * @return  Boolean
	 */
	public function save($data)
	{
		// Write the POST data to the database
		if (parent::save($data))
		{
			// Get the previous inserted major id
			$id = $this->getState($this->getName() . '.id');

			// Write the related semesters to the database
			self::saveSemester($id, JRequest::getVar('semesters'));

			if (!JRequest::getVar('id'))
			{
				// Insert a the root element of the tree
				self::createRootNode($id);
			}
			return true;
		}
	}

	/**
	 * Method to write the root element of the given major
	 *
	 * @param   Integer  $pk  Primarykey
	 *
	 * @return  void
	 */
	private function createRootNode($pk)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select the first semester of the given major
		$query = "SELECT * FROM #__thm_organizer_semesters_majors WHERE major_id = $pk AND semester_id = 1";
		$db->setQuery($query);
		$row = $db->loadObjectList();
		$firstSemester = $row[0]->id;

		// Writes the root element to the database
		$query = $db->getQuery(true);
		$query->insert('#__thm_organizer_assets_tree');
		$query->set("asset = 0");
		$query->set("parent_id = null");
		$query->set("depth = null");
		$query->set("lineage = 'none'");

		$db->setQuery($query);
		$db->query();
		$insertid = $db->insertid();

		// Maps the inserted root element to the first semester
		$query = $db->getQuery(true);
		$query->insert('#__thm_organizer_assets_semesters');
		$query->set("assets_tree_id = $insertid");
		$query->set("semesters_majors_id = $firstSemester");

		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Method to write the given semesters to the database
	 *
	 * @param   Integer  $id         Id
	 * @param   Array    $semesters  Semesters
	 *
	 * @return  void
	 */
	private function saveSemester($id, $semesters)
	{
		$db = JFactory::getDbo();
		$pk = JRequest::getVar('id');
		$query = $db->getQuery(true);

		if (isset($pk))
		{
			// Determine the current saved semesters
			$query->select("*");
			$query->from("#__thm_organizer_semesters_majors as sem_paths");
			$query->where("sem_paths.major_id = $pk");
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			// Iterate over each found semester
			foreach ($rows as $row)
			{
				// Is the current semester still part of the POST request?
				if (!in_array($row->semester_id, $semesters))
				{
					// Delete the semester
					$query = $db->getQuery(true);
					$query->delete("#__thm_organizer_semesters_majors");
					$query->where("major_id = $pk");
					$query->where("semester_id = $row->semester_id");

					$db->setQuery($query);
					$db->query($query);
				}
			}
		}

		// Iterate over each semester
		foreach ($semesters as $semester)
		{
			$query = $db->getQuery(true);
			$sem = intval($semester);

			// Writes the data to the database
			$query->insert('#__thm_organizer_semesters_majors');
			$query->set("semester_id = $sem");
			$query->set("major_id = $id");

			$db->setQuery($query);
			$db->query();
		}
	}

	/**
	 * Method to get the table
	 *
	 * @param   String  $type    Type  			(default: 'Majors')
	 * @param   String  $prefix  Prefix  		(default: 'THM_OrganizerTable')
	 * @param   Array   $config  Configuration  (default: 'Array')
	 *
	 * @return  JTable object
	 */
	public function getTable($type = 'Majors', $prefix = 'THM_OrganizerTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the form
	 *
	 * @param   Array    $data      Data  	   (default: Array)
	 * @param   Boolean  $loadData  Load data  (default: true)
	 *
	 * @return  A Form object
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_thm_organizer.major', 'major', array('control' => 'jform', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_thm_organizer.edit.major.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}
}
