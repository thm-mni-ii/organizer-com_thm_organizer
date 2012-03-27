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
	 * save
	 *
	 * saves either an edited or a new class and redirects to list view
	 *
	 * @return void
	 * @see JControllerForm
	 */
	public function save($key = null, $urlVar = null) {
		if(!thm_organizerHelper::isAdmin('class_edit')) thm_organizerHelper::noAccess ();

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
