<?php
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
class thm_organizersViewRoom_IP extends JView
{
	function display($tpl = null)
	{
		$model = $this->getModel();
		$room_ip =& $this->get('Data');
		$isNew = ($room_ip->ip == '');
		$text = $isNew ? JText::_( 'Neu' ) : JText::_( 'Edit' );
		JToolBarHelper::title( 'Room IP: <small> [ '.$text.' ]</small>' );
		JToolBarHelper::save();
		if($isNew) JToolBarHelper::cancel();
		else JToolBarHelper::cancel( 'cancel', 'Close' );
	 	$this->assignRef('room_ip', $room_ip);
	 	$semesters = $model->getSemesters();
	 	$semesterbox =  JHTML::_('select.genericlist', $semesters, 'semester', 'id="semester" class="inputbox" size="1"', 'sid', 'name', $room_ip->sid);	
	 	$this->assignRef('semesterbox', $semesterbox);
	 	parent::display($tpl);
	}
}
?>
	