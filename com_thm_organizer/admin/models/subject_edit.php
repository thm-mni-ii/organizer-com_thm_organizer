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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/subjects.php';
require_once 'edit.php';

/**
 * Class loads a form for editing subject data.
 */
class THM_OrganizerModelSubject_Edit extends EditModel
{
    /**
     * Checks for user authorization to access the view.
     *
     * @return bool  true if the user can access the view, otherwise false
     */
    protected function allowEdit()
    {
        $subjectID = (isset($this->item->id) and !empty($this->item->id)) ? $this->item->id : 0;

        return Subjects::allowEdit($subjectID);
    }
}
