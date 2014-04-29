<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelEvent
 * @description create/edit appointment/event model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT . "/assets/classes/eventAccess.php";

/**
 * Retrieves stored event data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelEvent_Details extends JModelLegacy
{
    /**
     * @var int the id of the event in the database
     */
    public $eventID = 0;

    /**
     * @var array of event properties
     */
    public $event = null;

    /**
     * @var string containing the url of the event list menu item from which
     * the user came to this view (if the user came from the event list view)
     */
    public $listLink = "";

    /**
     * @var boolean true if the user is allowed to create events, otherwise false
     */
    public $canWrite = false;

    /**
     * construct
     *
     * calls class functions to load object variables with data
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadEvent();
        if ($this->event['id'] != 0)
        {
            $this->loadEventResources();
            $this->setMenuLinks();
        }
        $this->canWrite = THMEventAccess::canCreate();
    }

    /**
     * loadEvent
     *
     * creates an event as an array of properties and sets this as an object
     * variable
     *
     * @return void
     */
    public function loadEvent()
    {
        $eventID = JFactory::getApplication()->input->getInt('eventID')? JFactory::getApplication()->input->getInt('eventID'): 0;
        $dbo = JFactory::getDBO();

        $query = $dbo->getQuery(true);
        $query->select($this->getSelect());
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->innerJoin("#__users AS u ON c.created_by = u.id");
        $query->innerJoin("#__thm_organizer_categories AS ecat ON e.categoryID = ecat.id");
        $query->innerJoin("#__categories AS ccat ON ecat.contentCatID = ccat.id");
        $query->where("e.id = '$eventID'");
        $dbo->setQuery((string) $query);
        $event = $dbo->loadAssoc();

        if (isset($event))
        {
            $this->eventID = $event['id'];
            if (!empty($event['description']))
            {
                $event['description'] = trim($event['description']);
            }
            if ($event['global'] and $event['reserves'])
            {
                $event['displaybehavior'] = JText::_('COM_THM_ORGANIZER_E_GLOBALRESERVES_EXPLANATION');
            }
            elseif ($event['global'])
            {
                $event['displaybehavior'] = JText::_('COM_THM_ORGANIZER_E_GLOBAL_EXPLANATION');
            }
            elseif ($event['reserves'])
            {
                $event['displaybehavior'] = JText::_('COM_THM_ORGANIZER_E_RESERVES_EXPLANATION');
            }
            else
            {
                $event['displaybehavior'] = JText::_('COM_THM_ORGANIZER_E_NOGLOBALRESERVES_EXPLANATION');
            }
            if ($event['starttime'] == "00:00")
            {
                unset($event['starttime']);
            }
            if ($event['endtime'] == "00:00")
            {
                unset($event['endtime']);
            }
            if ($event['enddate'] == "00.00.0000")
            {
                unset($event['enddate']);
            }
        }
        else
        {
            $this->eventID = 0;
            $event = array();
            $event['id'] = 0;
            $event['title'] = JText::_('COM_THM_ORGANIZER_E_EMPTY');
            $event['alias'] = '';
            $event['description'] = JText::_('COM_THM_ORGANIZER_E_EMPTY_DESC');
            $event['categoryID'] = 0;
            $event['contentID'] = 0;
            $event['startdate'] = '';
            $event['enddate'] = '';
            $event['starttime'] = '';
            $event['endtime'] = '';
            $event['created_by'] = 0;
            $event['created'] = '';
            $event['modified_by'] = 0;
            $event['modified'] = '';
            $event['recurrence_number'] = 0;
            $event['recurrence_type'] = 0;
            $event['recurrence_counter'] = 0;
            $event['image'] = '';
            $event['register'] = 0;
            $event['unregister'] = 0;
        }
        $event['teachers'] = array();
        $event['groups'] = array();
        $event['rooms'] = array();
        if ($event['id'] != 0)
        {
            $event['access'] = THMEventAccess::canEdit($this->event['id']);
        }
        $this->event = $event;
    }

    /**
     * getSelect
     *
     * creates the select clause for the event properties
     *
     * @return string select clause
     */
    private function getSelect()
    {
        $select = "e.id AS id, ";
        $select .= "e.categoryID AS eventCategoryID, ";
        $select .= "DATE_FORMAT(e.startdate, '%d.%m.%Y') AS startdate, ";
        $select .= "DATE_FORMAT(e.enddate, '%d.%m.%Y') AS enddate, ";
        $select .= "SUBSTR(e.starttime, 1, 5) AS starttime, ";
        $select .= "SUBSTR(e.endtime, 1, 5) AS endtime, ";
        $select .= "e.recurrence_type AS rec_type, ";
        $select .= "ecat.title AS eventCategory, ";
        $select .= "ecat.description AS eventCategoryDesc, ";
        $select .= "ecat.contentCatID AS contentCategoryID, ";
        $select .= "ecat.global, ";
        $select .= "ecat.reserves, ";
        $select .= "c.title AS title, ";
        $select .= "c.fulltext AS description, ";
        $select .= "DATE_FORMAT(c.publish_up, '%d.%m.%Y') AS publish_up, ";
        $select .= "DATE_FORMAT(c.publish_down, '%d.%m.%Y') AS publish_down, ";
        $select .= "c.access AS contentAccess, ";
        $select .= "ccat.title AS contentCategory, ";
        $select .= "ccat.description AS contentCategoryDesc, ";
        $select .= "ccat.access AS contentCategoryAccess, ";
        $select .= "u.name AS author, ";
        $select .= "u.id AS authorID ";
        return $select;
    }

    /**
     * loadEventResources
     *
     * calls functions for loading differing sorts of event resources
     *
     * @return void
     */
    private function loadEventResources()
    {
        $this->loadEventRooms();
        $this->loadEventTeachers();
        $this->loadEventGroups();
    }

    /**
     * loadEventRooms
     *
     * loads room data into the event
     *
     * @return void
     */
    private function loadEventRooms()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("name");
        $query->from("#__thm_organizer_event_rooms AS er");
        $query->innerJoin("#__thm_organizer_rooms AS r ON er.roomID = r.id");
        $query->where("er.eventID = '$this->eventID'");
        $dbo->setQuery((string) $query);
        $this->event['rooms'] = $dbo->loadColumn();
    }

    /**
     * loadEventTeachers
     *
     * loads teacher data into the event
     *
     * @return void
     */
    private function loadEventTeachers()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("surname");
        $query->from("#__thm_organizer_event_teachers AS et");
        $query->innerJoin("#__thm_organizer_teachers AS t ON et.teacherID = t.id");
        $query->where("et.eventID = '$this->eventID'");
        $dbo->setQuery((string) $query);
        $this->event['teachers'] = $dbo->loadColumn();
    }

    /**
     * loadEventGroups
     *
     * loads group data into the event
     *
     * @return void
     */
    private function loadEventGroups()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("title AS name");
        $query->from("#__thm_organizer_event_groups AS eg");
        $query->innerJoin("#__usergroups AS ug ON eg.groupID = ug.id");
        $query->where("eg.eventID = '$this->eventID'");
        $dbo->setQuery((string) $query);
        $this->event['groups'] = $dbo->loadColumn();
    }

    /**
     * funtion setMenuLink
     *
     * retrieves the url of the event list menu item and sets the object
     * variable listLink with it
     *
     * @return void
     */
    private function setMenuLinks()
    {
        $menuID = JFactory::getApplication()->input->getInt('Itemid');
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("link");
        $query->from("#__menu AS eg");
        $query->where("id = $menuID");
        $query->where("link LIKE '%event_manager%'");
        $dbo->setQuery((string) $query);
        $link = $dbo->loadResult();
        if (isset($link) and $link != "")
        {
            $this->listLink = JRoute::_($link);
        }
    }
}
