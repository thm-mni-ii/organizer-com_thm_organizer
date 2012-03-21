<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * Room Edit Controller
 */
class thm_organizersControllerteacher extends JControllerForm
{	
	/**
	 * add
	 * 
	 * display the add (= edit) form
	 * @return void
	 */
	public function add() {
		if(!thm_organizerHelper::isAdmin('teacher_edit')) thm_organizerHelper::noAccess ();
		JRequest::setVar( 'view', 'teacher_edit' );
		parent::display();
	}
	/**
	 * edit
	 * 
	 * display the edit form
	 * @return void
	 */
	public function edit(){
		if(!thm_organizerHelper::isAdmin('teacher_edit')) thm_organizerHelper::noAccess ();
		JRequest::setVar( 'view', 'teacher_edit' );
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
		$jform		= JRequest::getVar('jform', null, null, null, 4);
		$name 		= trim($jform['name']);
		$gpuntisid 	= trim($jform['gpuntisID']);
		$id			= trim($jform['id']);
		
		// check data for emptiness
		$errors_exist = false;
		$error_message = JText::_('COM_THM_ORGANIZER_TM_EDIT_ERROR').'<br />';
		
		if ($name == null || strlen($name) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_TM_EDIT_ERROR_NAME_EMPTY').'<br />';	
		}
		if ($gpuntisid == null || strlen($gpuntisid) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_TM_EDIT_ERROR_GPUNTISID_EMPTY').'<br />';
		}
		else if (!$id)
		{
			// check for duplicate gpuntisid
			$model = $this->getModel('teacher_edit');
			if ($model->gpuntisidExists($gpuntisid))
			{
				$errors_exist = true;
				$error_message .= JText::_('COM_THM_ORGANIZER_TM_EDIT_ERROR_GPUNTISID_ALREADY_EXISTS').'<br />';
			}
		}
		
		// redirect if errors occurred
		if ($errors_exist) {
			$session =& JFactory::getSession();
			$session->set('oldPost', $_POST);
			
			$this->setRedirect(('index.php?option=com_thm_organizer&view=teacher_edit'), JText::_($error_message), 'error');
			$this->redirect();
		}
	}
	/**
	 * save
	 * 
	 * saves either an edited or a new room and redirects to list view
	 * 
	 * @return void
	 * @see JControllerForm
	 */
	public function save($key = null, $urlVar = null) {
		if(!thm_organizerHelper::isAdmin('teacher_edit')) thm_organizerHelper::noAccess ();
		
		$this->myValidate();
		
        $model = $this->getModel('teacher_edit');
        $result = $model->update();
		
		if ($result) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=teacher_manager', JText::_('COM_THM_ORGANIZER_TM_SAVE_OK'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=teacher_manager', JText::_('COM_THM_ORGANIZER_TM_SAVE_FAIL'), 'error');
		}
	}
	/**
	 * delete
	 * 
	 * deletes room entries specified by (maybe multiple) cids and redirects to list view
	 * @return void
	 */
	public function delete() {
		if(!thm_organizerHelper::isAdmin('teacher_edit')) thm_organizerHelper::noAccess ();
		
		$model = $this->getModel('teacher_edit');
		$teacherIDs = JRequest::getVar('cid', array(), 'post', 'array');
		$table = JTable::getInstance('teachers', 'thm_organizerTable');
		$error = false;
		
		foreach($teacherIDs as $teacherID)
		{
			$table->load($teacherID);
			if (!$model->delete($teacherID)) {
				$error = true;
			}
		} 
		
		if ($error) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=teacher_manager', JText::_('COM_THM_ORGANIZER_TM_DELETE_FAIL'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=teacher_manager', JText::_('COM_THM_ORGANIZER_TM_DELETE_OK'));
		}
	}
	/**
	 * cancel
	 * 
	 * redirect, when editing is cancelled
	 * @return void
	 */
	public function cancel() {
		$this->setRedirect('index.php?option=com_thm_organizer&view=teacher_manager');
	}
}
