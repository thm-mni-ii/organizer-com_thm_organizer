<?php
/**
 * @package  	Joomla.Administrator
 * @subpackage  com_THM_Organizer
 * @author   	Markus Baier <markus.baier@mni.fh-giessen.de>
 * @copyright	THM Mittelhessen 2011
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link     	http://www.mni.fh-giessen.de
 * @version		$Id$
 **/


defined('_JEXEC') or die('Restricte Access' );
jimport('joomla.application.component.modeladmin');

class THM_OrganizerModelSemester extends JModelAdmin {

	public function getTable($type = 'Semesters', $prefix = 'THM_OrganizerTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the row form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) {
		// Get the form.
		$form = $this->loadForm('com_thm_organizer.semester', 'semester', array('control' => 'jform', 'load_data' => $loadData));
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
	 * @since	1.6
	 */
	protected function loadFormData() {
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_thm_organizer.edit.semester.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
   
}