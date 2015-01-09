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
jimport('thm_core.list.model');
require_once JPATH_COMPONENT_SITE . "/helpers/access.php";
require_once JPATH_COMPONENT_SITE . "/helpers/event.php";

/**
 * Retrieves persistent data for output in the event list view.
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelEvent_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'startdate';

    protected $defaultDirection = 'DESC';

    public $params = null;

    /**
     * Constructor to set up the configuration and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('c.title', 'author', 'category', 'startdate');
        }
        $this->params = JFactory::getApplication()->getParams();
        $canCreate = THM_OrganizerHelperAccess::canCreateEvents();
        $this->params->set('access-create', $canCreate);
        $this->params->set('access-edit', false);
        $this->params->set('access-delete', false);
        parent::__construct($config);
    }

    /**
     * Method to select all event rows from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Perform the database request
        $query = $this->_db->getQuery(true);
        $select = 'DISTINCT e.id, c.title, c.created_by, cat.title as category, e.startdate, e.starttime, e.enddate, ';
        $select .= 'e.endtime, e.recurrence_type, u.name AS author, ';
        $parts = array("'index.php?option=com_thm_organizer&view=event_edit&id='", "e.id");
        $select .= $query->concatenate($parts) . " AS link";
        $query->select($select);
        $query->from('#__thm_organizer_events AS e');
        $query->innerJoin('#__content AS c ON e.id = c.id');
        $query->innerJoin('#__categories AS cat ON c.catid = cat.id');
        $query->innerJoin('#__users AS u ON c.created_by = u.id');

        // Add joins for resources
        $query->leftJoin('#__thm_organizer_event_groups AS eg ON e.id = eg.eventID');
        $query->leftJoin('#__usergroups AS g ON eg.groupID = g.id');
        $query->leftJoin('#__thm_organizer_event_teachers AS et ON e.id = et.eventID');
        $query->leftJoin('#__thm_organizer_teachers AS t ON et.teacherID = t.id');
        $query->leftJoin('#__thm_organizer_event_rooms AS er ON e.id = er.eventID');
        $query->leftJoin('#__thm_organizer_rooms AS r ON er.roomID = r.id');

        $groups = JFactory::getUser()->getAuthorisedViewLevels();
        $query->where("c.access in ('" . implode("', '", $groups) . "')");

        $search = $this->state->get( 'filter.search', '');
        if (!empty($search))
        {
            $contentColumns = array('c.title', 'c.introtext', 'c.fulltext', 'cat.title', 'cat.description');
            $resourceColumns = array('g.title', 't.surname', 't.forename', 'r.name', 'r.longname');
            $columns = array_merge($contentColumns, $resourceColumns);
            $this->setSearchFilter($query, $columns);
            $this->setResourceFilter($query);
        }
        $this->setIDFilter($query, 'categoryID', array('cat.id'));

        $displayType = $this->params->get('display_type', 0);
        $fromDate = $this->state->get('fromdate', '');

        // No specific start date selected and only current events displayed
        if (empty($fromDate) AND $displayType < 4)
        {
            $fromDate = date('Y-m-d');
        }

        if (!empty($fromDate) AND is_numeric($fromDate))
        {
            $fromStamp = strtotime($fromDate);
            $query->where(" $fromStamp BETWEEN e.start AND e.end");
        }

        $toDate = $this->state->get('todate', '');
        if (!empty($toDate) AND is_numeric($toDate))
        {
            $toStamp = strtotime($toDate);
            $query->where(" $toStamp BETWEEN e.start AND e.end");
        }

        $this->setOrdering($query);
        return $query;
    }

    /**
     * Function to feed the data in the table body correctly to the list view
     *
     * @return array consisting of items in the body
     */
    public function getItems()
    {
        $items = parent::getItems();
        $events = array();
        if (empty($items))
        {
            return $events;
        }

        $index = 0;
        foreach ($items as $event)
        {
            $events[$index] = array();
            $canEdit = THM_OrganizerHelperAccess::canEditEvent($event->id, $event->created_by);
            $editParam = $this->params->get('access-edit', false);
            if ($canEdit AND empty($editParam))
            {
                $this->params->set('access-edit', true);
            }
            $canDelete = THM_OrganizerHelperAccess::canDeleteEvent($event->id);
            $deleteParam = $this->params->get('access-delete', false);
            if ($canEdit AND empty($deleteParam))
            {
                $this->params->set('access-delete', true);
            }
            $timeText = THM_OrganizerHelperEvent::getDateText($event, false);
            $resourceText = $this->getResourceText($event->id);

            $events[$index]['checkbox'] = ($canEdit OR $canDelete)? JHtml::_('grid.id', $index, $event->id) : '';
            if ($canEdit)
            {
                $events[$index]['title'] = JHtml::_('link', $event->link, $event->title);
                $events[$index]['time'] = JHtml::_('link', $event->link, $timeText);
                $events[$index]['resources'] = JHtml::_('link', $event->link, $resourceText);
                $events[$index]['category'] = JHtml::_('link', $event->link, $event->category);
                $events[$index]['author'] = JHtml::_('link', $event->link, $event->author);
            }
            else
            {
                $events[$index]['title'] = $event->title;
                $events[$index]['time'] = $timeText;
                $events[$index]['resources'] = $resourceText;
                $events[$index]['category'] = $event->category;
                $events[$index]['author'] = $event->author;
            }
            $index++;
        }
        return $events;
    }

    /**
     * Creates a text listing the resources associated with the event
     *
     * @param   int  $eventID  the id of the event
     *
     * @return  string  the resources associated with the event
     */
    private function getResourceText($eventID)
    {
        $groupNames = $this->getResourceNames($eventID, 'group', 'title', '#__usergroups');
        $teacherNames = $this->getResourceNames($eventID, 'teacher', 'surname', '#__thm_organizer_teachers');
        $roomNames = $this->getResourceNames($eventID, 'room', 'longname', '#__thm_organizer_rooms');
        $allNames = array_merge($groupNames, $teacherNames, $roomNames);
        return implode(', ', $allNames);
    }

    /**
     * Gets the names for a specific resource type
     *
     * @param   int  $eventID  the event id
     * @param   string  $type  the resource type
     * @param   string  $nameColumn  the column in which the names ar stored
     * @param   string  $table       the table where the resources are stored
     *
     * @return  array  an array of names
     */
    private function getResourceNames($eventID, $type, $nameColumn, $table)
    {
        $query = $this->_db->getQuery(true);
        $query->select($nameColumn);
        $query->from("#__thm_organizer_event_{$type}s AS assoc");
        $query->innerJoin("$table AS resource ON assoc.{$type}ID = resource.id");
        $query->where("assoc.eventID = '$eventID'");
        $this->_db->setQuery($query);

        try
        {
            $names = $this->_db->loadAssoc();
            return empty($names)? array() : $names;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);

        $headers = array();
        $headers['checkbox'] = '';
        $headers['title'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_TITLE', 'title', $direction, $ordering);
        $headers['time'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DATES', 'run', $direction, $ordering);
        $headers['resources'] = JText::_('COM_THM_ORGANIZER_RESOURCES');
        $headers['category'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_CATEGORY', 'category', $direction, $ordering);
        $headers['author'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_AUTHOR', 'author', $direction, $ordering);

        return $headers;
    }
}
