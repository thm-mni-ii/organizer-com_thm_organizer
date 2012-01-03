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
    public $semesterName = '';
    public $institutions = null;
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
        $this->institutions = $this->getResources('institutions');
        if(is_numeric($this->getState('filter.institution')))$this->campuses = $this->getResources('campuses');
        if(is_numeric($this->getState('filter.campus')))$this->buildings = $this->getResources('buildings');
        $this->types = $this->getResources('types');
        if(is_numeric($this->getState('filter.type')))$this->details = $this->getResources('details');
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

        parent::populateState($ordering, $direction);
    }


    protected function getListQuery()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);

        $select = "r.id, r.name, i.name as institution, c.name as campus, ";
        $select .= "b.name as building, t.name as type, det.name as detail ";
        $query->select($select);
        $query->from("#__thm_organizer_rooms AS r");
        $query->innerJoin("#__thm_organizer_institutions AS i ON r.institutionID = i.id");
        $query->innerJoin("#__thm_organizer_campuses AS c ON r.campusID = c.id");
        $query->innerJoin("#__thm_organizer_buildings AS b ON r.buildingID = b.id");
        $query->innerJoin("#__thm_organizer_room_descriptions AS dsc ON r.descriptionID = dsc.id");
        $query->innerJoin("#__thm_organizer_room_types AS t ON dsc.typeID = t.id");
        $query->innerJoin("#__thm_organizer_room_details AS det ON dsc.descID = det.id");

        $search = $this->getState('filter.search');
        if($search AND $search != JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA'))
        {
            $search = $dbo->Quote("%{$dbo->escape($search, true)}%");
            $query->where('r.name LIKE '.$search);
        }

        $institution = $this->getState('filter.institution');
        if(is_numeric($institution))
        {
            $query->where("i.id = $institution");
            $campus = $this->getState('filter.campus');
            if(is_numeric($campus))
            {
                $query->where("c.id = $campus");
                $building = $this->getState('filter.building');
                if(is_numeric($building)) $query->where("b.id = $building");
            }
        }

        $type = $this->getState('filter.type');
        if(is_numeric($type))
        {
            $query->where("t.id = $type");
            $detail = $this->getState('filter.detail');
            if(is_numeric($detail)) $query->where("det.id = $detail");
        }


        $orderby = $dbo->getEscaped($this->getState('list.ordering', 'r.name'));
        $direction = $dbo->getEscaped($this->getState('list.direction', 'ASC'));
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
            'institutions' => 'i',
            'campuses' => 'c',
            'buildings' => 'b',
            'types' => 't',
            'details' => 'det'
        );
        $prefix = $roomResourceTables[$what];
        $dbo = $this->getDbo();
        $query = $this->getListQuery();
        $query->clear('select');
        $query->select("DISTINCT $prefix.id, $prefix.name");
        $query->clear('where');

        $search = $this->getState('filter.search');
        if($search AND $search != JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA'))
        {
            $search = $dbo->Quote("%{$dbo->escape($search, true)}%");
            $query->where('r.name LIKE '.$search);
        }

        $institution = $this->getState('filter.institution');
        if(is_numeric($institution))
        {
            if($what != 'institutions')$query->where("i.id = $institution");
            $campus = $this->getState('filter.campus');
            if(is_numeric($campus))
            {
                if($what != 'campuses')$query->where("c.id = $campus");
                $building = $this->getState('filter.building');
                if(is_numeric($building) AND $what != 'buildings')
                    $query->where("b.id = $building");
            }
        }
        $type = $this->getState('filter.type');
        if(is_numeric($type) AND $what != 'types') $query->where("t.id = $type");
        $query->order("$prefix.name ASC");

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
        else echo (string) $query;
        return $resources;
    }
}
