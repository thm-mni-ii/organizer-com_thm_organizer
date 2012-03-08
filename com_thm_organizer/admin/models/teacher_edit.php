<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * Teacher Edit Model
 */
class thm_organizersModelteacher_edit extends JModelAdmin
{
	/**
	 * gpuntisidExists
	 * 
	 * checks wether an entry with this gpuntisid already exists
	 * @param $gpuntisid String the gp untis id to check
	 * @return boolean true, if at least one entry with same gpuntisid exists
	 */
	public function gpuntisidExists($gpuntisid) {
		$dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        
        // query to get count on gpuntisid
        $query->select('COUNT(gpuntisID) AS appearance');
        $query->from('#__thm_organizer_teachers');
        $query->where("gpuntisID = '$gpuntisid'");
        
        // request query
        $dbo->setQuery((string)$query);
        $result = $dbo->loadResult();
        
        // return false on appearance = 0
        return !($result == 0);
	}
	/**
	 * update
	 *
	 * updates rooms table row information
	 */
	public function update()
	{
		$data = $this->cleanRequestData();
		
		// saving
		$table = JTable::getInstance('teachers', 'thm_organizerTable');
		if ($table->load($data['id'])) {  // update
			$success = $table->save($data);
		} else {  // create
			unset($data['id']);
			$table->save($data);
		}
		
		if ($success) {
			return ($data['id']) ? $data['id'] : true;
		} else {
			return false;
		}
	}
	/**
	 * delete
	 * 
	 * deletes the room specified by id
	 * @param $id unsignedint id of entry to delete
	 */
	public function delete($id) {
		$table = JTable::getInstance('teachers', 'thm_organizerTable');
		return $table->delete($id);
	}
	/**
	 * cleanRequestData
	 *
	 * filters the data from the request
	 *
	 * @return array cleaned request data
	 */
	protected function cleanRequestData()
	{
		$data = JRequest::getVar('jform', null, null, null, 4);
		$data['id'] 			= addslashes($data['id']);
		$data['name'] 			= addslashes($data['name']);
		$data['username'] 		= addslashes($data['username']);
		$data['gpuntisID'] 		= addslashes($data['gpuntisID']);
		$data['department'] 	= addslashes($data['department']);
		
		return $data;
	}
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	2.5
	 */
	public function getTable($type = 'teachers', $prefix = 'thm_organizerTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	2.5
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_thm_organizer.teacher_edit', 'teacher_edit',
		                        array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	2.5
	 */
	protected function loadFormData() 
	{
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$teacherIDs = JRequest::getVar('cid',  null, '', 'array');
		$teacherID = (empty($teacherIDs))? JRequest::getVar('teacherID') : $teacherIDs[0];
		$teacher = ($teacherID) ? parent::getItem($teacherID) : $this->getTable();
		return $teacher;
	}
}