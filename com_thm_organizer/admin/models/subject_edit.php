<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSubject_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizerModelSubject_Edit for component com_thm_organizer
 * Class provides methods to deal with course
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSubject_Edit extends JModelAdmin
{
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
		$form = $this->loadForm('com_thm_organizer.course', 'subject_edit', array('control' => 'jform', 'load_data' => $loadData));

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
        $subjectIDs = JRequest::getVar('cid',  null, '', 'array');
        $subjectID = (empty($subjectIDs))? JRequest::getVar('subjectID') : $subjectIDs[0];
		return $this->getItem($subjectID);
	}

	/**
	 * Method to get the table
	 *
	 * @param   String  $type    Type  (default: 'assets')
	 * @param   String  $prefix  Type  (default: 'THM_OrganizerTable')
	 * @param   Array   $config  Type  (default: 'Array')
	 *
	 * @return  JTable object
	 */
	public function getTable($type = 'subjects', $prefix = 'THM_OrganizerTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
}
