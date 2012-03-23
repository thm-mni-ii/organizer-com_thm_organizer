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
	 * save
	 *
	 * saves either an edited or a new department and redirects to list view
	 *
	 * @return void
	 * @see JControllerForm
	 */
	public function save($key = null, $urlVar = null) {
		if(!thm_organizerHelper::isAdmin('department_edit')) thm_organizerHelper::noAccess ();

		$model = $this->getModel('department_edit');
		$result = $model->update();

		if ($result) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=department_manager', JText::_('COM_THM_ORGANIZER_DPM_SAVE_OK'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=department_manager', JText::_('COM_THM_ORGANIZER_DPM_SAVE_FAIL'), 'error');
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

		// iterate through ids to delete
		foreach($departmentIDs as $departmentID)

		{

			$table->load($departmentID);
			if (!$model->delete($departmentID)) {
				$error = true;
			}

		}

		if ($error) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=department_manager', JText::_('COM_THM_ORGANIZER_DPM_DELETE_FAIL'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=department_manager', JText::_('COM_THM_ORGANIZER_DPM_DELETE_OK'));
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
