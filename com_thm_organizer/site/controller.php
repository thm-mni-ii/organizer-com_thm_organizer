<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2011 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Site main controller
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerController extends JControllerLegacy
{
    /**
     * Method to display
     *
     * @param   string  $cachable   (Default: false)
     * @param   string  $urlparams  (Default: false)
     *
     * @return    void
     */
    public function display($cachable = false, $urlparams = false)
    {
        parent::display($cachable, $urlparams);
    }
}
