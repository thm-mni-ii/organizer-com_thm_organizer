<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        model for
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class thm_organizerModelevent_list extends JModel
{
    public $events = null;
    private $total = null;
    public $pagination = null;
    public $categories = null;


    /**
    * Constructor
    *
    */
    function __construct($callParameters = null)
    {
        parent::__construct();
        $this->restoreState($callParameters);
        $this->loadEvents();
    }

    /**
     * restoreState()
     *
     * Restores/sets state variables from the menu parameters and user form entries
     *
     * @access private
     **/
    private function restoreState($callParameters = null)
    {
        $params = JFactory::getApplication()->getParams();
        $username = JFactory::getUser()->username;

		if(is_array($callParameters) && is_int($callParameters["display_type"]))
		{
			$display_type = $callParameters["display_type"];
		}
		else
		{
			$display_type = $params->get('display_type');
		}

        switch ($display_type) {
            case 1://current + category
                $categoryID = $params->get('category_restriction');
                $this->setState('categoryID', $categoryID);
                break;
            case 2://current + room
                $roomID = $params->get('room_restriction');
                $this->setState('roomID', $roomID);
                break;
            case 3://current + own
                $this->setState('author', $username);
                break;
            case 5://all + category
                $categoryID = $params->get('category_restriction');
                $this->setState('categoryID', $categoryID);
                break;
            case 6://all + rrom
                $roomID = $params->get('room_restriction');
                $this->setState('roomID', $roomID);
                break;
            case 7://all + own
                $this->setState('author', $username);
                break;
            default:
                break;
        }

        if(!isset($categoryID)) $categoryID = JRequest::getVar('categoryID');
        if(!isset($categoryID))
            $categoryID = $app->getUserStateFromRequest('com_thm_organizer.event_list.categoryID', 'categoryID', -1, 'int');
        $this->setState('category', $category);

        $fromDate = $app->getUserStateFromRequest('com_thm_organizer.event_list.fromdate', 'fromdate', '');
        if(!empty($fromDate)) $this->setState('fromdate', $fromDate);
        $todate = $app->getUserStateFromRequest('com_thm_organizer.event_list.todate', 'todate', '');
        if(!empty($todate)) $this->setState('todate', $todate);
        $search = $app->getUserStateFromRequest('com_thm_organizer.event_list.search', 'search', '');
        if(isset($search)) $this->setState('search', $search);
        $orderby = $app->getUserStateFromRequest('com_thm_organizer.event_list.orderby', 'orderby', 'date');
        if(isset($orderby)) $this->setState('orderby', $orderby);
        $orderbydir = $app->getUserStateFromRequest('com_thm_organizer.event_list.orderbydir', 'orderbydir', 'ASC');
        if(isset($orderbydir)) $this->setState('orderbydir', $orderbydir);

        $limit = $app->getUserStateFromRequest('com_thm_organizer.event_list.limit', 'limit', 10, 'int');
        $this->setState('limit', $limit);
        $limitstart = JRequest::getInt('limitstart');
        $this->setState('limitstart', $limitstart);
    }


    /**
     * loadEvents()
     *
     * loads event entries
     *
     * @access private
     **/
    private function loadEvents()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery();
        $query->select($this->getSelect());
        $query->from("#__thm_organizer_events");
        $query->innerJoin("#__content ON #__thm_organizer_events.id = #__content.id");
        $query->innerJoin("#__thm_organizer_categories ON #__thm_organizer_events.categoryID = #__thm_organizer_categories.id");
        $query->innerJoin("#__categories ON #__thm_organizer_categories.contentCatID = #__categories.id");
        $query->innerJoin("#__users ON #__content.created_by = #__users.id");
        $query->where($this->getWhere());
        $where = $this->_buildEventsWhere();
        $orderby = $this->_buildEventsOrderBy();

        return $query;
        $query = $this->buildQuery();
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

            $params = JFactory::getApplication()->getParams();
            $show_room = $params->get('show_room');
            if(isset($show_room) && $show_room == 1) $this->addRooms();
        }
        return $this->_data;
    }

    private function getSelect()
    {
        $select = "#__thm_organizer_events.id AS id, ";
        $select .= "#__thm_organizer_events.categoryID AS eventCategoryID, ";
        $select .= "DATE_FORMAT(#__thm_organizer_events.startdate, '%d.%m.%Y') AS startdate, ";
        $select .= "DATE_FORMAT(#__thm_organizer_events.enddate, '%d.%m.%Y') AS enddate, ";
        $select .= "SUBSTR(#__thm_organizer_events.starttime, 1, 5) AS starttime, ";
        $select .= "SUBSTR(#__thm_organizer_events.endtime, 1, 5) AS endtime, ";
        $select .= "#__thm_organizer_events.recurrence_type AS rec_type, ";
        $select .= "#__thm_organizer_categories.title AS eventCategory, ";
        $select .= "#__thm_organizer_categories.contentCatID AS contentCategoryID, ";
        $select .= "#__content.title AS title, ";
        $select .= "#__content.introtext AS description, ";
        $select .= "#__content.access AS contentAccess, ";
        $select .= "#__categories.title AS contentCategory, ";
        $select .= "#__categories.access AS contentCategoryAccess, ";
        $select .= "#__users.id AS authorID ";
        return $select;
    }

    /**
    * Build the where clause
    *
    * @access private
    * @return string
    */
    private function buildWhere()
    {
        $filter = $this->getState('filter');
        $author = $this->getState('author');
        $room = $this->getState('room');
        $category = $this->getState('category');
        $date = $this->getState('date');

        $params = JFactory::getApplication()->getParams();
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
     * Get Event Category Information
     *
     * @access public
     * @return category information packed in objects?
     */
    function getCategories()
    {
        $user =& JFactory::getUser();
        $gid = $user->gid;
        if(empty($this->_categories))
        {
            $query = "SELECT ecid, ecname, ecimage, ecdescription
                      FROM #__thm_organizer_categories
                      WHERE access <= '$gid'";
            $params = JFactory::getApplication()->getParams();
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