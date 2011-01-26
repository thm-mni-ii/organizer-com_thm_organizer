<?php
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.model' );
 
/**
 * Category List Model
 *
 * @package    Giessen Scheduler
 * @subpackage Components
 */
class thm_organizersModelCategory_List extends JModel
{
    var $data = null;

    function __construct()
    {
        parent::__construct();
        $this->data = new stdClass();
        $this->data->name = 'Category Manager';
        $this->getData();
    }

    function getData()
    {
        $query = "SELECT * FROM #__giessen_scheduler_categories";
	$this->data->data = $this->_getList( $query );
        $this->getLinks();
    }

    function getLinks()
    {
        $dbo = $this->getDBO();
        $query = "SELECT name, admin_menu_link AS link
                  FROM #__components AS c
                  WHERE c.option = 'com_thm_organizer'
                    AND name != '".$this->data->name."'
                  ORDER BY name ASC;";
        $dbo->setQuery( $query );
        $result = $dbo->loadAssocList();
        if(count($result) >= 0) $this->data->links = $result;
        else $this->data->links = '';
    }
	
}