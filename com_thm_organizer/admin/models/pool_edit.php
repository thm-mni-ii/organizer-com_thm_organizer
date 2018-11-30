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
 * Class loads a form for editing (subject) pool data.
 */
class THM_OrganizerModelPool_Edit extends THM_OrganizerModelEdit
{
    /**
     * Checks for user authorization to access the view.
     *
     * @return bool  true if the user can access the edit view, otherwise false
     */
    public function allowEdit()
    {
        $poolID = (isset($this->item->id) and !empty($this->item->id)) ? $this->item->id : 0;
        if (empty($poolID) or !THM_OrganizerHelperAccess::checkAssetInitialization('pool', $poolID)) {
            return THM_OrganizerHelperAccess::allowDocumentAccess();
        }

        return THM_OrganizerHelperAccess::allowDocumentAccess('pool', $poolID);
    }
}
