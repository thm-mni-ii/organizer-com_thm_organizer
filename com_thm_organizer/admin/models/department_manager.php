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
class thm_organizersModeldepartment_manager extends JModelList
{
	public $institutions	= null;
    public $campuses 		= null;
    public $department 		= null;

    public function __construct($config = array())
    {
    	parent::__construct();

        // get lists for filters
        $errorOccurred = false;  // variable to prevent to show the same error multiple times
        
        $this->institutions = $this->getResources('institutions');
        if (!$this->institutions) $errorOccurred = true;
        
        if($this->getState('filter.institution') && $this->getState('filter.institution') != '*') 
        {
        	$this->campuses = $this->getResources('campuses');
        	if (!$this->campuses) $errorOccurred = true;
        }
        
        $this->departments = $this->getResources('departments');
        if (!$this->departments) $errorOccurred = true;
        
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

        $campus = $this->getUserStateFromRequest($this->context.'.filter.campus', 'filter_campus');
        $this->setState('filter.campus', $campus);

        $institution = $this->getUserStateFromRequest($this->context.'.filter.institution', 'filter_institution');
        $this->setState('filter.institution', $institution);

        $department = $this->getUserStateFromRequest($this->context.'.filter.department', 'filter_department');
        $this->setState('filter.department', $department);

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
         * id
         * gpuntisID
         * name
         * institution
         * campus
         * department
         * subdepartment
         */
        $select = "id, gpuntisID, name, institution, campus, department, subdepartment";
        $query->select($select);
        $query->from("#__thm_organizer_departments");
        
        // searching
        $search = $this->getState('filter.search');
        if($search AND $search != JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA'))
        {
            $search = $dbo->Quote("%{$dbo->escape($search, true)}%");
            $query->where('name LIKE '.$search);
        }

        // filtering
        $institution = $this->getState('filter.institution');

        if(!is_null($institution) && $institution != '*')
        {
            $query->where("institution = '$institution'");
            $campus = $this->getState('filter.campus');
            if(!is_null($campus) && $campus != '*') $query->where("campus = '$campus'");
        }

        $department = $this->getState('filter.department');
        if(!is_null($department) && $department != '*')
        {
            $query->where("department = '$department'");
        }

		// sorting
        $orderby = $dbo->getEscaped($this->getState('filter_order'));
        $direction = $dbo->getEscaped($this->getState('filter_order_Dir'));

        // set $orderby and $direction if not set by html form
        if (!isset($orderby) || strlen($orderby) == 0)
        	$orderby = 'name';
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
        $dbo = $this->getDbo();
        $query = $this->getListQuery();
        $query->clear('select');
        
        switch ($what) {
        	case 'institutions':
	        	$query->select("DISTINCT institution AS id, institution AS name");
	        	break;
        	case 'campuses':
	        	$query->select("DISTINCT campus AS id, campus AS name");
	        	break;
        	case 'departments':
	        	$query->select("DISTINCT department AS id, department AS name");
	        	break;
        }
        
        $query->clear('where');

        // search
        $search = $this->getState('filter.search');
        if($search AND $search != JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA'))
        {
            $search = $dbo->Quote("%{$dbo->escape($search, true)}%");
            $query->where('name LIKE '.$search);
        }

        // filters
        $institution = $this->getState('filter.institution');
        if(!is_null($institution) && $institution != '*')
        {
            if($what != 'institution')$query->where("institution = '$institution'");
            
            $campus = $this->getState('filter.campus');
            if(!is_null($campus) && $campus != '*' && $what != 'campuses')
                $query->where("campus = '$campus'");
        }
        
        $department = $this->getState('filter.category');
        if (!is_null($department) && $department != '*' && $what != 'departments')
        {
        	$query->where("department = '$department'");
        }

        // ordering
        switch ($what) {
        	case 'institutions':
        		$query->order("institution ASC");
        		break;
        	case 'campuses':
        		$query->order("campus ASC");
        		break;
        	case 'departments':
	        	$query->order("department ASC");
	        	break;
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
