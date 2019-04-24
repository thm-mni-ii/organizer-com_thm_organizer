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
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/plan_pools.php';

/**
 * Class loads a form for editing plan (subject) pool data.
 */
class THM_OrganizerModelPlan_Pool_Edit extends THM_OrganizerModelEdit
{
    /**
     * Checks for user authorization to access the view.
     *
     * @return bool  true if the user can access the edit view, otherwise false
     */
    public function allowEdit()
    {
        if (empty($this->item->id)) {
            return false;
        }

        return THM_OrganizerHelperPlan_Pools::allowEdit([$this->item->id]);
    }
}
