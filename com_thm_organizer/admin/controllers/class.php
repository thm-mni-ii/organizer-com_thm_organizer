<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * class Edit Controller
 */
class thm_organizersControllerclass extends JControllerForm
{
	/**
	 * add
	 *
	 * display the add (= edit) form
	 * @return void
	 */
	public function add() {
		if(!thm_organizerHelper::isAdmin('class_edit')) thm_organizerHelper::noAccess ();
		JRequest::setVar( 'view', 'class_edit' );
		parent::display();
	}
	/**
	 * edit
	 *
	 * display the edit form
	 * @return void
	 */
	public function edit(){
		if(!thm_organizerHelper::isAdmin('class_edit')) thm_organizerHelper::noAccess ();
		JRequest::setVar( 'view', 'class_edit' );
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
		$jform				= JRequest::getVar('jform', null, null, null, 4);
		$class_name 		= trim($jform['name']);
		$class_alias		= trim($jform['alias']);
		$class_gpuntisid 	= trim($jform['gpuntisID']);
		$class_semester 	= trim($jform['semester']);
		$class_major 		= trim($jform['major']);
		$id					= trim($jform['id']);

		// check data for emptiness
		$errors_exist = false;
		$error_message = JText::_('COM_THM_ORGANIZER_CL_EDIT_ERROR').'<br />';

		if ($class_name == null || strlen($class_name) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_CL_EDIT_ERROR_NAME_EMPTY').'<br />';
		}
		if ($class_alias == null || strlen($class_alias) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_CL_EDIT_ERROR_ALIAS_EMPTY').'<br />';
		}
		if ($class_gpuntisid == null || strlen($class_gpuntisid) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_CL_EDIT_ERROR_GPUNTISID_EMPTY').'<br />';
		} else if (!$id) {
			// check for duplicate gpuntisid
			$model = $this->getModel('class_edit');
			if ($model->gpuntisidExists($class_gpuntisid)) {
				$errors_exist = true;
				$error_message .= JText::_('COM_THM_ORGANIZER_CL_EDIT_ERROR_GPUNTISID_ALREADY_EXISTS').'<br />';
			}
		}
		if ($class_semester == null || strlen($class_semester) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_CL_EDIT_ERROR_SEMESTER_EMPTY').'<br />';
		}
		if ($class_major == null || strlen($class_major) == 0) {
			$errors_exist = true;
			$error_message .= JText::_('COM_THM_ORGANIZER_CL_EDIT_ERROR_MAJOR_EMPTY').'<br />';
		}

		// redirect if errors occurred
		if ($errors_exist) {
			$session =& JFactory::getSession();
			$session->set('oldPost', $_POST);
				
			$this->setRedirect(('index.php?option=com_thm_organizer&view=class_edit'), JText::_($error_message), 'error');
			$this->redirect();
		}
	}
	/**
	 * save
	 *
	 * saves either an edited or a new class and redirects to list view
	 *
	 * @return void
	 * @see JControllerForm
	 */
	public function save($key = null, $urlVar = null) {
		if(!thm_organizerHelper::isAdmin('class_edit')) thm_organizerHelper::noAccess ();

		$this->myValidate();

		$model = $this->getModel('class_edit');
		$result = $model->update();

		if ($result) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=class_manager', JText::_('COM_THM_ORGANIZER_CL_SAVE_OK'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=class_manager', JText::_('COM_THM_ORGANIZER_CL_SAVE_FAIL'), 'error');
		}
	}
	/**
	 * delete
	 *
	 * deletes class entries specified by (maybe multiple) cids and redirects to list view
	 * @return void
	 */
	public function delete() {
		if(!thm_organizerHelper::isAdmin('class_edit')) thm_organizerHelper::noAccess ();

		$model = $this->getModel('class_edit');
		$classIDs = JRequest::getVar('cid', array(), 'post', 'array');
		$table = JTable::getInstance('classes', 'thm_organizerTable');
		$error = false;

		// iterate through ids to delete
		foreach($classIDs as $classID)
		{
			$table->load($classID);
			if (!$model->delete($classID)) {
				$error = true;
			}
		}

		if ($error) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=class_manager', JText::_('COM_THM_ORGANIZER_CL_DELETE_FAIL'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=class_manager', JText::_('COM_THM_ORGANIZER_CL_DELETE_OK'));
		}
	}
	/**
	 * cancel
	 *
	 * redirect, when editing is cancelled
	 * @return void
	 */
	public function cancel() {
		$this->setRedirect('index.php?option=com_thm_organizer&view=class_manager');
	}
}
