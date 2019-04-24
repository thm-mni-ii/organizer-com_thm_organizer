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

require_once 'form.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/plan_programs.php';

/**
 * Class loads a form for merging plan (degree) program / organizational grouping data.
 */
class THM_OrganizerModelPlan_Program_Merge extends THM_OrganizerModelForm
{
    /**
     * Checks for user authorization to access the view
     *
     * @return bool  true if the user can access the view, otherwise false
     */
    protected function allowEdit()
    {
        return THM_OrganizerHelperAccess::isAdmin();
    }
}
