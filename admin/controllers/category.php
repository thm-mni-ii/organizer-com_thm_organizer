<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
class thm_organizersControllerCategory extends JController
{
    function display(){  parent::display(); }
	
    function __construct()
    {
        parent::__construct();
        $this->registerTask( 'add', 'edit' );
    }
	
    function edit()
    {
        JRequest::setVar( 'view', 'category_edit' );
        parent::display();
    }
	
    function save()
    {
        $model = $this->getModel('category');
        $result = $model->store();
        $this->setRedirect( 'index.php?option=com_thm_organizer&view=category_list', $result);
    }
	
    function remove()
    {
        $model = $this->getModel('category');
        $res = $model->delete();
        if(isset($res) and $res == true)
            $msg = JText::_('Category has been successfully removed.');
        else
            $msg = JText::_('An error has occurred.');
        $this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager', $msg);
    }
	
    function cancel()
    {
        $msg = JText::_('Cancelled');
        $this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager', $msg );
    }
}