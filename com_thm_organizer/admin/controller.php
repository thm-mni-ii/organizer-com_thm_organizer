<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerController
 * @description main controller class for thm organizer admin area
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class for non-specific component calls
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerController extends JControllerLegacy
{
    /**
     * Typical view method for MVC based architecture
     *
     * This function is provide as a default implementation, in most cases
     * you will need to override it in your own controllers.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return  JControllerAdmin  A JControllerAdmin object to support chaining.
     */
    public function display($cachable = false, $urlparams = false)
    {
        thm_organizerHelper::addSubmenu(Factory::getApplication()->input->get('view', null, 'CMD'));
        parent::display($cachable, $urlparams);
    }
}
