<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * Department Edit Controller
 */
class thm_organizersControllerdepartment extends JControllerForm
{	
	/**
	 * add
	 * 
	 * display the add (= edit) form
	 * @return void
	 */
	public function add() {
		if(!thm_organizerHelper::isAdmin('department_edit')) thm_organizerHelper::noAccess ();
		JRequest::setVar( 'view', 'department_edit' );
		parent::display();
	}
	/**
	 * edit
	 * 
	 * display the edit form
	 * @return void
	 */
	public function edit(){
		if(!thm_organizerHelper::isAdmin('department_edit')) thm_organizerHelper::noAccess ();
		JRequest::setVar( 'view', 'department_edit' );
		parent::display();
	}
	/**
	 * myValdiate
	 * 
	 * looks for empty entries in the form
	 * @return void
	 */
	protected function myValidate() {
		// get entered data
		$jform			= JRequest::getVar('jform', null, null, null, 4);
		$id 			= trim($jform['id']);
		$gpuntisid 		= trim($jform['gpuntisID']);
		$name 			= trim($jform['name']);
		$institution 	= trim($jform['institution']);
		$campus 		= trim($jform['campus']);
		$department 	= trim($jform['department']);
		
		// check data for emptiness
		$errors_exist = false;
		$error_message = JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_EDIT_ERROR').'<br />';
	
		if ($gpuntisid == null || strlen($gpuntisid) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_EDIT_ERROR_GPUNTISID_EMPTY').'<br />';
		}
		else if (!$id)
		{
			// check for duplicate gpuntisid
			$model = $this->getModel('department_edit');
			if ($model->gpuntisidExists($gpuntisid))
			{
				$errors_exist = true;
				$error_message .= JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_EDIT_ERROR_GPUNTISID_ALREADY_EXISTS').'<br />';
			}
		}
		if ($name == null || strlen($name) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_EDIT_ERROR_NAME_EMPTY').'<br />';
		}
		if ($institution == null || strlen($institution) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_EDIT_ERROR_INSTITUTION_EMPTY').'<br />';
		}
		if ($campus == null || strlen($campus) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_EDIT_ERROR_CAMPUS_EMPTY').'<br />';
		}
		if ($department == null || strlen($department) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_EDIT_ERROR_DEPARTMENT_EMPTY').'<br />';
		}
		
		// redirect if errors occurred
		if ($errors_exist) {
			$session =& JFactory::getSession();
			$session->set('oldPost', $_POST);
			
			$this->setRedirect(('index.php?option=com_thm_organizer&view=department_edit'), JText::_($error_message), 'error');
			$this->redirect();
		}
	}
	/**
	 * save
	 * 
	 * saves either an edited or a new department and redirects to list view
	 * 
	 * @return void
	 * @see JControllerForm
	 */
	public function save($key = null, $urlVar = null) {
		if(!thm_organizerHelper::isAdmin('department_edit')) thm_organizerHelper::noAccess ();
		
		$this->myValidate();
		
        $model = $this->getModel('department_edit');
        $result = $model->update();
		
		if ($result) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=department_manager', JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_SAVE_OK'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=department_manager', JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_SAVE_FAIL'), 'error');
		}
	}
	/**
	 * delete
	 * 
	 * deletes department entries specified by (maybe multiple) cids and redirects to list view
	 * @return void
	 */
	public function delete() {
		if(!thm_organizerHelper::isAdmin('department_edit')) thm_organizerHelper::noAccess ();
		
		$model 			= $this->getModel('department_edit');
		$departmentIDs 	= JRequest::getVar('cid', array(), 'post', 'array');
		$table 			= JTable::getInstance('departments', 'thm_organizerTable');
		$error 			= false;
		
		foreach($departmentIDs as $departmentID)
		{
			$table->load($departmentID);
			if (!$model->delete($departmentID)) {
				$error = true;
			}
		} 
		
		if ($error) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=department_manager', JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_DELETE_FAIL'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=department_manager', JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_DELETE_OK'));
		}
	}
	/**
	 * cancel
	 * 
	 * redirect, when editing is cancelled
	 * @return void
	 */
	public function cancel() {
		$this->setRedirect('index.php?option=com_thm_organizer&view=department_manager');
	}
}
