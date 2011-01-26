<?php
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
class thm_organizersViewSemester extends JView
{
	function display($tpl = null)
	{
		$semester =& $this->get('Data');
		$isNew = ($semester->sid == 0);
		$text = $isNew ? JText::_( 'Neu' ) : JText::_( 'Edit' );
		JToolBarHelper::title( 'Semester: <small> [ '.$text.' ]</small>' );
		JToolBarHelper::save();
		if($isNew) JToolBarHelper::cancel();
		else JToolBarHelper::cancel( 'cancel', 'Close' );
	 	$this->assignRef('semester', $semester);
	 	
	 	parent::display($tpl);
	}
}
?>
	