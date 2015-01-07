<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelEvent
 * @description create/edit appointment/event model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_COMPONENT_SITE . "/helpers/access.php";

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
     * Loads event data
     *
     * @return  mixed    Object on success, false on failure.
     */
    public function getItem()
    {
        $app = JFactory::getApplication();
        $modal = $app->input->getInt('modal', 0);
        if (!empty($modal))
        {
            $formData = $app->input->get('jform', array(), 'array');
            $event = $this->loadFromForm($formData);
        }
        else
        {
            $event = $this->loadFromDB();
        }
        if (!empty($event))
        {
            $event->params = JFactory::getApplication()->getParams();
            $this->setAccess($event);
            $this->setEnvironmentVars($event);
        }
        return $event;
    }

    /**
     * Loads event information from the database if available
     *
     * @return  mixed  array on success, otherwise null
     */
    private function loadFromDB()
    {
        $eventID = JFactory::getApplication()->input->getInt('id', 0);
        if (empty($eventID))
        {
            return null;
        }

        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $select = "e.id AS id, c.title AS title, c.created_by, u.name AS author, c.introtext, c.fulltext, c.access";
        $query->select($select);
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->innerJoin("#__users AS u ON c.created_by = u.id");
        $query->innerJoin("#__categories AS cat ON e.categoryID = cat.id");
        $query->where("e.id = '$eventID'");
        $dbo->setQuery((string) $query);

        try
        {
            return $dbo->loadObject();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Loads event data from form preview function
     *
     * @param   array $formData  the data from the form
     *
     * @return  object
     */
    private function loadFromForm($formData)
    {
        return new stdClass;
    }

    /**
     * Sets access parameters for the event based on content handling
     *
     * @param   object  $event  the event object
     */
    private function setAccess(&$event)
    {
        $canCreate = THM_OrganizerHelperAccess::canCreateEvents();
        $event->params->set('access-create', $canCreate);

        $canEdit = THM_OrganizerHelperAccess::canEditEvent($event->id, $event->created_by);
        $event->params->set('access-edit', $canEdit);
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
        $menuID = $app->input->getInt('Itemid', 0);
        if (empty($menuID))
        {
            $event->isManager = false;
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
        }
        catch (Exception $exc)
        {
            $app->enqueueMessage($exc->getMessage(), 'error');
            $event->isManager = false;
        }
    }
}
