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
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/form.php';

/**
 * Class loads a form for merging room type data.
 */
class THM_OrganizerModelRoom_Type_Merge extends THM_OrganizerModelForm
{
    /**
     * Checks for user authorization to access the view
     *
     * @return bool  true if the user can access the view, otherwise false
     * @throws Exception
     */
    protected function allowEdit()
    {
        $user = JFactory::getUser();
        return ($user->authorise('core.admin', 'com_thm_organizer') or $user->authorise('organizer.fm', 'com_thm_organizer'));
    }
}
