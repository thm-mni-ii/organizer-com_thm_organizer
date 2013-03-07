<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMajor
 * @description THM_OrganizerModelMajor component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizerModelMajor for component com_thm_organizer
 *
 * Class provides methods to deal with major
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
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
			$majorID = $this->getState($this->getName() . '.id');

			// Write the related semesters to the database
			self::saveSemester($majorID, JRequest::getVar('semesters'));

			if (!JRequest::getVar('id'))
			{
				// Insert a the root element of the tree
				self::createRootNode($majorID);
			}
			return true;
		}
	}

	/**
	 * Method to write the root element of the given major
	 *	
	 * @param   Integer  $majorID  Primarykey
	 *
	 * @return  void
	 */
	private function createRootNode($majorID)
	{
		$dbo = JFactory::getDbo();

		// Select the first semester of the given major
		$selectQuery = $dbo->getQuery(true);
		$selectQuery->select('*');
		$selectQuery->from("#__thm_organizer_semesters_majors");
		$selectQuery->where("major_id = $majorID");
		$selectQuery->where("semester_id = 1");
		
		$dbo->setQuery((string) $selectQuery);
		$row = $dbo->loadObjectList();
		$firstSemester = $row[0]->id;

		// Writes the root element to the database
		$insertATQuery = $dbo->getQuery(true);
		$insertATQuery->insert('#__thm_organizer_assets_tree');
		$insertATQuery->set("asset = 0");
		$insertATQuery->set("parent_id = null");
		$insertATQuery->set("depth = null");
		$insertATQuery->set("lineage = 'none'");

		$dbo->setQuery((string) $insertATQuery);
		$dbo->query();
		$insertid = $dbo->insertid();

		// Maps the inserted root element to the first semester
		$insertASemQuery = $dbo->getQuery(true);
		$insertASemQuery->insert('#__thm_organizer_assets_semesters');
		$insertASemQuery->set("assets_tree_id = $insertid");
		$insertASemQuery->set("semesters_majors_id = $firstSemester");

		$dbo->setQuery((string) $insertASemQuery);
		$dbo->query();
	}

	/**
	 * Method to write the given semesters to the database
	 *
	 * @param   Integer  $majorID    Id
	 * @param   Array    $semesters  Semesters
	 *
	 * @return  void
	 */
	private function saveSemester($majorID, $semesters)
	{
		$dbo = JFactory::getDbo();
		$requestID = JRequest::getVar('id');
		$query = $dbo->getQuery(true);

		if (isset($requestID))
		{
			// Determine the current saved semesters
			$query->select("*");
			$query->from("#__thm_organizer_semesters_majors as sem_paths");
			$query->where("sem_paths.major_id = $requestID");
			$dbo->setQuery((string) $query);
			$rows = $dbo->loadObjectList();

			// Iterate over each found semester
			foreach ($rows as $row)
			{
				// Is the current semester still part of the POST request?
				if (!in_array($row->semester_id, $semesters))
				{
					// Delete the semester
					$query = $dbo->getQuery(true);
					$query->delete("#__thm_organizer_semesters_majors");
					$query->where("major_id = $requestID");
					$query->where("semester_id = $row->semester_id");

					$dbo->setQuery((string) $query);
					$dbo->query($query);
				}
			}
		}

		// Iterate over each semester
		foreach ($semesters as $semester)
		{
			$query = $dbo->getQuery(true);
			$sem = intval($semester);

			// Writes the data to the database
			$query->insert('#__thm_organizer_semesters_majors');
			$query->set("semester_id = $sem");
			$query->set("major_id = $majorID");

			$dbo->setQuery((string) $query);
			$dbo->query();
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
