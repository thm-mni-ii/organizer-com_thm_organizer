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
     * Performs access checks and redirects to the color edit view
     *
     * @return void
     */
    public function add()
    {
        $input = JFactory::getApplication()->input;
        $input->set('view', 'event_edit');
        $input->set('id', 0);
        parent::display();
    }

    /**
     * Performs access checks and redirects to the color edit view
     *
     * @return  void
     */
    public function edit()
    {
        $input = JFactory::getApplication()->input;
        $input->set('view', 'event_edit');

        $cids = $input->get('cid', array(), 'array');
        $eventID = count($cids)? $cids[0] : 0;
        $input->set('id', $eventID);
        parent::display();
    }

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
            $canSave = THM_OrganizerHelperAccess::canCreateEvent($event['categoryID']);
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
                $link = JRoute::_("index.php?option=com_thm_organizer&view=event_details&id={$event['id']}$menuParam", false);
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
        $model = $this->getModel('event');
        $app = JFactory::getApplication();
        $menuID = $app->input->getInt('Itemid', 0);
        $menuParam = empty($menuID)? '' : "&Itemid=$menuID";
        $redirect = "index.php?option=com_thm_organizer&view=event_manager$menuParam";

        $eventIDs = $app->input->get('cid', array(), 'array');
        foreach ($eventIDs as $eventID)
        {
            $canDelete = THM_OrganizerHelperAccess::canDeleteEvent($eventID);
            if (!$canDelete)
            {
                $app->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_ACTION', 'error');
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
                $link = JRoute::_($redirect, false);
                $this->setRedirect($link, $msg, 'error');
                return;
            }
        }

        $successes = 0;
        foreach ($eventIDs as $eventID)
        {
            $deleted = $model->delete($eventID);
            if ($deleted)
            {
                $successes++;
            }
        }

        if ($successes == count($eventIDs))
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
            $link = JRoute::_($redirect, false);
            $this->setRedirect($link, $msg);
        }
        elseif ($successes > 0)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_PARTIAL');
            $link = JRoute::_($redirect, false);
            $this->setRedirect($link, $msg, 'notice');
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAILED');
            $link = JRoute::_($redirect, false);
            $this->setRedirect($link, $msg, 'error');
        }
    }
}
