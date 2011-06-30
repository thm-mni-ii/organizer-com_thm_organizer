<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        model for
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

//echo "<pre>".print_r($this->menuParameters, true)."</pre>"; //template for test outputs
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.modelform');

define('CURRENT', 0);
define('CURRENT_CATEGORY', 1);
define('CURRENT_ROOM', 2);
define('CURRENT_OWN', 3);
define('ALL', 4);
define('ALL_CATEGORY', 5);
define('ALL_ROOM', 6);
define('ALL_OWN', 7);

class thm_organizerModelevent_list extends JModelForm
{
    private $callParameters = null;
    private $menuParameters = null;
    private $formParameters = null;
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
        jimport('joomla.html.pagination');
        $this->getTotal();
        $this->setLimits();
        $this->pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));

        if($this->total)
        {
            $this->loadEvents();
            $this->loadEventResources();
        }
        $this->loadCategories();
        $this->setUserPermissions();
        $form = $this->getForm();
        $form->bind($this->formParameters);
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
        if(count($this->callParameters)) $this->display_type = $this->callParameters["display_type"];
        else if($this->menuParameters->get('display_type'))
            $this->display_type = $this->menuParameters->get('display_type');
        else $this->display_type = 0;
        switch ($this->display_type)
        {
            case CURRENT_ROOM:
                $this->setRoomID();
                break;
            case CURRENT_OWN:
                $this->setState('author', $username);
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
        $this->formParameters = array();
        $this->setCategoryID();
        $this->setFromDate();
        $this->setToDate();
        $this->setSearch();
        $this->setOrderBy();
    }

    private function setCategoryID()
    {
        $application = JFactory::getApplication();
        $categoryRestriction = ($this->display_type == ALL_CATEGORY or $this->display_type == CURRENT_CATEGORY);
        if(isset($this->callParameters) and isset($this->callParameters["categoryID"]))
            $categoryID = $this->callParameters["categoryID"];
        else if($categoryRestriction and $this->menuParameters->get('category_restriction'))
            $categoryID = $this->menuParameters->get('category_restriction');
        else if(JRequest::getVar('categoryID'))
            $categoryID = JRequest::getVar('categoryID');
        else
            $categoryID = $application->getUserStateFromRequest('com_thm_organizer.event_list.categoryID', 'categoryID', -1, 'int');
        if($categoryID != -1) $this->setState('categoryID', $categoryID);
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
        $jform = JRequest::getVar('jform');
        if(isset($this->callParameters) and isset($this->callParameters["fromDate"]))
            $fromDate = $this->callParameters["fromDate"];
        else if(isset($jform) and isset($jform['fromdate'])) $fromDate = $jform['fromdate'];
        else $fromDate = $application->getUserStateFromRequest('com_thm_organizer.event_list.fromdate', 'fromdate', '');
        if(isset($fromDate))
        {
            $this->setState('fromdate', $fromDate);
            $this->formParameters['fromdate'] = $fromDate;
        }
        else $this->formParameters['fromdate'] = "";
    }

    private function setToDate()
    {
        $application = JFactory::getApplication();
        $jform = JRequest::getVar('jform');
        if(isset($this->callParameters) and isset($this->callParameters["toDate"]))
            $toDate = $this->callParameters["fromDate"];
        else if(isset($jform) and isset($jform['todate'])) $toDate = $jform['todate'];
        else $toDate = $application->getUserStateFromRequest('com_thm_organizer.event_list.todate', 'todate', '');
        if(isset($toDate))
        {
            $this->setState('todate', $toDate);
            $this->formParameters['todate'] = $toDate;
        }
        else $this->formParameters['todate'] = "";
    }

    private function setSearch()
    {
        $application = JFactory::getApplication();
        $jform = JRequest::getVar('jform');
        if(isset($this->callParameters) and isset($this->callParameters["search"]))
            $search = $this->callParameters["search"];
        else if(isset($jform) and isset($jform['thm_organizer_el_search_text']))
            $search = $jform['thm_organizer_el_search_text'];
        else $search = $application->getUserStateFromRequest('com_thm_organizer.event_list.search', 'search', '');
        if(isset($search))
        {
            $this->setState('search', $search);
            $this->formParameters['thm_organizer_el_search_text'] = $search;
        }
        else $this->formParameters['thm_organizer_el_search_text'] = "";
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

    private function setLimits()
    {
        $limit = (JRequest::getInt('limit'))? JRequest::getInt('limit') : $this->total;
        $this->setState('limit', $limit);

        $limitstart = (JRequest::getInt('limitstart'))? JRequest::getInt('limitstart') : 0;
        $this->setState('limitstart', $limitstart);
    }

    /**
     * funtion getTotal()
     *
     * counts the total number of entries fulfilling the search criteria
     *
     * @return int $total
     */
    private function getTotal()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('COUNT(DISTINCT(e.id))');
        $this->getFrom(&$query);
        $this->getWhere(&$query);
        $dbo->setQuery((string)$query);
        $total = $dbo->loadResult();
        $this->total = $total;
    }

    /**
     * getFrom
     *
     * sets the query's from clause
     *
     * @access private
     * @param object $query JDatabaseQuery Object the query to be modified
     */
    private function getFrom(&$query)
    {
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->innerJoin("#__thm_organizer_categories AS ecat ON e.categoryID = ecat.id");
        $query->innerJoin("#__categories AS ccat ON ecat.contentCatID = ccat.id");
        $query->innerJoin("#__users AS u ON c.created_by = u.id");
        $query->leftJoin("#__thm_organizer_event_teachers AS et ON e.id = et.eventID");
        $query->leftJoin("#__thm_organizer_teachers AS t ON et.teacherID = t.id");
        $query->leftJoin("#__thm_organizer_event_rooms AS er ON e.id = er.eventID");
        $query->leftJoin("#__thm_organizer_rooms AS r ON er.roomID = r.id");
        $query->leftJoin("#__thm_organizer_event_groups AS eg ON e.id = eg.eventID");
        $query->leftJoin("#__usergroups AS ug ON eg.groupID = ug.id");
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
    private function getWhere(&$query)
    {
        //view access
        $user = JFactory::getUser();
        $viewAccessLevels = $user->getAuthorisedViewLevels();
        sort($viewAccessLevels);
        $maxViewAccessLevel = array_pop($viewAccessLevels);
        $query->where("c.access <= '$maxViewAccessLevel' AND ccat.access <= '$maxViewAccessLevel'");

        //menu restrictions
        $author = $this->getState('author');
        if(isset($author)) $query->where("author = '$author'");
        $room = $this->getState('room');
        if(isset($room)) $query->where("r.id = '$room'");
        $categoryID = $this->getState('categoryID');
        if(isset($categoryID)) $query->where("e.categoryID = '$categoryID'");

        //search items
        $search = $this->getState('search');
        $searchItems = array();
        if(!empty($search))$searchItems = explode(",", $search);
        if(count($searchItems))
        {
            $wherray = array();
            foreach($searchItems as $item)
            {
                $restriction = "(c.title LIKE '%$item%') ";
                $restriction .= "OR (c.introtext LIKE '%$item%') ";
                $restriction .= "OR (ecat.title LIKE '%$item%') ";
                $restriction .= "OR (ccat.title LIKE '%$item%') ";
                $restriction .= "OR (r.name LIKE '%$item%') ";
                $restriction .= "OR (t.name LIKE '%$item%') ";
                $restriction .= "OR (u.name LIKE '%$item%') ";
                $restriction .= "OR (ug.title LIKE '%$item%') ";
                $wherray[] = "(".$restriction.")";
            }
            $query->where(implode(" AND ", $wherray));
        }

        $fromdate = $this->getState('fromdate');
        if(empty($fromdate) AND $this->display_type < 4) $fromdate = date ('Y-m-d');
        if(!empty($fromdate))
        {
            $temptime = strtotime($fromdate);
            $fromdate = date('Y-m-d', $temptime);
            $query->where("( startdate >= '$fromdate' OR enddate >= '$fromdate' )");
        }
        $todate = $this->getState('todate');
        if(!empty($todate))
        {
            $temptime = strtotime($todate);
            $todate = date('Y-m-d', $temptime);
            $query->where("( startdate <= '$todate' OR enddate <= '$todate' )");
        }
    }

    /**
     * loadEvents()
     *
     * loads event entries
     *
     * @access private
     */
    private function loadEvents()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $this->getSelect(&$query);
        $this->getFrom(&$query);
        $this->getWhere(&$query);
        $this->getOrderBy(&$query);
        $query = (string) $query;
        $dbo->setQuery($query, $this->getState('limitstart'), $this->getState('limit'));
        $events = $dbo->loadAssocList();

        //check for empty
        foreach ($events as $k => $v)
        {
            $edSet = $stSet = $etSet = false;
            $displayDates = $timestring = "";
            $edSet = ($v['enddate'] != "00.00.0000" and $v['enddate'] != $v['startdate']);
            $stSet = $v['starttime'] != "00:00";
            $etSet = $v['endtime'] != "00:00";
            if($stSet and $etSet) $timestring = " ({$v['starttime']} - {$v['endtime']})";
            else if($stSet) $timestring = " (ab {$v['starttime']})";
            else if($etSet) $timestring = " (bis {$v['endtime']})";
            else $timestring = " ".JText::_("COM_THM_ORGANIZER_EL_ALLDAY");
            if($edSet and $v['rec_type'] == 0)
            {
                $displayDates = "{$v['startdate']}";
                if($stSet) $displayDates .= " ({$v['starttime']})";
                $displayDates .= " - {$v['enddate']}";
                if($etSet) $displayDates .= " ({$v['endtime']})";
                $events[$k]['displayDates'] = $displayDates;
            }
            else if($edSet and $v['rec_type'] == 1)
                $events[$k]['displayDates'] = $v['startdate']." - ".$v['enddate']." ".$timestring;
            else
                $events[$k]['displayDates'] = $v['startdate']." ".$timestring;
            $events[$k]['detailsLink'] = "index.php?option=com_thm_organizer&view=event&eventID=".$v['id']."&Itemid=";
            $events[$k]['categoryLink'] = "index.php?option=com_thm_organizer&view=event_list&categoryID=".$v['eventCategoryID']."&Itemid=";
        }
        $this->total = count($events);
        $this->events = $events;
    }

    private function getSelect(&$query)
    {
        $select = "DISTINCT(e.id) AS id, ";
        $select .= "e.categoryID AS eventCategoryID, ";
        $select .= "DATE_FORMAT(e.startdate, '%d.%m.%Y') AS startdate, ";
        $select .= "DATE_FORMAT(e.startdate, '%Y.%m.%d') AS startdateStandardFormat, ";
        $select .= "DATE_FORMAT(e.enddate, '%d.%m.%Y') AS enddate, ";
        $select .= "SUBSTR(e.starttime, 1, 5) AS starttime, ";
        $select .= "SUBSTR(e.endtime, 1, 5) AS endtime, ";
        $select .= "e.recurrence_type AS rec_type, ";
        $select .= "ecat.title AS eventCategory, ";
        $select .= "ecat.contentCatID AS contentCategoryID, ";
        $select .= "c.title AS title, ";
        $select .= "c.introtext AS description, ";
        $select .= "c.access AS contentAccess, ";
        $select .= "ccat.title AS contentCategory, ";
        $select .= "ccat.access AS contentCategoryAccess, ";
        $select .= "u.name AS author, ";
        $select .= "u.id AS authorID ";
        $query->select($select);
    }

    /**
    * Build the order clause
    *
    * @access private
    * @return string
    */
    function getOrderBy(&$query)
    {
        $orderby = $this->getState('orderby');
        $sortCriteria = array('title', 'author', 'eventCategory', 'date');
        if(isset($orderby) && in_array($orderby, $sortCriteria))
        {
            $orderbydir = $this->getState('orderbydir');
            $orderbydir = isset($orderbydir)? $orderbydir : 'ASC';
            if($orderby == 'date') $orderbyClause = "startdateStandardFormat $orderbydir, starttime $orderbydir";
            else $orderbyClause = "$orderby $orderbydir";
            $query->order($orderbyClause);
        }
        else $query->order('startdateStandardFormat ASC, starttime ASC');
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
        $query = $dbo->getQuery(true);
        $query->select('id, title, description');
        $query->from('#__thm_organizer_categories');
        if($this->display_type == 1 or $this->display_type == 5)
        {
            $categoryID = $this->getState('categoryID');
            $query->where("id = '$categoryID'");
        }
        $dbo->setQuery((string)$query);
        $this->categories = $dbo->loadAssocList();
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
            $resourceNames = array();

            $query = $dbo->getQuery(true);
            $query->select('id, title AS name, "group" AS type ');
            $query->from('#__thm_organizer_event_groups AS eg');
            $query->innerJoin('#__usergroups AS ug ON eg.groupID = ug.id');
            $query->where("eventID = '$id'");
            $dbo->setQuery((string)$query);
            $resourcesResults = array_merge($resourcesResults, $dbo->loadAssocList());
            $resourceNames = array_merge($resourceNames, $dbo->loadResultArray(1));

            $query = $dbo->getQuery(true);
            $query->select('id, name, "teacher" AS type');
            $query->from('#__thm_organizer_event_teachers AS et');
            $query->innerJoin('#__thm_organizer_teachers AS t ON et.teacherID = t.id');
            $query->where("eventID = '$id'");
            $dbo->setQuery((string)$query);
            $resourcesResults = array_merge($resourcesResults, $dbo->loadAssocList());
            $resourceNames = array_merge($resourceNames, $dbo->loadResultArray(1));

            $query = $dbo->getQuery(true);
            $query->select('id, name, "room" AS type');
            $query->from('#__thm_organizer_event_rooms AS er');
            $query->innerJoin('#__thm_organizer_rooms AS r ON er.roomID = r.id');
            $query->where("eventID = '$id'");
            $dbo->setQuery((string)$query);
            $resourcesResults = array_merge($resourcesResults, $dbo->loadAssocList());
            $resourceNames = array_merge($resourceNames, $dbo->loadResultArray(1));

            $resourceNames = (count($resourceNames))? implode(", ", $resourceNames) : "";

            $this->events[$k]['resourceArray'] = $resourcesResults;
            $this->events[$k]['resources'] = $resourceNames;

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
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT c.id");
        $query->from("#__categories AS c");
        $query->innerJoin("#__thm_organizer_categories AS ec ON ec.contentCatID = c.id");
        $dbo->setQuery((string)$query);
        $categoryIDs = $dbo->loadResultArray();

        $canWrite = false;
        $user = JFactory::getUser();
        if(count($categoryIDs))
        {
            foreach($categoryIDs as $categoryID)
            {
                $canWrite = $user->authorize('core.create', 'com_content.category'.$categoryID);
                if($canWrite == true)break;
            }
        }
        return $canWrite;
    }

    private function canUserEdit()
    {
        $user = JFactory::getUser();
        $allowEdit = false;
        if(count($this->events))
            foreach($this->events as $k => $v)
            {
                $isAuthor = ($user->id == $v['authorID'])? true : false;
                $assetname = "com_conten.article.{$v['id']}";
                $canEdit = $user->authorise('core.edit', $assetname);
                $canEditOwn = ($isAuthor)? $user->authorise('core.edit.own') : false;
                $shouldAllowEdit = $canEdit or $canEditOwn;
                $this->events[$k]['userCanEdit'] = $shouldAllowEdit;
                if($shouldAllowEdit) $allowEdit = true;
            }
        return $allowEdit;
    }

    /**
     * Method to get the record form.
     *
     * @param array   $data Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return mixed A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_thm_organizer.event_list', 'event_list',
                                array('control' => 'jform', 'load_data' => $loadData));
        if(empty($form)) return false;
        return $form;
    }

    public function reservesobjects($catID)
    {
    	$dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("reservesobjects");
        $query->from("#__thm_organizer_categories");
        $query->where("id = $catID");
        $dbo->setQuery((string)$query);
        return (bool)$dbo->loadResult();
    }

    public function globaldisplay($catID)
    {
    	$dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("globaldisplay");
        $query->from("#__thm_organizer_categories");
        $query->where("id = $catID");
        $dbo->setQuery((string)$query);
        return (bool)$dbo->loadResult();
    }
}