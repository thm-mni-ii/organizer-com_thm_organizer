<?php
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.model' );
 
/**
 * Room IP List Model
 *
 * @package    Giessen Scheduler
 * @subpackage Components
 */
class thm_organizersModelcategory_manager extends JModel
{
    public $categories = null;

    public function __construct()
    {
        parent::__construct();
        $this->loadCategories();
        if(count($this->categories) > 0) $this->setCategoryEditLinks();
    }

    private function loadCategories()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('toc.id AS id, toc.title AS title, globaldisplay AS global, reservesobjects AS reserves, c.title AS contentCat');
        $query->from('#__thm_organizer_categories AS toc');
        $query->innerJoin('#__categories AS c ON toc.contentCatID = c.id');
        $dbo->setQuery((string)$query);
        $categories = $dbo->loadAssocList();
        if(empty($categories)) $this->categories = array();
        else $this->categories = $categories;
    }

    //todo change categoryID to category in usages
    private function setCategoryEditLinks()
    {
        foreach($this->categories as $key => $value)
        {
            $this->categories[$key]['link'] = 'index.php?option=com_thm_organizer&view=category_edit&categoryID='.$value['id'];
        }
    }
}