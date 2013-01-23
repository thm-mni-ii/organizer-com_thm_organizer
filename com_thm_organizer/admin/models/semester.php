<?php
/**
 * @version     v0.1.0
 * @category	Joomla component
 * @package  	THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @author   	Markus Baier, <markus.baier@mni.thm.de>
 * @copyright	2011 TH Mittelhessen
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link     	www.mni.mni.de
 */


defined('_JEXEC') or die('Restricte Access' );
jimport('joomla.application.component.modeladmin');

class THM_OrganizerModelSemester extends JModelAdmin
{
	public function getTable($type = 'Semesters', $prefix = 'THM_OrganizerTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the row form.
	 *
	 * @param	array	 $data		Data for the form.
	 * @param	boolean	 $loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	 A JForm object on success, false on failure
	 * 
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
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
	 * 
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_thm_organizer.edit.semester.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
}
