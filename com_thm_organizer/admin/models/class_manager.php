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
class thm_organizersModelclass_manager extends JModelList
{
    public $managers 	= null;
    public $semesters 	= null;
    public $majors		= null;

    public function __construct($config = array())
    {
    	parent::__construct();

        // get lists for filters
        $errorOccurred = false;  // variable to prevent to show the same error multiple times
        
        $this->managers = $this->getResources('managers');
        if (!$this->managers) $errorOccurred = true;
        
        $this->semesters = $this->getResources('semesters');
        if (!$this->semesters) $errorOccurred = true;
        
        $this->majors = $this->getResources('majors');
        if (!$this->majors) $errorOccurred = true;
        
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

        $manager = $this->getUserStateFromRequest($this->context.'.filter.manager', 'filter_manager');
        $this->setState('filter.manager', $manager);

        $semester = $this->getUserStateFromRequest($this->context.'.filter.semester', 'filter_semester');
        $this->setState('filter.semester', $semester);
        
        $major = $this->getUserStateFromRequest($this->context.'.filter.major', 'filter_major');
        $this->setState('filter.major', $major);

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
         * class AS c
         * teacher AS t
         * c.manager = t.username
         * ******
         * c.gpuntisID
         * c.name
         * c.alias
         * t.name AS c_manager
         * c.semester
         * c.major
         */
        $select = "c.id, c.gpuntisID, c.name, c.alias, c.alias, t.name AS c_manager, c.semester, c.major";
        $query->select($select);
        $query->from("#__thm_organizer_classes AS c");
        $innerJoin = "(SELECT name, username FROM #__thm_organizer_teachers WHERE username != '' ";
        $innerJoin .= "UNION SELECT '' AS name, '' AS username ) ";
        $innerJoin .= "AS t ON c.manager = t.username";
        $query->innerJoin($innerJoin);
        
        $search = $this->getState('filter.search');
        if($search AND $search != JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA'))
        {
            $search = $dbo->Quote("%{$dbo->escape($search, true)}%");
            $query->where('c.name LIKE '.$search);
        }

        $manager = $this->getState('filter.manager');
		if(!is_null($manager) && $manager != '*')
        	$query->where("t.name = '$manager'");
        
        $semester = $this->getState('filter.semester');
        if(!is_null($semester) && $semester != '*')
        	$query->where("c.semester LIKE '%$semester%'");

        $major = $this->getState('filter.major');
        if(!is_null($major) && $major != '*')
        	$query->where("c.major = '$major'");

		// sorting
        $orderby = $dbo->getEscaped($this->getState('filter_order'));
        $direction = $dbo->getEscaped($this->getState('filter_order_Dir'));

        // set $orderby and $direction if not set by html form
        if (!isset($orderby) || strlen($orderby) == 0)
        	$orderby = 'c.name';
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
        	case 'managers':
	        	$query->select("DISTINCT t.name AS id, t.name AS name");
	        	break;
        	case 'semesters':
	        	$query->select("DISTINCT TRIM(c.semester) AS id, TRIM(c.semester) AS name");
	        	break;
        	case 'majors':
	        	$query->select("DISTINCT c.major AS id, c.major AS name");
	        	break;
        }
        
        $query->clear('where');

        $search = $this->getState('filter.search');
        if($search AND $search != JText::_('COM_THM_ORGANIZER_SEARCH_CRITERIA'))
        {
            $search = $dbo->Quote("%{$dbo->escape($search, true)}%");
            $query->where('c.name LIKE '.$search);
        }
        
        $manager = $this->getState('filter.manager');
        if($manager && $manager != '*')
        {
        	$query->where("t.name = '$manager'");
        }
        
        $semester = $this->getState('filter.semester');
        if($semester && $semester != '*')
        {
        	$query->where("c.semester LIKE '%$semester%'");
        }
        
        $major = $this->getState('filter.major');
        if($major && $major != '*')
        {
        	$query->where("c.major = '$major'");
        }

        // ordering
        switch ($what) {
        	case 'managers':
        		$query->order("t.name ASC");
        		break;
        	case 'semesters':
	        	$query->order("c.semester ASC");
	        	break;
        	case 'majors':
	        	$query->order("c.major ASC");
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
