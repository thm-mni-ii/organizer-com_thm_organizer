<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerControllerEvent
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') OR die;
require_once JPATH_COMPONENT . "/assets/classes/eventAccess.php";
require_once JPATH_SITE . '/components/com_thm_organizer/helpers/access.php';

/**
 * Performs access checks and user actions for events and associated resources
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THM_OrganizerControllerEvent extends JControllerLegacy
{
    /**
     * Performs access checks and calls the save function of the event model. Redirects to the event details view of the
     * created event upon success, or returns to the event edit view on failure.
     *
     * @return void
     */
    public function save()
    {
        $input = JFactory::getApplication()->input;
        $event = $input->get('jform', array(), 'array');

        if (empty($event['id']))
        {
            $canSave = THM_OrganizerHelperAccess::canSaveEvent($event['categoryID']);
        }
        else
        {
            $canSave = THM_OrganizerHelperAccess::canEditEvent($event['id'], $event['created_by']);
        }

        if ($canSave)
        {

            $model = $this->getModel('event');
            $model->save($event);

            $menuID = $input->getInt('Itemid', 0);
            $menuParam = empty($menuID)? '' : "&Itemid=$menuID";

            if (empty($event['id']))
            {
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
                $link = JRoute::_("index.php?option=com_thm_organizer&view=event_edit$menuParam", false);
                $this->setRedirect($link, $msg, 'error');
            }
            else
            {
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
                $link = JRoute::_("index.php?option=com_thm_organizer&view=event_details&eventID={$event['id']}$menuParam", false);
                $this->setRedirect($link, $msg);
            }
        }
        else
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS'), 'error');
        }
    }

    /**
     * delete
     *
     * performs access checks calls the delete function of the event model for
     * one or multiple eventsted items
     *
     * @return void
     */
    public function delete()
    {
        $eventID = JRequest::getInt('eventID');
        $eventIDs = JRequest::getVar('eventIDs');
        $menuID = JRequest::getVar('Itemid');
        $success = false;
        $model = $this->getModel('event');
        if (isset($eventID) && $eventID != 0)
        {
            (THMEventAccess::canDelete($eventID))?
                $success = $model->delete($eventID) : THMEventAccess::noAccess();
        }
        elseif (isset($eventIDs) and count($eventIDs))
        {
            foreach ($eventIDs as $id)
            {
                if (THMEventAccess::canDelete($id))
                {
                    $success = $model->delete($id);
                }
                else
                {
                    THMEventAccess::noAccess();
                }
            }
        }
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_EVENT_DELETED');
            $link = JRoute::_("index.php?option=com_thm_organizer&view=event_manager&Itemid=$menuID", false);
            $this->setRedirect($link, $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_EVENT_DELETE_FAILED');
            $link = JRoute::_("index.php?option=com_thm_organizer&view=event_manager&Itemid=$menuID", false);
            $this->setRedirect($link, $msg, 'error');
        }
    }
}
