<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerControllerEvent
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') OR die;
jimport('joomla.application.component.controller');

/**
 * Performs access checks and user actions for events and associated resources
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class THM_OrganizerControllerConsumption extends JControllerAdmin
{
    /**
     * edit
     *
     * performs access checks for the current user against the id of the event
     * to be edited, or content (event) creation access if id is missing or 0
     *
     * @return void
     */
    public function getConsumption()
    {
        // Do security checks here
        $scheduleID = $this->input->getInt('activated', 0);

        $url = "index.php?option=com_thm_organizer&view=consumption";
        $url .= "&activated=" . $scheduleID;
        $this->setRedirect(JRoute::_($url, false));
    }
}
