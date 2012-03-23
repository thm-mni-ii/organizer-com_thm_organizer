<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * Department Edit Model
 */
class thm_organizersModeldepartment_edit extends JModelAdmin
{
	/**
   	 * getAllGpuntisIds
   	 * 
   	 * returns a list of all gpuntisids
   	 * @return array containing all gpuntisids
	 * @return boolean false if no results were found
	 */
	public function getAllGpuntisIds() {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        
        // query to get count on gpuntisid
        $query->select('DISTINCT gpuntisID');
        $query->from('#__thm_organizer_departments');
        
        // request query
        $dbo->setQuery($query);

        return $dbo->loadResultArray();
	}
	/**

	 * update

	 *

	 * updates departments table row information

	 */

	public function update()

	{

		$data = $this->cleanRequestData();
		
		// saving

		$table = JTable::getInstance('departments', 'thm_organizerTable');

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
	 * deletes the department specified by id
	 * @param $id unsignedint id of entry to delete
	 */
	public function delete($id) {
		$table = JTable::getInstance('departments', 'thm_organizerTable');

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
		$data['gpuntisID'] 		= addslashes($data['gpuntisID']);
		$data['institution'] 	= addslashes($data['institution']);
		$data['campus'] 		= addslashes($data['campus']);
		$data['department'] 	= addslashes($data['department']);
		$data['subdepartment'] 	= addslashes($data['subdepartment']);
		

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
	public function getTable($type = 'departments', $prefix = 'thm_organizerTable', $config = array()) 
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
		$form = $this->loadForm('com_thm_organizer.department_edit', 'department_edit',
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
		$ids = JRequest::getVar('cid',  null, '', 'array');

		$id = (empty($ids))? JRequest::getVar('departmentID') : $ids[0];

		$item = ($id) ? parent::getItem($id) : $this->getTable();

		return $item;

	}
}