<?php
/**
* 
* Notelist Model for Giessen Times Component
* 
*/

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class GiessenSchedulerModelEventList extends JModel
{	
    /**
    * Events data array
    *
    * @var array
    */
    var $_data = null;

    /**
    * Events total
    *
    * @var integer
    */
    var $_total = null;

    /**
    * Pagination object
    *
    * @var object
    */
    var $_pagination = null;

    /**
    * Categories object
    *
    * @var object
    */
    var $_categories = null;


    /**
    * Constructor
    *
    * @since 1.5
    */
    function __construct()
    {
        parent::__construct();
        global $mainframe;

        //echo print_r($_POST, true)."<br /><br /><br />";
        // Get the paramaters of the active menu item
        $params =& $mainframe->getParams('com_thm_organizer');

        /*
         * Display Types
         * 0 = active appointments
         * 1 = active appointments for a particular category
         * 2 = active appointments for a particular room
         * 3 = active appointments that the user has authored
         * 4 = all appointments
         * 5 = all appointments for a particular category
         * 6 = all apoointments for a particular room
         * 7 = all appointments that the user has authored
         */
        $display_type = $params->get('display_type');

        //get the number of events from database
        $limit = $mainframe->getUserStateFromRequest('com_thm_organizer.giessenscheduler_el.limit', 'limit', 10, 'int');
        $this->setState('limit', $limit);

        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
        $this->setState('limitstart', $limitstart);

        if(isset($display_type) && ($display_type == 1 || $display_type == 5))
            $category = $params->get('category');
        else
        {
            $category = JRequest::getVar('category');
            if(!isset($category))
                $category = $mainframe->getUserStateFromRequest('com_thm_organizer.giessenscheduler_el.category', 'category', -1, 'int');
        }
        $this->setState('category', $category);

        if(isset($display_type) && ($display_type == 2 || $display_type == 6))
            $room = $params->get('room');
        if(isset($room))
            $this->setState('room', $room);

        $user =& JFactory::getUser();
        if(isset($display_type) && ($display_type == 3 || $display_type == 7))
            $author = $user->username;
        if(isset($author))
            $this->setState('author', $author);

        $date = $mainframe->getUserStateFromRequest('com_thm_organizer.giessenscheduler_el.date', 'date', '');
        if(isset($date) && $date != '')$this->setState('date', $date);

        $filter = $mainframe->getUserStateFromRequest('com_thm_organizer.giessenscheduler_el.filter', 'filter', '');
        if(isset($filter)) $this->setState('filter', $filter);
        
        $orderby = $mainframe->getUserStateFromRequest('com_thm_organizer.giessenscheduler_el.orderby', 'orderby', 'date');
        if(isset($orderby)) $this->setState('orderby', $orderby);
        
        $orderbydir = $mainframe->getUserStateFromRequest('com_thm_organizer.giessenscheduler_el.orderbydir', 'orderbydir', 'ASC');
        if(isset($orderbydir)) $this->setState('orderbydir', $orderbydir);
        
    }

    /**
     *Get event objects preformatted
     *
     * @return eventobject[]
     */
    function getEvents()
    {
        global $mainframe;
        // Lets load the content if it doesn't already exist
        if (empty($this->_data))
        {
            $query = $this->_buildQuery();
            $pagination = $this->getPagination();
            $this->_data = $this->_getList( $query, $pagination->limitstart, $pagination->limit );
            foreach ($this->_data as $event)
            { 
                $displaydate = $displaytime = "";
                if($event->starttime != "00:00" && $event->endtime != "00:00")
                    $timestring = " (".substr($event->starttime, 0, 5)."-".substr($event->endtime, 0, 5).")";
                else if($event->starttime != "00:00")
                    $timestring = " (ab ".substr($event->starttime, 0, 5).")";
                else if($event->endtime != "00:00")
                    $timestring = " (bis ".substr($event->endtime, 0, 5).")";
                if($event->enddate != "00.00.0000")
                {
                    switch($event->rec_type)
                    {
                        case 0:
                            $starttime = $endtime = "";
                            if($event->starttime != '00:00') $starttime = "(".$event->starttime.")";
                            if($event->endtime != '00:00') $endtime = "(".$event->endtime.") ";
                            $event->displaydt = $event->startdate." ".$starttime." - ".$event->enddate." ".$endtime;
                            break;
                        case 1:
                            $event->displaydt = $event->startdate."  -  ".$event->enddate." ".$timestring;
                            break;
                    }
                }
                else
                {
                    $event->displaydt = $event->startdate." ".$timestring;
                }
                $event->detlink = "index.php?option=com_thm_organizer&view=event&eventid=".$event->eid."&Itemid=";
                $event->editlink = "index.php?option=com_thm_organizer&view=event_edit&eventid=".$event->eid."&Itemid=";
                $event->dellink = "index.php?option=com_thm_organizer&controller=event_edit&task=delete_event&eventid=".$event->eid."&Itemid=";
                $event->catlink = "index.php?option=com_thm_organizer&view=eventlist&category=".$event->ecid."&Itemid=";
                $event->filterlink = "index.php?option=com_thm_organizer&view=eventlist&Itemid=";
            }
            //echo $query."<br /><br /><br />";

            $params =& $mainframe->getParams('com_thm_organizer');
            $show_room = $params->get('show_room');
            if(isset($show_room) && $show_room == 1) $this->addRooms();
        }
        return $this->_data;
    }

    /**
     * Get Event Category Information
     *
     * @access public
     * @return category information packed in objects?
     */
    function getCategories()
    {
        global $mainframe;
        $user =& JFactory::getUser();
        $gid = $user->gid;
        if(empty($this->_categories))
        {
            $query = "SELECT ecid, ecname, ecimage, ecdescription
                      FROM #__thm_organizer_categories
                      WHERE access <= '$gid'";
            $params =& $mainframe->getParams('com_thm_organizer');
            $display_type = $params->get('display_type');
            if(isset($display_type) && ($display_type == 1 || $display_type == 5))
                $category = $params->get('category');
            if(isset($category)) $query = $query." AND ecid = '$category'";
            $this->_categories = $this->_getList($query);
        }
        return $this->_categories;
    }

    /**
    * Total nr of events
    *
    * @access public
    * @return integer
    */
    function getTotal()
    {
        // Lets load the total nr if it doesn't already exist
        if(empty($this->_total))
        {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }

    /**
    * Method to get a pagination object for the events
    *
    * @access public
    * @return integer
    */
    function getPagination()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination))
        {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination( $this->getTotal(),  $this->getState('limitstart'), $this->getState('limit') );
        }
        return $this->_pagination;
    }

    /**
    * Build the query
    *
    * @access private
    * @return string
    */
    function _buildQuery()
    {
        // Get the WHERE and ORDER BY clauses for the query
        $where = $this->_buildEventsWhere();
        $orderby = $this->_buildEventsOrderBy();

        //Get Events from Database
        $query = "SELECT DISTINCT (eid), name AS author,
                    DATE_FORMAT(startdate, '%d.%m.%Y') AS startdate, DATE_FORMAT(enddate, '%d.%m.%Y') AS enddate,
                    SUBSTR(starttime, 1, 5) AS starttime, SUBSTR(endtime, 1, 5) AS endtime,
                    e.title, ecname, ecid, username, access, recurrence_type AS rec_type
                  FROM #__thm_organizer_events AS e
                  INNER JOIN #__users
                        ON created_by = id
                  INNER JOIN #__thm_organizer_categories
                        ON ecid = ecatid "
                  .$where
                  .$orderby;
        return $query;
    }

    /**
    * Build the order clause
    *
    * @access private
    * @return string
    */
    function _buildEventsOrderBy()
    {
        $orderby = $this->getState('orderby');
        if(isset($orderby) && $orderby == 'date')$orderby = 'startdate';
        if(isset($orderby) && $orderby == 'category')$orderby = 'ecname';
        $orderbydir = $this->getState('orderbydir');
        if(isset($orderby) && isset($orderbydir)) return "ORDER BY $orderby $orderbydir";
        else return'ORDER BY startdate ASC';
    }

    /**
    * Build the where clause
    *
    * @access private
    * @return string
    */
    function _buildEventsWhere()
    {
        global $mainframe;

        $filter = $this->getState('filter');
        $author =  $this->getState('author');
        $room =  $this->getState('room');
        $category = $this->getState('category');
        $date =  $this->getState('date');

        $params =& $mainframe->getParams('com_thm_organizer');
        $display_type = $params->get('display_type');

        if(isset($author))  $wherray[] = "(username = '$author')";

        if(isset($room) && is_array($room) && $room[0] != "")
            $wherray[] = "(oid = '".implode("' OR oid = '", $room)."')";
        else if(isset($room)) $wherray[] = "(oid = '$room')";

        if(isset($category) && is_array($category) && $category[0] != ""&& $category[0] != "-1")
            $wherray[] = "(ecid = '".implode("' OR ecid = '", $category)."')";
        else if(isset($category) && !is_array($category) && $category != -1 ) $wherray[] = "(ecid = '$category')";

        if(isset($date) && $date != '')  $wherray[] = "(startdate <= '$date' AND enddate >= '$date')";
        else if((!isset($filter) || $filter == '') && isset($display_type) && ($display_type <= 3))
            $wherray[] = "(startdate >= '".date('Y-m-d')."' OR enddate >= '".date('Y-m-d')."')";

        if(isset($filter) && $filter != '')
        {
            $filters = explode(",", $filter);
            if(count($filters) > 1)
            {
                foreach($filters as $f)
                {
                    $f = strtolower($f);
                    $filterray[] = "(startdate <= '$f' AND enddate >= '$f')";
                    $filterray[] = "(ecname LIKE '%$f%')";
                    $filterray[] = "(oname LIKE '%$f%')";
                    $filterray[] = "(title LIKE '%$f%')";
                    $filterray[] = "(name LIKE '%$f%')";
                    $likeobjects[] = "( ".implode(" OR ", $filterray)." )";
                    unset($filterray);
                }
                $wherray[] = "( ".implode(" AND ", $likeobjects)." )";
            }
            else
            {
                $f = strtolower($filter);
                $likeobjects[] = "(startdate <= '$f' AND enddate >= '$f')";
                $likeobjects[] = "(ecname LIKE '%$f%')";
                $likeobjects[] = "(oname LIKE '%$f%')";
                $likeobjects[] = "(title LIKE '%$f%')";
                $likeobjects[] = "(name LIKE '%$f%')";
                $wherray[] = "( ".implode(" OR ", $likeobjects)." )";
            }
        }
        $user =& JFactory::getUser();
        $gid = $user->gid;
        $wherray[] = "(access <= '$gid')";
        return "WHERE ( ".implode(" AND ", $wherray)." )";
    }

    /**
    * Build the order clause
    *
    * @access private
    * @return string
    */
    function addRooms()
    {
        $eids = ''; $initial = true;
        foreach($this->_data as $event)
        {
            if(!$initial) $eids .= ', ';
            else $initial = false;
            $eids .= "'".$event->eid."'";
        }
        $where = $this->_buildEventsWhere();
        $query = "SELECT eid, oname
        FROM #__thm_organizer_events AS e
        INNER JOIN #__thm_organizer_eventobjects
                ON eid = eventid
        INNER JOIN #__thm_organizer_objects
                ON oid = objectid
        INNER JOIN #__thm_organizer_categories
                ON ecid = ecatid
        INNER JOIN #__users
                ON e.created_by = id
        $where
        AND eid in ( $eids )";
        $roomlist = $this->_getList($query);
        if(count($roomlist) > 0)
            foreach($this->_data as $event)
            {
                foreach($roomlist as $room)
                {
                    if($event->eid == $room->eid)
                    {
                        if(isset($event->rooms))$event->rooms .= ", ".$room->oname;
                        else $event->rooms = $room->oname;
                    }
                }
            }
    }



}