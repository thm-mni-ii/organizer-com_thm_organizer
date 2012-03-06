<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * Description Edit Controller
 */
class thm_organizersControllerdescription extends JControllerForm
{	
	/**
	 * add
	 * 
	 * display the add (= edit) form
	 * @return void
	 */
	public function add() {
		if(!thm_organizerHelper::isAdmin('description_edit')) thm_organizerHelper::noAccess ();
		JRequest::setVar( 'view', 'description_edit' );
		parent::display();
	}
	/**
	 * edit
	 * 
	 * display the edit form
	 * @return void
	 */
	public function edit(){
		if(!thm_organizerHelper::isAdmin('description_edit')) thm_organizerHelper::noAccess ();
		JRequest::setVar( 'view', 'description_edit' );
		parent::display();
	}
	/**
	 * save
	 * 
	 * saves either an edited or a new description and redirects to list view
	 * 
	 * @return void
	 * @see JControllerForm
	 */
	public function save($key = null, $urlVar = null) {
		if(!thm_organizerHelper::isAdmin('description_edit')) thm_organizerHelper::noAccess ();
        $model = $this->getModel('description_edit');
        $result = $model->update();
		
		if ($result) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=description_manager', JText::_('COM_THM_ORGANIZER_DS_SAVE_OK'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=description_manager', JText::_('COM_THM_ORGANIZER_DS_SAVE_FAIL'), 'error');
		}
	}
	/**
	 * delete
	 * 
	 * deletes description entries specified by (maybe multiple) cids and redirects to list view
	 * @return void
	 */
	public function delete() {
		if(!thm_organizerHelper::isAdmin('description_edit')) thm_organizerHelper::noAccess ();
		
		$model = $this->getModel('description_edit');
		$descriptionIDs = JRequest::getVar('cid', array(), 'post', 'array');
		$table = JTable::getInstance('descriptions', 'thm_organizerTable');
		$error = false;
		
		foreach($descriptionIDs as $descriptionID)
		{
			$table->load($descriptionID);
			if (!$model->delete($descriptionID)) {
				$error = true;
			}
		} 
		
		if ($error) {
			$this->setRedirect('index.php?option=com_thm_organizer&view=description_manager', JText::_('COM_THM_ORGANIZER_DS_DELETE_FAIL'));
		} else {
			$this->setRedirect('index.php?option=com_thm_organizer&view=description_manager', JText::_('COM_THM_ORGANIZER_DS_DELETE_OK'));
		}
	}
	/**
	 * cancel
	 * 
	 * redirect, when editing is cancelled
	 * @return void
	 */
	public function cancel() {
		$this->setRedirect('index.php?option=com_thm_organizer&view=description_manager');
	}
}
