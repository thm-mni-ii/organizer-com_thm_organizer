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

define('CURRENT', 0);
define('CURRENT_CATEGORY', 1);
define('CURRENT_ROOM', 2);
define('CURRENT_OWN', 3);
define('ALL', 4);
define('ALL_CATEGORY', 5);
define('ALL_ROOM', 6);
define('ALL_OWN', 7);

class thm_organizerModelevent_list extends JModel
{
    private $callParameters = null;
    private $menuParameters = null;
    public $display_type;
    public $events = null;
    public $total = null;
    public $pagination = null;
    public $categories = null;
    public $canWrite = false;
    public $canEdit = true;


    /**
     * Constructor
     *
     * @param array $callParameters an array containing the values normally in post
     *  for external calls on this model
     * <pre>
     *  'display_type': int values 0-7 **REQUIRED**
     *  'categoryID': int id of an entry in #__thm_organizer_event_categories
     *  'roomID': int id of an entry in #__thm_organizer_rooms
     *  'author': int id of an entry in #__users
     *  'fromDate': date the 'since when' date
     *  'toDate': date the 'until when' date
     *  'search': string a comma seperated list of search items
     *  'orderby': sort criteria for result set
     *  'orderbydir': string values ASC/DESC sort criteria direction
     *  'limit': the maximal number of entries which should be retrieved
     *  'limitstart': the lower inclusive bound for the entry set
     * </pre>
     **/
    function __construct($callParameters = null)
    {
        parent::__construct();
        if(isset($callParameters)) $this->callParameters = $callParameters;
        $this->menuParameters = JFactory::getApplication()->getParams();
        $this->restoreState();
        $this->loadEvents();
        if(count($this->events))$this->loadEventResources();
        $this->loadCategories();
        $this->setUserPermissions();
    }

    /**
     * restoreState()
     *
     * Restores/sets state variables from the menu parameters and user form entries
     *
     * @access private
     **/
    private function restoreState()
    {
        $username = JFactory::getUser()->username;
        $this->display_type = (count($this->callParameters))?
            $this->callParameters["display_type"] : $this->menuParameters->get('display_type');
        switch ($this->display_type)
        {
            case CURRENT_CATEGORY:
                $this->setCategoryID();
                break;
            case CURRENT_ROOM:
                $this->setRoomID();
                break;
            case CURRENT_OWN:
                $this->setState('author', $username);
                break;
            case ALL_CATEGORY:
                $this->setCategoryID();
                break;
            case ALL_ROOM:
                $this->setRoomID();
                break;
            case ALL_OWN:
                $this->setState('author', $username);
                break;
            default:
                break;
        }
        $this->setFromDate();
        $this->setToDate();
        $this->setSearch();
        $this->setOrderBy();
        $this->setLimit();
    }

    private function setCategoryID()
    {
        $application = JFactory::getApplication();
        if(isset($callParameters) and isset($callParameters["categoryID"]))
            $categoryID = $callParameters["categoryID"];
        else if($this->menuParameters->get('category_restriction'))
            $categoryID = $this->menuParameters->get('category_restriction');
        else if(JRequest::getVar('categoryID'))
            $categoryID = JRequest::getVar('categoryID');
        else $categoryID = $application->getUserStateFromRequest('com_thm_organizer.event_list.categoryID', 'categoryID', -1, 'int');
        $this->setState('categoryID', $categoryID);
    }

    private function setRoomID()
    {
        $roomID = (isset($this->callParameters) and isset($this->callParameters['roomID']))?
            $callParameters["roomID"] : $this->menuParameters->get('room_restriction');
        if(isset($roomID)) $this->setState('roomID', $roomID);
    }

    private function setFromDate()
    {
        $application = JFactory::getApplication();
        if(isset($this->callParameters) and isset($this->callParameters["fromDate"]))
            $fromDate = $this->callParameters["fromDate"];
        else $fromDate = $application->getUserStateFromRequest('com_thm_organizer.event_list.fromdate', 'fromdate', '');
        if($fromDate) $this->setState('fromdate', $fromDate);
    }

    private function setToDate()
    {
        $application = JFactory::getApplication();
        if(isset($this->callParameters) and isset($this->callParameters["toDate"]))
            $toDate = $this->callParameters["fromDate"];
        else $toDate = $application->getUserStateFromRequest('com_thm_organizer.event_list.todate', 'todate', '');
        if($toDate) $this->setState('todate', $toDate);
    }

    private function setSearch()
    {
        $application = JFactory::getApplication();
        if(isset($this->callParameters) and isset($this->callParameters["search"]))
            $search = $this->callParameters["fromDate"];
        else $search = $application->getUserStateFromRequest('com_thm_organizer.event_list.search', 'search', '');
        if($search) $this->setState('search', $search);
    }

    private function setOrderBy()
    {
        $application = JFactory::getApplication();
        if(isset($this->callParameters) and isset($this->callParameters["orderby"]))
            $orderby = $this->callParameters["orderby"];
        else $orderby = $application->getUserStateFromRequest('com_thm_organizer.event_list.orderby', 'orderby', 'date');
        $this->setState('orderby', $orderby);

        if(isset($this->callParameters) and isset($this->callParameters["orderbydir"]))
            $orderbydir = $this->callParameters["orderbydir"];
        else $orderbydir = $application->getUserStateFromRequest('com_thm_organizer.event_list.orderbydir', 'orderbydir', 'ASC');
        $this->setState('orderbydir', $orderbydir);
    }

    private function setLimit()
    {
        $application = JFactory::getApplication();
        if(isset($this->callParameters) and isset($this->callParameters["limit"]))
            $limit = $this->callParameters["limit"];
        else if(!isset($this->callParameters))
            $limit = $application->getUserStateFromRequest('com_thm_organizer.event_list.limit', 'limit', 10, 'int');
        if(isset($limit)) $this->setState('limit', $limit);

        if(isset($this->callParameters) and isset($this->callParameters["limitstart"]))
            $limitstart = $this->callParameters["limitstart"];
        else if(!isset($this->callParameters))
            $limitstart = JRequest::getInt('limitstart');
        if(isset($limitstart)) $this->setState('limitstart', $limitstart);
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
        $query = $dbo->getQuery(true);
        $query->select($this->getSelect());
        $query->from("#__thm_organizer_events");
        $query->innerJoin("#__content ON #__thm_organizer_events.id = #__content.id");
        $query->innerJoin("#__thm_organizer_categories ON #__thm_organizer_events.categoryID = #__thm_organizer_categories.id");
        $query->innerJoin("#__categories ON #__thm_organizer_categories.contentCatID = #__categories.id");
        $query->innerJoin("#__users ON #__content.created_by = #__users.id");
        //$query->where($this->getWhere());
        //$query->order($this->getOrderBy());
        $query = (string)$query;
        //$query = $query.$limit;

//        $pagination = $this->getPagination();
//        $results = $this->_getList( (string) $query, $pagination->limitstart, $pagination->limit );


        $dbo->setQuery($query);
        $events = $dbo->loadAssocList();

        //check for empty
        foreach ($events as $k => $v)
        {
            $edSet = $stSet = $etSet = false;
            $displayDates = $timestring = "";
            $edSet = $v['enddate'] != "00.00.0000";
            $stSet = $v['starttime'] != "00:00";
            $etSet = $v['endtime'] != "00:00";
            if($stSet and $etSet) $timestring = " ({$v['starttime']} - {$v['endtime']})";
            else if($stSet) $timestring = " (ab {$v['starttime']})";
            else if($etSet) $timestring = " (bis {$v['endtime']})";
            if($edSet and $v['rec_type'] == 0)
            {
                $displayDates = "{$v['startdate']}";
                if($stSet) $displayDates .= " ( {$v['starttime']} )";
                $displayDates .= " - {$v['enddate']}";
                if($etSet) $displayDates .= " ( {$v['endtime']} )";
                $events[$k]['displayDates'] = $displayDates;
            }
            else if($edSet and $v['rec_type'] == 1)
                $events[$k]['displayDates'] = $v['startdate']." - ".$v['enddate']." ".$timestring;
            else
                $events[$k]['displayDates'] = $v['startdate']." ".$timestring;
            $events[$k]['detailsLink'] = "index.php?option=com_thm_organizer&view=event&eventID=".$v['id']."&Itemid=";
            $events[$k]['userCanEdit'] = true;
        }
        $this->total = count($events);
        $this->events = $events;
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
        $select .= "#__users.name AS author, ";
        $select .= "#__users.id AS authorID ";
        return $select;
    }

    /**
     * getWhere()
     *
     * Build the where clause
     *
     * @access private
     * @return string $where the where clause to a query based on model state information
     *
     */
    private function getWhere()
    {
        $search = $this->getState('search');
        $author = $this->getState('author');
        $room = $this->getState('room');
        $category = $this->getState('category');
        $fromdate = $this->getState('fromdate');
        $todate = $this->getState('todate');
        $display_type = $this->display_type;

        if(isset($author)) $wherray[] = "(author = '$author')";
        if(isset($room)) $wherray[] = "( id = '$room' )";
        if(isset($category)) $wherray[] = "(id = '$category')";

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
    function loadCategories()
    {
        $dbo = JFactory::getDbo();
        $userAccessLevels = JAccess::getAuthorisedViewLevels(JFactory::getUser()->id);
        sort($userAccessLevels);
        $maxLevel = array_pop($userAccessLevels);
        $query = $dbo->getQuery(true);
        $query->select('toc.id, toc.title, toc.description');
        $query->from('#__thm_organizer_categories AS toc');
        $query->innerJoin('#__categories AS c ON c.id = toc.contentCatID');
        $query->where("c.access < '$maxLevel'");
        if($this->display_type == 1 or $this->display_type == 5)
        {
            $category = $this->getState('category');
            $query->where("toc.id = '$category'");
        }
        $dbo->setQuery((string)$query);
        $this->categories = $dbo->loadAssocList();
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
            $this->_pagination = new JPagination( $this->total,  $this->getState('limitstart'), $this->getState('limit') );
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
    private function loadEventResources()
    {
        $dbo = JFactory::getDbo();
        foreach($this->events as $k => $v)
        {
            $id = $v['id'];
            $resourcesResults = array();

            $query = $dbo->getQuery(true);
            $query->select('id, title AS name, "group" AS type ');
            $query->from('#__thm_organizer_event_groups AS eg');
            $query->innerJoin('#__usergroups AS ug ON eg.groupID = ug.id');
            $query->where("eventID = '$id'");
            $dbo->setQuery((string)$query);
            $resourcesResults = array_merge($resourcesResults, $dbo->loadAssocList());
            
            $query = $dbo->getQuery(true);
            $query->select('id, name, "teacher" AS type');
            $query->from('#__thm_organizer_event_teachers AS et');
            $query->innerJoin('#__thm_organizer_teachers AS t ON et.teacherID = t.id');
            $query->where("eventID = '$id'");
            $dbo->setQuery((string)$query);
            $resourcesResults = array_merge($resourcesResults, $dbo->loadAssocList());

            $query = $dbo->getQuery(true);
            $query->select('id, name, "room" AS type');
            $query->from('#__thm_organizer_event_rooms AS er');
            $query->innerJoin('#__thm_organizer_rooms AS r ON er.roomID = r.id');
            $query->where("eventID = '$id'");
            $dbo->setQuery((string)$query);
            $resourcesResults = array_merge($resourcesResults, $dbo->loadAssocList());

            $resources = array();
            foreach($resourcesResults as $result) $resources[] = $result['id'];

            $resourceString = (count($resources))? implode(", ", $resources) : "";
            $this->events[$k]['resourceArray'] = $resourcesResults;
            $this->events[$k]['resources'] = $resourceString;

        }
    }

    private function setUserPermissions()
    {
        $this->canWrite = $this->canUserWrite();
        $this->canEdit = $this->canUserEdit();
    }

    /**
     * function canWrite
     *
     * checks whether the registered user has permission to write content in at
     * least one associated content category
     *
     * @access private
     * @return boolean $canWrite true if user can write an an associated content
     * category, otherwise false
     */
    private function canUserWrite()
    {
        $canWrite = false;

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT asset_id");
        $query->from("#__categories AS c");
        $query->innerJoin("#__thm_organizer_categories AS ec ON ec.contentCatID = c.id");
        $dbo->setQuery((string)$query);
        $assetIDs = $dbo->loadResultArray();
        if(count($assetIDs))
        {
            foreach($assetIDs as $assetID)
            {
                if($canWrite == true)return $canWrite;
                else $canWrite = JAccess::check (JFactory::getUser ()->id, 'core.create', $assetID);
            }
            return $canWrite;
        }
        return $canWrite;
    }

    private function canUserEdit()
    {
        return true;
    }

}