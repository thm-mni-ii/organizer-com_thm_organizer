<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelEvent_Edit
 * @description create/edit appointment/event model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('thm_core.edit.model');
require_once JPATH_COMPONENT_SITE . '/helpers/access.php';

/**
 * Retrieves persistent data for output in the event edit view.
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelEvent_Edit extends THM_CoreModelEdit
{

    /**
     * Loads event data
     *
     * @param  int  $eventID  the id of the event to be loaded
     *
     * @return  mixed    Object on success, false on failure.
     */
    public function getItem($eventID = null)
    {
        $event = $this->loadEvent($eventID);
        $event->params = JFactory::getApplication()->getParams();
        $this->setAccess($event);
        $this->setEnvironmentVars($event);
        return $event;
    }

    /**
     * Retrieves data for existing events composed of data from the events table and the content table
     *
     * @param   int  $eventID  the id of the event and associated content
     *
     * @return  object|bool  object with event information on success, otherwise false
     */
    private function loadEvent($eventID)
    {
        if (empty($eventID))
        {
            return $this->getEmptyEvent();
        }

        $select = "e.id AS id, e.categoryID, ";
        $select .= "e.startdate, e.enddate,e.starttime, e.endtime, ";
        $select .= "e.recurrence_type, e.global, e.reserves, ";
        $select .= "c.title AS title, c.fulltext AS description, ";
        $select .= "c.publish_up, c.publish_down, c.created_by";

        $query = $this->_db->getQuery(true);
        $query->select($select);
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->where("e.id = '$eventID'");
        $this->_db->setQuery((string) $query);

        try
        {
            $event = $this->_db->loadObject();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        if (empty($event))
        {
            return $this->getEmptyEvent();
        }

        if (!empty($event->description))
        {
            $event->description = stripslashes($event->description);
        }

        $event->startdate = date_format(date_create($event->startdate), 'd.m.Y');
        $event->enddate = date_format(date_create($event->enddate), 'd.m.Y');
        $event->enddate = ($event->enddate== '00.00.0000')? '' : $event->enddate;
        $event->starttime = date_format(date_create($event->starttime), 'H:i');
        $event->starttime = ($event->starttime == '00:00')? '' : $event->starttime;
        $event->endtime = date_format(date_create($event->endtime), 'H:i');
        $event->endtime = ($event->endtime == '00:00')? '' : $event->endtime;

        $event->publish_up = date_format(date_create($event->publish_up), 'd.m.Y');
        $event->publish_down = date_format(date_create($event->publish_down), 'd.m.Y');

        return $event;
    }

    /**
     * Creates an empty event
     *
     * @return  object  an object encompassing the properties of the events table
     */
    private function getEmptyEvent()
    {
        $return = new stdClass;
        $return->id = 0;
        $return->categoryID = '';
        $return->startdate = '';
        $return->enddate = '';
        $return->starttime = '';
        $return->endtime = '';
        $return->recurrence_type = 0;
        $return->global = 0;
        $return->reserves = 0;
        $return->title = '';
        $return->description = '';
        $return->publish_up = '';
        $return->publish_down = '';
        return $return;
    }

    /**
     * Sets access parameters for the event based on content handling
     *
     * @param   object  $event  the event object
     */
    private function setAccess(&$event)
    {
        $canCreate = THM_OrganizerHelperAccess::canCreateEvents();

        if (empty($event->id) AND $canCreate)
        {
            $event->params->set('access-create', true);
            return;
        }

        $canEdit = THM_OrganizerHelperAccess::canEditEvent($event->id, $event->created_by);
        if (!empty($event->id) AND $canEdit)
        {
            $event->params->set('access-edit', true);
            return;
        }
    }

    /**
     * Checks whether the view is associated with a menu entry or a call from the scheduler view.
     *
     * @param   object  &$event  the event object
     *
     * @return void  sets object variables
     */
    private function setEnvironmentVars(&$event)
    {
        $app = JFactory::getApplication();
        $event->scheduleCall = $app->input->getInt('scheduleCall', 0);
        $menuID = $app->input->getInt('Itemid', 0);
        if (empty($menuID))
        {
            $event->isManager = false;
            $event->isEdit = false;
            return;
        }

        $query = $this->_db->getQuery(true);
        $query->select("link");
        $query->from("#__menu");
        $query->where("id = '$menuID'");
        $this->_db->setQuery((string) $query);
        
        try
        {
            $link = $this->_db->loadResult();
            $event->isManager = strpos($link,'event_manager') !== false;
            $event->managerLink = empty($event->isManager)? '' : $link;
            $event->isEdit = strpos($link,'event_edit') !== false;
        }
        catch (Exception $exc)
        {
            $app->enqueueMessage($exc->getMessage(), 'error');
            $event->isManager = false;
            $event->isEdit = false;
        }
    }
}
