<?php
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelthm_organizers extends JModel
{
    var $data = null;

    function __construct()
    {
        parent::__construct();
        $this->data = new stdClass();
        $this->data->name = 'Giessen Scheduler';
        $this->getData();
    }

    function getData()
    {
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