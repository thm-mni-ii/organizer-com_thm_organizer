<?php 
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewCategory_manager extends JView
{
	
    public function display($tpl = null)
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $this->addToolBar();
        thm_organizerHelper::addSubmenu('category_manager');

        $model = $this->getModel();
        $categories = $model->categories;
        $this->assignRef( 'categories', $categories );

        parent::display($tpl);
    }
	
    private function addToolBar()
    {
        JToolBarHelper::title( JText::_( 'Category Manager' ), 'generic.png' );
        $allowedActions = thm_organizerHelper::getActions('category_manager');
        if($allowedActions->get("core.admin") or $allowedActions->get("core.manage"))
        {
            if($allowedActions->get("core.admin") or $allowedActions->get("core.create"))
                JToolBarHelper::custom ('category.edit', 'new.png', 'new.png', JText::_('New'), false);
            if($allowedActions->get("core.admin") or $allowedActions->get("core.edit"))
                JToolBarHelper::custom ('category.edit', 'edit.png', 'edit.png', JText::_('Edit'), false);
            if($allowedActions->get("core.admin") or $allowedActions->get("core.delete"))
                JToolBarHelper::deleteList( JText::_('Are you sure you wish to delete the marked categories?'), 'category.delete');
        }
    }
	
	
}