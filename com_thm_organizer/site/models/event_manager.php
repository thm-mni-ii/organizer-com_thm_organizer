<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelEvent_manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modelform');
require_once JPATH_COMPONENT . "/assets/classes/eventAccess.php";
define('CURRENT', 0);
define('CURRENT_CATEGORY', 1);
define('CURRENT_ROOM', 2);
define('CURRENT_OWN', 3);
define('ALL', 4);
define('ALL_CATEGORY', 5);
define('ALL_ROOM', 6);
define('ALL_OWN', 7);

/**
 * Retrieves persistent data for output in the event list view.
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelEvent_Manager extends JModelForm
{
    private $_callParameters = null;

    private $_menuParameters = null;

    private $_formParameters = null;

    public $display_type;

    public $events = null;

    public $total = null;

    public $pagination = null;

    public $categories = null;

    public $canWrite = false;

    public $canEdit = false;

    /**
     * Builds the model for the event list view
     *
     * @param   array  $callParameters  an array containing the values normally in post
     *                                  for external calls on this model
     */
    public function __construct($callParameters = null)
    {
        parent::__construct();
        if (isset($callParameters))
        {
            $this->_callParameters = $callParameters;
        }
        $this->_menuParameters = JFactory::getApplication()->getParams();
        $this->restoreState();
        jimport('joomla.html.pagination');
        $this->getTotal();
        $this->setLimits();
        $this->pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));

        if ($this->total)
        {
            $this->loadEvents();
            $this->loadEventResources();
        }
        $this->loadCategories();
        $this->setUserPermissions();
        $form = $this->getForm();
        $form->bind($this->_formParameters);
    }

    /**
     * Restores/sets state variables from the menu parameters and user form entries
     *
     * @return void
     */
    private function restoreState()
    {
        $username = JFactory::getUser()->username;
        if (count($this->_callParameters))
        {
            $this->display_type = $this->_callParameters["display_type"];
        }
        elseif ($this->_menuParameters->get('display_type'))
        {
            $this->display_type = $this->_menuParameters->get('display_type');
        }
        else
        {
            $this->display_type = 0;
        }
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
        $this->_formParameters = array();
        $this->setCategoryID();
        $this->setFromDate();
        $this->setToDate();
        $this->setSearch();
        $this->setOrderBy();
    }

    /**
     * Sets the category id used in the building of the event list based upon
     * menu settings and user selection
     *
     * @return void
     */
    private function setCategoryID()
    {
        $app = JFactory::getApplication();
        $categoryRestriction = ($this->display_type == ALL_CATEGORY OR $this->display_type == CURRENT_CATEGORY);
        $useCallParameters = (!empty($this->_callParameters) AND !empty($this->_callParameters["categoryID"]));
        $useMenuParameters = ($categoryRestriction AND !empty($this->_menuParameters->get('category_restriction')));
        if ($useCallParameters)
        {
            $categoryID = $this->_callParameters["categoryID"];
        }
        elseif ($useMenuParameters)
        {
            $categoryID = $this->_menuParameters->get('category_restriction');
        }
        elseif ($app->input->getInt('categoryID'))
        {
            $categoryID = JFactory::getApplication()->input->get('categoryID');
        }
        else
        {
            $categoryID = $app->getUserStateFromRequest('com_thm_organizer.event_manager.categoryID', 'categoryID', -1, 'int');
        }

        if ($categoryID != -1)
        {
            $this->setState('categoryID', $categoryID);
        }
    }

    /**
     * Sets the room id used in the search based upon menu settings and user
     * request data
     *
     * @return void
     */
    private function setRoomID()
    {
        $roomID = (isset($this->_callParameters) and isset($this->_callParameters['roomID']))?
            $this->_callParameters["roomID"] : $this->_menuParameters->get('room_restriction');
        if (isset($roomID))
        {
            $this->setState('roomID', $roomID);
        }
    }

    /**
     * Sets the date from when events should be selected
     *
     * @return void
     */
    private function setFromDate()
    {
        $app = JFactory::getApplication();
        $jform = $app->input->get('jform', array(), 'array');
        if (isset($this->_callParameters) and isset($this->_callParameters["fromDate"]))
        {
            $fromDate = $this->_callParameters["fromDate"];
        }
        elseif (isset($jform) and isset($jform['fromdate']))
        {
            $fromDate = $jform['fromdate'];
        }
        else
        {
            $fromDate = $app->getUserStateFromRequest('com_thm_organizer.event_manager.fromdate', 'fromdate', '');
        }
        if (isset($fromDate))
        {
            $this->setState('fromdate', $fromDate);
            $this->_formParameters['fromdate'] = $fromDate;
        }
        else
        {
            $this->_formParameters['fromdate'] = "";
        }
    }

    /**
     * Sets a maximal date for the run of an event
     *
     * @return void
     */
    private function setToDate()
    {
        $app = JFactory::getApplication();
        $jform = $app->input->get('jform', array(), 'array');
        if (isset($this->_callParameters) and isset($this->_callParameters["toDate"]))
        {
            $toDate = $this->_callParameters["fromDate"];
        }
        elseif (isset($jform) and isset($jform['todate']))
        {
            $toDate = $jform['todate'];
        }
        else
        {
            $toDate = $app->getUserStateFromRequest('com_thm_organizer.event_manager.todate', 'todate', '');
        }
        if (isset($toDate))
        {
            $this->setState('todate', $toDate);
            $this->_formParameters['todate'] = $toDate;
        }
        else
        {
            $this->_formParameters['todate'] = "";
        }
    }

    /**
     * Sets search parameters as entered by the user
     *
     * @return void
     */
    private function setSearch()
    {
        $app = JFactory::getApplication();
        $jform = $app->input->get('jform', array(), 'array');
        if (isset($this->_callParameters) and isset($this->_callParameters["search"]))
        {
            $search = $this->_callParameters["search"];
        }
        elseif (isset($jform) and isset($jform['thm_organizer_el_search_text']))
        {
            $search = $jform['thm_organizer_el_search_text'];
        }
        else
        {
            $search = $app->getUserStateFromRequest('com_thm_organizer.event_manager.search', 'search', '');
        }
        if (isset($search))
        {
            $this->setState('search', $search);
            $this->_formParameters['thm_organizer_el_search_text'] = $search;
        }
        else
        {
            $this->_formParameters['thm_organizer_el_search_text'] = "";
        }
    }

    /**
     * Sets the column and direction of the query used for sorting
     *
     * @return void
     */
    private function setOrderBy()
    {
        $application = JFactory::getApplication();
        if (isset($this->_callParameters) and isset($this->_callParameters["orderby"]))
        {
            $orderby = $this->_callParameters["orderby"];
        }
        else
        {
            $orderby = $application->getUserStateFromRequest('com_thm_organizer.event_manager.orderby', 'orderby', 'date');
        }
        $this->setState('orderby', $orderby);

        if (isset($this->_callParameters) and isset($this->_callParameters["orderbydir"]))
        {
            $orderbydir = $this->_callParameters["orderbydir"];
        }
        else
        {
            $orderbydir = $application->getUserStateFromRequest('com_thm_organizer.event_manager.orderbydir', 'orderbydir', 'ASC');
        }
        $this->setState('orderbydir', $orderbydir);
    }

    /**
     * Sets the limits to the number of entries returned by the event list
     *
     * @return void
     */
    private function setLimits()
    {
        $input = JFactory::getApplication()->input;
        $limit = ($input->getInt('limit'))? $input->getInt('limit') : 0;
        $this->setState('limit', $limit);

        $limitStart = ($input->getInt('limitstart'))? $input->getInt('limitstart') : 0;
        $this->setState('limitstart', $limitStart);
    }

    /**
     * funtion getTotal()
     *
     * counts the total number of entries fulfilling the search criteria
     *
     * @return int $total
     */
    public function getTotal()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('COUNT(DISTINCT(e.id))');
        $this->getFrom($query);
        $this->getWhere($query);
        $dbo->setQuery((string) $query);
        
        try
        {
            $total = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        $this->total = $total;
    }

    /**
     * Builds the query's from clause
     *
     * @param   object  &$query  JDatabaseQuery Object the query to be modified
     *
     * @return  void
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
     * Build the where clause for the event list query
     *
     * @param   object  &$query  the query object used to build the list
     *
     * @return string $where the where clause to a query based on model state information
     */
    private function getWhere(&$query)
    {
        // View access
        $user = JFactory::getUser();
        $viewAccessLevels = $user->getAuthorisedViewLevels();
        $viewAccessLevels = "'" . implode("', '", $viewAccessLevels) . "'";
        $query->where("c.access  IN ( $viewAccessLevels ) AND ccat.access IN ( $viewAccessLevels ) ");

        // Menu restrictions
        $author = $this->getState('author');
        if (isset($author))
        {
            $query->where("u.username = '$author'");
        }
        $room = $this->getState('room');
        if (isset($room))
        {
            $query->where("r.id = '$room'");
        }
        $categoryID = $this->getState('categoryID');
        if (isset($categoryID))
        {
            $query->where("e.categoryID = '$categoryID'");
        }

        // Search items
        $search = $this->getState('search');
        $searchItems = array();
        if (!empty($search))
        {
            $searchItems = explode(",", $search);
        }
        if (count($searchItems))
        {
            $wherray = array();
            foreach ($searchItems as $item)
            {
                $restriction = "(c.title LIKE '%$item%') ";
                $restriction .= "OR (c.introtext LIKE '%$item%') ";
                $restriction .= "OR (ecat.title LIKE '%$item%') ";
                $restriction .= "OR (ccat.title LIKE '%$item%') ";
                $restriction .= "OR (r.longname LIKE '%$item%') ";
                $restriction .= "OR (t.surname LIKE '%$item%') ";
                $restriction .= "OR (u.name LIKE '%$item%') ";
                $restriction .= "OR (ug.title LIKE '%$item%') ";
                $wherray[] = "(" . $restriction . ")";
            }
            $query->where(implode(" AND ", $wherray));
        }

        $fromdate = $this->getState('fromdate');
        if (empty($fromdate) AND $this->display_type < 4)
        {
            $fromdate = date('Y-m-d');
        }
        if (!empty($fromdate))
        {
            $temptime = strtotime($fromdate);
            $fromdate = date('Y-m-d', $temptime);
            $query->where("( startdate >= '$fromdate' OR enddate >= '$fromdate' )");
        }
        $todate = $this->getState('todate');
        if (!empty($todate))
        {
            $temptime = strtotime($todate);
            $todate = date('Y-m-d', $temptime);
            $query->where("( startdate <= '$todate' OR enddate <= '$todate' )");
        }
    }

    /**
     * Loads event entries
     *
     * @return void
     */
    private function loadEvents()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $this->getSelect($query);
        $this->getFrom($query);
        $this->getWhere($query);
        $this->getOrderBy($query);
        $dbo->setQuery($query, $this->getState('limitstart'), $this->getState('limit'));
        
        try 
        {
            $events = $dbo->loadAssocList();
            foreach ($events as &$event) 
            {
               $event['startdate'] = date_format(date_create($event['startdate']), 'd.m.Y');
               $event['startdateStandardFormat'] = date_format(date_create($event['startdateStandardFormat']), 'Y.m.d');
               $event['enddate'] = date_format(date_create($event['enddate']), 'd.m.Y');
               $event['starttime'] = date_format(date_create($event['starttime']), 'H:i');
               $event['endtime'] = date_format(date_create($event['endtime']), 'H:i');
            }
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        // Check for empty
        foreach ($events as $k => $v)
        {
            $displayDates = $timestring = "";
            $edSet = ($v['enddate'] != "00.00.0000" and $v['enddate'] != $v['startdate']);
            $stSet = $v['starttime'] != "00:00";
            $etSet = $v['endtime'] != "00:00";
            if ($stSet and $etSet)
            {
                $timestring = " ({$v['starttime']} - {$v['endtime']})";
            }
            elseif ($stSet)
            {
                $timestring = " (ab {$v['starttime']})";
            }
            elseif ($etSet)
            {
                $timestring = " (bis {$v['endtime']})";
            }
            else
            {
                $timestring = " " . JText::_("COM_THM_ORGANIZER_EL_ALLDAY");
            }
            if ($edSet and $v['rec_type'] == 0)
            {
                $displayDates = "{$v['startdate']}";
                if ($stSet)
                {
                    $displayDates .= " ({$v['starttime']})";
                }
                $displayDates .= " - {$v['enddate']}";
                if ($etSet)
                {
                    $displayDates .= " ({$v['endtime']})";
                }
                $events[$k]['displayDates'] = $displayDates;
            }
            elseif ($edSet and $v['rec_type'] == 1)
            {
                $events[$k]['displayDates'] = "{$v['startdate']} - {$v['enddate']} $timestring";
            }
            else
            {
                $events[$k]['displayDates'] = "{$v['startdate']} $timestring";
            }
            $events[$k]['detailsLink'] = "index.php?option=com_thm_organizer&view=event_details&eventID={$v['id']}&Itemid=";
            $events[$k]['categoryLink'] = "index.php?option=com_thm_organizer&view=event_manager&categoryID={$v['eventCategoryID']}&Itemid=";
        }
        $this->total = count($events);
        $this->events = $events;
    }

    /**
     * Builds and sets the select query
     *
     * @param   object  &$query  the query object used for building the event list
     *
     * @return  void
     */
    private function getSelect(&$query)
    {
        $select = "DISTINCT(e.id) AS id, ";
        $select .= "e.categoryID AS eventCategoryID, ";
        $select .= "e.startdate AS startdate, ";
        $select .= "e.startdate AS startdateStandardFormat, ";
        $select .= "e.enddate AS enddate, ";
        $select .= "e.starttime AS starttime, ";
        $select .= "e.endtime AS endtime, ";
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
     * @param   object  &$query  the query object to be modified
     *
     * @return void
     */
    private function getOrderBy(&$query)
    {
        $orderby = $this->getState('orderby');
        $orderbydir = $this->getState('orderbydir');
        $sortCriteria = array('title', 'author', 'eventCategory', 'date');
        if (isset($orderby) AND in_array($orderby, $sortCriteria))
        {
            $orderbydir = isset($orderbydir)? $orderbydir : 'ASC';
            if ($orderby == 'date')
            {
                $orderbyClause = "startdateStandardFormat $orderbydir, starttime $orderbydir";
            }
            else
            {
                $orderbyClause = "$orderby $orderbydir";
            }
            $query->order($orderbyClause);
        }
        else
        {
            $query->order('startdateStandardFormat ASC, starttime ASC');
        }
    }

    /**
     * Get Event Category Information
     *
     * @access public
     * @return category information packed in objects?
     */
    private function loadCategories()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, title, description');
        $query->from('#__thm_organizer_categories');
        if ($this->display_type == 1 or $this->display_type == 5)
        {
            $categoryID = $this->getState('categoryID');
            $query->where("id = '$categoryID'");
        }
        $dbo->setQuery((string) $query);
        
        try 
        {
            $this->categories = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
    }

    /**
     * Build the order clause
     *
     * @return string
     */
    private function loadEventResources()
    {
        $dbo = JFactory::getDbo();
        foreach ($this->events as $k => $v)
        {
            $id = $v['id'];

            $groupQuery = $dbo->getQuery(true);
            $groupQuery->select('id, title AS name, "group" AS type ');
            $groupQuery->from('#__thm_organizer_event_groups AS eg');
            $groupQuery->innerJoin('#__usergroups AS ug ON eg.groupID = ug.id');
            $groupQuery->where("eventID = '$id'");
            $dbo->setQuery((string) $groupQuery);
            
            try 
            {
                $groups = $dbo->loadAssocList();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }
            
            try
            {
                $groupNames = $dbo->loadColumn(1);
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }

            $teacherQuery = $dbo->getQuery(true);
            $teacherQuery->select('id, surname, "teacher" AS type');
            $teacherQuery->from('#__thm_organizer_event_teachers AS et');
            $teacherQuery->innerJoin('#__thm_organizer_teachers AS t ON et.teacherID = t.id');
            $teacherQuery->where("eventID = '$id'");
            $dbo->setQuery((string) $teacherQuery);
            
            try
            {
                $teachers = $dbo->loadAssocList();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }
            
            try
            {
                $teacherNames = $dbo->loadColumn(1);
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }

            $roomQuery = $dbo->getQuery(true);
            $roomQuery->select('id, longname, "room" AS type');
            $roomQuery->from('#__thm_organizer_event_rooms AS er');
            $roomQuery->innerJoin('#__thm_organizer_rooms AS r ON er.roomID = r.id');
            $roomQuery->where("eventID = '$id'");
            $dbo->setQuery((string) $roomQuery);
            
            try 
            {
                $rooms = $dbo->loadAssocList();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }
            
            try
            {
                $roomNames = $dbo->loadColumn(1);
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }

            $resources = array_merge($groups, array_merge($teachers, $rooms));

            $resourceNames = array_merge($groupNames, array_merge($teacherNames, $roomNames));

            $resourceNameList = (count($resourceNames))? implode(", ", $resourceNames) : "";

            $this->events[$k]['resourceArray'] = $resources;
            $this->events[$k]['resources'] = $resourceNameList;

        }
    }

    /**
     * Sets which actions the user is able to perform on the respective event entries
     *
     * @return void
     */
    private function setUserPermissions()
    {
        $this->canWrite = THMEventAccess::canCreate();
        if (count($this->events))
        {
            foreach ($this->events as $k => $v)
            {
                $shouldAllowEdit = THMEventAccess::canEdit($v['id']);
                $this->events[$k]['userCanEdit'] = $shouldAllowEdit;
                if ($shouldAllowEdit)
                {
                    $this->canEdit = true;
                }
            }
        }
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed               A JForm object on success, false on failure
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_thm_organizer.event_manager',
                                'event_manager',
                                array('control' => 'jform', 'load_data' => $loadData)
                               );
        if (empty($form))
        {
            return false;
        }
        return $form;
    }

    /**
     * Determines whether the category in question reserves resources
     *
     * @param   int  $catID  the id of the category to be checked
     *
     * @return  boolean  true if the value reserves, otherwise false
     */
    public function checkReserves($catID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("reserves");
        $query->from("#__thm_organizer_categories");
        $query->where("id = $catID");
        $dbo->setQuery((string) $query);
        try 
        {
            $reserves = (bool) $dbo->loadResult();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
        
        return $reserves;
    }

    /**
     * Determines whether the category in question is global
     *
     * @param   int  $catID  the id of the category to be checked
     *
     * @return  boolean  true if the value is global, otherwise false
     */
    public function checkGlobal($catID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("global");
        $query->from("#__thm_organizer_categories");
        $query->where("id = $catID");
        $dbo->setQuery((string) $query);
        
        try
        {
            $global = (bool) $dbo->loadResult();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
        
        return $global;
    }
}
