<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * HelloWorld Model
 */
class thm_organizersModelroom_edit extends JModelAdmin
{
	/**
	 * update
	 *
	 * updates rooms table row information
	 */
	public function update()
	{
		$data = $this->cleanRequestData();
		
		// saving
		$table = JTable::getInstance('rooms', 'thm_organizerTable');
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
		$table = JTable::getInstance('rooms', 'thm_organizerTable');
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
		$data['managerID'] 		= addslashes($data['managerID']);
		$data['gpuntisID'] 		= addslashes($data['gpuntisID']);
		$data['name'] 			= addslashes($data['name']);
		$data['alias'] 			= addslashes($data['alias']);
		$data['floor'] 			= addslashes($data['floor']);
		$data['capacity'] 		= addslashes($data['capacity']);
		$data['campusID'] 		= addslashes($data['campusID']);
		$data['buildingID'] 	= addslashes($data['buildingID']);
		$data['descriptionID'] 	= addslashes($data['descriptionID']);
		
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
	public function getTable($type = 'rooms', $prefix = 'thm_organizerTable', $config = array()) 
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
		$form = $this->loadForm('com_thm_organizer.room_edit', 'room_edit',
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
		$roomIDs = JRequest::getVar('cid',  null, '', 'array');
		$roomID = (empty($roomIDs))? JRequest::getVar('roomID') : $roomIDs[0];
		$room = ($roomID) ? parent::getItem($roomID) : $this->getTable();
		return $room;
	}
}