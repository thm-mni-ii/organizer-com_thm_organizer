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
 * @version     1.7.0
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewcategory_edit extends JView
{
    function display($tpl = null)
    {
        $document = & JFactory::getDocument();
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
        $this->access = thm_organizerHelper::isAdmin('category_edit');
        $titleText = ($this->id)?
            JText::_( 'COM_THM_ORGANIZER_CAT_EDIT_TITLE' ) : JText::_( 'COM_THM_ORGANIZER_CAT_EDIT_TITLE_NEW' );
        JToolBarHelper::title( $titleText, 'generic.png' );
        if($this->access) $this->addToolBar();

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
	