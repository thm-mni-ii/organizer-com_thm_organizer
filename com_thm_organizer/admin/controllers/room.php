<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * Room Edit Controller
 */
class thm_organizersControllerroom extends JControllerForm
{
	/**
	 * add
	 *
	 * display the add (= edit) form
	 * @return void
	 */
	public function add() {
		if(!thm_organizerHelper::isAdmin('room_edit')) thm_organizerHelper::noAccess ();

		JRequest::setVar( 'view', 'room_edit' );

		parent::display();
	}

	/**
	 * edit
	 *

	 * display the edit form

	 * @return void

	 */

	public function edit(){
		if(!thm_organizerHelper::isAdmin('room_edit')) thm_organizerHelper::noAccess ();

		JRequest::setVar( 'view', 'room_edit' );

		parent::display();

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
		if(!thm_organizerHelper::isAdmin('room_edit')) thm_organizerHelper::noAccess();

		$model = $this->getModel('room_edit');
		$result = $model->update();

		if ($result) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=room_manager', JText::_('COM_THM_ORGANIZER_RM_SAVE_OK'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=room_manager', JText::_('COM_THM_ORGANIZER_RM_SAVE_FAIL'), 'error');
		}
	}
	/**
	 * delete
	 *
	 * deletes room entries specified by (maybe multiple) cids and redirects to list view
	 * @return void
	 */
	public function delete() {
		if(!thm_organizerHelper::isAdmin('room_edit')) thm_organizerHelper::noAccess ();


		$model = $this->getModel('room_edit');
		$roomIDs = JRequest::getVar('cid', array(), 'post', 'array');
		$table = JTable::getInstance('rooms', 'thm_organizerTable');

		$error = false;

		// iterate through ids to delete
		foreach($roomIDs as $roomID)

		{

			$table->load($roomID);
			if (!$model->delete($roomID)) {
				$error = true;
			}

		}

		if ($error) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=room_manager', JText::_('COM_THM_ORGANIZER_RM_DELETE_FAIL'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=room_manager', JText::_('COM_THM_ORGANIZER_RM_DELETE_OK'));
		}
	}
	/**
	 * cancel
	 *
	 * redirect, when editing is cancelled
	 * @return void
	 */
	public function cancel() {
		$this->setRedirect('index.php?option=com_thm_organizer&view=room_manager');
	}
}
