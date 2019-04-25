<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/views/edit.php';

/**
 * Class loads the schedule upload form into display context.
 */
class THM_OrganizerViewSchedule_Edit extends THM_OrganizerViewEdit
{
    /**
     * creates the joomla adminstrative toolbar
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::title(\JText::_('THM_ORGANIZER_SCHEDULE_EDIT_TITLE'), 'organizer_schedules');
        \JToolbarHelper::custom('schedule.upload', 'upload', 'upload', 'THM_ORGANIZER_ACTION_UPLOAD', false);
        \JToolbarHelper::cancel('schedule.cancel');
    }
}
