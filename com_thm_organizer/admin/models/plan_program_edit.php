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

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/plan_programs.php';
require_once 'edit.php';

/**
 * Class loads a form for editing plan (degree) program / organizational grouping data.
 */
class THM_OrganizerModelPlan_Program_Edit extends THM_OrganizerModelEdit
{
    protected $deptResource = 'program';

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

        return THM_OrganizerHelperPlan_Programs::allowEdit([$this->item->id]);
    }
}
