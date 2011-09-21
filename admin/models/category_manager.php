<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model category manager view
 * @description database abstraction file for the category manager view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
jimport( 'joomla.application.component.model' );
class thm_organizersModelcategory_manager extends JModel
{
    public $categories = null;

    public function __construct()
    {
        parent::__construct();
        $this->loadCategories();
    }

    /**
     * loadCategories
     *
     * retrieves information about saved categories and create links to the edit
     * view
     */
    private function loadCategories()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('toc.id AS id, toc.title AS title, globaldisplay AS global, reservesobjects AS reserves, c.title AS contentCat');
        $query->from('#__thm_organizer_categories AS toc');
        $query->innerJoin('#__categories AS c ON toc.contentCatID = c.id');
        $dbo->setQuery((string)$query);
        $categories = $dbo->loadAssocList();
        if(empty($categories))$this->categories = array();
        else
        {
            foreach($categories as $key => $value)
                $categories[$key]['link'] = 'index.php?option=com_thm_organizer&view=category_edit&categoryID='.$value['id'];
            $this->categories = $categories;
        }
        
    }
}