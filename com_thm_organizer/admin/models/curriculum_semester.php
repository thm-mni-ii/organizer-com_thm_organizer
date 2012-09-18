<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerModelSemester
 * @description THM_OrganizerModelSemester component admin model
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizerModelSemester for component com_thm_organizer
 *
 * Class provides methods to deal with semester
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerModelSemester extends JModelAdmin
{
	/**
	 * Method to get the table
	 *
	 * @param   String  $type    Type  			(default: 'Semesters')
	 * @param   String  $prefix  Prefix  		(default: 'THM_CurriculumTable')
	 * @param   Array   $config  Configuration  (default: 'Array')
	 *
	 * @return  JTable object
	 */
	public function getTable($type = 'Semesters', $prefix = 'THM_CurriculumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the row form.
	 *
	 * @param	Array	 $data		Data for the form.														(default: Boolean)
	 * @param	Boolean	 $loadData  True if the form is to load its own data (default case), false if not.  (default: Array)
	 *
	 * @return	mixed	A JForm object on success, false on failure
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
