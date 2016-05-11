<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewschedule_edit
 * @description html view for schedule upload and editing
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;
jimport('thm_core.edit.view');

/**
 * Class loading schedule data into output context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.thm.de
 */
class THM_OrganizerViewSchedule_Edit extends THM_CoreViewEdit
{
    /**
     * loads persistent data into view context and intitiates functions for the
     * creation of html elements
     *
     * @param   object  $tpl  the template object
     *
     * @return void
     */
    public function display($tpl = null)
    {
        parent::display($tpl);
    }

    /**
     * creates the joomla adminstrative toolbar
     *
     * @return void
     */
    protected function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER_SCHEDULE_EDIT_NEW_VIEW_TITLE');
        JToolbarHelper::title($title, "organizer_schedules");
        JToolbarHelper::custom('schedule.upload', 'upload', 'upload', 'COM_THM_ORGANIZER_ACTION_UPLOAD', false);
        JToolbarHelper::cancel('schedule.cancel');
    }
}
