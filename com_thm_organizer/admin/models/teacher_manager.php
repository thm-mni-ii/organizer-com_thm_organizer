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
class thm_organizersModelteacher_manager extends JModelList
{
    public $departments 	= null;
    public $campuses 		= null;
    public $institutions 	= null;

    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] =
                array(
                    'name', 't.name',
                    'department', 't.departmentID',
                	'campus', 'd.campus',
                	'institution', 'd.institution'
                );
        }
        parent::__construct($config);

        // load filter data
        $this->departments = $this->getResources('departments');
        $this->campuses = $this->getResources('campuses');
        $this->institutions = $this->getResources('institutions');
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

        $department = $this->getUserStateFromRequest($this->context.'.filter.department', 'filter_department');
        $this->setState('filter.department', $department);

        $campus = $this->getUserStateFromRequest($this->context.'.filter.campus', 'filter_campus');
        $this->setState('filter.campus', $campus);
        
        $institution = $this->getUserStateFromRequest($this->context.'.filter.institution', 'filter_institution');
        $this->setState('filter.institution', $institution);
        
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

        $select = "DISTINCT t.id, t.gpuntisID, t.name, t.username, d.department, d.institution, d.campus, d.subdepartment";
        $query->select($select);
        $query->from("#__thm_organizer_teachers AS t");
        $query->innerJoin("#__thm_organizer_departments AS d ON t.departmentID = d.id");
        
        $search = $this->getState('filter.search');
        if($search AND $search != JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA'))
        {
            $search = $dbo->Quote("%{$dbo->escape($search, true)}%");
            $query->where('t.name LIKE '.$search);
        }

        // applied filters
    	$department = $this->getState('filter.department');
        if(is_numeric($department))
        {
            $query->where("d.id = $department");
        }
        
        $campus = $this->getState('filter.campus');
        if($campus && $campus != '*')
        {
        	$query->where("d.campus LIKE \"$campus\"");
        }
        
        $institution = $this->getState('filter.institution');
        if($institution && $institution != '*')
        {
        	$query->where("d.institution LIKE \"$institution\"");
        }

		// sorting
        $orderby = $dbo->getEscaped($this->getState('filter_order'));
        $direction = $dbo->getEscaped($this->getState('filter_order_Dir'));

        // set $orderby and $direction if not set by html form
        if (!isset($orderby) || strlen($orderby) == 0)
        	$orderby = 't.name';
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
     * @return array
     */
    private function getResources($what)
    {
        $roomResourceTables = array(
            'departments' 	=> 'd',
        	'campuses'		=> 'd',
        	'institutions'	=> 'd'
        );
        $prefix = $roomResourceTables[$what];
        $dbo = $this->getDbo();
        $query = $this->getListQuery();
        $query->clear('select');
        
        // distinguish between filters
        switch ($what)
        {
        	case 'departments':
        		$query->select("DISTINCT $prefix.id, 
        						(CASE WHEN $prefix.subdepartment NOT LIKE \"\" THEN
        							CONCAT($prefix.department, \" (\", $prefix.subdepartment, \")\")
        						ELSE 
        							($prefix.department)
        						END) 
        						AS name");
        		break;
        	case 'campuses':
        		$query->select("DISTINCT $prefix.campus AS id, $prefix.campus AS name");
        		break;
        	case 'institutions':
        		$query->select("DISTINCT $prefix.institution AS id, $prefix.institution AS name");
        		break;
        	default:
        		return null;
        		break;
        }
        
        $query->clear('where');

        $search = $this->getState('filter.search');
        if($search AND $search != JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA'))
        {
            $search = $dbo->Quote("%{$dbo->escape($search, true)}%");
            $query->where('t.name LIKE '.$search);
        }

     	// applied filters
    	$department = $this->getState('filter.department');
        if(is_numeric($department))
        {
            $query->where("d.id = $department");
        }
        
        $campus = $this->getState('filter.campus');
        if($campus && $campus != '*')
        {
        	$query->where("d.campus LIKE \"$campus\"");
        }
        
        $institution = $this->getState('filter.institution');
        if($institution && $institution != '*')
        {
        	$query->where("d.institution LIKE \"$institution\"");
        }
        
        $query->order("name ASC");

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
