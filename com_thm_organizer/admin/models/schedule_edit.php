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
require_once JPATH_ROOT . '/media/com_thm_organizer/models/edit.php';

/**
 * Class loads a form for uploading schedule data.
 */
class THM_OrganizerModelSchedule_Edit extends THM_OrganizerModelEdit
{
    /**
     * Checks for user authorization to access the view.
     *
     * @return bool  true if the user can access the edit view, otherwise false
     * @throws Exception
     */
    public function allowEdit()
    {
        return THM_OrganizerHelperAccess::allowSchedulingAccess();
    }
}
