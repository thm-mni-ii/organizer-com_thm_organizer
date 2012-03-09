<?php
/**
 * @version		$Id: sysinfo.php 22030 2011-09-02 12:41:22Z chdemko $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @since		1.6
 */
class thm_organizersModelroom_manager extends JModelList
{
    public $campuses = null;
    public $buildings = null;
    public $types = null;

    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] =
                array(
                    'name', 'r.name',
                    'institution', 'r.institution',
                    'campus', 'r.campus',
                    'building', 'r.building',
                    'type', 'desc.typeID'
                );
        }
        parent::__construct($config);

        // get lists for filters
        $errorOccurred = false;
        
        $this->campuses = $this->getResources('campuses');
        
        if (!$this->campuses) $errorOccurred = true;
        
        if($this->getState('filter.campus') && $this->getState('filter.campus') != '*') 
        {
        	$this->buildings = $this->getResources('buildings');
        	if (!$this->buildings) $errorOccurred = true;
        }
        
        $this->types = $this->getResources('types');
        if (!$this->types) $errorOccurred = true;
        
        if($this->getState('filter.type') && $this->getState('filter.type') != '*') 
        {
        	$this->details = $this->getResources('details');
        	if (!$this->details) $errorOccurred = true;
        }
        
        if ($errorOccurred)
        {
        	JError::raiseNotice(667, JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA_NO_RESULTS'));
        }
    }

    /**
     *
     * @param string $ordering
     * @param string $direction
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $institution = $this->getUserStateFromRequest($this->context.'.filter.institution', 'filter_institution');
        $this->setState('filter.institution', $institution);

        $campus = $this->getUserStateFromRequest($this->context.'.filter.campus', 'filter_campus');
        $this->setState('filter.campus', $campus);

        $building = $this->getUserStateFromRequest($this->context.'.filter.building', 'filter_building');
        $this->setState('filter.building', $building);

        $type = $this->getUserStateFromRequest($this->context.'.filter.type', 'filter_type');
        $this->setState('filter.type', $type);

        $detail = $this->getUserStateFromRequest($this->context.'.filter.detail', 'filter_detail');
        $this->setState('filter.detail', $detail);

        // sorting
        $filter_order = JRequest::getCmd('filter_order');
        $filter_order_Dir = JRequest::getCmd('filter_order_Dir');
        
        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
        parent::populateState($ordering, $direction);
    }


    protected function getListQuery()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);

        /*
         * room AS r
         * description AS d
         * r.id
         * r.gpuntisID
         * r.name AS room_name
         * r.alias
         * r.campus
         * r.building
         * r.capacity
         * r.floor
         * d.category
         * d.description
         */
        $select = "r.id, r.gpuntisID, r.name AS room_name, r.alias, r.campus, r.building, r.capacity, r.floor, ";
        $select .= "d.category, d.description";
        $query->select($select);
        $query->from("#__thm_organizer_rooms AS r");
        $query->innerJoin("#__thm_organizer_descriptions AS d ON r.descriptionID = d.id");
        
        $search = $this->getState('filter.search');
        if($search AND $search != JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA'))
        {
            $search = $dbo->Quote("%{$dbo->escape($search, true)}%");
            $query->where('r.name LIKE '.$search);
        }

        $campus = $this->getState('filter.campus');

        if(!is_null($campus) && $campus != '*')
        {
        	//var_dump($campus); blah();
            $query->where("r.campus = '$campus'");
            $building = $this->getState('filter.building');
            if(!is_null($building) && $building != '*') $query->where("r.building = '$building'");
        }

        $type = $this->getState('filter.type');
        if(!is_null($type) && $type != '*')
        {
            $query->where("d.category = '$type'");
            $detail = $this->getState('filter.detail');
            if(!is_null($detail) && $detail != '*') $query->where("description = '$detail'");
        }

		// sorting
        $orderby = $dbo->getEscaped($this->getState('filter_order'));
        $direction = $dbo->getEscaped($this->getState('filter_order_Dir'));

        // set $orderby and $direction if not set by html form
        if (!isset($orderby) || strlen($orderby) == 0)
        	$orderby = 'r.name';
        if (!isset($direction) || strlen($direction) == 0)
        	$direction = 'ASC';
        
        $query->order("$orderby $direction");
        
        return $query;
    }

    /**
     * getResources
     *
     * retrieves a list of resources of a specific type
     *
     * @param string $what the name of the resource
     * @param ??? $where a bunch of selection restrictions in some format tbd
     * @return array
     */
    private function getResources($what)
    {
        $roomResourceTables = array(
            'campuses' => 'c',
            'buildings' => 'b',
            'types' => 't',
            'details' => 'det'
        );
        $prefix = $roomResourceTables[$what];
        $dbo = $this->getDbo();
        $query = $this->getListQuery();
        $query->clear('select');
        if ($prefix == 'c') {
        	$query->select("DISTINCT r.campus AS id, r.campus AS name");
        } else if ($prefix == 'b') {
        	$query->select("DISTINCT r.building AS id, r.building AS name");
        } else if ($prefix == 't') {
        	$query->select("DISTINCT d.category AS id, d.category AS name");
        } else if ($prefix == 'det') {
        	$query->select("DISTINCT d.description AS id, d.description AS name");
        }
        
        $query->clear('where');

        $search = $this->getState('filter.search');
        if($search AND $search != JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA'))
        {
            $search = $dbo->Quote("%{$dbo->escape($search, true)}%");
            $query->where('r.name LIKE '.$search);
        }

        $campus = $this->getState('filter.campus');
        if(!is_null($campus) && $campus != '*')
        {
            if($what != 'campuses')$query->where("r.campus = '$campus'");
            $building = $this->getState('filter.building');
            
            if(!is_null($building) && $building != '*' AND $what != 'buildings')
                $query->where("r.building = '$building'");
        }
        $type = $this->getState('filter.type');
        if(!is_null($type) && $type != '*' AND $what != 'types') $query->where("d.category = '$type'");
        if ($prefix == 'c') {
        	$query->order("r.campus ASC");
        } else if ($prefix == 'b') {
        	$query->order("r.building ASC");
        } else if ($prefix == 't') {
        	$query->order("d.category ASC");
        } else if ($prefix == 'det') {
        	$query->order("d.description ASC");
        }

        $dbo->setQuery((string)$query);
        $results = $dbo->loadAssocList();
        $resources = array();
        if(count($results))
        {
            foreach($results as $index => $data)
            {
                $resources[$data['id']]['id'] = $data['id'];
                $resources[$data['id']]['name'] = JText::_($data['name']);
            }
        }
        else $resources = false;
        return $resources;
    }
}
