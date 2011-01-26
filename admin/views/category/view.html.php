<?php
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
class thm_organizersViewCategory extends JView
{
	function display($tpl = null)
	{
		$category =& $this->get('Data');
		$isNew = ($category->ecid == 0);
		$text = $isNew ? JText::_( 'Neu' ) : JText::_( 'Edit' );
		JToolBarHelper::title( 'Kategorie: <small> [ '.$text.' ]</small>' );
		JToolBarHelper::save();
		if($isNew) JToolBarHelper::cancel();
		else JToolBarHelper::cancel( 'cancel', 'Close' );
	 	$this->assignRef('category', $category);
	 	$model = $this->getModel();
	 	$usergroups = $model->getUserGroups();
		$this->assignRef('usergroups', JHTML::_('select.genericlist', $usergroups, 'access','size="1" class="inputbox"', 'id', 'name', $category->access ));
		
	 	$javascript = "onchange=\"javascript:if (document.forms[0].image.options[selectedIndex].value!='') {document.imagelib.src='../images/thm_organizer/categories/' + document.forms[0].image.options[selectedIndex].value} else {document.imagelib.src='../images/blank.png'}\"";
		$imagelist = JHTML::_('list.images', 'image', $category->ecimage, $javascript, '/images/thm_organizer/categories/' );
	 	$this->assignRef('imagelist', $imagelist);
	 	parent::display($tpl);
	}
}
?>
	