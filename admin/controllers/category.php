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
        $model = $this->getModel('category_edit');
        $result = $model->store();
        if($result)
        {
            $msg = JText::_("The category has been successfully saved.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager', $msg);
        }
        else
        {
            $msg = JText::_("An error occured while saving the category.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager', $msg, 'error');
        }
    }

    function save2new()
    {
        $model = $this->getModel('category_edit');
        $result = $model->store();
        if($result)
        {
            $msg = JText::_("The category has been successfully saved.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=category_edit', $msg);
        }
        else
        {
            $msg = JText::_("An error occured while saving the category.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=category_edit', $msg, 'error');
        }
    }
	
    function delete()
    {
        $model = $this->getModel('category_edit');
        $result = $model->delete();
        if($result)
        {
            $msg = JText::_('The selected categories have been successfully removed.');
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager', $msg);
        }
        else
        {
            $msg = JText::_('An error has while deleting the selected categories.');
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager', $msg, 'error');
        }
    }
	
    function cancel()
    {
        $this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager' );
    }
}