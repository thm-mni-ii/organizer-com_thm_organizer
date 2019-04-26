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
 * Class loads a form for editing (degree) program data.
 */
class THM_OrganizerModelProgram_Edit extends THM_OrganizerModelEdit
{
    public $children = null;

    /**
     * Checks for user authorization to access the view.
     *
     * @return bool  true if the user can access the edit view, otherwise false
     */
    public function allowEdit()
    {
        $programID = (isset($this->item->id) and !empty($this->item->id)) ? $this->item->id : 0;
        if (empty($programID) or !Access::checkAssetInitialization('program', $programID)) {
            return Access::allowDocumentAccess();
        }

        return Access::allowDocumentAccess('program', $programID);
    }
}
