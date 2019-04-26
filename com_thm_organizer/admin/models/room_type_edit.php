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

require_once 'edit.php';

/**
 * Class loads a form for editing room type data.
 */
class THM_OrganizerModelRoom_Type_Edit extends THM_OrganizerModelEdit
{
    /**
     * Checks for user authorization to access the view.
     *
     * @return bool  true if the user can access the view, otherwise false
     */
    protected function allowEdit()
    {
        return Access::allowFMAccess();
    }
}
