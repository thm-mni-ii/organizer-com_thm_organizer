<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        monitor editor view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
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

        $id = $model->id;
        $this->assignRef( 'id', $id );
        $title = $model->title;
        $this->assignRef( 'title', $title );
        $description = $model->description;
        $this->assignRef( 'description', $description );
        $global = $model->global;
        $this->assignRef( 'global', $global );
        $reserves = $model->reserves;
        $this->assignRef( 'reserves', $reserves );
        $temp = $model->temp;
        $this->assignRef( 'temp', $temp );

        $contentCat = $model->contentCat;
        $contentCategories = $model->contentCategories;
        if(count($contentCategories))
        {
            $attributes = array( 'id' => 'thm_organizer_se_content_cat_box',
                                 'class' => 'thm_organizer_se_content_cat_box',
                                 'size' => '1' );
            $contentCatBox =  JHTML::_('select.genericlist', $contentCategories, 'contentCat', $attributes, 'id', 'title', $contentCat);
            $this->assignRef('contentCatBox', $contentCatBox);
        }

        $isNew = (!$id)? true : false;
        $allowedActions = thm_organizerHelper::getActions('category_edit');
        if($allowedActions->get("core.admin") or $allowedActions->get("core.manage"))
            $this->addToolBar($allowedActions, $isNew);

        parent::display($tpl);
    }

    private function addToolBar($allowedActions, $isNew = true)
    {
        $canSave = false;
        if($isNew)
        {
            $titleText = JText::_( 'Category Manager: Add a New Category' );
            if($allowedActions->get("core.create") or $allowedActions->get("core.edit"))
                $canSave = true;
        }
        else
        {
            $titleText = JText::_( 'Category Manager: Edit an Existing Category' );
            if($allowedActions->get("core.edit")) $canSave = true;
        }
        JToolBarHelper::title( $titleText, 'generic.png' );
        if($canSave) JToolBarHelper::save('category.save', 'JTOOLBAR_SAVE');
        if($allowedActions->get("core.create"))
            JToolBarHelper::custom('category.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        JToolBarHelper::cancel( 'category.cancel', 'JTOOLBAR_CANCEL');
    }
}
	