<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        view category editor
 * @description provides a form for editing information about an event category
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5.0
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );

class thm_organizersViewcategory_edit extends JView
{
    function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();

        $this->id = $model->id;
        $this->title = $model->title;
        $this->description = $model->description;
        $this->global = $model->global;
        $this->reserves = $model->reserves;
        $this->contentCat = $model->contentCat;
        $this->contentCategories = $model->contentCategories;
        if(count($this->contentCategories))$this->addCategorySelectionBox();
        
        
        $title = JText::_('COM_THM_ORGANIZER').': ';
        $title .= ($this->id)? JText::_('JTOOLBAR_NEW') : JText::_('JTOOLBAR_EDIT');
        $title .= " ".JText::_('JCATEGORY');        
        JToolBarHelper::title( $title, 'mni' );
        $this->addToolBar();
        
        parent::display($tpl);
    }

    /**
     * addToolBar
     *
     * generates buttons for user interaction
     */
    private function addToolBar()
    {
        JToolBarHelper::save('category.save', 'JTOOLBAR_SAVE');
        JToolBarHelper::custom('category.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        JToolBarHelper::cancel( 'category.cancel', 'JTOOLBAR_CLOSE');
    }
    
    /**
     * addCategorySelectionBox
     *
     * creates a select box for the associated content category
     */
    private function addCategorySelectionBox()
    {
        $attributes = array( 'id' => 'thm_organizer_se_content_cat_box',
                             'class' => 'thm_organizer_se_content_cat_box',
                             'size' => '1',
                             'onChange' => "changeCategoryInformation();");
        $this->contentCatBox =  JHTML::_('select.genericlist', $this->contentCategories, 'contentCat', $attributes, 'id', 'title', $this->contentCat);

    }
}
	