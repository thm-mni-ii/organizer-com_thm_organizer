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
class thm_organizersModelRoom_IP_List extends JModel
{
    var $data = null;

    function __construct()
    {
        parent::__construct();
        $this->data = new stdClass();
        $this->data->name = 'Monitor Manager';
        $this->getData();
    }

    function getData()
    {
        $query = "SELECT *
                  FROM #__giessen_scheduler_roomip AS rip
                    LEFT JOIN #__giessen_scheduler_semester AS s ON rip.sid = s.sid";
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