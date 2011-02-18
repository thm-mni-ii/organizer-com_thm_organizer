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
        $this->categories = array();
        $this->loadCategories();
        if(count($this->categories) > 0) $this->setcategoryEditLinks();
    }

    private function loadcategories()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_categories AS c');
        $query->leftJoin('#__thm_organizer_categories AS r ON r.id = c.id');
        $dbo->setQuery((string)$query);
        $categories = $dbo->loadAssocList();
        foreach($categories as $k => $v)
            if(empty($v['name']))$categories[$k]['name'] = $categories[$k]['id'];
        $this->categories = $categories;
    }

    //todo change categoryID to category in usages
    private function setcategoryEditLinks()
    {
        foreach($this->categories as $mKey => $mValue)
        {
            $this->categories[$mKey]['link'] = 'index.php?option=com_thm_organizer&view=category_edit&categoryID='.$mValue['id'];
        }
    }
}